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

- `RUN_CONFIG_CACHE=true`，Render staging 当前已完成数据库、安装状态、前后台和媒体验证；如部署后出现异常，可在 Render Environment Variables 中临时设为 `false` 回滚。
- `RUN_ROUTE_CACHE=false`，确认 TastyIgniter route cache 兼容后再启用。
- `RUN_VIEW_CACHE=false`，确认主题和扩展兼容后再启用。

staging 首次部署阶段曾建议先保持 `RUN_CONFIG_CACHE=false`。当前外部 MySQL / MariaDB、Render Environment Variables、安装状态、前后台页面、Livewire 和媒体持久化都已通过基础验证，因此可以单独开启 config cache；route cache 和 view cache 仍保持默认关闭。

### 外部 MySQL / MariaDB

- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `DB_PREFIX`
- `DB_CONNECT_TIMEOUT`
- `MYSQL_ATTR_INIT_COMMAND`

建议第一版：

- `DB_CONNECTION=mysql`
- `DB_PORT=3306`
- `DB_CONNECT_TIMEOUT=5`

`DB_CONNECT_TIMEOUT` 用于限制 PHP 连接 MySQL / MariaDB 的等待时间。Render 上如果 `DB_HOST`、端口、防火墙、用户名或密码配置错误，动态页面可能一直等待数据库连接，最终由 Render 返回 504。设置较短超时可以更快暴露数据库连接问题，便于排查；它不能替代正确的外部 MySQL / MariaDB 配置。

`MYSQL_ATTR_INIT_COMMAND` 是可选项。第一版默认不设置，保持 Laravel / MySQL 原行为。如果 staging 使用 DigitalOcean Managed MySQL 且全局 `sql_require_primary_key=ON`，普通数据库用户通常不能执行 `SET GLOBAL`，但可以执行 session 级设置。此时可在 Render Environment Variables 中设置：

```text
MYSQL_ATTR_INIT_COMMAND=SET SESSION sql_require_primary_key = OFF
```

这个变量只影响当前应用创建的 MySQL session，不会硬编码 DigitalOcean，也不会默认关闭 primary key 要求。不要把数据库密码、完整连接字符串或 Render secret 写入 GitHub。

## DigitalOcean Managed MySQL primary key 限制记录

记录日期：2026-07-08

已知 staging 现象：

- `mysql select 1` 已成功，说明 Render 到 DigitalOcean Managed MySQL 的基本连接可用。
- `show tables` 已成功，但 staging 数据库为空。
- 执行 TastyIgniter 初始化时失败：`SQLSTATE[HY000]: General error: 3750 Unable to create or change a table without a primary key, when sql_require_primary_key is set.`
- DigitalOcean Managed MySQL 全局 `sql_require_primary_key=ON`。
- 普通数据库用户不能执行 `SET GLOBAL`。
- 普通数据库用户可以执行 `SET SESSION sql_require_primary_key = OFF`。

处理方式：

- `config/database.php` 已新增 `MYSQL_ATTR_INIT_COMMAND` 支持。
- Render staging 可通过 Environment Variable 设置 session 初始化命令。
- 不在代码中硬编码 DigitalOcean。
- 不默认强制关闭 `sql_require_primary_key`。
- 不修改业务逻辑，不写入数据库。

下一次 staging 初始化前需要确认：

1. Render Environment Variables 已设置 `MYSQL_ATTR_INIT_COMMAND`。
2. Render 已重新部署，使新配置进入容器。
3. 再次确认当前数据库仍是 staging 空库。
4. 再执行 `php artisan igniter:install --force`。
5. 交互时 `Install demo data?` 仍选择 `no`。

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
- Render 启动脚本在 staging 早期曾将 `RUN_CONFIG_CACHE` 的默认值设为 `false`，避免数据库未确认时卡在 `php artisan package:discover` / `config:cache`；当前阶段改为默认 `true`，并保留 `RUN_CONFIG_CACHE=false` 回滚方式。
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

启动脚本默认会运行 `php artisan package:discover --ansi` 和 `php artisan config:cache`，这只刷新 Laravel package / config cache，不执行数据库初始化、迁移或 seed。如 staging 发现异常，可设置 `RUN_CONFIG_CACHE=false` 跳过。

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

## Render staging 数据库连接排查记录

记录日期：2026-07-08

测试地址：

```text
https://le-chateau-des-enfants.onrender.com
```

外部可验证结果：

| 检查项 | 结果 | 说明 |
| --- | --- | --- |
| `/healthz` | Pass | 返回 200 和 `ok`，说明 Render Web Service、Nginx 和健康检查端点正常。 |
| `/favicon.svg` | Pass | 静态资源返回 200。 |
| `/` | Failing | 返回 200，但页面标题为 `Database Error Was Encountered`，首字节约 10 秒。 |
| `/admin/login` | Failing | 返回 200，但页面标题为 `Database Error Was Encountered`，首字节约 10 秒。 |
| `/default/menus` | Failing | 返回 200，但页面标题为 `Database Error Was Encountered`，首字节约 10 秒。 |
| `/cart` | Failing | 返回 200，但页面标题为 `Database Error Was Encountered`，首字节约 10 秒。 |
| `/default/reservation` | Failing | 返回 200，但页面标题为 `Database Error Was Encountered`，首字节约 10 秒。 |

