# Render Production Runtime Readiness

## 目标

本文件记录 Render production runtime 的使用方式和上线前检查。当前阶段只新增部署 runtime 文件，不直接部署，不连接真实数据库，不写入生产数据。

## 本 PR 做了什么

- 新增 `Dockerfile.render`，用于 Render Docker Web Service。
- 新增 `docker/render/nginx.conf.template`，用于 Nginx 生产配置。
- 新增 `docker/render/php-production.ini`，用于 PHP / OPcache 生产配置。
- 新增 `docker/render/start.sh`，用于 Render 容器启动。
- 更新 `.dockerignore`，避免把本地依赖、`.env`、`.local`、备份和敏感文件放入 Docker build context。
- 更新 `.gitattributes`，固定 Render runtime 文件使用 LF 行尾，避免 Linux 容器启动脚本出现 Windows 行尾问题。
- 保留现有本地 Docker baseline，不修改 `Dockerfile` 和 `docker-compose.yml`。
- Docker build 中对 `tastyigniter/*` Composer 包使用 source 安装，避免 TastyIgniter Composer dist zip 校验和不一致导致生产构建失败。
- Docker build 不复制本地 `bootstrap/cache/*.php`，避免把开发缓存带入生产镜像。

## 本地 Build 验证结果

验证日期：2026-07-08

已执行：

```bash
docker build -f Dockerfile.render -t tastyigniter-render-test .
```

结果：

- Docker build 已成功。
- Laravel Mix 生产构建已成功执行 `npm run prod`。
- Composer 生产安装已成功执行 `composer install --no-dev --optimize-autoloader`。
- PHP 扩展检查通过，已确认 `bcmath`、`curl`、`exif`、`gd`、`intl`、`mbstring`、`pdo_mysql`、`zip` 和 `Zend OPcache` 存在。
- Nginx template 使用 `PORT=10000` 渲染后，`nginx -t` 语法检查通过。
- 镜像内已确认没有 `/var/www/html/.env` 或 `/var/www/html/.local`。

构建过程中发现并已处理的问题：

- TastyIgniter Composer dist zip 曾出现 checksum mismatch，因此 `Dockerfile.render` 对 `tastyigniter/*` 包使用 source 安装。
- 本地开发缓存 `bootstrap/cache/*.php` 曾引用 dev-only provider，因此 `.dockerignore` 排除该目录下的缓存文件，并在 Docker build 中清理缓存。

未执行：

- 未连接真实数据库。
- 未运行迁移。
- 未运行 seed。
- 未运行 `php artisan igniter:install`。
- 未提交订单。
- 未提交预约。
- 未测试真实支付。

## Render composer.lock 兼容修复

记录日期：2026-07-08

Render staging 已确认使用 `Dockerfile.render`，但 build 曾失败在：

```text
COPY composer.json composer.lock ./
/composer.lock: not found
```

原因：

- Render build 使用了正确的 `Dockerfile.render`。
- GitHub 仓库当前没有可供 Docker build 复制的 `composer.lock`。
- Dockerfile 原写法强制要求 `composer.lock` 存在，导致 build 在 Composer install 前就失败。

修复：

- `Dockerfile.render` 已改为 `COPY composer.* ./`。
- 如果以后仓库包含 `composer.lock`，Docker 会一起复制。
- 如果仓库暂时只有 `composer.json`，Docker build 不会在 COPY 阶段失败。
- 后续 `composer install` 逻辑保持不变。

注意：

- 没有 `composer.lock` 时，Composer install 会根据 `composer.json` 解析依赖版本，生产可重复性不如锁文件稳定。
- 后续如果项目决定提交 `composer.lock`，Render build 会自动使用它。
- 本次修复仍未表示 Render staging 已完整部署成功，还需要继续观察下一次 Render build / deploy 日志。

## Render Web Service 设置

在 Render 创建 Web Service 时：

| 设置项 | 建议值 |
| --- | --- |
| Runtime / Language | Docker |
| Dockerfile Path | `Dockerfile.render` |
| Persistent Disk Mount Path | `/var/www/html/storage` |
| Health Check Path | `/healthz` |
| Database | 外部托管 MySQL / MariaDB |

不要选择 Render PostgreSQL 作为第一版生产数据库。不要在 Render Persistent Disk 上自托管 MySQL / MariaDB。

## Persistent Disk

Render Persistent Disk 计划挂载到：

```text
/var/www/html/storage
```

启动脚本会确保以下目录存在：

- `/var/www/html/storage/app`
- `/var/www/html/storage/app/public`
- `/var/www/html/storage/app/media`
- `/var/www/html/storage/framework`
- `/var/www/html/storage/framework/cache`
- `/var/www/html/storage/framework/sessions`
- `/var/www/html/storage/framework/views`
- `/var/www/html/storage/logs`
- `/var/www/html/bootstrap/cache`

