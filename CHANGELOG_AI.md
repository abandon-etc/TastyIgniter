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

## 2026-07-08 前台问题修复方案设计

### 验证内容

- 已同步最新 `4.x` 分支。
- 已新建 `frontend-remediation-plan` 分支。
- 已确认 Docker baseline 可启动。
- 已确认 frontend `http://127.0.0.1:8000` 返回 `200`。
- 已确认 admin login `http://127.0.0.1:8000/admin/login` 返回 `200`。
- 已阅读 `ADMIN_CONFIGURATION_TRACKER.md`、`ADMIN_CONFIGURATION_GUIDE.md`、`BUSINESS_CONFIGURATION_PLAN.md`、`LOCALIZATION_CHECKLIST.md`、`PROJECT_NOTES.md` 和 `CHANGELOG_AI.md`。

### 调查内容

- 已调查 Q-004：首页 Delivery address 搜索入口来自 Orange 主题首页的 `igniter-orange::local-search` Livewire 组件。
- 已确认 `LocalSearch` 组件支持 `hideSearch` 属性，可隐藏搜索框并显示菜单入口。
- 已调查 Q-002：当前未发现 Orange 主题内置可见语言切换入口。
- 已确认当前 localization 支持 request / browser / session locale，但本地直接访问 `/fr_CA`、`/en_CA`、`/fr_CA/default/menus` 和 `/en_CA/default/menus` 返回 `404`，因此第一版不建议直接使用 URL prefix 作为语言切换。
- 已调查 Q-005：大量英文来自缺少 `fr_CA` 翻译包、Orange 主题语言 key、扩展语言 key、后台 demo content 和少量主题/菜单硬编码。
- 已调查 Q-001：Carté Key 可通过 `.env` 的 `IGNITER_CARTE_KEY` 或后台 Updates / Marketplace Attach Carté Key 配置；本次未配置、未读取、未提交 Carté Key。

### 修改文件

- 新增 `FRONTEND_REMEDIATION_PLAN.md`：记录 Q-001、Q-002、Q-004、Q-005 的来源分析、风险、推荐修复方案、优先级和后续小 PR 拆分。
- 更新 `CHANGELOG_AI.md`：记录本次前台问题修复方案设计。

### 未修改内容

- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未修改 Orange 主题源码。
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
- 未提交 `.env`、管理员密码、数据库真实密码、API key、token、Carté Key、真实顾客信息或真实支付信息。

### 风险说明

- Q-004 是最高优先级，因为首页 Delivery address 搜索会误导顾客。
- Q-002 需要后续主题改造或等价非侵入式前台展示改造。
- Q-005 应先处理关键前台文案，不建议一次性翻译全部 2992 条。
- Q-001 取决于是否安全配置 Carté Key；没有 Carté Key 时可以先用 `lang/vendor` 覆盖关键前台文案。

## 2026-07-08 首页单店 CTA 实施

### 验证内容

- 已同步最新 `4.x` 分支。
- 已新建 `homepage-single-location-cta` 分支。
- 已确认 Docker baseline 可启动。
- 已确认 frontend `http://127.0.0.1:8000` 返回 `200`。
- 已确认 admin login `http://127.0.0.1:8000/admin/login` 返回 `200`。
- 已重新确认首页搜索模块来自 Orange theme 的 `igniter-orange::local-search` Livewire component。
- 已确认首页使用 `vendor/tastyigniter/ti-theme-orange/resources/views/_pages/home.blade.php` 渲染该 component，但本次未修改 `vendor/`。

### 修改内容

- 新增 `resources/views/vendor/igniter-orange/livewire/local-search.blade.php`。
- 通过项目级 Orange 视图覆盖隐藏首页 local search / delivery address 搜索区域。
- 将原搜索区域替换为两个 CTA：
  - `Commander / Order Now`
  - `Réserver une fête / Book a Party`