判断：

- 当前已经不是 Docker build、Nginx 启动或 Render health check 问题。
- 当前也不再表现为外部浏览器 504；动态页面已能返回 Laravel / TastyIgniter 的数据库错误页。
- 首字节约 10 秒，符合当前 staging runtime 的数据库连接超时保护表现。
- 主要嫌疑仍是外部 MySQL / MariaDB 连接配置、DigitalOcean Trusted Sources / Firewall、数据库名称或数据库初始化状态。

仍需在 Render / DigitalOcean 后台确认，不要把真实值写入 GitHub：

| 检查项 | 当前状态 | 说明 |
| --- | --- | --- |
| `DB_HOST` 是否为 DigitalOcean Public host | 未确认 | 需要在 Render Environment Variables 中检查，只记录是否正确，不记录真实值。 |
| `DB_PORT` 是否与 DigitalOcean Connection Details 一致 | 未确认 | 不要假设是 3306；DigitalOcean Managed MySQL 常见端口可能不同。 |
| DigitalOcean Trusted Sources 是否允许 Render 连接 | 未确认 | staging 排查阶段可临时允许 `0.0.0.0/0`，排查通过后再收紧。 |
| Render Shell 中 `mysql select 1` 是否成功 | 未执行 | 需要在 Render Shell / Console / one-off command 中执行，不要把密码输出到文档或聊天。 |
| `show tables` 是否为空 | 未执行 | 只有 `select 1` 成功后再检查。 |

下一步：

1. 在 Render Environment Variables 中确认 `DB_*` 配置是否完整且使用 DigitalOcean Public connection。
2. 在 DigitalOcean Managed MySQL 中确认 Trusted Sources / Firewall 允许 Render 连接。
3. 在 Render Shell / Console 中执行 `mysql ... -e "select 1;"`。
4. 如果 `select 1` 成功，再执行 `show tables;` 判断 staging 数据库是否已经初始化。
5. 如果数据库为空，先确认这是 staging 空库，再使用 TastyIgniter 推荐安装流程；不要运行 `migrate:fresh`、`migrate:refresh` 或未经确认的 seed。

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

## Staging 第一阶段性能优化

记录日期：2026-07-09

本阶段目标是先处理低风险 Render runtime 性能问题，不处理业务级或数据库级重构。

已准备的 runtime 调整：

- `_assets` 和 `admin/_assets` 继续保留 Laravel / TastyIgniter combiner fallback。
- 当 combined asset 不存在于磁盘时，进入专用 named FastCGI location。
- combined asset 响应由 Nginx 设置 `Cache-Control: public, max-age=86400`。
- combined asset FastCGI buffer 增大，减少大 CSS / JS 响应写入 Nginx 临时文件。
- `/storage` 和 `/media` 增加 `Cache-Control: public, max-age=604800`。
- `/livewire/` location 不变，继续 fallback 到 Laravel，避免 Livewire JS 404。
- `/healthz` location 不变，继续由 Nginx 静态返回。

本阶段不启用：

- Laravel route cache。
- Laravel config cache 默认值变更。
- Laravel view cache 默认值变更。
- DB 查询优化。
- TastyIgniter boot 优化。
- 主题渲染重构。
- Cloudflare 或 production 配置。

部署到 staging 后必须复查：

| 检查项 | 期望 |
| --- | --- |
| `/healthz` | 200 `ok` |
| 首页、菜单页、购物车、预约页 | 200 |
| 后台登录页、dashboard | 可打开 |
| Livewire JS | 200，仍走正确 Laravel fallback |
| `_assets` / `admin/_assets` | 200，`Cache-Control` 包含 `max-age=86400` |
| `/storage/media/uploads/staging-test-upload.png` | 200，`Cache-Control` 包含 `max-age=604800` |
| Render logs | 无新的 404 / 500 / PHP fatal / Laravel exception / storage permission error |
| upstream buffering warning | 应减少或消失；如仍出现，记录具体路径，不在本阶段扩大范围 |

注意：

- dynamic HTML TTFB 慢的问题预计仍会存在，需要后续单独拆分数据库、TastyIgniter boot、theme rendering 和 Laravel cache 影响。
- 媒体路径使用 7 天缓存，真实内容录入时应避免同名替换文件造成浏览器短期看到旧图。

## Staging 第二阶段性能诊断：动态 HTML TTFB 拆分

记录日期：2026-07-09

本阶段目标是定位动态 HTML 5-8s TTFB 的主要来源，不改变运行行为。

运行时确认：

