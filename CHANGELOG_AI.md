# CHANGELOG_AI

本文件记录 AI 对 `abandon-etc/TastyIgniter` fork 做过的修改。

## 2026-07-07

### 仓库访问

- 已成功访问 GitHub fork：`https://github.com/abandon-etc/TastyIgniter`
- 已克隆到本地：`<local-workspace>\work\TastyIgniter`
- 已确认进入正确项目根目录。
- 已从 `4.x` 新建并切换到分支：`project-audit-and-docker-setup`

### 新增文件

- `Dockerfile`
- `docker-compose.yml`
- `.docker/entrypoint.sh`
- `.env.docker.example`
- `PROJECT_NOTES.md`
- `CHANGELOG_AI.md`
- `package-lock.json`

### 修改文件

- `package.json`：新增 `webpack@^5.94.0` 到开发依赖，用于解决 Laravel Mix 6 在当前环境下自动安装 `webpack@5.108.4` 后构建失败的问题。

### 修改范围

- 只新增本地 Docker 开发环境配置和说明文档。
- 未修改 TastyIgniter 核心代码。
- 未修改业务功能。
- 未修改订单、支付、预约、认证或安全相关逻辑。

### 依赖说明

- 没有向 TastyIgniter 应用代码引入新的业务依赖。
- Docker 镜像中安装了本地运行需要的 PHP 扩展、Composer、Node 和 npm。
- 新增 MariaDB Docker 服务用于本地数据库。
- `webpack` 是前端构建工具依赖，用于让已有 Laravel Mix 构建流程稳定运行，不是业务功能依赖。

### Docker 体检结果

- Docker Desktop：已运行。
- 容器启动：成功。
- Composer 依赖安装：成功。
- npm 依赖安装：成功。
- 前端资源构建：首次失败，锁定兼容 webpack 版本后成功。
- `.env`：已为本地 Docker 自动创建，未纳入 Git。
- 数据库连接：成功。
- `php artisan`：可用。
- `php artisan igniter:install --no-interaction --force`：成功。
- TastyIgniter 页面访问：`http://127.0.0.1:8000/admin` 可打开初始设置页面。

### 风险说明

- 第一次 Docker 启动会下载镜像并安装 Composer/npm 依赖，耗时较长。
- 如果 Composer 或 npm 上游网络不可用，首次启动可能失败。
- `.env.docker.example` 只适合本地开发，不能作为正式上线配置。
- 当前 Docker 配置是最小开发方案，不等同于正式生产部署方案。
- 后台初始设置页面需要用户自己填写管理员账号和店铺信息，AI 未编造账号密码。

## 2026-07-07 安装后验证

### 验证内容

- 确认 Docker Desktop 正在运行。
- 确认 `app` 和 `mysql` containers 正常运行。
- 确认 MariaDB 数据库可连接。
- 确认 `php artisan` 可用。
- 确认 Composer dependencies 可用。
- 确认 npm dependencies 可用。
- 确认 `npm run dev` 可以成功构建 Laravel Mix 资源。
- 确认 frontend `/` 返回 `200`。
- 确认 admin login `/admin/login` 返回 `200`。
- 确认后台功能入口存在，并且未登录时跳转到 `/admin/login`。
- 确认 `igniter-orange` theme 已安装并设为默认主题。
- 确认 `igniter.reservation` addon 已被发现，预约相关数据表存在。
- 确认菜单、订单、支付、邮件、主题、扩展相关后台路由存在。
- 执行语言规范检查，确认 Docker/code/config 文件没有中文代码注释、中文变量名、中文配置名或拼音命名。

### 修改文件

- 修改 `PROJECT_NOTES.md`：新增 `## 安装后验证`，记录可运行基线、后台入口、风险、`webpack@^5.94.0` 锁定原因和后续语言规范。
- 修改 `CHANGELOG_AI.md`：记录本次安装后验证和语言规范整理。
- 修改 `docker-compose.yml`：改为从本机 `.env` 读取 `DB_PASSWORD`，并使用随机 MariaDB root password，避免仓库文件保存数据库密码。
- 修改 `.env.docker.example`：移除示例数据库密码值，只保留空占位。

### 未修改内容

- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证逻辑。
- 未修改安全相关逻辑。
- 未提交任何管理员账号、密码、token、密钥或个人敏感信息。
- Docker 本地数据库密码保存在本机 `.env`，不写入仓库文件。

### 风险说明

- Dashboard 登录后页面未由 AI 直接验证，因为需要管理员账号密码。
- 真实下单、真实支付、真实邮件发送和预约冲突检测尚未做端到端测试。
- `working_hours` 当前为空，需要下一阶段在后台配置营业时间。
- `mail_templates` 当前为空，邮件模板需要继续通过后台或 TastyIgniter 推荐流程确认。
- `webpack@^5.94.0` 是本地 Laravel Mix 构建兼容锁定，未来升级 TastyIgniter 时应重新评估。

## 2026-07-07 业务配置和本地化规划

### 验证内容

- 已同步最新 `4.x` 分支。
- 已新建 `business-configuration-and-localization-plan` 分支。
- 已确认 Docker baseline 仍可启动。
- 已确认 frontend `http://127.0.0.1:8000` 返回 `200`。
- 已确认 admin login `http://127.0.0.1:8000/admin/login` 返回 `200`。
- 已检查 TastyIgniter Languages、`lang/`、`lang/vendor/`、Orange theme、mail layout/template 和 localization middleware 的现有能力。

### 修改文件

- 新增 `BUSINESS_CONFIGURATION_PLAN.md`：记录冰淇淋店第一版业务配置、生日派对预约映射、自取/配送建议、邮件和支付配置建议，以及魁北克英法双语本地化原则。
- 新增 `LOCALIZATION_CHECKLIST.md`：记录英法双语上线前检查清单。
- 更新 `CHANGELOG_AI.md`：记录本次规划文档工作。

### 未修改内容

- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未修改 `PROJECT_NOTES.md`。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证逻辑。
- 未修改安全相关逻辑。
- 未开发业务功能。
- 未提交 `.env`、管理员密码、数据库真实密码、API key、token 或真实顾客信息。

### 风险说明

- 前台是否已有可直接使用的语言切换按钮仍需登录后台和前台页面实际确认；当前代码检查未发现明确的 Orange theme language switch component。
- 菜单商品、分类和商品选项是否能按语言分别填写，需要在后台界面实际确认；如果不能，第一版可临时使用双语名称，后续再考虑主题或扩展方案。
- 邮件模板和邮件布局支持语言相关配置，但是否能按顾客选择语言自动发送不同语言邮件需要后续端到端验证。
- TastyIgniter 语言安装命令示例使用完整 locale code，例如 `fr_FR`；魁北克第一版建议优先尝试 `fr_CA` / `en_CA`，但需确认当前 TastyIgniter marketplace 或后台是否支持。