- 两个 CTA 使用当前默认门店 slug 生成链接：
  - 点单入口：`http://127.0.0.1:8000/default/menus`
  - 预约入口：`http://127.0.0.1:8000/default/reservation`
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`：将 Q-004 标记为 Resolved，并记录本次首页 CTA 检查结果。
- 更新 `CHANGELOG_AI.md`：记录本次实施。

### 验收结果

- 首页不再显示 `Enter delivery address`。
- 首页不再显示 `Find a restaurant near you`。
- 首页不再显示 location search / address search 输入框。
- 首页显示 `Commander / Order Now`。
- 首页显示 `Réserver une fête / Book a Party`。
- 点单按钮可打开菜单页。
- 预约按钮可打开预约页。
- 菜单页仍显示 `Pick-up` 和 `in 30 min`。
- 390px 移动端宽度下两个按钮可见且未超出屏幕。
- 前台未发现系统错误。
- 后台登录页仍可访问。

### 未修改内容

- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未直接修改 Orange vendor theme。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证逻辑。
- 未修改安全相关逻辑。
- 未写入数据库。
- 未登录后台。
- 未提交订单。
- 未提交预约。
- 未测试真实支付。
- 未处理 Q-001 Carté Key。
- 未处理完整法语翻译。
- 未添加完整语言切换。
- 未提交 `.env`、管理员密码、数据库真实密码、API key、token、Carté Key、真实顾客信息或真实支付信息。

### 风险说明

- Q-004 已解决：首页误导性的 Delivery / 地址搜索入口已隐藏。
- Q-002 仍为 Open：前台仍没有可见语言切换入口。
- Q-005 仍为 Open：首页 CTA 当前使用法语在前、英语辅助的临时双语文案；完整本地化后应改为语言 key 或本地翻译覆盖。

## 2026-07-08 前台语言切换入口实施

### 验证内容

- 已同步最新 `4.x` 分支，其中包含已合并的 PR #8。
- 已新建 `frontend-language-switcher` 分支。
- 已确认 Docker baseline 可启动。
- 已确认 frontend `http://127.0.0.1:8000` 返回 `200`。
- 已确认 admin login `http://127.0.0.1:8000/admin/login` 返回 `200`。
- 已确认 TastyIgniter localization middleware 会读取 request、browser、session、default locale。
- 已确认 session locale key 由 TastyIgniter localization service 管理，内部 key 为 `igniter.translation.locale`。
- 已确认可通过 `app('translator.localization')->setSessionLocale($locale)` 设置 session locale。
- 已确认当前不应使用 `/fr_CA` 或 `/en_CA` URL prefix。
- 已确认 Orange header 来自 `igniter-orange::includes.header`，本次通过项目级 view override 覆盖。

### 修改内容

- 修改 `routes/web.php`：新增 `language.switch` route。
- 新增 `resources/views/vendor/igniter-orange/includes/header.blade.php`。
- 通过项目级 Orange header view override 显示 `Français | English`。
- 语言切换链接：
  - `http://127.0.0.1:8000/language/fr_CA`
  - `http://127.0.0.1:8000/language/en_CA`