启动脚本会安全处理：

- `public/storage -> storage/app/public`
- `public/media -> storage/app/media`

安全规则：

- 如果 symlink 已正确存在，则跳过。
- 如果目标路径不存在，则创建目录。
- 如果 `public/media` 不存在，则创建 symlink。
- 如果 `public/media` 是空目录，则替换为 symlink。
- 如果 `public/media` 是非空目录，则停止启动并输出错误，避免误删真实上传文件。

## 必需 Environment Variables

只列 key，不写真实值。

### 应用

- `APP_NAME`
- `APP_ENV`
- `APP_KEY`
- `APP_DEBUG`
- `APP_URL`
- `APP_TIMEZONE`
- `LOG_CHANNEL`
- `LOG_LEVEL`

### Render / Runtime

- `PORT`
- `RUN_CONFIG_CACHE`
- `RUN_ROUTE_CACHE`
- `RUN_VIEW_CACHE`

建议：

- `RUN_CONFIG_CACHE=false`
- `RUN_ROUTE_CACHE=false`，确认 TastyIgniter route cache 兼容后再启用。
- `RUN_VIEW_CACHE=false`，确认主题和扩展兼容后再启用。

staging 首次部署建议先保持 `RUN_CONFIG_CACHE=false`。外部 MySQL / MariaDB、Render Environment Variables、安装状态和动态页面都确认正常后，再单独评估是否改为 `true`。

### 外部 MySQL / MariaDB

- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `DB_PREFIX`
- `DB_CONNECT_TIMEOUT`

建议第一版：

- `DB_CONNECTION=mysql`
- `DB_PORT=3306`
- `DB_CONNECT_TIMEOUT=5`

`DB_CONNECT_TIMEOUT` 用于限制 PHP 连接 MySQL / MariaDB 的等待时间。Render 上如果 `DB_HOST`、端口、防火墙、用户名或密码配置错误，动态页面可能一直等待数据库连接，最终由 Render 返回 504。设置较短超时可以更快暴露数据库连接问题，便于排查；它不能替代正确的外部 MySQL / MariaDB 配置。

## Render staging 动态页面 504 排查记录

记录日期：2026-07-08

已测试站点：

```text
https://le-chateau-des-enfants.onrender.com
```

检查结果：

- 静态资源 `https://le-chateau-des-enfants.onrender.com/favicon.svg` 返回 200。
- 首页 `/` 在约 60 秒后返回 504。
- 后台登录页 `/admin/login` 在约 60 秒后返回 504。
- 菜单页 `/default/menus` 在 20 秒内没有返回首字节。
- DNS、HTTPS、Cloudflare、Render 入口和 Nginx 静态资源服务初步正常。

判断：

- 问题不在域名解析或 TLS。
- 问题也不像是 Nginx 完全没有启动，因为静态文件可以返回。
- 动态页面卡住，最可能原因是 Laravel / TastyIgniter 在请求期间等待外部 MySQL / MariaDB 连接。
- 如果 Render Environment Variables 中的数据库配置缺失、错误，或数据库防火墙不允许 Render 访问，就会出现这种现象。

已做的低风险修复：

- `config/database.php` 的 MySQL 连接新增 `DB_CONNECT_TIMEOUT` 支持。
- 默认连接超时为 5 秒。
- Render 启动脚本中 `RUN_CONFIG_CACHE` 的默认值改为 `false`，避免 staging 数据库未确认时卡在 `php artisan package:discover` / `config:cache`。
- 生产 PHP 配置中 `default_socket_timeout` 设置为 10 秒，作为外部服务网络等待的辅助保护。
- Nginx 新增 `/healthz` 静态健康检查端点，返回 `200 ok`，不进入 Laravel。
- Nginx 对根路径 `HEAD /` 健康探测直接返回 200，避免 Render 默认探测打到 Laravel 动态首页并占满 PHP-FPM worker。
- 该修复不写入数据库，不修改订单、支付、预约、认证或安全逻辑。
- 本地黑洞型数据库地址模拟中，静态资源可返回 200，但动态请求仍可能超过 20 秒；因此根本修复仍是正确配置 Render 的外部 MySQL / MariaDB 连接和数据库防火墙。

仍需在 Render 确认：

- `DB_CONNECTION=mysql`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `DB_PREFIX`
- 外部 MySQL / MariaDB 是否允许 Render Web Service 的出站连接。
- 数据库是否已经完成 TastyIgniter 所需安装或迁移。

### Cache / Session / Queue

- `CACHE_DRIVER`
- `SESSION_DRIVER`
- `QUEUE_CONNECTION`

第一版可以从低复杂度开始：

