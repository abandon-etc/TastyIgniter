# PROJECT_NOTES

本文件记录 `abandon-etc/TastyIgniter` 这个 fork 的第一阶段体检结果。本阶段只做仓库定位、Docker 本地运行确认和说明文档，不开发业务功能。

## 当前仓库确认

- GitHub fork：`https://github.com/abandon-etc/TastyIgniter`
- 本地项目根目录：`C:\Users\xinra\Documents\Codex\2026-07-07\laravel-tastyigniter-fork-tastyigniter-1-tastyigniter\work\TastyIgniter`
- 当前分支：`project-audit-and-docker-setup`
- 原始默认分支：`4.x`
- 项目根目录已确认包含：
  - `composer.json`
  - `artisan`
  - `package.json`
  - `.env.example`
  - `.git`

## 项目是什么形态

这是 TastyIgniter 4.x 的完整 Laravel 应用项目，不是单独主题包，也不是单独扩展包。

它的核心能力来自 Composer 依赖：

- `laravel/framework`
- `tastyigniter/core`

前端资源使用 Laravel Mix 构建，不是 Vite。

## Docker 启动方式

本项目当前新增了一个最小本地 Docker 开发方案，避免要求你在 Windows 本机安装 PHP、Composer、Node、npm 或 MySQL。

启动前需要确认 Docker Desktop 正在运行。

在项目根目录执行：

```bash
docker compose up --build
```

第一次启动会自动做这些事：

1. 如果没有 `.env`，从 `.env.docker.example` 复制一份。
2. 安装 PHP/Composer 依赖。
3. 生成 Laravel 应用密钥。
4. 安装 npm 依赖。
5. 使用 Laravel Mix 构建前端资源。
6. 启动 Laravel 本地网站。

本地访问地址：

```text
http://127.0.0.1:8000
```

后台初始设置地址：

```text
http://127.0.0.1:8000/admin
```

当前 Docker 体检已经确认：`/admin` 会打开 TastyIgniter 的 `Initial Setup: Admin & Restaurant Info` 页面。这里需要你自己填写超级管理员账号、密码和店铺信息；AI 不会替你编造这些信息。

数据库从 Windows 访问：

```text
127.0.0.1:33060
```

容器内部数据库连接：

```text
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=tastyigniter
DB_USERNAME=tastyigniter
DB_PASSWORD=tastyigniter
```

## Docker 服务说明

- `app`：运行 TastyIgniter。包含 PHP 8.3、Composer、Node、npm，用于安装依赖、构建前端资源和启动网站。
- `mysql`：运行 MariaDB 10.11，用作本地数据库。
- `tastyigniter-mysql-data`：Docker 数据卷，用于保存数据库数据，避免容器重启后数据丢失。

## 新增 Docker 文件说明

- `Dockerfile`：定义本地开发用的应用容器，包含 PHP 8.3、Composer、Node、npm 和常用 PHP 扩展。
- `docker-compose.yml`：定义网站容器和数据库容器，以及端口、环境变量和数据库持久化。
- `.docker/entrypoint.sh`：容器启动时自动准备 `.env`、安装依赖、生成应用密钥、构建前端资源。
- `.env.docker.example`：给 Docker 本地开发使用的 `.env` 模板。
- `package-lock.json`：记录本次可成功安装和构建的 npm 依赖版本。

这些文件只影响本地开发环境，不修改 TastyIgniter 核心业务逻辑。

## 本次 Docker 体检结果

- Docker Desktop：已运行。
- fork 仓库：已成功访问并克隆。
- 项目根目录：已确认。
- 当前分支：`project-audit-and-docker-setup`。
- Docker 容器：`app` 和 `mysql` 均能启动。
- Composer 依赖：已成功安装。
- npm 依赖：已成功安装。
- 前端资源：首次失败于 `webpack@5.108.4` 与 Laravel Mix 6 的兼容问题；已通过在 `package.json` 中锁定 `webpack@^5.94.0` 解决，`npm run dev` 已成功。
- `.env`：已基于 `.env.docker.example` 自动创建本地版本。`.env` 只用于本机，不应提交。
- 数据库连接：容器内数据库连接成功。
- TastyIgniter 安装命令：`php artisan igniter:install --no-interaction --force` 已成功执行。
- 网页访问：`http://127.0.0.1:8000/admin` 可访问初始设置页面。

## 当前还需要你完成的事

打开：

```text
http://127.0.0.1:8000/admin
```

然后填写：

- 超级管理员姓名
- 超级管理员邮箱
- 超级管理员密码
- 店铺名称
- 店铺邮箱
- 电话
- 地址、城市、省/州、邮编、国家

这些信息会进入本地数据库，用来创建后台管理员和默认店铺资料。

## 关键目录说明

- `app/`：Laravel 应用层代码。当前阶段不要改。
- `config/`：配置文件。可以在理解含义后谨慎修改。
- `database/`：数据库迁移和种子。当前阶段不要改。
- `extensions/`：适合放自定义 TastyIgniter 扩展。
- `themes/`：适合放自定义前台主题。
- `lang/`：适合放语言翻译和文案。
- `resources/`：前端资源和视图资源。
- `public/`：公开入口和构建后的前端资源。
- `storage/`：运行时文件、日志、缓存、上传文件。
- `vendor/`：Composer 安装的依赖，不要手工修改。

## 不应该修改的目录

原则上不要直接修改：

- `vendor/`
- `vendor/tastyigniter/core/`
- `vendor/tastyigniter/ti-ext-*`
- `vendor/tastyigniter/ti-theme-*`
- `bootstrap/`
- `public/index.php`
- `artisan`
- 订单、支付、预约、认证、安全相关核心流程

原因：这些地方属于框架或 TastyIgniter 核心，直接修改会让以后升级变困难，也更容易造成线上故障。

## 可以安全自定义的地方

更推荐使用这些非侵入方式改造成冰淇淋店网站：

- 后台配置：菜单、价格、营业时间、自取、配送、支付、邮件、预约规则。
- `themes/`：自定义冰淇淋店前台主题。
- `extensions/`：需要生日派对特殊流程时，开发独立扩展。
- `lang/`：中文文案、邮件文案、后台显示文字。
- `resources/css` 和 `resources/js`：项目级样式和脚本。

## 后续适合开发的任务清单

1. 先完成 Docker 首次启动和安装页面访问。
2. 在后台配置店铺基础信息。
3. 配置冰淇淋菜单分类、口味、尺寸、配料和库存。
4. 配置自取、配送区域和营业时间。
5. 配置开发阶段邮件为日志模式，正式上线前再配置真实邮件服务。
6. 配置支付方式，正式上线前再接入 Stripe、PayPal 或 Square。
7. 评估 TastyIgniter 自带 Reservation 是否能满足生日派对场地预约。
8. 如果不够，再开发独立生日派对预约扩展。
9. 新建自定义主题，避免直接修改官方主题。

## 给非程序员的说明

这次新增的 Docker 配置相当于给项目配了一个“本地厨房”：网站、数据库和构建工具都放在 Docker 里运行。这样你的 Windows 电脑不需要单独安装 PHP、Composer、Node 和 MySQL，也更容易重复启动。

如果 Docker 启动失败，优先检查 Docker Desktop 是否打开，其次看终端中的第一条红色错误信息。

停止本地环境：

```bash
docker compose down
```

再次启动：

```bash
docker compose up -d
```
