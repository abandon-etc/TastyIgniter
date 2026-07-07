# PROJECT_NOTES

本文件记录 `abandon-etc/TastyIgniter` 这个 fork 的第一阶段体检结果。本阶段只做仓库定位、Docker 本地运行确认和说明文档，不开发业务功能。

## 当前仓库确认

- GitHub fork：`https://github.com/abandon-etc/TastyIgniter`
- 本地项目根目录：`<local-workspace>\work\TastyIgniter`
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

第一次启动前，先从模板创建本机 `.env` 文件：

```bash
copy .env.docker.example .env
```

然后打开 `.env`，只在本机填写 `DB_PASSWORD`。这个值不要提交到 GitHub。

之后在项目根目录执行：

```bash
docker compose up --build
```

启动会自动做这些事：

1. 安装 PHP/Composer 依赖。
2. 生成 Laravel 应用密钥。
3. 安装 npm 依赖。
4. 使用 Laravel Mix 构建前端资源。
5. 启动 Laravel 本地网站。

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
DB_PASSWORD=<set in local .env, do not commit>
```

## Docker 服务说明

- `app`：运行 TastyIgniter。包含 PHP 8.3、Composer、Node、npm，用于安装依赖、构建前端资源和启动网站。
- `mysql`：运行 MariaDB 10.11，用作本地数据库。
- `tastyigniter-mysql-data`：Docker 数据卷，用于保存数据库数据，避免容器重启后数据丢失。

## 新增 Docker 文件说明

- `Dockerfile`：定义本地开发用的应用容器，包含 PHP 8.3、Composer、Node、npm 和常用 PHP 扩展。
- `docker-compose.yml`：定义网站容器和数据库容器，以及端口、环境变量和数据库持久化。数据库密码从本机 `.env` 读取，不在仓库文件里写死。
- `.docker/entrypoint.sh`：容器启动时自动准备 `.env`、安装依赖、生成应用密钥、构建前端资源。
- `.env.docker.example`：给 Docker 本地开发使用的 `.env` 模板，只包含占位配置，不保存真实密码或密钥。
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
- `.env`：已基于 `.env.docker.example` 创建本地版本。`.env` 只用于本机，不应提交。
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

## 安装后验证

本节记录用户完成 TastyIgniter 初始设置后的可运行基线。验证日期：2026-07-07。

### 本地访问地址

- Frontend: `http://127.0.0.1:8000`
- Admin: `http://127.0.0.1:8000/admin`
- Admin login: `http://127.0.0.1:8000/admin/login`

### Docker 常用命令

启动本地环境：

```bash
docker compose up -d
```

首次构建或 Docker 配置变化后启动：

```bash
docker compose up --build
```

停止本地环境：

```bash
docker compose down
```

重新构建前端资源：

```bash
docker compose exec app npm run dev
```

进入容器执行 `artisan` 命令：

```bash
docker compose exec app php artisan list
```

示例：

```bash
docker compose exec app php artisan cache:clear
```

### Docker 和依赖状态

已确认：

- Docker Desktop 正在运行。
- `app` container 可以启动。
- `mysql` container 可以启动，并且 health check 正常。
- Composer dependencies 可用。
- npm dependencies 可用。
- `npm run dev` 可以成功构建 Laravel Mix 资源。
- `.env` 已存在并可被应用读取；不要提交 `.env`。
- MariaDB 数据库连接成功。
- `php artisan` 可用，当前 Laravel 版本为 `12.63.0`。
- TastyIgniter 后台可访问。
- TastyIgniter 前台可访问。

### 页面访问验证

未登录状态下的访问结果：

- `/` 返回 `200`，前台首页可访问。
- `/admin/login` 返回 `200`，后台登录页可访问。
- `/admin` 返回 `302`，跳转到 `/admin/login`，这是正常的登录保护。
- `/admin/dashboard` 返回 `302`，跳转到 `/admin/login`，这是正常的登录保护。
- `/admin/menus` 返回 `302`，跳转到 `/admin/login`，说明菜单管理入口存在且受保护。
- `/admin/orders` 返回 `302`，跳转到 `/admin/login`，说明订单管理入口存在且受保护。
- `/admin/reservations` 返回 `302`，跳转到 `/admin/login`，说明预约管理入口存在且受保护。
- `/admin/themes` 返回 `302`，跳转到 `/admin/login`，说明主题管理入口存在且受保护。
- `/admin/extensions` 返回 `302`，跳转到 `/admin/login`，说明扩展管理入口存在且受保护。
- `/admin/mail_templates` 返回 `302`，跳转到 `/admin/login`，说明邮件模板入口存在且受保护。
- `/admin/payments` 返回 `302`，跳转到 `/admin/login`，说明支付设置入口存在且受保护。