- Render staging live commit 为 `6cb1e62`，对应 PR #30。
- `APP_DEBUG=false`。
- `CACHE_DRIVER=file`。
- `SESSION_DRIVER=file`。
- `QUEUE_CONNECTION=sync`。
- `RUN_CONFIG_CACHE=false`。
- `RUN_ROUTE_CACHE=false`。
- `RUN_VIEW_CACHE=false`。
- PHP-FPM 下 OPcache 已启用：
  - `Server API => FPM/FastCGI`。
  - `opcache.enable => On`。
  - `opcache.validate_timestamps => Off`。
  - `opcache.memory_consumption => 192`。
  - `opcache.max_accelerated_files => 20000`。
- `bootstrap/cache/config.php` 不存在，说明 config cache 当前未启用。
- route cache 当前未启用。
- `storage/framework/views` 中已有编译视图文件，view 编译不是当前最大嫌疑。

内部请求测量：

| 页面 | 内部 TTFB / total | 查询数 | 查询累计耗时 | 结论 |
| --- | --- | --- | --- | --- |
| `/` | 约 5.05-5.61s | 约 30 | 约 5.52s | DB-bound |
| `/default/menus` | 约 7.28-8.06s | 约 44 | 约 7.20s | DB-bound |
| `/cart` | 约 6.17-6.88s | 约 37 | 约 6.05s | DB-bound |
| `/default/reservation` | 约 6.27-6.83s | 约 37 | 约 6.79s | DB-bound |
| `/admin/login` | 约 2.81-3.11s | 约 15 | 约 2.78s | DB-bound |

数据库基础延迟：

- Laravel bootstrap 后连续 `select 1` 平均约 151ms。
- 当前公开页面的查询数乘以单次远程往返成本，已经足以解释大部分 TTFB。
- 菜单页抽样显示存在 schema / settings / extension / pages 等多次查询；多数约 170ms，首个 schema 检查可超过 600ms。

已排除：

- 当前主要瓶颈不是 OPcache 未启用。
- 当前主要瓶颈不是 Cloudflare 或公网链路；容器内部 HTTP timing 与外部 timing 接近。
- 当前主要瓶颈不是 Nginx buffering；PR #30 后 upstream buffering warning 已消失。
- 当前主要瓶颈不是 Livewire JS 404。
- 当前主要瓶颈不是测试图片或 Persistent Disk。
- 当前主要瓶颈不是静态 `_assets` / `admin/_assets` 缓存头。

缓存策略判断：

- config cache 是下一步最低风险优化候选。建议先用独立 PR 开启或验证，并保留 `RUN_CONFIG_CACHE` fallback。
- view cache 可以继续验证，但当前已有编译视图，预计收益小于 config cache / DB query 减少。
- route cache 风险最高，仍不建议默认启用。TastyIgniter extension / admin routes 可能依赖动态注册。

Dashboard 判断：

- 已登录浏览器下 dashboard 可打开，但体感仍慢。
- CLI 不携带后台 session，访问 `/admin/dashboard` 只得到未登录 302，因此不能用本轮 CLI query profile 代表 dashboard。
- 如需定位 dashboard 特有慢点，应单独创建 staging-only、环境变量控制的轻量诊断 PR。

推荐后续 PR：

1. `Enable safe Laravel config cache on Render`
   - 只处理 config cache。
   - 保留环境变量关闭方式。
   - 不默认启用 route cache。
2. `Add lightweight staging performance diagnostics`
   - 用于进一步定位 dashboard 和重复 query 来源。
   - 默认关闭，只能通过环境变量在 staging 启用。
   - 不记录 secret 或真实业务数据。
3. `Evaluate Render database latency options`
   - 评估数据库区域、连接路径、缓存层或 Redis / persistent cache。
4. `Assess dashboard loading bottlenecks`
   - 需要先取得 authenticated dashboard profile。
   - 不修改订单、预约、支付、认证或安全逻辑。

## Staging 轻量性能诊断策略

记录日期：2026-07-09

当前策略：

- 诊断能力默认关闭。
- 仅在 `APP_ENV` 非 `production` 的 Render staging 短时间设置 `ENABLE_STAGING_PERF_DIAGNOSTICS=true` 后启用。
- `APP_ENV=production` 时强制关闭，即使误设 `ENABLE_STAGING_PERF_DIAGNOSTICS=true` 也不会启用。
- 完成采样后必须设置回 `false` 并重新部署。
- 诊断日志事件名为 `staging_perf_diagnostics`。

允许记录：

- HTTP method。
- path，不含 query string。
- route name。
- response status。
- request duration。
- query count。
- total / max query time。
- 聚合后的 query fingerprint。
- schema / settings / extensions / theme / pages / menus / cart / reservation 等分类。
- 非敏感 source file 摘要。

禁止记录：

- SQL bindings。
- 完整 SQL 值。
- 请求 body。
- cookie。
- session ID。
- CSRF token。
- 用户 ID。
- 真实顾客数据。
- 真实订单数据。
- 真实预约数据。
- 真实支付数据。

启用方式：

1. 合并并部署 `Add lightweight staging performance diagnostics` PR。
2. 确认 Render staging 的 `APP_ENV` 不是 `production`。
3. 在 Render staging Environment Variables 设置 `ENABLE_STAGING_PERF_DIAGNOSTICS=true`。
4. 重新部署 staging。
5. 访问公开页面和已登录 dashboard。
6. 在 Render logs 中筛选 `staging_perf_diagnostics`。
7. 采样完成后设置 `ENABLE_STAGING_PERF_DIAGNOSTICS=false` 并重新部署。