- route 只允许 `fr_CA` 和 `en_CA`。
- route 设置 session locale 后返回当前站内页面；外部来源会回到首页，避免 open redirect。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`：将 Q-002 标记为 Resolved，并记录本次语言切换检查结果。
- 更新 `CHANGELOG_AI.md`：记录本次实施。

### 验收结果

- 首页显示 `Français | English`。
- 默认 `<html lang>` 为 `fr_CA`。
- 点击 `English` 后 `<html lang>` 变为 `en_CA`。
- 点击 `Français` 后 `<html lang>` 回到 `fr_CA`。
- 非允许 locale，例如 `/language/es_MX`，返回 `404`。
- 外部 `Referer` 不会导致跳转到外部网站。
- 站内 `Referer` 会返回原页面。
- 390px 移动端宽度下 `Français` 和 `English` 都可见且未超出屏幕。

### 未修改内容

- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未直接修改 Orange vendor theme。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证逻辑。
- 未修改安全相关逻辑。
- 未写入数据库。
- 未登录后台。
- 未提交订单。
- 未提交预约。
- 未测试真实支付。
- 未处理 Q-001 Carté Key。
- 未处理完整法语翻译。
- 未配置菜单、商品或支付。
- 未提交 `.env`、管理员密码、数据库真实密码、API key、token、Carté Key、真实顾客信息或真实支付信息。

### 风险说明

- Q-002 已解决：前台现在有可见语言切换入口。
- Q-001 仍为 Open：法语翻译导入仍需要 Carté Key 或本地翻译方案。
- Q-005 仍为 Open：切换到 `fr_CA` 后仍有大量英文-only 文案，因为完整翻译尚未处理。

## 2026-07-08 关键前台文案本地化覆盖

### 验证内容

- 已同步最新 `4.x` 分支，其中包含已合并的 PR #9。
- 已新建 `frontend-critical-localization` 分支。
- 已确认 Docker baseline 可启动。
- 已确认 frontend `http://127.0.0.1:8000` 返回 `200`。
- 已确认 admin login `http://127.0.0.1:8000/admin/login` 返回 `200`。
- 已调查关键前台文案来源，确认本次优先覆盖：
  - `igniter-orange`
  - `igniter-local`
  - `igniter-cart`
  - `igniter-reservation`
- 已确认主导航部分文字可能来自后台菜单内容或 demo content，本次不写入数据库，不处理后台内容。

### 修改内容

- 新增少量 `lang/vendor` 本地翻译覆盖：
  - `lang/vendor/igniter-orange/fr_CA/default.php`
  - `lang/vendor/igniter-orange/en_CA/default.php`
  - `lang/vendor/igniter-local/fr_CA/default.php`
  - `lang/vendor/igniter-local/en_CA/default.php`
  - `lang/vendor/igniter-cart/fr_CA/default.php`
  - `lang/vendor/igniter-cart/en_CA/default.php`
  - `lang/vendor/igniter-reservation/fr_CA/default.php`
  - `lang/vendor/igniter-reservation/en_CA/default.php`
- 修改 `resources/views/vendor/igniter-orange/livewire/local-search.blade.php`：
  - 将首页 CTA 从临时双语硬编码改为 `igniter.orange::default` language key。
  - `fr_CA` 显示法语单语 CTA。
  - `en_CA` 显示英语单语 CTA。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`：记录 Q-005 关键文案部分覆盖，状态仍为 Open。
- 更新 `CHANGELOG_AI.md`：记录本次实施。

### 验收结果

- 首页可访问。
- 菜单页可访问。
- 预约页可访问。
- 购物车页可访问。
- 后台登录页可访问。
- `fr_CA` 下首页 CTA 为法语单语，不再显示 `Order Now` 或 `Book a Party`。
- `en_CA` 下首页 CTA 为英语单语。
- 语言切换仍正常。
- 首页 CTA 仍正常跳转。
- 首页仍不显示 Delivery address / Find Location 搜索框。
- 菜单页 Pickup 关键文案已部分覆盖。
- 预约页关键文案已部分覆盖。
- 购物车空状态关键文案已部分覆盖。
- 本地 Chrome 只读工具不能可靠切换到 390px 视口；本次未修改 CTA 的 Bootstrap 响应式布局类，沿用前一阶段已验证的按钮结构。

### 未修改内容

- 未处理 Q-001 Carté Key。
- 未导入 Marketplace 完整语言包。
- 未一次性翻译所有前台文案。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未直接修改 Orange vendor theme。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证逻辑。
- 未修改安全相关逻辑。
- 未写入数据库。
- 未登录后台。
- 未提交订单。
- 未提交预约。
- 未配置真实菜单、商品或支付。
- 未提交 `.env`、`.local`、数据库备份、本地配置 JSON、管理员密码、数据库真实密码、API key、token、Carté Key、真实顾客信息或真实支付信息。

### 风险说明

- Q-001 仍为 Open：完整 Marketplace 法语翻译导入仍需要安全配置 Carté Key，或后续继续扩展本地翻译覆盖。
- Q-002 保持 Resolved：语言切换入口仍存在。
- Q-004 保持 Resolved：首页地址搜索入口仍隐藏。
- Q-005 保持 Open：关键文案已部分覆盖，但完整站点翻译、后台 demo content 和剩余英文文案仍需后续处理。

## 2026-07-08 第一版顾客前台视觉系统

### 验证内容

- 已从最新 `4.x` 新建 `frontend-visual-system-local` 分支。
- 已确认 Docker baseline 可启动。
- 已确认首页、菜单页、预约页、购物车页、结账入口、登录页、注册页和后台登录页均可访问。
- 已确认 `fr_CA` / `en_CA` 语言切换仍正常。
- 已确认 390px 移动端下首页、菜单页和预约页无明显破版。

### 修改内容

- 新增 `public/css/brand-colors.css`：
  - 应用粉色主背景 `#FAC8D5`。
  - 应用奶油白内容卡片背景 `#FFF8F2`。
  - 应用薰衣草紫按钮 / 强调色 `#ADA4CB`。
  - 应用深色正文 `#332B4F`。
  - 统一 header、footer、按钮、表单、卡片、菜单状态区域和 cookie banner 的基础视觉风格。