说明：AI 没有也不应该读取你的管理员密码，所以没有模拟登录进入 Dashboard。用户在浏览器中登录后，应可以访问这些后台页面。

### 后台主要功能入口

已确认存在的后台入口：

- Menu categories: `/admin/categories`
- Menu items: `/admin/menus`
- Menu item options: `/admin/menu_options`
- Orders: `/admin/orders`
- Reservations: `/admin/reservations`
- Locations: `/admin/locations`
- Mealtimes: `/admin/mealtimes`
- Payments: `/admin/payments`
- Mail templates: `/admin/mail_templates`
- Mail layouts: `/admin/mail_layouts`
- Settings: `/admin/settings`
- Themes: `/admin/themes`
- Extensions: `/admin/extensions`

这些入口适合下一阶段优先通过后台配置完成冰淇淋店基础设置。

### 当前已确认可用的功能

- Orange Theme 已安装，`themes` table 中 `igniter-orange` 的 `status=1` 且 `is_default=1`。
- Frontend 页面使用 `igniter-orange` 资源。
- Frontend 首页包含 `View Menu` 和 `Reservation` 链接。
- Reservation addon 已被 TastyIgniter addon manifest 发现，代码为 `igniter.reservation`。
- Reservation 相关数据表存在：`reservations`、`reservation_tables`、`dining_areas`、`dining_sections`、`dining_tables`。
- Cart/Menu 相关数据表存在，并且已有基础菜单数据。
- PayRegister 相关数据表存在，并且已有 6 个 payment method 记录。
- Mail 相关数据表存在：`mail_layouts`、`mail_partials`、`mail_templates`。
- 数据库中已有 1 个 `admin_users` 记录，说明初始管理员设置已经完成。

### 当前仍未确认或存在风险的功能

- Dashboard 的登录后页面未由 AI 直接验证，因为需要管理员账号密码。出于安全要求，AI 不读取、不记录、不要求你把密码写入仓库。
- 实际下单流程、真实支付流程、真实邮件发送、预约冲突检测没有做端到端测试。本阶段只确认入口和基础安装，不测试业务交易。
- `working_hours` 当前为 0 条记录，说明营业时间需要在后台继续配置。
- `mail_templates` 当前为 0 条记录，邮件模板可能需要通过后台或 TastyIgniter 的推荐流程进一步初始化/配置后再确认。
- npm audit 报告存在旧前端依赖的安全提示；本阶段没有运行 `npm audit fix --force`，因为那可能引入破坏性升级。

### `webpack@^5.94.0` 锁定原因

项目使用 Laravel Mix 6。首次执行 `npm install` 时，由于没有 `package-lock.json`，npm 自动安装了较新的 `webpack@5.108.4`。这个版本与当前 Laravel Mix 6 的进度插件配置不兼容，导致 `npm run dev` 报错：

```text
Progress Plugin has been initialized using an options object that does not match the API schema.
```

为保持最小改动，只在 `package.json` 中增加：

```json
"webpack": "^5.94.0"
```

这样可以让现有 Laravel Mix 构建流程稳定运行。它只影响本地/前端资源构建，不改变 TastyIgniter 的订单、支付、预约、认证或安全逻辑。

未来升级 TastyIgniter 时，如果官方更新了 `package.json` 或 Laravel Mix/webpack 版本，应重新评估这个锁定；它不是永久业务逻辑依赖。

### 后续开发语言规范

- 与用户沟通使用中文。
- Code identifiers 使用英文，包括 variable names、function names、class names、file names、database fields、config keys、Git branch names、commit messages、PR titles、technical comments。
- Customer-facing 或 staff-facing 文案可以按业务需要使用中文、英文或双语。
- 不在源码命名中使用中文或拼音。
- `PROJECT_NOTES.md` 和 `CHANGELOG_AI.md` 可以使用中文；命令、路径和代码标识符保持英文原样。
- 不为了语言规范去修改 TastyIgniter core、third-party dependencies 或 `vendor/`。