可选调节项：

- `STAGING_PERF_DIAGNOSTICS_SLOW_QUERY_MS`
- `STAGING_PERF_DIAGNOSTICS_MAX_PATTERNS`
- `STAGING_PERF_DIAGNOSTICS_LOG_CHANNEL`

## Staging 轻量性能诊断采样结果

记录日期：2026-07-09

执行状态：

- PR #33 已合并并部署到 Render staging，live commit 为 `bbd9376`。
- Render Shell 已确认 `APP_ENV=staging`，不是 `production`。
- 已短时间启用 `ENABLE_STAGING_PERF_DIAGNOSTICS=true` 并重新部署。
- 已采样公开页面和已登录 dashboard。
- 采样完成后已设置 `ENABLE_STAGING_PERF_DIAGNOSTICS=false` 并重新部署。
- 关闭后 config cache 中 `DIAG_ENABLED=false`，再次访问首页未新增 `staging_perf_diagnostics` 日志。

采样摘要：

| 页面 | duration | query_count | query_total | query_max | 主要来源 |
| --- | ---: | ---: | ---: | ---: | --- |
| `/` | 3018.26ms | 19 | 2893.4ms | 171.89ms | theme / pages / other |
| `/default/menus` | 5114.76ms | 33 | 4978.89ms | 161.96ms | settings / other / menus |
| `/cart` | 4377ms | 26 | 4315.51ms | 167.54ms | theme / pages / settings |
| `/default/reservation` | 4468.38ms | 26 | 4372.31ms | 169.36ms | theme / pages / settings |
| `/admin/login` | 710.24ms | 4 | 688.27ms | 175.15ms | user login / settings / cart status middleware |
| `/admin/dashboard` | 4739.67ms | 24 | 3672.89ms | 165.05ms | users / orders aggregate / reservation aggregate / dashboard widgets |

结论：

- 动态 HTML TTFB 仍主要由远程数据库多次往返与重复查询叠加造成。
- 每个采样页面的 query_total_ms 基本覆盖页面 duration_ms。
- 公开页面主要来源集中在 theme / pages / settings / menus。
- dashboard 额外包含订单、预约、客户和用户偏好相关 widget / aggregate 查询。
- 当前结果支持优先评估数据库区域、连接路径和缓存策略；仅继续调整 Nginx / asset cache 不会解决主要 TTFB。

## 下一步

1. 创建 `Evaluate database latency options`，评估数据库区域、Render 到数据库连接路径、缓存层或 Redis / persistent cache 策略。
2. 必要时创建 `Reduce repeated settings and schema queries`，聚焦 theme / pages / settings / menus 等重复查询来源。
3. 评估 `Assess cache backend for Render staging`，确认 file cache、database cache、Redis 或其他 persistent cache 对 TastyIgniter 的实际收益和风险。
4. 可以并行规划 Cloudflare / custom domain / production 前置事项，但不要直接进入 production。
5. Production readiness 仍受当前动态 HTML TTFB 性能风险影响，正式上线前必须继续处理。

## Staging 数据库延迟方案评估

记录日期：2026-07-09

评估结果：

- 当前 Render staging 已部署到 PR #34 合并提交 `bd1c4fe0`。
- 运行容器出口地理摘要为 Boardman, Oregon / AWS。
- DigitalOcean DB 解析目标的地理摘要为 Clifton, New Jersey / DigitalOcean。
- 当前 DB 连接为 public / external host；不是 Render private network。
- 当前路径跨云且跨美国东西部，足以解释较高 RTT。
- PDO 新连接平均 328.08ms；PDO 同连接 `select 1` 平均 80.94ms。
- Laravel 重连后首查平均 651.35ms；Laravel 同连接 `select 1` 平均 161.89ms。
- 当前 `config/database.php` 未启用 persistent PDO connection；本阶段不直接启用。

方案判断：

1. 优先做 same-region staging DB / service 实验。
   - 如果保持 Render Oregon app，优先测试更靠近 Oregon 的 DO Managed MySQL staging test DB。
   - 如果保持当前 DO DB，优先测试更靠近 New Jersey / NYC3 的新 Render staging service。
   - Render 现有 service region 不能直接修改，需要新建 service 或新建 DB 做 A/B 验证。
2. 并行评估 cache backend。
   - 目标是减少 settings / pages / theme / menus 查询。
   - Redis / Valkey / persistent cache 需要单独验证费用、生命周期和 TastyIgniter 实际命中路径。
3. 后续再优化重复查询。
   - 只在可扩展层或项目层做小 PR。
   - 不修改 vendor、TastyIgniter core、订单、支付、预约、认证或安全逻辑。
4. Cloudflare / custom domain 不是当前 DB-bound TTFB 的主修复。
   - Cloudflare 可以改善 DNS、TLS、静态缓存和边缘体验。
   - Cloudflare 不能降低 Render 应用服务器到数据库的 RTT。

