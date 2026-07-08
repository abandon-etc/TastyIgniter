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

## 2026-07-07 后台配置执行指南

### 验证内容

- 已同步最新 `4.x` 分支。
- 已新建 `admin-configuration-guide` 分支。
- 已确认 Docker baseline 仍可启动。
- 已确认 frontend `http://127.0.0.1:8000` 返回 `200`。
- 已确认 admin login `http://127.0.0.1:8000/admin/login` 返回 `200`。
- 已基于 `BUSINESS_CONFIGURATION_PLAN.md`、`LOCALIZATION_CHECKLIST.md`、`PROJECT_NOTES.md` 和 `CHANGELOG_AI.md` 编写后台配置执行指南。

### 修改文件

- 新增 `ADMIN_CONFIGURATION_GUIDE.md`：记录非程序员可照着操作的后台配置顺序，包括语言、店铺信息、营业时间、自取、菜单分类、商品选项、商品、生日派对预约、邮件、支付和前台检查。
- 更新 `CHANGELOG_AI.md`：记录本次后台配置指南工作。

### 未修改内容

- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证逻辑。
- 未修改安全相关逻辑。
- 未开发业务功能。
- 未提交 `.env`、管理员密码、数据库真实密码、API key、token、真实顾客信息或真实支付信息。

### 风险说明

- 本指南未登录后台验证每个按钮的实际名称；无法确认的按钮已标注为“需要后台实际确认”。
- 前台语言切换按钮、菜单字段是否支持分语言填写、邮件是否能按顾客语言自动发送，仍需要用户在后台和前台实际配置后确认。

## 2026-07-07 后台配置记录与问题跟踪

### 验证内容

- 已同步最新 `4.x` 分支。
- 已新建 `admin-configuration-tracker` 分支。
- 已确认 Docker baseline 仍可启动。
- 已确认 frontend `http://127.0.0.1:8000` 返回 `200`。
- 已确认 admin login `http://127.0.0.1:8000/admin/login` 返回 `200`。
- 已基于 `ADMIN_CONFIGURATION_GUIDE.md`、`BUSINESS_CONFIGURATION_PLAN.md`、`LOCALIZATION_CHECKLIST.md`、`PROJECT_NOTES.md` 和 `CHANGELOG_AI.md` 编写后台配置跟踪文档。

### 修改文件

- 新增 `ADMIN_CONFIGURATION_TRACKER.md`：记录第一批后台配置目标、准备清单、语言设置记录表、店铺基础信息记录表、营业时间记录表、自取设置记录表、问题跟踪表和验收清单。
- 更新 `CHANGELOG_AI.md`：记录本次后台配置跟踪文档工作。

### 未修改内容

- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证逻辑。
- 未修改安全相关逻辑。
- 未开发业务功能。
- 未提交 `.env`、管理员密码、数据库真实密码、API key、token、真实顾客信息或真实支付信息。

### 风险说明

- 本文件是记录模板，不代表后台配置已经完成。
- 第一批配置只覆盖语言设置、店铺基础信息、营业时间和自取设置；菜单、商品、生日派对预约和支付放到后续批次。

## 2026-07-07 第一批后台配置问题诊断

### 验证内容

- 已新建 `admin-first-batch-configuration-issues` 分支。
- 已诊断 Q-001：`fr_CA` 翻译导入失败的原因是缺少 TastyIgniter Marketplace Carté Key；项目代码读取 `IGNITER_CARTE_KEY`，后台 Updates 页面也提供 Attach Carté Key 入口。
- 已诊断 Q-002：Orange theme 当前未发现可见语言切换组件；前台没有语言切换入口应记录为后续主题改造任务。
- 已诊断 Q-003：本地数据库中 CAD 仍存在且为默认币种，但 `currency_rate` 为 `0.00000000`；日志还显示 Currency 列表页渲染 `currency_rate` 浮点值时触发 TastyIgniter core 类型错误。
- 已确认 `fr_CA` 和 `en_CA` 均为 enabled，且 `fr_CA` 为默认语言。
- 已确认 `fr_CA` 翻译数量仍为 `0`。
- 已确认 frontend `http://127.0.0.1:8000` 返回 `200`。
- 已确认 admin login `http://127.0.0.1:8000/admin/login` 返回 `200`。

### 数据库修复

- 已在本地开发数据库中将 CAD 设为唯一默认币种。
- 已将 CAD 设置为 enabled。
- 已将 CAD `currency_rate` 修复为 `1.00000000`。
- 已清理本地货币缓存 `igniter.currency` 和 `igniter.currency.rates`。

### 修改文件

- 更新 `ADMIN_CONFIGURATION_TRACKER.md`：记录第一批语言设置结果、店铺基础信息配置进度、Q-001、Q-002、Q-003 和验收清单状态。
- 更新 `CHANGELOG_AI.md`：记录本次问题诊断和本地数据库修复。

### 未修改内容

- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证逻辑。
- 未修改安全相关逻辑。
- 未开发业务功能。
- 未提交 `.env`、管理员密码、数据库真实密码、API key、token、真实顾客信息或真实支付信息。

### 风险说明

- Q-001 仍为 Open：没有 Carté Key 时，Marketplace 翻译导入不能完成；后续可安全配置 Carté Key 或评估本地翻译文件方案。
- Q-002 仍为 Open：语言切换入口需要后续通过自定义主题或主题覆盖处理。
- Q-003 数据层已修复：CAD 是唯一默认币种且 rate 为 `1.00000000`；后台 Currencies 页面是否恢复需要用户刷新后台确认。如果仍报错，应记录为 TastyIgniter core 兼容风险，先不要直接修改 core。