- 新增项目级 Orange head override：`resources/views/vendor/igniter-orange/includes/head.blade.php`，用于加载项目级 CSS。
- 更新项目级 Orange local-search override：`resources/views/vendor/igniter-orange/livewire/local-search.blade.php`，为首页 CTA 添加稳定样式 class。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`：记录第一版前台视觉系统检查结果。

### 验收结果

- 首页可访问。
- 首页不显示 hero slider、大图或装饰图。
- 首页使用 solid `#FAC8D5` 粉色背景。
- 首页 CTA 居中，按钮仍可打开菜单页和预约页。
- 菜单页可访问。
- 菜单页商品卡片布局正常。
- 本次 CSS 未匹配或隐藏 `.menu-item-image`；当前本地 demo 数据未渲染商品缩略图元素。
- 菜单页 Pickup / Cueillette 正常。
- 预约页可访问，表单布局正常。
- 购物车页可访问，空购物车状态正常。
- 后台登录页可访问。
- 语言切换仍正常。
- CSS 未使用 `linear-gradient`、`radial-gradient` 或 `background-image` 渐变。
- CSS 未使用全局 `img { display: none; }`、`.carousel { display: none; }` 或 `.card img { display: none; }`。

### 未修改内容

- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未直接修改 Orange vendor theme。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证逻辑。
- 未修改安全相关逻辑。
- 未写入数据库。
- 未登录后台。
- 未提交订单。
- 未提交预约。
- 未处理 Carté Key。
- 未导入 Marketplace 完整语言包。
- 未提交 `.env`、`.local`、数据库备份、本地配置 JSON、管理员密码、数据库真实密码、API key、token、Carté Key、真实顾客信息或真实支付信息。

### 风险说明

- Q-001 保持 Open：Carté Key / Marketplace 翻译导入仍未处理。
- Q-002 保持 Resolved：前台语言切换入口仍正常。
- Q-004 保持 Resolved：首页 Delivery / Find Location 搜索入口仍隐藏。
- Q-005 保持 Open：本次只统一视觉，不完成全站翻译。

## 2026-07-08 菜单真实数据录入暂缓计划

### 决策内容

- 暂时跳过本地大量真实菜品录入。
- 本地只保留少量测试分类、测试商品、测试价格和可选测试图片。
- 继续推进结账流程验证、预约流程验证和部署准备。
- 真实菜单数据等正式或准生产环境准备好后，再通过管理员后台录入。

### 原因