## 下一步更新

1. 创建 `Create same-region staging database test`，先由用户确认新增费用和测试资源方向。
2. Codex 负责指导配置 staging test DB / test service，并执行非 destructive 连通性和 TTFB 验证。
3. 创建或规划 `Assess cache backend for Render staging`。
4. 如区域实验仍不足，再拆 `Reduce repeated settings and schema queries`。
5. Cloudflare / custom domain / production 前置规划可以并行，但 production readiness 仍受当前 DB RTT 风险影响。

## Canada unified hosting architecture

记录日期：2026-07-09

当前决策：

- 长期方向改为加拿大同区统一架构。
- 目标是让 app、database、media storage、cache 和 backup 尽量位于加拿大同一区域。
- 现有 Render staging 继续保留为 fallback，不直接迁 production。
- 不再优先做 Render + DigitalOcean 跨云局部优化实验。

首选候选：

- Google Cloud Run。
- Google Cloud SQL for MySQL。
- Google Cloud Storage。
- 可选 Google Memorystore / Redis。
- 首选 Montréal `northamerica-northeast1`，备选 Toronto `northamerica-northeast2`。

官方区域确认摘要：

- Google Cloud Run、Cloud SQL for MySQL、Cloud Storage 和 Memorystore 官方文档均列出 Montréal / Toronto。
- Cloud Storage 支持加拿大区域 bucket，也支持 Canada configurable dual-region；单区域有较低延迟和较低成本，dual-region 更适合备份 / 灾备。
- AWS 提供 Canada Central / Canada West 区域，RDS / Aurora MySQL 可作为数据库候选；但 App Runner 不建议作为新方案入口。
- DigitalOcean TOR1 是加拿大候选，区域矩阵显示 App Platform、Managed Databases、Spaces 等资源可作为简化方案验证。

架构目标：

1. Cloud Run 部署 Docker app。
2. Cloud SQL for MySQL 作为主数据库，保持 MySQL 兼容。
3. Cloud Storage 替代 Render Persistent Disk 保存 media / uploads。
4. Secret Manager 或平台环境变量保存 secrets。
5. Cloud Logging 保存运行日志。
6. Cloud SQL automated backups + media bucket lifecycle / versioning / export 作为备份基础。
7. Cloudflare / custom domain / HTTPS 在 staging 完成后再规划，不作为当前 DB RTT 修复手段。

Montréal vs Toronto：

- 对 Montréal 本地用户，Montréal 通常是首选。
- 对本项目当前性能问题，关键不是离用户近，而是 app 和 DB 同区；Montréal 和 Toronto 都能满足同区原则。
- 如果 Montréal 账号配额、服务可用性、成本或 Cloud Storage 区域风险不理想，Toronto 是合理备选。
- 若需要更强媒体灾备，可考虑 Canada dual-region bucket 或定期备份到 Toronto / Montréal 另一区域，但这会增加成本和复杂度。

Cloud Run runtime 需要重新验证：

- `Dockerfile.render` 是否可复用。
- `docker/render/start.sh` 是否应拆出 cloud-agnostic startup script。
- Nginx + PHP-FPM 是否继续保留，还是为 Cloud Run 简化 HTTP listener。
- `/healthz`。
- storage symlink。
- media upload。
- public media URL。
- config cache。
- Livewire。
- TastyIgniter asset combiner。
- Cloud Run stateless filesystem 与 media persistence。

Storage 适配路径：

1. Cloud Storage FUSE volume mount：
   - 最接近现有 local filesystem 语义。
   - 适合先做 staging experiment。
   - 需要验证 TastyIgniter media manager、缩略图、权限、并发写入和 URL 生成。
2. Laravel / TastyIgniter filesystem disk 到 Cloud Storage：
   - 长期更云原生。
   - 需要确认 TastyIgniter media library 是否可安全配置到非 local disk。
3. Cloud Filestore / NFS：
   - POSIX 语义更强。
   - 成本和网络配置更高，只作为 fallback。

候选平台比较：

| 平台 | Docker | MySQL | Media storage | Canada region | Private networking | Backup | Rollback | 适合 future production |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Google Cloud Canada | Cloud Run | Cloud SQL MySQL | Cloud Storage / Filestore | Montréal / Toronto | Cloud SQL connector / private IP | Cloud SQL backup + bucket lifecycle | 保留 Render staging | 是，首选 |
| AWS Canada | ECS / Fargate | RDS / Aurora MySQL | S3 / EFS | Canada Central / West | VPC | RDS backup + S3 lifecycle | 保留 Render staging | 是，但复杂 |
| DigitalOcean TOR1 | App Platform / DOKS / Droplet | Managed MySQL | Spaces / Volumes | Toronto | VPC / trusted sources | Managed backups + Spaces | 保留 Render staging | 可能，需验证 |
| Azure Canada | Container Apps / App Service | Azure Database for MySQL | Blob / Files | Canada Central / East | VNet | Managed backups | 保留 Render staging | 可能，当前优先级较低 |