## 2026-07-07 第一批后台配置写入

### 执行内容

- 已在本地开发数据库写入第一批后台配置。
- 写入前已创建备份文件：`.local/backups/admin-first-batch-before-20260707-194406.json`。
- 写入前已确认 `settings.supported_languages` 使用序列化数组格式。
- 写入前已确认 `location_settings` 中 `collection` / `delivery` 配置使用 JSON 格式。
- 写入前已确认 `working_hours` 使用 `weekday`、`type`、`opening_time`、`closing_time`、`status` 字段。
- 写入时使用数据库事务；写入成功，没有触发回滚。

### 修改的数据表

- `locations`：写入默认门店基础信息，包括店铺名称、公开地址、城市、省份、邮编、公开电话、公开邮箱，并设为默认启用门店。
- `settings`：写入 `site_name`、`site_email`、`timezone`、`supported_languages`。
- `languages`：确认 `fr_CA` 启用并设为默认语言，确认 `en_CA` 启用。
- `currencies`：确认 CAD 启用、设为默认币种，并将 `currency_rate` 设置为 `1.00000000`。
- `working_hours`：写入每天 12:00-22:00 的 `opening` 和 `collection` 时间。
- `location_settings`：启用 `collection` / Pickup，设置普通订单准备时间为 30 minutes；关闭或后置 `delivery`。

### 未写入内容

- 未写入冰淇淋蛋糕配置。
- 未写入生日派对预约配置。
- 未写入菜单、商品或商品选项。
- 未写入真实邮件服务。
- 未写入真实支付方式。
- 未写入 Carté Key。
- 未写入法语翻译内容。
- 未做语言切换主题改造。

### 验证结果

- 已确认 frontend `http://127.0.0.1:8000` 返回 `200`。
- 已确认 admin login `http://127.0.0.1:8000/admin/login` 返回 `200`。
- 已确认 CAD 是默认币种且 rate 为 `1.00000000`。
- 已确认 `fr_CA` 是默认语言。
- 已确认 `en_CA` 为 enabled。
- 已确认默认门店基础信息已更新；文档未记录完整地址、电话或邮箱。
- 已确认每天 `opening` 和 `collection` 时间均为 12:00-22:00。
- 已确认 Pickup 已启用，普通订单准备时间为 30 minutes。
- 已确认 Delivery 已关闭或后置。
- 未登录后台验证 Currencies 页面；未登录访问该页面会跳转到后台登录页。

### 修改文件

- 更新 `ADMIN_CONFIGURATION_TRACKER.md`：记录第一批配置已写入本地开发数据库，更新店铺基础信息、营业时间、自取设置、Q-003 和验收清单。
- 更新 `CHANGELOG_AI.md`：记录本次数据库写入和验证。

### 未修改内容

- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证逻辑。
- 未修改安全相关逻辑。
- 未开发业务功能。
- 未提交 `.env`、管理员密码、数据库真实密码、API key、token、真实顾客信息或真实支付信息。

### 风险说明

- Q-001 仍为 Open：缺少 Carté Key 时，Marketplace 翻译导入仍不能完成。
- Q-002 仍为 Open：前台仍没有可见语言切换入口，后续应通过主题改造或主题覆盖处理。
- Q-003 数据层已恢复：CAD 是默认币种且 rate 为 `1.00000000`；Currencies 页面需要店主在已登录后台刷新确认。

## 2026-07-07 非登录前台只读检查

### 检查内容

- 已新建 `frontend-readonly-check` 分支。
- 已进行只读 HTTP 检查和浏览器检查。
- 已确认 frontend `http://127.0.0.1:8000` 返回 `200`。
- 已确认首页可显示，未发现系统错误。
- 已确认 `<html lang>` 当前为 `fr_CA`。
- 已确认菜单页可通过浏览器打开，未发现系统错误。
- 已确认预约页可打开，未提交预约。
- 已确认购物车页面可打开，未提交订单。
- 已确认空购物车访问 checkout 会返回菜单页，未提交订单。
- 已确认 390px 移动端宽度下未发现明显横向溢出。

### 发现的问题

- Q-002 仍为 Open：前台没有可见语言切换入口。
- 新增 Q-004：Delivery 已在配置中关闭或后置，但首页仍显示 delivery address 搜索入口，可能误导顾客。
- 新增 Q-005：`<html lang>` 已是 `fr_CA`，但前台可见文字仍大量为英文。
- 菜单页仍为示例菜单内容；第一批配置不包含菜单和商品，因此暂不作为新问题。

### 修改文件

- 更新 `ADMIN_CONFIGURATION_TRACKER.md`：记录非登录前台只读检查结果，更新 Q-002、Q-003、Q-004、Q-005 和前台自取/配送显示状态。
- 更新 `CHANGELOG_AI.md`：记录本次只读检查。

### 未修改内容

- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证逻辑。
- 未修改安全相关逻辑。
- 未开发业务功能。
- 未写入数据库。
- 未登录后台。
- 未提交订单。
- 未提交预约。
- 未测试真实支付。
- 未提交 `.env`、管理员密码、数据库真实密码、API key、token、真实顾客信息或真实支付信息。

### 风险说明

- Q-004 和 Q-005 都会影响上线前顾客体验。
- Q-004 优先检查后台是否能改首页搜索模式；如果不能，后续应通过主题改造处理。
- Q-005 优先处理翻译配置；仍无法翻译的主题文字再进入主题改造。