- `CACHE_DRIVER=file`
- `SESSION_DRIVER=file`
- `QUEUE_CONNECTION=sync`

如果后续需要多实例、队列或更高性能，再评估 Redis / queue worker。

### 邮件

- `MAIL_MAILER`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_ENCRYPTION`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`

### 支付

支付方式未决定前不要配置真实生产密钥。后续以实际 payment extension 文档为准。

可能需要的 key：

- `STRIPE_KEY`
- `STRIPE_SECRET`
- `PAYPAL_CLIENT_ID`
- `PAYPAL_SECRET`
- `SQUARE_ACCESS_TOKEN`
- `SQUARE_LOCATION_ID`

### TastyIgniter / Marketplace

- `IGNITER_CARTE_KEY`

Carté Key 只在需要 Marketplace / 翻译导入时配置，不进入 GitHub。

## 禁止自动运行的命令

生产启动脚本不得自动运行：

- `php artisan migrate:fresh`
- `php artisan migrate:refresh`
- `php artisan db:seed`
- `php artisan igniter:install`
- 任何会清空、重建或写入 demo 数据的命令

生产启动脚本不得自动：

- 覆盖 `APP_KEY`
- 删除 `storage`
- 删除 `public/media`
- 删除上传文件
- 修改真实数据库

启动脚本会运行 `php artisan package:discover --ansi` 和 `php artisan config:cache`，这只刷新 Laravel package / config cache，不执行数据库初始化、迁移或 seed。

数据库迁移必须在备份完成后人工确认。

## 首次 Staging 部署步骤

1. 在 Render 创建 Docker Web Service。
2. Dockerfile Path 填 `Dockerfile.render`。
3. 配置外部托管 MySQL / MariaDB 的 staging 数据库。
4. 配置 Render Persistent Disk，mount path 为 `/var/www/html/storage`。
5. 设置 Render Environment Variables。
6. 部署 staging。
7. 查看 Render logs，确认 Nginx 和 PHP-FPM 启动。
8. 首次 staging 可在确认是空库后人工执行安装或迁移。
9. 上传一张非敏感测试图片。
10. 重启服务，确认图片仍存在。
11. 验证首页、后台登录、菜单、购物车、checkout 表单和预约页。

## 验证 storage / media 持久化

Staging 环境验证步骤：

1. 上传非敏感测试图片。
2. 记录图片在后台和前台是否可见。
3. 重启 Render service。
4. 再次打开图片。
5. 如果图片仍可见，说明 Persistent Disk 和 symlink 初步可用。

如果图片丢失：

- 检查 Persistent Disk mount path 是否为 `/var/www/html/storage`。
- 检查 `public/media` 是否正确指向 `storage/app/media`。
- 检查启动日志中是否有 symlink 保护错误。

## 验证 Nginx / PHP-FPM / OPcache

需要确认：

- Render logs 显示 Nginx 启动成功。
- Render logs 显示 PHP-FPM 启动成功。
- Web Service 监听 Render `$PORT`。
- 首页返回 200。
- 后台登录页返回 200。
- PHP 错误不直接显示到浏览器。
- OPcache 已启用。

不要通过公开页面输出 `phpinfo()`。如果需要检查 OPcache，应使用安全的临时 one-off command 或后台安全方式，并避免暴露到公网。

## 前台功能验证

Staging 至少验证：

- 首页。
- 后台登录页。
- 菜单页。
- 购物车页。
- checkout 表单入口。
- 预约页。
- `fr_CA` 默认语言。
- `en_CA` 语言切换。
- 移动端显示。

限制：

- 不提交真实订单。
- 不提交真实预约。
- 不测试真实支付。
- 不使用真实顾客信息。

## Cloudflare custom domain 后续步骤

1. 在 Render Web Service 中添加 custom domain。
2. 复制 Render 提供的 DNS 目标。
3. 到 Cloudflare DNS 添加对应记录。
4. 等待 DNS 生效。
5. 确认 Render TLS certificate 正常。
6. 更新 Render `APP_URL` 为正式 HTTPS 域名。
7. 检查前台链接、邮件链接和后台登录。

不要把 Cloudflare API token 写入 GitHub、文档、聊天或 `.env` 文件。

## 当前仍未上线

当前 runtime 文件只为后续 staging / production 部署做准备。

现在仍不要：

- 录入大量真实菜单。
- 上传真实菜品图片。
- 配置真实支付。
- 配置真实邮件密码。
- 配置生产 Carté Key。
- 提交真实顾客信息。
- 提交真实订单或预约。

## 下一步

1. 审查本 PR。
2. 在 Render 创建 staging Web Service。
3. 配置外部 MySQL / MariaDB。
4. 配置 Persistent Disk。
5. 配置 Render Environment Variables。
6. 验证 staging。
7. 再决定是否准备 production。