推荐的第一个 staging experiment：

1. 用户确认 Google Cloud project、billing、预算上限和目标区域。
2. 创建 Canada staging project / resources，仅用于测试。
3. 创建 Cloud SQL for MySQL staging instance。
4. 创建 Cloud Storage staging media bucket。
5. 创建 Cloud Run staging service，先复用 Docker runtime。
6. 使用平台 UI / Secret Manager 输入 secrets，不发到聊天。
7. 初始化空 staging DB 或迁移非真实测试数据。
8. 验证 `/healthz`、首页、菜单页、购物车、预约页、后台登录页、dashboard、Livewire、assets、media upload、重新部署持久性和 TTFB。
9. 验证 backup / restore 基线。
10. 保留 Render staging 作为 rollback / comparison baseline。

下一步更新：

1. 创建 `Plan Google Cloud Canada staging experiment`。
2. 用户确认是否接受 Google Cloud 计费和测试资源成本。
3. 用户确认首选区域：Montréal 或 Toronto。
4. 用户在 Google Cloud UI 输入 secrets；不要发送到聊天。
5. 在 Canada staging 通过前，不进入 production。

参考官方文档：

- Google Cloud Run locations: https://docs.cloud.google.com/run/docs/locations
- Cloud SQL for MySQL locations: https://docs.cloud.google.com/sql/docs/mysql/locations
- Cloud Storage bucket locations: https://docs.cloud.google.com/storage/docs/locations
- Memorystore for Redis regions: https://docs.cloud.google.com/memorystore/docs/redis/regions
- Cloud Run Cloud Storage volume mounts: https://docs.cloud.google.com/run/docs/configuring/services/cloud-storage-volume-mounts
- Cloud SQL private IP: https://docs.cloud.google.com/sql/docs/mysql/private-ip
- AWS Regions: https://docs.aws.amazon.com/global-infrastructure/latest/regions/aws-regions.html
- AWS App Runner availability change: https://aws.amazon.com/apprunner/
- DigitalOcean regional availability: https://docs.digitalocean.com/platform/regional-availability/

## Google Cloud Canada staging experiment plan

记录日期：2026-07-09

范围：

- 仅规划 Canada staging。
- 不创建 Google Cloud 资源。
- 不产生费用。
- 不迁 production。
- 不输入或记录真实 secrets。

推荐配置：

| 类别 | 推荐 |
| --- | --- |
| Google Cloud project | 独立 staging project，例如 `le-chateau-staging-ca` |
| Region | Montréal `northamerica-northeast1` |
| Backup region / fallback region | Toronto `northamerica-northeast2` 作为备选 |
| Container registry | Artifact Registry |
| Compute | Cloud Run service |
| Database | Cloud SQL for MySQL |
| Media storage | Cloud Storage bucket，优先测试 Cloud Storage FUSE volume mount |
| Secrets | Secret Manager |
| Logs | Cloud Logging |
| Optional cache | Memorystore / Redis，先不创建 |

Budget / billing 确认点：

1. 用户确认 Google Cloud billing 已启用。
2. 用户确认 staging 月度预算上限。
3. 用户确认 budget alerts 百分比。
4. 用户确认是否允许创建 Cloud SQL，因为它通常是 staging experiment 的主要持续成本。
5. 用户确认实验结束后是否允许停止或删除 Cloud SQL / Cloud Run / bucket，以控制费用。

资源创建顺序草案：

1. 创建或选择 Google Cloud staging project。
2. 启用必要 APIs。
3. 创建 Artifact Registry repository。
4. 构建并推送 Docker image。
5. 创建 Cloud SQL for MySQL staging instance。
6. 创建 staging database 和 user，真实 password 只在 Google Cloud UI / Secret Manager 输入。
7. 创建 Cloud Storage media bucket。
8. 创建 Secret Manager secrets。
9. 部署 Cloud Run service。
10. 配置 Cloud SQL connection。
11. 配置 media storage mount 或 adapter。
12. 配置 `APP_URL` / `ASSET_URL` 到 Canada staging URL。
13. 初始化 TastyIgniter staging。
14. 执行 Canada staging 验收。

Secret Manager 名称清单：

- `staging-app-key`
- `staging-db-password`
- `staging-db-username`
- `staging-db-database`
- `staging-db-host-or-connection-name`
- `staging-mysql-attr-init-command`
- `staging-app-url`
- `staging-asset-url`
- `staging-mail-host`
- `staging-mail-username`
- `staging-mail-password`
- `staging-cache-connection`
- `staging-session-driver`
- `staging-queue-connection`
- `staging-cloud-storage-bucket`
- `staging-cloud-storage-service-account`
- `staging-payment-placeholder-secrets`
- `staging-cloudflare-or-domain-placeholder-secrets`

Cloud Run runtime checklist：