- 本地 Docker 后台响应慢，影响大量录菜效率。
- 当前慢主要来自 Windows bind mount、Laravel / TastyIgniter 大量小文件读取、`php artisan serve`、CLI OPcache 未生效、`APP_DEBUG=true` 和缓存未启用。
- 数据库、Docker 网络和基础服务不是主要瓶颈。
- 当前本地性能不应直接等同于正式 Linux 生产环境性能。

### 修改内容

- 新增 `MENU_DATA_ENTRY_PLAN.md`：记录菜单数据录入暂缓决策、部署后录入真实菜单的注意事项和风险。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`：记录“菜单真实数据录入 Deferred”。
- 更新 `CHANGELOG_AI.md`：记录本次文档更新。

### 未修改内容

- 未修改业务代码。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未写入数据库。
- 未提交真实菜单、价格、图片、顾客信息或支付信息。
- 未提交 `.env`、`.local`、密码、密钥、token 或 Carté Key。

### 风险说明

- 如果生产部署脚本会重建数据库，真实菜单会丢失。
- 如果上传目录未持久化，菜品图片会丢失。
- 如果准生产后台性能仍慢，需要再做生产性能诊断。

## 2026-07-08 部署准备 checklist

### 决策内容

- 新增部署准备 checklist，重点保护生产数据库、上传目录、真实菜单数据、生产配置和备份流程。
- 明确真实菜单数据会在准生产或生产环境后台录入，因此部署方案必须避免覆盖生产数据库和上传文件。

### 修改内容

- 新增 `DEPLOYMENT_READINESS_CHECKLIST.md`。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`：记录部署准备 checklist 已新增。
- 更新 `CHANGELOG_AI.md`：记录本次文档更新。

### 未修改内容

- 未修改业务代码。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未修改 Docker 配置。
- 未写入数据库。
- 未提交真实菜单、真实顾客信息、真实订单、真实预约或真实支付信息。
- 未提交 `.env`、`.local`、密码、密钥、token 或 Carté Key。

### 风险说明

- 上线前仍需确认 Q-001、Q-005、真实菜单数据、支付、邮件、生日派对预约规则、税费 / 小费 / 支付设置和生产性能。
- 生产环境必须验证数据库、`storage` / uploads、`.env`、邮件配置和支付配置不会被部署流程覆盖。

## 2026-07-08 前台流程低风险验证

### 验证内容

- 已同步最新 `4.x` 分支，其中包含已合并的部署准备 checklist。
- 已新建 `frontend-flow-readiness-check` 分支。
- 已确认 Docker baseline 可启动。
- 已确认首页、菜单页、预约页、购物车页、checkout 入口和后台登录页均返回 200。
- 已确认 `fr_CA` / `en_CA` 语言切换仍正常。
- 已确认首页 CTA 仍能打开菜单页和预约页。
- 已确认 390px 移动端下首页、菜单页、购物车页和预约页无明显破版。

### 修改内容

