# Render 部署方案设计

## 目标

本文件用于规划将当前 TastyIgniter fork 部署到 Render 的安全方案。当前阶段只做方案设计，不直接部署，不修改生产数据，不提交任何密钥。

本方案重点保护：

- 生产数据库。
- `storage`。
- uploads / media。
- 菜品图片。
- Render Environment Variables。
- `.env`。
- Carté Key。
- 邮件和支付密钥。
- 真实顾客、订单和预约数据。

参考的 Render 官方文档：

- [Docker on Render](https://render.com/docs/docker)
- [Web Services](https://render.com/docs/web-services)
- [Persistent Disks](https://render.com/docs/disks)
- [Environment Variables and Secrets](https://render.com/docs/configure-environment-variables)
- [Deploy a PHP Web App with Laravel and Docker](https://render.com/docs/deploy-php-laravel-docker)

## 当前项目是否适合 Render Docker Web Service

当前项目适合走 Render Docker Web Service 路线，但当前仓库里的 Docker 配置仍是“本地开发基线”，不应直接当作生产部署方案。

适合 Render Docker Web Service 的原因：

- 项目已经有 `Dockerfile`。
- TastyIgniter / Laravel 是 PHP 应用，Render 官方建议 PHP 等非原生 runtime 场景可以使用 Docker。
- Render Web Service 支持从 GitHub repo 的 Dockerfile 构建镜像。
- Render Web Service 支持自定义域名、HTTPS、Environment Variables 和 Persistent Disk。

当前 Dockerfile 不适合作为最终生产方案的原因：

- 当前基础镜像是 `php:8.3-cli-bookworm`。
- 当前启动命令是 `php artisan serve --host=0.0.0.0 --port=8000`，适合本地开发，不适合生产。
- 当前没有 Nginx。
- 当前没有 PHP-FPM。
- 当前没有明确 OPcache 生产配置。
- 当前没有生产启动脚本来执行安全的 cache、symlink、权限检查。
- 当前没有使用 Render 的 `$PORT`，Render Web Service 推荐绑定 `0.0.0.0:$PORT`，默认 `$PORT` 通常是 `10000`。

结论：

- 第一版推荐使用 Render Docker Web Service。
- 需要后续单独做一个“生产 Docker PR”，准备 Nginx + PHP-FPM + OPcache + Laravel cache + Render `$PORT` 支持。
- 本 PR 只记录方案，不修改 Dockerfile。

## Render 原生 PostgreSQL 是否适合本项目

不建议第一版直接使用 Render 原生 PostgreSQL。

原因：

- 当前本地环境和项目配置使用 `mysql` / MariaDB。
- 当前 Dockerfile 只安装了 `pdo_mysql`，没有安装 `pdo_pgsql`。
- TastyIgniter 虽然基于 Laravel，但 TastyIgniter core、扩展、迁移和查询是否完全兼容 PostgreSQL 需要额外验证。
- 项目此前已围绕 MySQL / MariaDB 做过本地 Docker 基线和第一批配置验证。
- 为了降低部署风险，第一版不要同时更换部署平台和数据库类型。

推荐方案：

- Render Web Service 跑应用。
- 数据库使用外部托管 MySQL / MariaDB。
- 可选供应商包括托管 MySQL / MariaDB 服务，例如 AWS RDS、DigitalOcean Managed MySQL、Aiven for MySQL、PlanetScale 或其他可靠 MySQL 服务。
- 数据库必须开启自动备份。
- 数据库连接信息只放在 Render Environment Variables 中，不写入 GitHub。

不推荐的方案：

- 不建议把 MySQL / MariaDB 自己跑在 Render Persistent Disk 上作为生产数据库。
- Render 文档说明 Persistent Disk 可用于自定义 datastore，但数据库恢复应靠数据库备份工具，而不是直接恢复磁盘快照。生产第一版更适合使用专业托管 MySQL / MariaDB。

## storage / uploads / media 持久化方案

Render 服务默认文件系统是临时的。没有 Persistent Disk 时，服务重启或重新部署后，本地文件变更会丢失。因此，菜品图片、上传文件和运行时 `storage` 必须持久化。

当前项目相关路径：

- Laravel storage：`storage`
- Laravel public storage link：`public/storage -> storage/app/public`
- TastyIgniter media disk：`public/media`
- 日志：`storage/logs`
- 缓存 / session / views：`storage/framework`

推荐第一版 Render Persistent Disk 设计：

- Disk mount path：`/var/www/html/storage`
- 因为当前 Dockerfile 的 `WORKDIR` 是 `/var/www/html`，Render Docker disk mount path 应使用容器内绝对路径。
- `storage` 目录整体放到 Persistent Disk。
- 在生产启动脚本中确保以下目录存在：
  - `/var/www/html/storage/app`
  - `/var/www/html/storage/app/public`
  - `/var/www/html/storage/app/media`
  - `/var/www/html/storage/framework/cache`
  - `/var/www/html/storage/framework/sessions`
  - `/var/www/html/storage/framework/views`
  - `/var/www/html/storage/logs`
- 在生产启动脚本中运行或确认：
  - `php artisan storage:link`
  - `public/media` 指向磁盘内的 media 目录，例如 `/var/www/html/storage/app/media`

为什么需要特别处理 `public/media`：

- 当前 `config/filesystems.php` 中 `media` disk 的 root 是 `public_path('media')`。
- 如果只挂载 `/var/www/html/storage`，`public/media` 默认仍在临时文件系统中，菜品图片和媒体文件可能在重启后丢失。
- 后续生产 Docker PR 应通过安全方式把 `public/media` 变成指向持久磁盘目录的 symlink。
- 不要用会删除已有 `public/media` 内容的脚本。生产上如果目录已存在，必须先备份再迁移。

Render Persistent Disk 注意事项：

- Persistent Disk 只保护 mount path 下的文件。
- Disk 只在运行时可用，build command 和 pre-deploy command 阶段不可访问。
- 使用 Persistent Disk 的服务不能横向扩展到多个实例。
- 使用 Persistent Disk 会影响 zero-downtime deploy，部署时可能有短暂停机。
- Disk 有自动快照，但上传文件仍应做单独备份。

## Render Environment Variables

生产 `.env` 不进入 GitHub。Render 上使用 Environment Variables 配置生产环境。

以下只列字段名和说明，不写真实值。

### 应用基础配置

| Key | 说明 | 示例值类型 |
| --- | --- | --- |
| `APP_NAME` | 网站名称 | 店铺公开名称 |
| `APP_ENV` | 生产用 `production`，准生产用 `staging` | `production` |
| `APP_KEY` | Laravel app key | 使用安全方式生成 |
| `APP_DEBUG` | 生产必须关闭 | `false` |
| `APP_URL` | Render 或正式域名 HTTPS URL | `https://example.com` |
| `ASSET_URL` | 如需要强制 HTTPS asset URL，可设置 | `https://example.com` |
| `APP_TIMEZONE` | 如果项目使用该变量则设置 | `America/Toronto` |
| `IGNITER_LOCATION_MODE` | 当前项目可保持已有策略 | `multiple` 或后台确认值 |
| `IGNITER_CARTE_KEY` | Carté Key，仅在需要 Marketplace 时配置 | 不写入文档 |

### 数据库配置

| Key | 说明 |
| --- | --- |
| `DB_CONNECTION` | 第一版建议 `mysql` |
| `DB_HOST` | 外部托管 MySQL / MariaDB host |
| `DB_PORT` | 通常 `3306` |
| `DB_DATABASE` | 生产数据库名 |
| `DB_USERNAME` | 生产数据库用户名 |
| `DB_PASSWORD` | 生产数据库密码 |
| `DB_PREFIX` | 如无特殊需要保持空 |

### 缓存 / Session / Queue

| Key | 建议 |
| --- | --- |
| `CACHE_DRIVER` | 第一版可用 `file`；后续可评估 Redis |
| `SESSION_DRIVER` | 第一版可用 `file`；如多实例不可用，但 Persistent Disk 单实例可接受 |
| `QUEUE_CONNECTION` | 第一版可用 `sync`；如邮件 / 任务增加再评估 worker |
| `LOG_CHANNEL` | 可用 `stack` 或按生产日志方案配置 |
| `LOG_LEVEL` | 建议 `warning` 或 `error`，staging 可用 `debug` |

### 邮件配置

| Key | 说明 |
| --- | --- |
| `MAIL_MAILER` | 上线前配置真实邮件服务 |
| `MAIL_HOST` | 邮件服务 host |
| `MAIL_PORT` | 邮件服务 port |
| `MAIL_USERNAME` | 邮件用户名 |
| `MAIL_PASSWORD` | 邮件密码 |
| `MAIL_ENCRYPTION` | `tls` / `ssl` 等 |
| `MAIL_FROM_ADDRESS` | 店铺公开发件地址 |
| `MAIL_FROM_NAME` | 店铺公开名称 |

### 支付配置

支付方式未最终决定前不要配置真实密钥。

可能需要的环境变量：

- `STRIPE_KEY`
- `STRIPE_SECRET`
- `PAYPAL_CLIENT_ID`
- `PAYPAL_SECRET`
- `SQUARE_ACCESS_TOKEN`
- `SQUARE_LOCATION_ID`

具体变量名必须以后续选定的 TastyIgniter payment extension 文档为准。

## Dockerfile / Nginx / PHP-FPM / OPcache / Laravel cache 准备

后续生产 Docker PR 应准备：

- 使用 PHP 8.3+。
- 使用 PHP-FPM，而不是 `php artisan serve`。
- 使用 Nginx 作为前端 HTTP server。
- Nginx 监听 Render `$PORT`，并绑定 `0.0.0.0`。
- PHP 安装生产需要扩展：
  - `pdo_mysql`
  - `gd`
  - `intl`
  - `mbstring`
  - `zip`
  - `bcmath`
  - `curl`
  - `exif`
  - OPcache
- Composer 使用生产模式：
  - `composer install --no-dev --optimize-autoloader`
- Node / npm 用于 build 阶段构建前端资源：
  - `npm ci`
  - `npm run prod` 或项目实际生产构建命令
- 最终镜像不应包含不必要的开发依赖。
- 应新增 `.dockerignore`，避免把 `.env`、`.local`、备份文件、测试浏览器目录和不必要文件放入 Docker build context。
- 应准备生产启动脚本：
  - 检查必要目录。
  - 确保 `storage` 权限。
  - 确保 `public/storage` symlink。
  - 安全处理 `public/media` symlink。
  - 执行 `php artisan config:cache`。
  - 执行 `php artisan route:cache`，如果 TastyIgniter 当前路由兼容。
  - 执行 `php artisan view:cache`，如果当前主题 / 扩展兼容。
  - 启动 Nginx 和 PHP-FPM。

注意：

- 生产 Docker PR 需要单独验证，不要和业务功能混在一个 PR。
- 如果 `route:cache` 或 `view:cache` 与 TastyIgniter 扩展不兼容，应记录并只启用安全缓存。
- 不要在启动脚本中执行会清空数据库的命令。

## 首次部署命令策略

### 可以自动运行的命令

以下命令通常可以在 build 或启动阶段自动运行：

| 命令 | 阶段 | 说明 |
| --- | --- | --- |
| `composer install --no-dev --optimize-autoloader` | build | 安装 PHP 依赖 |
| `npm ci` | build | 安装前端依赖 |
| `npm run prod` 或实际生产构建命令 | build | 构建前端资源 |
| `php artisan config:cache` | runtime start | 缓存配置 |
| `php artisan view:cache` | runtime start | 视图缓存，需先验证兼容 |
| `php artisan storage:link` | runtime start | 创建 public storage link |

### 必须人工确认后才能运行的命令

以下命令可能影响生产数据库或真实数据，必须人工确认：

| 命令 | 风险 | 建议 |
| --- | --- | --- |
| `php artisan migrate --force` | 修改生产数据库结构 | 首次 staging 可运行；production 必须先备份并人工确认 |
| `php artisan igniter:install` | 可能初始化或覆盖安装状态 | 生产禁止自动运行，除非确认是全新空库 |
| `php artisan db:seed` | 可能写入 demo / 测试数据 | 生产禁止自动运行 |
| `php artisan migrate:fresh` | 清空并重建数据库 | 生产绝对禁止 |
| `php artisan migrate:refresh` | 回滚并重跑迁移 | 生产绝对禁止 |
| `php artisan key:generate` | 改变 `APP_KEY` 会影响加密数据 | 生产首次手动生成一次，不要重复覆盖 |
| `php artisan cache:clear` | 一般低风险，但可能短暂影响性能 | 可手动执行，避免频繁自动执行 |

首次部署原则：

- Staging 可以先用空数据库验证。
- Production 不允许自动安装或重建数据库。
- Production 的 `migrate --force` 必须在数据库备份完成后人工触发。

## 如何避免覆盖生产数据库和上传目录

数据库保护：

- 生产数据库必须独立于本地数据库。
- 不要把本地 `docker compose` 数据库导入生产。
- 不要在 production 自动运行 `igniter:install`、`migrate:fresh`、`migrate:refresh` 或 seed。
- 所有数据库结构变更前先备份。
- 真实菜单录入前备份数据库。
- 真实菜单录入后再次备份数据库。

上传目录保护：

- Render Persistent Disk 必须在真实菜单图片上传前配置完成。
- `storage` 和 `public/media` 迁移或 symlink 前必须备份。
- 不要在启动脚本中 `rm -rf storage`、`rm -rf public/media` 或清空上传目录。
- 不要把上传目录放在临时文件系统。
- 真实菜单图片上传后立即备份 Persistent Disk 内容。

配置保护：

- 不提交 `.env`。
- 不提交 `.local`。
- 不提交 Render Environment Variables。
- 不提交 Carté Key。
- 不提交邮件密码。
- 不提交支付密钥。
- 不把生产 `.env` 用本地 `.env.docker.example` 覆盖。

## Staging 验证 Checklist

| 检查项 | 当前状态 | 验收方式 | 备注 |
| --- | --- | --- | --- |
| Render Web Service 创建 | Pending | 服务从 GitHub branch 成功构建 | 使用 Docker runtime |
| Web Service 绑定 `$PORT` | Pending | Render logs 无端口错误 | Nginx 监听 `0.0.0.0:$PORT` |
| 外部 MySQL / MariaDB 连接 | Pending | 应用能连接 staging DB | 不用 production DB |
| Persistent Disk 挂载 | Pending | `storage` 写入文件后重启仍存在 | Disk mount 到 `/var/www/html/storage` |
| `public/storage` link | Pending | 上传或公开文件可访问 | 使用 `php artisan storage:link` |
| `public/media` 持久化 | Pending | 上传 media 后重启仍存在 | 需要 symlink 或等价方案 |
| `APP_DEBUG=false` | Pending | 错误页不泄露堆栈 | staging 可短期开启，但最终应关闭 |
| 首页 | Pending | 返回 200，视觉正常 | 检查语言切换 |
| 菜单页 | Pending | 返回 200，测试商品可见 | 不录入大量真实菜单 |
| 购物车 | Pending | 测试商品可加入、修改、移除 | 不提交真实订单 |
| Checkout 表单 | Pending | 可进入表单 | 不点击最终下单 |
| 预约页 | Pending | 日期、人数、时间选择可见 | 不提交真实预约 |
| 后台登录 | Pending | 店主可登录 | 不在文档记录密码 |
| 图片上传 | Pending | 后台上传测试图片，重启后仍存在 | 使用非敏感测试图片 |
| 邮件 | Pending | 使用测试邮件服务验证 | 不使用真实顾客 |
| 支付 | Pending | 使用沙盒模式验证 | 不配置真实收款密钥 |
| 语言 | Pending | `fr_CA` 默认，`en_CA` 可切换 | Q-001 / Q-005 仍需处理 |
| 移动端 | Pending | 首页、菜单、预约、购物车无明显破版 | 390px 检查 |
| 备份 | Pending | staging DB 和 disk 备份可执行 | 练习恢复流程 |

## 正式上线前 Checklist

| 检查项 | 当前状态 | 验收方式 | 备注 |
| --- | --- | --- | --- |
| Production Render service | Pending | 使用 production branch / tag 部署 | 不直接连 staging DB |
| Production MySQL / MariaDB | Pending | 自动备份开启 | 不使用本地 DB |
| Production Persistent Disk | Pending | `storage` 和 media 持久化验证通过 | 确认磁盘大小 |
| `.env` / Environment Variables | Pending | 人工核对所有必需变量 | 不导出到 GitHub |
| `APP_DEBUG=false` | Pending | 确认关闭 | 上线前必须 |
| `APP_URL` | Pending | 使用正式 HTTPS 域名 | 检查邮件链接 |
| HTTPS / custom domain | Pending | 浏览器证书正常 | Render 提供 TLS |
| 后台登录 | Pending | 店主可登录 | 不记录密码 |
| Carté Key | Pending | 如需 Marketplace 翻译，安全配置 | Q-001 |
| 法语默认 | Pending | `fr_CA` 默认 | 魁北克第一版要求 |
| 英语切换 | Pending | `en_CA` 可切换 | Q-002 已实现 |
| 完整翻译风险 | Pending | 关键页面人工检查 | Q-005 仍 Open |
| 真实菜单录入前备份 | Pending | DB 和 disk 备份完成 | 录入前必须 |
| 真实菜单录入 | Pending | 店主后台录入 | 不在 GitHub 记录真实价格细节 |
| 真实菜单录入后备份 | Pending | DB 和 disk 再备份 | 录入后必须 |
| Pickup 流程 | Pending | 测试订单流程到最终确认前 | 避免真实支付 |
| 支付配置 | Pending | 沙盒验证后再切生产 | 税费 / 小费需确认 |
| 邮件配置 | Pending | 顾客和店员通知可发送 | 不提交密码 |
| 预约流程 | Pending | 生日派对规则确认 | 不改预约冲突逻辑 |
| 生产性能 | Pending | 后台录菜和前台访问可接受 | 如慢再诊断 |
| 备份恢复演练 | Pending | 至少演练一次非生产恢复 | 上线前强烈建议 |

## 当前风险

| 风险 | 严重性 | 说明 | 建议 |
| --- | --- | --- | --- |
| 当前 Dockerfile 不是生产方案 | High | 使用 `php artisan serve`，没有 Nginx / PHP-FPM / OPcache | 先做生产 Docker PR |
| Render Postgres 兼容性未验证 | High | TastyIgniter 当前验证基于 MySQL / MariaDB | 第一版使用外部 MySQL / MariaDB |
| `public/media` 不在 `storage` 下 | High | 只挂载 `storage` 不能自动保护 `public/media` | 生产启动脚本处理 media symlink |
| Persistent Disk 限制 | Medium | 单实例、部署短暂停机、build / pre-deploy 不可访问 disk | 第一版可接受，需记录 |
| 生产数据库被初始化脚本覆盖 | High | 真实菜单、订单、预约会丢失 | 禁止自动 `igniter:install` / fresh / seed |
| 真实菜单图片丢失 | High | 上传目录未持久化会丢失图片 | 上传前验证 disk 和备份 |
| Q-001 | Medium | Carté Key / fr_CA Marketplace 翻译仍 Open | 上线前决定是否配置 |
| Q-005 | Medium | 完整站点翻译仍 Open | 继续补关键页面文案 |
| 邮件未配置 | Medium | 顾客和店员通知不可用 | staging 先测邮件 |
| 支付未配置 | High | 上线收款不可用 | 沙盒验证后再切生产 |

## 推荐部署顺序

1. 先保留当前 `4.x` 稳定功能，不录入大量真实菜单。
2. 创建独立 PR：生产 Docker / Render runtime 准备。
   - Nginx。
   - PHP-FPM。
   - OPcache。
   - `$PORT`。
   - `.dockerignore`。
   - 安全启动脚本。
   - `storage` / `public/media` 持久化处理。
3. 在 Render 创建 staging Web Service。
4. 给 staging 配置外部 MySQL / MariaDB。
5. 给 staging 配置 Persistent Disk。
6. 在 staging 跑空库安装或迁移验证。
7. 在 staging 上传测试图片，重启后确认图片仍存在。
8. 在 staging 验证首页、菜单、购物车、checkout 表单、预约页、后台登录、语言切换和移动端。
9. 在 staging 验证邮件测试服务和支付沙盒。
10. 准备 production 数据库和 Persistent Disk。
11. 部署 production 前先确认不会自动清空数据库。
12. Production 首次部署后，先只录入少量测试数据验证。
13. 做 production 数据库和 disk 备份。
14. 店主在 production 或准生产后台录入真实菜单。
15. 真实菜单录入后立即再次备份。
16. 最后再处理正式域名、真实支付、真实邮件和上线公告。

## 本阶段不做的事

- 不直接部署 Render。
- 不修改 Dockerfile。
- 不新增 Render secret。
- 不提交 `.env`。
- 不提交 `.local`。
- 不提交数据库备份。
- 不写入数据库。
- 不修改 TastyIgniter core。
- 不修改 `vendor/`。
- 不修改订单、支付、预约冲突、认证或安全逻辑。
- 不提交密码、密钥、token、Carté Key、真实顾客信息或真实支付信息。