- Confirm `Dockerfile.render` builds in Cloud Build.
- Confirm Cloud Run passes `$PORT` and Nginx listens on it.
- Remove or no-op Render Persistent Disk assumptions.
- Confirm `/healthz` returns 200.
- Confirm PHP-FPM starts.
- Confirm Nginx serves TastyIgniter public assets.
- Confirm Livewire asset route returns 200.
- Confirm config cache is generated only after env / secrets are available.
- Confirm Cloud Run filesystem writes are not relied on for persistent media.

Cloud SQL connection options:

1. Cloud SQL connector / Cloud Run integration:
   - Preferred first experiment.
   - Avoids exposing public DB host.
   - Needs service account permissions.
2. Private IP:
   - Requires VPC planning and serverless VPC access / direct VPC egress.
   - More production-like, but more moving parts.
3. Public IP:
   - Not preferred for the Canada unified architecture.
   - Only use temporarily if connector/private path blocks staging validation.

Cloud Storage media options:

1. Cloud Storage FUSE volume mount:
   - Best first experiment because it preserves local filesystem semantics.
   - Must validate media manager upload, thumbnail generation, public URL, cache headers and concurrent writes.
2. Laravel / TastyIgniter Cloud Storage adapter:
   - Better long-term if TastyIgniter media manager supports it cleanly.
   - Requires code/config PR after investigation.
3. Cloud Filestore:
   - Fallback if POSIX semantics are mandatory.
   - Higher cost and network complexity.

Canada staging acceptance checklist:

- `/healthz` returns 200.
- `/` returns 200.
- `/default/menus` returns 200.
- `/cart` returns 200.
- `/default/reservation` returns 200.
- `/livewire/livewire*.js` returns 200.
- `/admin/login` returns 200.
- Logged-in `/admin/dashboard` renders normally.
- TastyIgniter admin CSS / JS returns 200.
- Test media upload works.
- Uploaded media persists after Cloud Run redeploy.
- Test category / test item can be created or restored without real menu data.
- Dynamic HTML TTFB baseline is recorded.
- Cloud SQL query RTT baseline is recorded.
- Logs show no new PHP fatal, Laravel exception, 500 or storage permission error.
- Cloud SQL backup is enabled or documented.
- Media bucket backup / lifecycle / versioning decision is documented.

Rollback checklist:

- Keep Render staging live and unchanged.
- Use separate Canada staging URL.
- Use separate Canada staging DB.
- Use separate Canada staging media bucket.
- Do not change production DNS.
- Do not configure production payment.
- If Canada staging fails, stop Cloud Run and Cloud SQL after confirming whether test data should be retained.
- Document resource deletion / shutdown steps before creating resources.

Next gate:

- User confirms Google Cloud billing and budget.
- User confirms Montréal or Toronto.
- User confirms permission to create Cloud Run, Cloud SQL, Cloud Storage, Artifact Registry and Secret Manager resources.
- User enters secrets in Google Cloud UI / Secret Manager.
- Only then proceed to `Create Google Cloud Canada staging resources`.

## 2026-07-09 - Cloud Run Canada staging runtime readiness

This stage records the minimum Cloud Run runtime adaptation before creating the
Cloud Run service.

Added:

- `Dockerfile.cloudrun`
- `docker/cloudrun/start.sh`
- `CLOUD_RUN_CANADA_STAGING_RUNTIME.md`

Render staging remains the fallback. `Dockerfile.render` and
`docker/render/start.sh` are unchanged.

Cloud Run readiness decisions:

- Listen on Cloud Run `$PORT`, defaulting to `8080` only when absent.
- Use the Cloud SQL connector socket path `/cloudsql/<INSTANCE_CONNECTION_NAME>`.
- Bind database secrets through Secret Manager.
- Mount the staging media bucket at `/var/www/html/storage/app/media`.
- Preserve the current TastyIgniter media symlink structure.
- Avoid recursive ownership changes under the Cloud Storage mount.
- Build images under the Canada Artifact Registry repository:
  `northamerica-northeast1-docker.pkg.dev/le-chateau-canada-staging/tastyigniter-staging/tastyigniter:<git-sha>`.

No Cloud Run service was created in this stage. No service account key was
created or downloaded. No production resources were changed.

## 2026-07-10 - Cloud Run Canada staging validation

The Canada staging service is deployed from git SHA `44940004` with the PR #40
FUSE visibility compatibility fix. Cloud Run revision
`le-chateau-canada-staging-00009-tvs` serves 100% of traffic, while Render
staging remains the fallback.

Validation summary:

- `FILESYSTEM_SKIP_VISIBILITY=true` is enabled only on Canada staging.
- `/`, `/default/menus`, `/cart`, `/default/reservation`, `/admin/login`, and
  `/livewire/livewire.min.js` returned HTTP 200.
- A non-business test image `IMG_2484.png` returned HTTP 200 with
  `image/png` and remained available after redeploy. It was not committed.
- Cloud Run logs contain no new FUSE visibility, chmod, storage permission,
  Laravel cache, Cloud SQL, PHP-FPM, Nginx, fatal, exception, or 500 error.
- Warm HTTP TTFB was approximately 0.60-0.66s for public dynamic pages and
  0.39s for admin login. PDO/Laravel connection timings remain a separate
  follow-up measurement.