- 新增 `FRONTEND_FLOW_READINESS_CHECK.md`：记录首页、菜单、购物车、结账入口、预约页和移动端低风险验证结果。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`：新增 Q-006 并记录前台流程验证结果。
- 更新 `CHANGELOG_AI.md`：记录本次检查。

### 发现的问题

- 新增 Q-006：当前本地环境能显示 demo 商品，但尝试点击测试商品后购物车仍为空，结账按钮显示 `CLOSED`；带商品购物车、数量修改、移除商品和 checkout 表单未能完成验证。

### 未修改内容

- 未修改业务代码。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未写入数据库，除本地浏览器 session / cart 尝试外没有业务数据写入。
- 未提交真实订单。
- 未提交真实预约。
- 未配置真实支付。
- 未配置真实邮件。
- 未提交 `.env`、`.local`、密码、密钥、token、Carté Key、真实顾客信息或真实支付信息。

### 风险说明

- 目前不建议正式上线，原因是 Q-006 仍为 Open，带商品购物车和 checkout 表单需要在可下单状态下复测。
- 可以继续部署准备、备份方案和准生产环境搭建。

## 2026-07-08 Q-006 临时营业时间复测

### 执行内容

- 继续在 `frontend-flow-readiness-check` 分支上更新 PR #14。
- 已确认 Q-006 初次失败原因是本地测试时间不在营业时间内，前台显示 `CLOSED`。
- 已创建本地数据库备份：`.local/backups/q006-before-temp-open-20260708-102353.json`。
- 已临时修改本地开发数据库，使当前时间处于可下单窗口内：
  - 当前星期 `opening` 时间临时设为 `00:00-23:59`。
  - 当前星期 `collection` 时间临时设为 `00:00-23:59`。
  - Pickup / collection 保持启用。
  - Delivery 保持关闭。
- 已清理本地缓存。
- 已验证测试商品可加入购物车。
- 已验证购物车可显示商品名称、数量、价格、小计和订单总计。
- 已验证数量可增加、减少，并可移除商品。
- 已验证移除商品后空购物车状态可恢复。
- 已验证有测试商品时可进入 checkout 表单。
- 已确认 checkout 页面显示顾客信息字段、Pickup 信息和 payment area。
- 已恢复备份中的原始营业时间和相关配置。
- 恢复后已确认当前星期 `opening` / `collection` 时间回到 `12:00-22:00`，Delivery 仍关闭。

### 修改文件

- 更新 `FRONTEND_FLOW_READINESS_CHECK.md`：记录 Q-006 临时营业时间复测结果。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`：将 Q-006 标记为 Resolved，并记录备份、临时修改和恢复情况。
- 更新 `CHANGELOG_AI.md`：记录本次复测。

### 未修改内容

- 未修改业务代码。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证逻辑。
- 未修改安全相关逻辑。
- 未提交真实订单。
- 未提交真实预约。
- 未配置真实支付。
- 未配置真实邮件。
- 未提交 `.env`、`.local`、数据库备份、本地配置 JSON、密码、密钥、token、Carté Key、真实顾客信息或真实支付信息。

### 风险说明

- Q-006 已在本地临时营业状态下验证通过，当前状态为 Resolved。
- 本次没有完成真实订单提交、真实支付、真实邮件或真实预约提交。
- 上线前仍需在准生产环境验证完整订单流程、支付配置、邮件配置和备份流程。

## 2026-07-08 Render 部署方案设计

### 执行内容

- 新增 `RENDER_DEPLOYMENT_PLAN.md`，记录 Render 部署方案设计。
- 推荐第一版使用 Render Docker Web Service，但明确当前 Dockerfile 仍是本地开发方案，后续需要单独准备生产 Docker / Nginx / PHP-FPM / OPcache。
- 不建议第一版直接使用 Render 原生 PostgreSQL；推荐使用外部托管 MySQL / MariaDB，降低数据库兼容性风险。
- 记录 Render Persistent Disk 对 `storage`、uploads / media 和菜品图片的持久化要求。
- 记录生产 Render Environment Variables 字段清单，但不写真实值。
- 区分可以自动运行的部署命令和必须人工确认的数据库相关命令。
- 记录如何避免覆盖生产数据库和上传目录。
- 补充 staging 验证 checklist、正式上线前 checklist、当前风险和推荐部署顺序。

### 修改文件

- 新增 `RENDER_DEPLOYMENT_PLAN.md`。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`：记录 Render 部署方案状态和风险。
- 更新 `CHANGELOG_AI.md`：记录本次文档规划。

### 未修改内容

- 未直接部署 Render。
- 未修改业务代码。
- 未修改 Docker 配置。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未写入数据库。
- 未提交 `.env`、`.local`、数据库备份、密码、密钥、token、Carté Key、真实顾客信息或真实支付信息。

### 风险说明

- 当前 Render 部署方案仍是规划，不代表可以立即上线。
- 后续需要单独创建生产 Docker / Render runtime PR。
- 正式录入真实菜单前必须确认生产数据库、`storage`、uploads / media 和备份流程已经稳定。