- `/healthz` remains an independent Cloud Run frontend 404 and is not part of
  the FUSE visibility change.

Current next steps:

1. Create a focused `Fix Cloud Run health check routing` task.
2. Run a separate approved PDO/Laravel connection-latency measurement.
3. Keep Render staging available as the rollback and comparison environment.

## 2026-07-10 - Cloud Run health and database latency validation

Canada staging validation completed for the deployed liveness path and the
approved read-only database latency sample.

- Image SHA: `2796d2c6`.
- Cloud Run revision: `le-chateau-canada-staging-00010-fh9`.
- Traffic: 100% to the Ready revision.
- Liveness path: `/healthz/`, HTTP 200, with request evidence in Cloud Run
  logs. Bare `/healthz` remains the separate known Google frontend 404.
- Public/admin-login smoke checks for homepage, menus, cart, reservation,
  Livewire JavaScript, and retained test media returned HTTP 200.
- Read-only latency averages: PDO new 140.85 ms, PDO same connection 2.23 ms,
  Laravel reconnect first query 147.87 ms, Laravel same connection 4.33 ms.
  Compared with the former Render averages, approximate reductions are 57%,
  97%, 77%, and 97%.
- The temporary latency Job was deleted. Render staging remains the fallback.

Current readiness: Canada staging is suitable for continued staging-only
functional work, subject to a fresh authenticated dashboard check. Production
readiness remains deferred until the public `/healthz` behavior is separately
resolved or explicitly accepted and the broader production checklist is
completed.
## 2026-07-10 - Canada staging dashboard acceptance and reservation audit

Environment: Canada staging only. Status: Dashboard acceptance resolved;
reservation requirements audit remains open.

- Authenticated dashboard acceptance covered the dashboard, Orders,
  Reservations, Categories, Menus, Media Manager, Settings, Extensions, and
  Staff members pages. All rendered successfully and the session persisted.
- Same-origin assets observed on the tested pages returned HTTP 200. No
  localhost, old Render URL, browser console error, or page-level 5xx was
  observed. The existing `Broadcast is not defined` warning is non-blocking.
- `/healthz/` remains the Cloud Run liveness path. Bare `/healthz` remains the
  known Google frontend 404 and is not mixed into this acceptance record.
- The public reservation flow was inspected without submitting a form. Current
  settings show reservations and automatic table assignment enabled, 15-minute
  interval, 45-minute stay, 2-20 guests, 2-30 day advance window, no guest
  count limiter, zero-minute cancellation timeout, and 24/7 location schedule.
- Render staging and DigitalOcean fallback resources remain available.

Production readiness has not started. The next gate is a business requirements
decision for reservation behavior, followed by small staging-only changes and
focused acceptance tests if needed.

## 2026-07-10 - Birthday reservation rules implementation

Environment: local build only. Status: Pending PR review and Canada staging
validation.

- Birthday rules are isolated behind `BIRTHDAY_BOOKING_RULES_ENABLED`, which is
  false by default. Render staging must keep the flag disabled as fallback.
- The implementation adds two fixed venue slots, a plus-2 through plus-60 date
  window, server-side availability validation, and a unique reservation slot
  key without modifying vendor or TastyIgniter core.
- The additive migration changes only the existing reservations schema by
  adding a `birthday_booking` boolean with default false, nullable Birthday
  slot fields, and a location/key unique index.
- Local PHP lint, Dockerfile.cloudrun build, config cache, and 7 automated
  Birthday rules tests / 15 assertions passed. No staging deployment or
  database migration was run in this stage.

Next step: after PR review, migrate and enable the feature only on Canada
staging, then verify frontend availability, admin conflict handling, and
concurrent claims. Production readiness remains deferred.

## 2026-07-11 - Birthday reservation rules staging validation

Canada staging service-side validation for merged PR #48 completed on Cloud Run
revision `le-chateau-canada-staging-00014-2kd` at SHA `0a19c37f`.
`/healthz/` and the core public/admin pages remained healthy after deployment.
The Birthday flow exposed only the two fixed venue slots and enforced the
Toronto-local plus-2 through plus-60 date window. In the two-task concurrent
execution, one claim succeeded and one lost with the expected unique-conflict
path; the final occupying-row count was one. Synthetic records and temporary
QA Jobs were removed afterward.

The browser telephone widget rejected the synthetic number before a form
submission could create a record. This is a separate non-blocking input-widget
follow-up and is not a failure of the Birthday availability rules, but it means
end-to-end browser submission remains pending. No real data, payment, mail,
production, or destructive database operation was used. PR #45 was closed as
superseded by PR #46 and subsequent staging fixes/validation. Render staging and
the DigitalOcean fallback remain available.

Next steps:

1. Review and merge the Birthday staging validation documentation PR.
2. Decide whether to create a focused fix for the telephone input widget.
3. Continue only with separately scoped staging PRs for reservation UX and
   later payment, registration, add-on, bilingual, and delivery work.
4. Keep production readiness deferred until the remaining functional and
   operational gates are explicitly accepted.
