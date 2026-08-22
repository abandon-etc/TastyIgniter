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

## 2026-07-08 Render 方案 A 架构决策记录

### 执行内容

- 记录已正式选择方案 A：Render Docker Web Service 跑 TastyIgniter / Laravel 应用，数据库使用外部托管 MySQL / MariaDB。
- 记录第一版不使用 Render PostgreSQL，原因是当前项目、本地 Docker、配置验证和 Q-006 流程验证均基于 MySQL / MariaDB。
- 记录不在 Render Persistent Disk 上自托管 MySQL / MariaDB。
- 记录 Render Persistent Disk 只用于 `storage`、uploads / media、菜品图片和必要运行时文件。
- 记录 Cloudflare 域名已购买，后续通过 Render custom domain 和 Cloudflare DNS 接入。
- 记录下一阶段建议创建 Render production runtime PR，准备 Nginx + PHP-FPM + OPcache + Render `$PORT` + Persistent Disk symlink / directory setup + `.dockerignore` + safe startup script。

### 修改文件

- 更新 `RENDER_DEPLOYMENT_PLAN.md`。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`。
- 更新 `CHANGELOG_AI.md`。

### 未修改内容

- 未修改业务代码。
- 未修改 Docker 配置。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未写入数据库。
- 未提交 `.env`、`.local`、数据库备份、密码、密钥、token、Carté Key、Cloudflare token、Render secret、真实顾客信息或真实支付信息。

## 2026-07-08 Render production runtime

### 执行内容

- 新增 `Dockerfile.render`，用于 Render Docker Web Service 的生产 runtime。
- 新增 `docker/render/nginx.conf.template`，使用 Nginx 服务 Laravel / TastyIgniter public 目录，并支持 Render `$PORT`。
- 新增 `docker/render/php-production.ini`，启用生产 PHP / OPcache 配置。
- 新增 `docker/render/start.sh`，启动 PHP-FPM 和 Nginx，并安全准备 `storage`、`public/storage` 和 `public/media`。
- 更新 `.dockerignore`，避免 `.env`、`.local`、备份、本地依赖和上传数据进入 Docker build context。
- 新增 `RENDER_RUNTIME_READINESS.md`，记录 Render Web Service、Persistent Disk、Environment Variables、staging 验证和 Cloudflare custom domain 后续步骤。
- 更新 `.gitattributes`，固定 Render runtime 文件使用 LF 行尾。
- 更新 `RENDER_DEPLOYMENT_PLAN.md` 和 `ADMIN_CONFIGURATION_TRACKER.md`。
- 已执行本地 Docker build：`docker build -f Dockerfile.render -t tastyigniter-render-test .`，结果成功。
- 已检查生产镜像 PHP 扩展，确认 `bcmath`、`curl`、`exif`、`gd`、`intl`、`mbstring`、`pdo_mysql`、`zip` 和 `Zend OPcache` 存在。
- 已渲染 Nginx template 并执行 `nginx -t`，结果通过。
- 已确认测试镜像内没有 `/var/www/html/.env` 或 `/var/www/html/.local`。

### 安全边界

- 未直接部署 Render。
- 未修改业务代码。
- 未修改本地开发 `Dockerfile` 或 `docker-compose.yml`。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未写入数据库。
- 未自动运行数据库迁移、seed、install、fresh 或 refresh 命令。
- 未配置真实数据库、真实域名 DNS、真实支付、真实邮件或 Render secrets。
- 未提交 `.env`、`.local`、数据库备份、密码、密钥、token、Carté Key、Cloudflare token、Render secret、真实顾客信息或真实支付信息。

### 构建中发现并处理的问题

- TastyIgniter Composer dist zip 曾出现 checksum mismatch；已在 `Dockerfile.render` 中让 `tastyigniter/*` 包使用 source 安装，避免 Render build 受到该 dist 校验问题影响。
- 本地开发缓存 `bootstrap/cache/*.php` 曾引用 dev-only provider；已在 `.dockerignore` 排除这些缓存文件，并在 Docker build 中清理缓存。

### 下一步

- 审查 Render production runtime PR。
- 创建 Render staging Web Service。
- 配置外部 MySQL / MariaDB。
- 配置 Render Persistent Disk 到 `/var/www/html/storage`。
- 配置 Render Environment Variables。
- 在 staging 验证首页、后台登录、菜单、购物车、checkout 表单、预约页、storage / media 持久化和 OPcache。

## 2026-07-08 Render composer.lock build 修复

### 执行内容

- 已确认 Render staging build 使用的是 `Dockerfile.render`。
- 已记录 Render build 失败原因：`Dockerfile.render` 强制复制 `composer.lock`，但 GitHub 仓库当前没有 `composer.lock`。
- 已将 `Dockerfile.render` 中的 Composer 文件复制方式改为 `COPY composer.* ./`。
- 这样如果仓库以后有 `composer.lock` 会一起复制；如果当前只有 `composer.json`，Docker build 不会在 COPY 阶段失败。
- 后续 `composer install` 逻辑保持不变。
- 更新 `RENDER_RUNTIME_READINESS.md`。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`。
- 更新 `CHANGELOG_AI.md`。

### 未修改内容

- 未修改业务代码。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未写入数据库。
- 未连接真实数据库。
- 未提交 `.env`、`.local`、密码、密钥、token、Carté Key、Render secret、数据库密码、真实顾客信息或真实支付信息。

### 风险说明

- 本次只修复 Render build 的 `composer.lock` 复制问题。
- Render staging 仍未确认完整部署成功，需要等待下一次 Render build / deploy 日志。
- 没有 `composer.lock` 时，Composer install 的生产可重复性弱于锁文件；后续可以单独评估是否提交 `composer.lock`。

## 2026-07-08 Render staging 访问问题排查与数据库连接超时修复

### 执行内容

- 使用浏览器 / HTTP 工具检查 `https://le-chateau-des-enfants.onrender.com`。
- 已确认静态资源 `favicon.svg` 返回 200。
- 已确认首页 `/` 和后台登录页 `/admin/login` 约 60 秒后返回 504。
- 已确认菜单页 `/default/menus` 在短超时测试中没有返回首字节。
- 判断域名、HTTPS、Render 入口和 Nginx 静态资源服务初步正常。
- 判断动态页面最可能卡在 Laravel / TastyIgniter 访问外部 MySQL / MariaDB 连接阶段。
- 在 `config/database.php` 的 MySQL 配置中新增 `DB_CONNECT_TIMEOUT` 支持，默认 5 秒。
- 将 Render 启动脚本中 `RUN_CONFIG_CACHE` 的默认值改为 `false`，避免 staging 数据库未确认时卡在 `php artisan package:discover` / `config:cache`。
- 在 `docker/render/php-production.ini` 中将 `default_socket_timeout` 设置为 10 秒，作为外部服务网络等待的辅助保护。
- 在 `docker/render/nginx.conf.template` 中新增 `/healthz` 静态健康检查端点。
- 让 Nginx 对根路径 `HEAD /` 直接返回 200，避免 Render 默认健康探测进入 Laravel 动态首页并占满 PHP-FPM worker。
- 更新 `RENDER_RUNTIME_READINESS.md`，记录线上访问诊断、`DB_CONNECT_TIMEOUT` 和仍需确认的 Render 数据库环境变量。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`，记录 Render staging 访问问题排查结果。

### 未修改内容

- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证逻辑。
- 未修改安全相关逻辑。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未写入数据库。
- 未连接真实数据库。
- 未提交 `.env`、`.local`、密码、密钥、token、Carté Key、Render secret、数据库密码、真实顾客信息或真实支付信息。

### 风险说明

- `DB_CONNECT_TIMEOUT` 只能让数据库不可达时更快暴露错误，不能替代正确配置外部 MySQL / MariaDB。
- `RUN_CONFIG_CACHE=false` 是 staging 首次部署的保守默认值；数据库和动态页面确认正常后，可以再单独评估是否在 Render 环境变量中改为 `true`。
- `default_socket_timeout=10` 是运行时辅助保护；本地黑洞型数据库地址模拟中动态请求仍可能超过 20 秒，因此不能替代正确配置外部 MySQL / MariaDB。
- `/healthz` 和 `HEAD /` 处理只能保护 Render 健康检查，不代表 Laravel 首页已经能在数据库不可用时正常显示。
- Render 仍需要确认 `DB_CONNECTION`、`DB_HOST`、`DB_PORT`、`DB_DATABASE`、`DB_USERNAME`、`DB_PASSWORD`、`DB_PREFIX` 和数据库防火墙设置。
- 如果数据库尚未安装 / 迁移，动态页面仍会显示数据库相关错误，需要在备份和人工确认后处理。

## 2026-07-08 Render staging 数据库连接复查记录

### 执行内容

- 复查 `https://le-chateau-des-enfants.onrender.com/healthz`，确认返回 200 和 `ok`。
- 复查静态资源 `/favicon.svg`，确认返回 200。
- 复查首页 `/`、后台登录页 `/admin/login`、菜单页 `/default/menus`、购物车 `/cart` 和预约页 `/default/reservation`。
- 已确认上述动态页面当前返回 200，但页面标题为 `Database Error Was Encountered`，不是正常前台或后台登录页。
- 已确认动态页面首字节约 10 秒，符合当前数据库连接超时保护的表现。
- 更新 `RENDER_RUNTIME_READINESS.md`，记录当前 staging 数据库连接排查结果和下一步。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`，记录外部可验证结果。

### 未修改内容

- 未修改业务代码。
- 未修改 Docker / Nginx / PHP runtime 文件。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未写入数据库。
- 未连接真实数据库。
- 未读取或记录 Render / DigitalOcean 后台中的真实环境变量。
- 未提交 `.env`、`.local`、密码、密钥、token、APP_KEY、Carté Key、Render secret、DigitalOcean token、数据库密码、真实顾客信息或真实支付信息。

### 下一步

- 在 Render Environment Variables 中确认 `DB_*` 是否完整且使用 DigitalOcean Public connection。
- 在 DigitalOcean Managed MySQL 中确认 Trusted Sources / Firewall 是否允许 Render 连接。
- 在 Render Shell / Console 中执行 `mysql ... -e "select 1;"`。
- 如果 `select 1` 成功，再执行 `show tables;` 判断 staging 数据库是否为空或缺表。

## 2026-07-08 MySQL init command option for DigitalOcean staging

### 执行内容

- 根据 Render staging 初始化失败日志，记录 DigitalOcean Managed MySQL `sql_require_primary_key=ON` 兼容问题。
- 已确认用户侧检查结果：Render Shell 中 `mysql select 1` 成功，`show tables` 成功但 staging 数据库为空。
- 已记录失败原因：`SQLSTATE[HY000]: General error: 3750 Unable to create or change a table without a primary key, when sql_require_primary_key is set.`
- 已记录 DigitalOcean Managed MySQL 普通用户不能执行 `SET GLOBAL`，但可以执行 `SET SESSION sql_require_primary_key = OFF`。
- 在 `config/database.php` 的 MySQL `options` 中新增 `\PDO::MYSQL_ATTR_INIT_COMMAND => env('MYSQL_ATTR_INIT_COMMAND')`。
- 保留已有 `DB_CONNECT_TIMEOUT` 和 `MYSQL_ATTR_SSL_CA` 支持。
- 更新 `RENDER_RUNTIME_READINESS.md`。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`。

### 未修改内容

- 未修改业务代码。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证或安全逻辑。
- 未写入数据库。
- 未运行 `igniter:install`。
- 未运行 `migrate:fresh`、`migrate:refresh` 或 `db:seed`。
- 未提交 `.env`、`.local`、APP_KEY、DB_HOST、DB_USERNAME、DB_PASSWORD、Render secret、DigitalOcean token、Carté Key、支付密钥、真实顾客信息或真实支付信息。

### 风险说明

- `MYSQL_ATTR_INIT_COMMAND` 只有在 Render Environment Variables 中显式设置时才生效。
- 代码不硬编码 DigitalOcean，也不默认强制关闭 `sql_require_primary_key`。
- 本次修复只为 staging 数据库初始化提供 session 级兼容入口；生产环境是否使用该变量需要单独确认。

## 2026-07-09 Render staging 前台交互 smoke test

### 执行内容

- 已确认本地仓库在 `4.x`，并同步到 live commit `d0aa9ff`。
- 已验证 `https://le-chateau-des-enfants.onrender.com/healthz` 返回 200 和 `ok`。
- 已验证首页 `/`、菜单页 `/default/menus`、购物车 `/cart`、预约页 `/default/reservation` 和 checkout 页面在测试购物车会话下可访问。
- 已验证 `/livewire/livewire.min.js?id=42cd7fd5` 返回 200，content type 为 JavaScript。
- 已检查前台页面引用的 CSS / JS 资源，均返回 200，未发现 public `localhost` 资源 URL。
- 已确认 `Test Category`、`Test Item` 和测试价格在菜单页可见。
- 已确认测试上传文件 `/storage/media/uploads/staging-test-upload.png` 返回 200。
- 已记录当前 `Test Item` 菜品卡片未渲染商品图片元素；测试图片文件本身存在且可访问，后续真实内容录入时需要绑定商品图片后再复查。
- 已使用 `Test Item` 验证加入购物车、数量增加 / 减少、删除商品、空车后再次添加商品。
- 已从购物车点击 `Checkout` 进入 checkout 表单，但未点击最终 `Confirm`。
- 已确认 checkout 表单字段、条款勾选和 Cash On Delivery 选项可见。
- 已打开预约页，确认人数和时间下拉控件可选择测试值。
- 已检查浏览器控制台，未发现前台页面 JavaScript error。
- 已检查 Render 最近日志，未发现新的 HTTP 404 / 500、PHP fatal、Laravel exception、Livewire error、payment error 或 storage permission error。
- 已记录 Nginx 对较大 `_assets` 响应有 upstream buffering warning；对应资源返回 200 / 304，当前不作为 smoke test blocker。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`，记录本次 staging 前台交互 smoke test 结果。
- 更新 `CHANGELOG_AI.md`，记录本次验收。

### 未修改内容

- 未修改业务代码。
- 未修改 Docker / Nginx / PHP runtime 文件。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未运行 `migrate:fresh`、`migrate:refresh` 或 `db:seed`。
- 未提交订单。
- 未提交预约。
- 未创建真实顾客、真实地址、真实订单、真实预约或真实支付数据。
- 未配置真实支付。
- 未触碰 production。
- 未提交 `.env`、`.local`、数据库 dump、真实上传文件、密码、密钥、token、APP_KEY、DB_PASSWORD、Render secret、DigitalOcean token、Cloudflare token、Carté Key、支付密钥、邮件密码或真实顾客信息。

### 结论

- 当前 staging 前台核心交互 smoke test 通过。
- 未发现需要代码修复 PR 的 blocker。
- 可以进入 staging 性能基线测试。
- 后续真实内容录入前，建议单独确认商品图片绑定和前台商品图片展示。

## 2026-07-09 Render staging 第一阶段性能优化：asset cache headers + buffering

### 执行内容

- 已基于 staging 性能基线结果调查 `_assets`、`admin/_assets`、`/storage`、Livewire JS 和测试图片的响应头。
- 已确认 `_assets` / `admin/_assets` 当前响应只有 `Cache-Control: public`，缺少 `max-age`。
- 已确认测试上传图片 `/storage/media/uploads/staging-test-upload.png` 当前没有 `Cache-Control`。
- 已确认 Render 日志中的 upstream buffering warning 主要来自较大的 `_assets` / `admin/_assets` CSS / JS 响应。
- 已确认当前仓库中没有可提交的 `public/_assets` 或 `public/admin/_assets` 静态文件；这些资源在 Render 上通过 Laravel / TastyIgniter combiner 动态输出。
- 更新 `docker/render/nginx.conf.template`：
  - `_assets` 和 `admin/_assets` 改为先尝试磁盘文件，缺失时进入专用 named FastCGI location。
  - 专用 combined asset location 设置 `Cache-Control: public, max-age=86400`。
  - 专用 combined asset location 增大 FastCGI buffer，减少大资源响应写入临时文件。
  - `/storage` 和 `/media` 设置 `Cache-Control: public, max-age=604800`。
  - 保持 `/livewire/` Laravel fallback 不变。
  - 保持 `/healthz` 静态健康检查不变。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`，记录第一阶段性能优化范围、剩余问题和部署后复测要求。
- 更新 `CHANGELOG_AI.md`，记录本次优化。
- 更新 `RENDER_RUNTIME_READINESS.md`，记录 Render runtime asset caching / buffering 策略。

### 未修改内容

- 未修改业务代码。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改登录认证或安全逻辑。
- 未运行 `migrate:fresh`、`migrate:refresh` 或 `db:seed`。
- 未默认启用 Laravel route cache、config cache 或 view cache。
- 未做 DB 查询优化、TastyIgniter boot 优化、主题渲染重构或资源构建流程重构。
- 未触碰 production、Cloudflare、正式域名、真实菜单、真实图片、真实订单、真实预约或真实支付。
- 未提交 `.env`、`.local`、数据库 dump、真实上传文件、密码、密钥、token、APP_KEY、DB_PASSWORD、Render secret、DigitalOcean token、Cloudflare token、Carté Key、支付密钥、邮件密码或真实顾客信息。

### 验证计划

- 本地需验证 Nginx 配置语法和 Docker build。
- 合并并部署到 Render staging 后，需要复测：
  - `/healthz`、首页、菜单页、购物车、预约页、Livewire JS、后台登录页和 dashboard。
  - `_assets` / `admin/_assets` 是否返回 200 且带 `max-age=86400`。
  - 测试图片 URL 是否返回 200 且带 `max-age=604800`。
  - `Test Category` 和 `Test Item` 是否仍存在。
  - Render 日志是否无新的 fatal / exception / 500 / storage permission error。
  - upstream buffering warning 是否减少或消失。

### 风险说明

- `_assets` / `admin/_assets` 使用保守 1 天缓存，不使用 immutable，降低后台或主题资源变更后的缓存风险。
- `/storage` / `/media` 使用 7 天缓存；如果后续存在同名替换图片场景，浏览器可能短期缓存旧文件，真实内容录入流程应优先使用新文件名或重新上传生成新路径。
- 本 PR 主要改善重复访问和资源缓存，不预期显著降低动态 HTML TTFB。

## 2026-07-09 Render staging 第二阶段性能诊断：动态 HTML TTFB 拆分

### 执行内容

- 已同步最新 `4.x`，确认本地包含 PR #30：`6cb1e629 Improve Render asset caching and buffering (#30)`。
- 已通过 Render Events 确认 staging live commit 为 `6cb1e62`，对应 PR #30。
- 已使用外部 HTTP timing 复测 `/healthz`、首页、菜单页、购物车、预约页、后台登录页和 Livewire JS，均返回 200。
- 已在 Render Shell 中使用只读命令确认运行时状态：
  - `APP_DEBUG=false`。
  - `CACHE_DRIVER=file`。
  - `SESSION_DRIVER=file`。
  - `QUEUE_CONNECTION=sync`。
  - `RUN_CONFIG_CACHE=false`。
  - `RUN_ROUTE_CACHE=false`。
  - `RUN_VIEW_CACHE=false`。
- 已确认 PHP-FPM 下 OPcache 生效：
  - `Server API => FPM/FastCGI`。
  - `opcache.enable => On`。
  - `opcache.validate_timestamps => Off`。
  - `opcache.memory_consumption => 192`。
  - `opcache.max_accelerated_files => 20000`。
- 已确认 Laravel config cache 未启用，`bootstrap/cache/config.php` 不存在。
- 已确认 route cache 未启用。
- 已确认 view cache 启动开关未启用，但 `storage/framework/views` 中已有约 128 个编译视图文件。
- 已用 Render 容器内部 `curl` 测量动态页面，内部 TTFB 与外部 TTFB 接近，说明主要慢点不在 Cloudflare 或公网链路。
- 已用 Laravel query event 监听统计公开页面查询量和查询耗时：
  - 首页约 30 次查询，累计约 5.52s。
  - 菜单页约 44 次查询，累计约 7.20s。
  - 购物车约 37 次查询，累计约 6.05s。
  - 预约页约 37 次查询，累计约 6.79s。
  - 后台登录页约 15 次查询，累计约 2.78s。
- 已测 `select 1` 基础数据库往返，平均约 151ms。
- 已观察菜单页查询样本，包含 schema / settings / extension / pages 等多次查询；这些查询每次约 170ms，第一条 schema 检查约 600ms 以上。
- 已记录 dashboard 仍需要单独的已登录 profile；CLI 访问 `/admin/dashboard` 未携带后台 session，只返回未登录 302，不作为 dashboard 查询拆分依据。
- 更新 `ADMIN_CONFIGURATION_TRACKER.md`，记录第二阶段性能诊断结果。
- 更新 `CHANGELOG_AI.md`，记录本次诊断。
- 更新 `RENDER_RUNTIME_READINESS.md`，记录 Render runtime 当前性能结论和后续 PR 拆分建议。

### 诊断结论

- 当前动态 HTML 5-8s TTFB 的最大来源是远程数据库多次往返和 TastyIgniter / Laravel 请求过程中的重复查询，不是 OPcache、Nginx buffering、静态资源缓存、Livewire JS 或图片访问。
- OPcache 已在 PHP-FPM 中启用，配置值合理。
- `APP_DEBUG=false` 已确认。
- `RUN_CONFIG_CACHE=false` 导致 config cache 未启用，可能使启动和配置读取阶段产生额外数据库 / 文件系统工作；这是下一步低风险优化候选。
- view cache 可以继续评估，但当前已有编译视图文件，预计不是最大瓶颈。
- route cache 风险仍高，不建议默认启用；TastyIgniter extension / admin 动态路由可能不兼容。
- 数据库基础往返约 151ms，公开页面 15-44 次查询会自然叠加到 2.8-7.2s 级别。
- dashboard 慢点仍需更精确的 authenticated profile，不建议直接改 dashboard 业务逻辑。

### 推荐后续 PR 拆分

- PR A：`Enable safe Laravel config cache on Render`
  - 仅启用或验证 config cache。
  - 保留环境变量 fallback。
  - 不默认启用 route cache。
- PR B：`Add lightweight staging performance diagnostics`
  - 仅在需要进一步定位 dashboard 或重复 query 来源时创建。
  - 必须由环境变量控制。
  - 不记录 secret、真实顾客数据、真实订单或真实预约。
- PR C：`Evaluate Render database latency options`
  - 评估数据库区域、连接路径、缓存策略或 Redis / persistent cache 的必要性。
- PR D：`Assess dashboard loading bottlenecks`
  - 仅在取得已登录 dashboard profile 后处理。
  - 不改订单、预约、支付、认证或安全逻辑，除非先单独确认风险。

### 未修改内容

- 未修改运行代码。
- 未修改 Docker / Nginx / PHP runtime 文件。
- 未修改 TastyIgniter core。
- 未修改 `vendor/`。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改认证或安全逻辑。
- 未运行 `migrate:fresh`、`migrate:refresh` 或 `db:seed`。
- 未提交订单。
- 未提交预约。
- 未触碰 production、Cloudflare、正式域名、真实菜单、真实图片、真实顾客数据或真实支付。
- 未提交 `.env`、`.local`、数据库 dump、真实上传文件、密码、密钥、token、APP_KEY、DB_PASSWORD、Render secret、DigitalOcean token、Cloudflare token、Carté Key、支付密钥、邮件密码或真实顾客信息。

### 风险说明

- 当前性能问题不是功能 blocker，但仍是 production readiness 风险。
- config cache 是低风险优先项，但仍需 staging 部署后完整复测后台、前台、媒体、Livewire 和日志。
- route cache 暂不建议启用，除非单独验证 TastyIgniter 4.x extension / admin routes 兼容。
- 如果数据库区域或网络路径导致单次往返长期保持约 150ms，仅靠 PHP cache 不能完全解决 30-44 次查询页面的 TTFB。

## 2026-07-09 Enable safe Laravel config cache on Render

### 执行内容

- 已同步最新 `4.x`，确认 PR #31 已合并，最新合并提交为 `5c4b09b3 Record staging TTFB diagnostics (#31)`。
- 已在创建运行时修改前复测 staging：
  - `/healthz` 返回 200。
  - 首页返回 200。
  - `/default/menus` 返回 200。
  - `/cart` 返回 200。
  - `/default/reservation` 返回 200。
  - `/admin/login` 返回 200。
- 已确认 PR #31 为文档记录 PR，未修改运行代码、Docker/Nginx/PHP runtime、vendor、TastyIgniter core 或业务逻辑。
- 已调查 `docker/render/start.sh` 的 cache 开关：
  - `RUN_CONFIG_CACHE` 此前默认 `false`。
  - `RUN_ROUTE_CACHE` 默认 `false`。
  - `RUN_VIEW_CACHE` 默认 `false`。
- 已确认 Laravel 关键运行配置来自 config 文件中的环境变量读取：
  - `APP_URL` / `ASSET_URL`。
  - `DB_CONNECTION`、`DB_HOST`、`DB_PORT`、`DB_DATABASE`、`DB_USERNAME`、`DB_PASSWORD`、`DB_PREFIX`。
  - `DB_CONNECT_TIMEOUT` 和 `MYSQL_ATTR_INIT_COMMAND`。
  - `CACHE_DRIVER`、`SESSION_DRIVER`、`QUEUE_CONNECTION`。
- 已调整 Render 启动脚本：
  - `RUN_CONFIG_CACHE` 默认改为 `true`。
  - 仍可通过 `RUN_CONFIG_CACHE=false` 回滚。
  - 在 Render URL fallback、runtime 目录和权限准备完成后，再运行 `package:discover` 和 `config:cache`。
  - `RUN_ROUTE_CACHE` 和 `RUN_VIEW_CACHE` 仍默认 `false`。
- 已更新 `ADMIN_CONFIGURATION_TRACKER.md` 和 `RENDER_RUNTIME_READINESS.md`，记录 config cache 启用策略、回滚方式和后续验证要求。

### 修改范围

- `docker/render/start.sh`
- `ADMIN_CONFIGURATION_TRACKER.md`
- `CHANGELOG_AI.md`
- `RENDER_RUNTIME_READINESS.md`

### 未修改内容

- 未修改 vendor。
- 未修改 TastyIgniter core。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改认证或安全逻辑。
- 未默认启用 route cache。
- 未默认启用 view cache。
- 未运行 `migrate:fresh`、`migrate:refresh` 或 `db:seed`。
- 未提交测试订单或测试预约。
- 未触碰 production、Cloudflare、正式域名、真实菜单、真实图片、真实顾客数据或真实支付。
- 未提交 `.env`、`.local`、数据库 dump、真实上传文件、密码、密钥、token、APP_KEY、DB_PASSWORD、Render secret、DigitalOcean token、Cloudflare token、Carté Key、支付密钥、邮件密码或真实顾客信息。

### 验证结果

- PR #31 合并后的 staging smoke check 已通过：`/healthz`、首页、菜单页、购物车、预约页和后台登录页均返回 200。
- Render Docker 镜像构建通过。
- 容器环境中已确认没有 `.env` 文件时，`php artisan package:discover --ansi` 和 `php artisan config:cache` 可成功运行。
- 已确认 config cache 文件生成，并包含预期的非敏感运行配置值：staging URL、MySQL session init command、file cache、file session 和 sync queue。
- 已验证 `php artisan config:clear` 可清理 config cache，作为回滚辅助。
- 已验证 Render Nginx 配置语法和启动脚本 shell 语法。
- 合并并部署到 staging 后仍需复测前台、后台、Livewire、媒体、日志和动态 HTML TTFB。

### 风险说明

- config cache 会固定启动时读取到的环境变量；Render 环境变量变更后必须重新部署或重新启动容器，才能让新配置进入 cache。
- 如果 staging 部署后出现配置异常，可先设置 `RUN_CONFIG_CACHE=false` 并重新部署回滚。
- 当前动态 HTML TTFB 主要仍由远程数据库多次往返造成，config cache 可能改善启动和配置读取成本，但不保证解决 5-8s TTFB。
- route cache 仍不建议默认启用，因为 TastyIgniter extension / admin routes 可能依赖动态注册。

## 2026-07-09 Add lightweight staging performance diagnostics

### 执行内容

- 已同步最新 `4.x`，确认本地包含 PR #32：`9cbbea34 Enable Render config cache (#32)`。
- 已通过 Render Events 确认 staging live commit 为 `9cbbea3`。
- 已延续前序验证结论：`RUN_CONFIG_CACHE=true`，`bootstrap/cache/config.php` 已生成，config cache 已实际启用。
- 已确认 config cache 对动态 HTML TTFB 改善有限，当前公开页面仍为 5-8s 级别 TTFB。
- 已确认现有非侵入手段足以判断公开页面 DB-bound，但不足以定位已登录 dashboard 的服务端 query 来源。
- 新增默认关闭的 staging performance diagnostics：
  - 配置文件：`config/staging_performance_diagnostics.php`。
  - 中间件：`app/Http/Middleware/StagingPerformanceDiagnostics.php`。
  - 挂载点：`app/Http/Kernel.php` 全局 middleware stack。
- 诊断只通过环境变量开启：
  - `ENABLE_STAGING_PERF_DIAGNOSTICS=true`。
  - 可选 `STAGING_PERF_DIAGNOSTICS_SLOW_QUERY_MS`。
  - 可选 `STAGING_PERF_DIAGNOSTICS_MAX_PATTERNS`。
  - 可选 `STAGING_PERF_DIAGNOSTICS_LOG_CHANNEL`。
- 默认关闭时不写诊断日志。
- `APP_ENV=production` 时强制关闭，即使误设 `ENABLE_STAGING_PERF_DIAGNOSTICS=true` 也不会启用。
- 启用后每个请求写一条 `staging_perf_diagnostics` 日志，用于定位：
  - path / status / duration。
  - query count / total query time / max query time。
  - 聚合后的 query fingerprint。
  - schema / settings / extensions / theme / pages / menus / cart / reservation 等分类。
  - 可能的 app / extension / theme / TastyIgniter source file 摘要。

### 安全边界

- 不记录 SQL bindings。
- 不记录完整请求 body。
- 不记录 cookie。
- 不记录 session ID。
- 不记录 CSRF token。
- 不记录用户 ID。
- 不记录真实顾客数据、真实订单、真实预约或真实支付数据。
- 不提交 `.env`、`.local`、数据库 dump、真实上传文件、密码、密钥、token、APP_KEY、DB_PASSWORD、Render secret、DigitalOcean token、Cloudflare token、Carté Key、支付密钥或邮件密码。

### 修改范围

- `app/Http/Kernel.php`
- `app/Http/Middleware/StagingPerformanceDiagnostics.php`
- `config/staging_performance_diagnostics.php`
- `ADMIN_CONFIGURATION_TRACKER.md`
- `CHANGELOG_AI.md`
- `RENDER_RUNTIME_READINESS.md`

### 未修改内容

- 未修改 vendor。
- 未修改 TastyIgniter core。
- 未修改订单逻辑。
- 未修改支付逻辑。
- 未修改预约冲突检测逻辑。
- 未修改认证或安全逻辑。
- 未默认启用 route cache。
- 未默认启用 view cache。
- 未引入 Redis。
- 未迁移数据库。
- 未运行 `migrate:fresh`、`migrate:refresh` 或 `db:seed`。
- 未提交测试订单或测试预约。
- 未触碰 production、Cloudflare、正式域名、真实菜单、真实图片、真实顾客数据或真实支付。

### 验证结果

- PHP 8.3 CLI 容器语法检查通过：
  - `app/Http/Middleware/StagingPerformanceDiagnostics.php`
  - `config/staging_performance_diagnostics.php`
- Render Docker 镜像构建通过。
- 容器环境中 `php artisan config:cache` 验证通过。
- 默认未设置 `ENABLE_STAGING_PERF_DIAGNOSTICS` 时，config cache 中诊断状态为 disabled。
- `APP_ENV=staging` 且设置 `ENABLE_STAGING_PERF_DIAGNOSTICS=true` 时，config cache 中诊断状态为 enabled。
- `APP_ENV=production` 且设置 `ENABLE_STAGING_PERF_DIAGNOSTICS=true` 时，config cache 中诊断状态仍为 disabled。

### 风险说明

- 启用诊断会给每个请求增加 query listener 和日志聚合开销；只能在 staging 短时间开启。
- production 环境默认不可启用 diagnostics；如需生产级性能观测，必须另做单独方案和审批。
- 日志会增加 Render log volume；采样完成后必须关闭 `ENABLE_STAGING_PERF_DIAGNOSTICS` 并重新部署。
- 诊断日志只用于定位 query 来源，不作为功能修复。
- 动态 HTML TTFB 仍可能主要由数据库区域 / 网络 RTT 和重复查询共同造成；后续优化必须基于采样结果拆小 PR。

### 下一步建议

- 合并并部署诊断 PR。
- 确认 Render staging 的 `APP_ENV` 不是 `production`，再设置 `ENABLE_STAGING_PERF_DIAGNOSTICS=true` 并重新部署。
- 访问 `/`、`/default/menus`、`/cart`、`/default/reservation`、`/admin/login` 和已登录 `/admin/dashboard`。
- 从 Render logs 收集 `staging_perf_diagnostics` 摘要。
- 完成采样后设置 `ENABLE_STAGING_PERF_DIAGNOSTICS=false` 并重新部署。
- 基于采样结果决定是否创建：
  - `Evaluate database latency options`
  - `Reduce repeated settings and schema queries`
  - `Assess cache backend for Render staging`

## 2026-07-09 PR #33 部署与 staging performance diagnostics 采样

### 执行内容

- 已确认 PR #33：`Add lightweight staging performance diagnostics` 已合并。
- 已同步最新 `4.x`，Render staging 已部署到 `bbd9376`。
- 已在 Render Shell 中确认 `APP_ENV=staging`，不是 `production`。
- 已短时间设置 `ENABLE_STAGING_PERF_DIAGNOSTICS=true` 并重新部署 staging。
- 已采样 `/`、`/default/menus`、`/cart`、`/default/reservation`、`/admin/login` 和已登录 `/admin/dashboard`。
- 采样完成后已立即设置 `ENABLE_STAGING_PERF_DIAGNOSTICS=false` 并重新部署 staging。
- 已确认 config cache 中 diagnostics 为 disabled；关闭后访问首页不会新增 `staging_perf_diagnostics` 日志。

### 采样结果

| 页面 | duration | query_count | query_total | query_max | 主要来源 |
| --- | ---: | ---: | ---: | ---: | --- |
| `/` | 3018.26ms | 19 | 2893.4ms | 171.89ms | theme / pages / other |
| `/default/menus` | 5114.76ms | 33 | 4978.89ms | 161.96ms | settings / other / menus |
| `/cart` | 4377ms | 26 | 4315.51ms | 167.54ms | theme / pages / settings |
| `/default/reservation` | 4468.38ms | 26 | 4372.31ms | 169.36ms | theme / pages / settings |
| `/admin/login` | 710.24ms | 4 | 688.27ms | 175.15ms | user login / settings / cart status middleware |
| `/admin/dashboard` | 4739.67ms | 24 | 3672.89ms | 165.05ms | users / orders aggregate / reservation aggregate / dashboard widgets |

### 验证结果

- 关闭 diagnostics 后 `/healthz`、首页、`/default/menus`、`/cart`、`/default/reservation`、`/admin/login`、Livewire JS 和已登录 dashboard 均正常。
- 关闭 diagnostics 后 `ENABLE_STAGING_PERF_DIAGNOSTICS=false`，config cache 中 `DIAG_ENABLED=false`。
- 关闭后再次访问首页，`staging_perf_diagnostics` 日志计数未增加。
- 采样期间未发现新的 PHP fatal、Laravel exception、500 或 storage permission error；日志中两条 ERROR 为早前旧记录。

### 结论

- 当前动态 HTML TTFB 主要由远程数据库多次往返与重复查询叠加造成；query_total_ms 基本覆盖页面 duration_ms。
- 公开页面重复来源集中在 theme / pages / settings / menus。
- dashboard 额外包含订单、预约、客户和用户偏好等 widget / aggregate 查询。
- config cache 已启用，但无法抵消每次数据库往返约 150ms 的成本。

### 安全边界

- 未记录 SQL bindings、请求 body、cookie、session ID、CSRF token、用户 ID、真实顾客、真实订单、真实预约或真实支付数据。
- 未提交 `.env`、`.local`、数据库 dump、真实上传文件、密码、密钥、token、APP_KEY、DB_PASSWORD、Render secret、DigitalOcean token、Cloudflare token、Carté Key、支付密钥或邮件密码。
- 未提交测试订单或测试预约。
- 未运行 `migrate:fresh`、`migrate:refresh` 或 `db:seed`。
- 未触碰 production。

### 下一步建议

- 优先创建 `Evaluate database latency options`，评估数据库区域、Render 到数据库连接路径、缓存层或 Redis / persistent cache 策略。
- 后续可拆分评估 `Reduce repeated settings and schema queries`。
- 可并行规划 Cloudflare / custom domain / production 前置事项，但不要直接进入 production。
- Production readiness 仍受动态 HTML TTFB 性能风险影响，正式上线前必须继续处理。

## 2026-07-09 Evaluate database latency options

### 执行内容

- 已同步最新 `4.x`，确认包含 PR #34 合并提交 `bd1c4fe0`。
- 已确认 PR #34 已合并并部署到 Render staging。
- 已复测 staging 基础页面：
  - `/healthz` 返回 200。
  - `/` 返回 200。
  - `/default/menus` 返回 200。
  - `/cart` 返回 200。
  - `/default/reservation` 返回 200。
  - `/admin/login` 返回 200。
- 已用安全摘要检查 app / DB 位置，不输出 DB host、IP 或 credentials。
- 已复测 PDO 和 Laravel DB `select 1` timing，未执行 destructive 数据库操作。
- 已比较数据库延迟解决方案和推荐顺序。

### 调查结果

- Render Dashboard 未在普通文本中暴露 service region 值；运行容器出口地理摘要为 Boardman, Oregon / AWS。
- 当前 DigitalOcean DB 主机未输出；解析 IP 的地理摘要为 Clifton, New Jersey / DigitalOcean。
- 当前数据库连接为 public / external host，不是 Render private network。
- 当前路径可判断为跨云、跨美国东西部：Render Oregon / AWS 到 DigitalOcean New Jersey。
- PDO 新连接平均 328.08ms，p50 332.93ms。
- PDO 同连接 `select 1` 平均 80.94ms，p50 82.68ms。
- Laravel `DB::purge()` 后首个 `select 1` 平均 651.35ms，p50 665.33ms。
- Laravel 同连接 `select 1` 平均 161.89ms，p50 166.3ms。
- 当前 `config/database.php` 未启用 `PDO::ATTR_PERSISTENT`；本阶段未修改配置。

### 方案比较

| 方案 | 预期收益 | 复杂度 / 风险 | 结论 |
| --- | --- | --- | --- |
| A. Render app + 更靠近 Oregon 的 DO Managed MySQL staging test DB | 中到高；减少跨美国东西部 RTT | 需要新 DB、可能新增费用、需要迁移或重装 staging 数据 | 第一优先级实验候选。 |
| B. 当前 DO DB + 更靠近 New Jersey / NYC3 的 Render staging service | 中到高；减少 app 到 DB 距离 | Render 现有服务不能直接改 region，需要新 service 和 env/disk 配置 | 第二优先级实验候选。 |
| C. App 和 DB 放同一平台 / 同一区域 | 高；可消除跨云 RTT | 迁移复杂度较高；Render PostgreSQL 不匹配当前 MySQL 假设 | 中期架构候选，不能直接进 production。 |
| D. 增加 cache backend | 中；减少 settings / pages / theme / menus 重复查询 | 需验证 TastyIgniter cache 使用路径；Redis / Valkey 有费用 | 可与区域实验并行。 |
| E. 优化重复查询 | 中；减少 query_count | 不能改 vendor / core；需避开订单、支付、预约、认证逻辑 | 后续小 PR。 |
| F. Cloudflare / custom domain | 低；改善边缘、DNS、静态缓存 | 不能降低服务器到数据库 RTT | 可并行规划，但不是当前主解。 |

### 结论

- 当前动态 HTML 慢的主因是数据库 RTT 与重复查询叠加。
- 每请求新建连接成本很高；即便同一连接，Laravel 层单次 `select 1` 仍约 150-170ms。
- 仅继续调整 Nginx、asset cache、Livewire 或 Cloudflare 不能解决主要瓶颈。
- 持久连接可能降低连接建立成本，但会引入 MySQL session state / failover / connection lifecycle 风险；不建议本阶段直接开启。

### 安全边界

- 未输出 DB host 全值、IP、用户名、密码、APP_KEY、token 或 Render / DigitalOcean / Cloudflare secret。
- 未提交 `.env`、`.local`、数据库 dump、真实上传文件、真实顾客、真实订单、真实预约或真实支付数据。
- 未运行 `migrate:fresh`、`migrate:refresh` 或 `db:seed`。
- 未提交测试订单或测试预约。
- 未修改 vendor、TastyIgniter core、订单、支付、预约、认证或安全逻辑。
- 未触碰 production。

### 下一步建议

- 建议优先创建 `Create same-region staging database test`，由用户确认费用并在 DigitalOcean 或 Render UI 创建测试资源；Codex 负责配置指导和非 destructive 验证。
- 并行创建或规划 `Assess cache backend for Render staging`。
- 后续再拆 `Reduce repeated settings and schema queries`。
- 可以并行规划 Cloudflare / custom domain / production 前置事项，但不要直接进入 production。

## 2026-07-09 Evaluate Canada unified hosting architecture

### 决策更新

- 用户已决定长期方向改为方案 C：加拿大同区统一架构。
- 不再优先做 Render + DigitalOcean 的局部优化实验。
- 当前目标改为评估新 Canada staging，使 app、database、media storage、cache、backup 尽量位于加拿大同一区域。

### 调研范围

- Google Cloud Run + Cloud SQL for MySQL + Cloud Storage + 可选 Memorystore / Redis。
- AWS ECS / Fargate + RDS MySQL + S3 / EFS。
- DigitalOcean TOR1：App Platform / Managed MySQL / Spaces / cache 资源。
- Azure Canada：Container Apps / Azure Database for MySQL / Blob Storage，作为第三方备选。

### 官方文档依据

- Google Cloud Run locations 列出 Montréal `northamerica-northeast1` 和 Toronto `northamerica-northeast2`。
- Cloud SQL for MySQL locations 列出 Montréal 和 Toronto。
- Cloud Storage locations 列出 Montréal 和 Toronto，并支持 Canada configurable dual-region。
- Memorystore for Redis locations 列出 Montréal 和 Toronto。
- AWS 有 Canada Central `ca-central-1` 和 Canada West `ca-west-1`；RDS / Aurora MySQL 覆盖加拿大区域，但 App Runner 已公告将不再接受新客户，因此不建议作为新架构入口。
- DigitalOcean regional availability 列出 TOR1，并显示 App Platform、Managed Databases、Spaces 等产品在区域矩阵中可用；仍需在 UI 中最终确认当前账号和产品可创建性。

### 推荐方案

- 首选平台：Google Cloud。
- 首选区域：Montréal `northamerica-northeast1`。
- 备选区域：Toronto `northamerica-northeast2`。
- 理由：
  - Montréal 更贴近本地用户。
  - Google Cloud 在 Montréal / Toronto 同时覆盖 Cloud Run、Cloud SQL MySQL、Cloud Storage 和 Memorystore。
  - Cloud Run 能继续使用 Docker 容器路线。
  - Cloud SQL for MySQL 保持 MySQL 兼容优先，不需要切 PostgreSQL。
  - Cloud Storage 可替代 Render Persistent Disk 的 media 持久化，但需要单独验证 TastyIgniter media storage 适配。

### 迁移对象

- Docker app runtime。
- MySQL database。
- uploaded media / storage。
- env vars / secrets。
- `APP_URL` / `ASSET_URL`。
- cache / session / queue。
- logs。
- backup。
- custom domain / HTTPS。
- rollback plan。

### 需要重新验证的 runtime 内容

- `Dockerfile.render` 是否可复用，或是否需要拆出 cloud-agnostic Dockerfile。
- `docker/render/start.sh` 中 Render `$PORT`、Persistent Disk 和 Nginx template 逻辑是否需要抽象。
- Nginx + PHP-FPM 是否适合 Cloud Run 单容器；是否需要简化为 Cloud Run native HTTP listener。
- `/healthz`。
- storage symlink。
- media upload。
- public media URL。
- config cache。
- Livewire。
- TastyIgniter assets。
- persistent storage 替代方案。

### 存储策略

- Render Persistent Disk 不能直接照搬到 Cloud Run。
- 首选评估 Cloud Storage bucket：
  - 方案 1：通过 Cloud Storage FUSE volume mount 映射到现有 `storage` / media 路径，低代码改动但需验证性能、并发和权限。
  - 方案 2：适配 Laravel / TastyIgniter filesystem disk 到 Cloud Storage，长期更云原生，但需要确认 TastyIgniter media manager 支持点。
  - 方案 3：Cloud Filestore / NFS，仅在 media manager 强依赖 POSIX 文件语义且 Cloud Storage 方案不可行时评估。
- 不上传真实图片，不提交真实上传文件。

### 数据库策略

- 必须继续 MySQL 兼容优先。
- 不改 PostgreSQL，除非单独做 TastyIgniter 兼容验证。
- 新 Canada DB 只做 staging test。
- 用户必须在 Google Cloud / AWS / DigitalOcean UI 中输入 DB password 和 secret，不发到聊天。
- 不运行 `migrate:fresh`、`migrate:refresh` 或 `db:seed`。

### 方案比较

| 方案 | 预期收益 | 复杂度 | 是否适合 staging 先试 | 主要风险 |
| --- | --- | --- | --- | --- |
| Google Cloud Canada | 高 | 中 | 是 | Cloud Storage media 适配、Cloud Run domain mapping / load balancer、Cloud SQL 成本 |
| AWS Canada | 高 | 中到高 | 是 | ECS / Fargate 配置更复杂；App Runner 不适合作为新方案 |
| DigitalOcean TOR1 | 中到高 | 低到中 | 是 | 长期平台能力、private networking、对象存储与 media manager 适配需验证 |
| Azure Canada | 高 | 中到高 | 是 | 当前项目上下文少，需要额外学习和配置 |

### 下一步建议

- 创建 `Plan Google Cloud Canada staging experiment`。
- 用户先确认 Google Cloud billing、预算上限、项目名称、首选区域 Montréal / Toronto。
- Codex 后续负责生成非 secret 配置清单、部署步骤、验收清单和 rollback checklist。
- Render staging 保留为 fallback，直到 Canada staging 完整通过。

### 安全边界

- 未创建付费资源。
- 未提交 secret。
- 未要求用户把 password、token、APP_KEY、DB_PASSWORD、Google Cloud key、AWS key、DigitalOcean token 或 Cloudflare token 发到聊天。
- 未碰 production。
- 未导入真实顾客数据、真实菜单、真实图片、真实订单、真实预约或真实支付数据。
- 未运行 `migrate:fresh`、`migrate:refresh` 或 `db:seed`。
- 未修改 vendor、TastyIgniter core、订单、支付、预约、认证或安全逻辑。

## 2026-07-09 Plan Google Cloud Canada staging experiment

### 执行内容

- 已同步最新 `4.x`，确认 PR #36 已合并，合并提交为 `b4710f22`。
- 已创建 Google Cloud Canada staging experiment 规划。
- 本阶段只更新文档，不创建 Google Cloud 资源，不产生费用，不迁 production。

### 规划结论

- 首选平台：Google Cloud。
- 首选区域：Montréal `northamerica-northeast1`。
- 备选区域：Toronto `northamerica-northeast2`。
- 第一个 staging experiment：Cloud Run + Cloud SQL for MySQL + Cloud Storage + Secret Manager。
- Optional：Memorystore / Redis 先不创建，只在 cache 评估阶段启用。
- Render staging 保留为 fallback。

### Google Cloud 前置条件

- 独立 Google Cloud staging project。
- Billing 已启用。
- Budget / alerts 已设置。
- 用户确认目标区域。
- 用户确认允许创建：
  - Artifact Registry。
  - Cloud Run。
  - Cloud SQL for MySQL。
  - Cloud Storage bucket。
  - Secret Manager secrets。
  - Cloud Logging / Monitoring。
  - 可选 Memorystore / Redis。

### Secret 清单

仅记录 secret 名称，不记录真实值：

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

### Runtime 影响

- `Dockerfile.render` 可能可作为 Cloud Run Docker runtime 起点。
- `docker/render/start.sh` 需要后续验证：
  - `$PORT` / Nginx template。
  - Render Persistent Disk 逻辑是否需要抽象。
  - `/healthz`。
  - storage symlink。
  - config cache。
  - Livewire 和 TastyIgniter assets。
- 本 PR 不修改 runtime；后续如需适配，单独创建 PR。

### Storage 方案

- 第一阶段建议评估 Cloud Storage FUSE volume mount，尽量保持现有 media filesystem 语义。
- 如果 FUSE 不适合，再评估 Laravel / TastyIgniter media storage adapter。
- 不上传正式图片，不提交真实上传文件。

### Cloud SQL 方案

- 继续 MySQL 优先。
- 只创建 Canada staging DB。
- 不使用 production 数据。
- 优先通过 Cloud Run / Cloud SQL connector 或 private connectivity，避免 public DB host。
- 不运行 `migrate:fresh`、`migrate:refresh` 或 `db:seed`。

### 验收清单

- `/healthz`。
- 首页、菜单页、购物车、预约页。
- Livewire JS。
- 后台登录页和 dashboard。
- admin assets。
- media upload。
- media 重新部署持久性。
- Cloud SQL RTT / dynamic HTML TTFB baseline。
- logs 无 fatal / exception / 500 / storage permission error。
- backup / restore baseline。
- Render staging fallback 可用。

### 安全边界

- 未创建付费资源。
- 未提交 secret。
- 未要求用户把 password、token、APP_KEY、DB_PASSWORD、Google Cloud key、Cloudflare token 发到聊天。
- 未碰 production。
- 未导入真实顾客数据、真实菜单、真实订单、真实预约或真实支付数据。
- 未运行 `migrate:fresh`、`migrate:refresh` 或 `db:seed`。
- 未修改 vendor、TastyIgniter core、订单、支付、预约、认证或安全逻辑。

### 下一步建议

- 用户确认 Google Cloud billing / budget。
- 用户确认 Montréal vs Toronto。
- 用户确认是否允许创建 Cloud Run / Cloud SQL / Cloud Storage / Secret Manager。
- 用户在 Google Cloud UI / Secret Manager 输入 secrets。
- 确认后进入 `Create Google Cloud Canada staging resources`。

## 2026-07-09 - Prepare Cloud Run Canada staging runtime

### Summary

- Added a dedicated Cloud Run staging runtime entry point for the Canada experiment.
- Added `CLOUD_RUN_CANADA_STAGING_RUNTIME.md` with Cloud Run, Cloud SQL, Secret Manager, media storage, Artifact Registry, validation, and rollback checklists.
- Kept Render staging runtime files unchanged so Render remains the fallback.
- Did not create a Cloud Run service.
- Did not create or download a service account key.
- Did not commit secrets or production configuration.

### Files

- `Dockerfile.cloudrun`
- `docker/cloudrun/start.sh`
- `CLOUD_RUN_CANADA_STAGING_RUNTIME.md`
- `ADMIN_CONFIGURATION_TRACKER.md`
- `CHANGELOG_AI.md`
- `RENDER_RUNTIME_READINESS.md`

### Runtime Notes

- Cloud Run uses `$PORT`, defaulting to `8080` only when the platform value is absent.
- Cloud SQL should use the Cloud Run connector socket at `/cloudsql/<INSTANCE_CONNECTION_NAME>`.
- The first Cloud Storage media experiment should mount `le-chateau-canada-staging-media` at `/var/www/html/storage/app/media`.
- The Cloud Run start script avoids recursive ownership changes under the media mount.

### Safety

- Staging only.
- No production changes.
- No real customer, order, reservation, payment, or menu data.
- No destructive database commands.
## 2026-07-09 - Cloud Run Canada staging runtime blocker

Environment: Canada staging only. Status: Blocked pending PR #39.

- Initialized the Canada staging Cloud SQL schema without demo seed data.
- Created a staging-only administrator using a Secret Manager reference; no credential value was recorded.
- Confirmed Livewire JavaScript and static assets were reachable.
- Diagnosed dynamic HTTP 500 responses to a missing Laravel file-cache directory: `storage/framework/cache/data`.
- Prepared PR #39 with the minimal Cloud Run start-script fix.
- Render staging and production were not changed.

Next step: merge PR #39, rebuild and deploy the Cloud Run image, then repeat smoke tests and logs verification.

## 2026-07-10 - Cloud Storage FUSE visibility blocker

Environment: Canada staging only. Status: Blocked pending a focused runtime/configuration PR.

- PR #39 fixed the Laravel file-cache directory; menu, cart, reservation, admin login, and Livewire now return 200.
- Homepage requests still failed when Flysystem attempted to set visibility with chmod on the Cloud Storage FUSE mount.
- Prepared a configuration-only switch, FILESYSTEM_SKIP_VISIBILITY, which defaults to disabled so Render behavior is unchanged.
- /healthz remains separate from this issue because Cloud Run frontend returns 404 before the container.

Next step: merge and deploy the visibility fix, then validate homepage, media upload/persistence, logs, and TTFB.

## 2026-07-10 - Record Cloud Run Canada staging validation

Environment: Canada staging only. Status: Resolved for the PR #40 runtime
issue; `/healthz` remains a separate blocker.

- Deployed git SHA: `44940004`.
- Cloud Build succeeded with `Dockerfile.cloudrun` and pushed the SHA-tagged
  image `northamerica-northeast1-docker.pkg.dev/le-chateau-canada-staging/tastyigniter-staging/tastyigniter:44940004`
  to the Canada Artifact Registry repository. Cloud Build emitted a
  non-blocking Cloud Shell Regional Access Boundary warning; the build itself
  completed successfully.
- Cloud Run revision `le-chateau-canada-staging-00009-tvs` serves 100% of
  traffic at `https://le-chateau-canada-staging-j675sib2hq-nn.a.run.app` in
  `northamerica-northeast1`. The service URL and region remain Canada staging
  only.
- `FILESYSTEM_SKIP_VISIBILITY=true` is configured only on the Cloud Run
  staging service. Existing Cloud SQL, Secret Manager, service account, and
  Cloud Storage mount configuration was preserved.
- Final smoke checks returned HTTP 200 for `/`, `/default/menus`, `/cart`,
  `/default/reservation`, `/admin/login`, and `/livewire/livewire.min.js`.
- Test media `IMG_2484.png` was uploaded to the staging bucket, returned HTTP
  200 with `image/png`, and remained available after the same-image redeploy.
  The object size was 109065 bytes; no image file was committed.
- The `staging-inspector` database user is retained as a Canada staging
  maintenance account only. It is not used as the Cloud Run runtime account or
  in production, and its password is not recorded.
- Final warm HTTP TTFB was approximately 0.66s for `/`, 0.60s for menus,
  0.60s for cart, 0.62s for reservation, 0.39s for admin login, and 0.34s
  for Livewire JavaScript. Compared with the former Render ranges, this is a
  material improvement of roughly 78-92% for the public dynamic pages; the
  result does not by itself isolate PDO connection latency.
- No new FUSE visibility, chmod, storage permission, Laravel cache, Cloud SQL,
  PHP-FPM, Nginx, fatal, exception, or 500 errors were found in the revision
  logs.
- `/healthz` still returns a Google frontend 404 and remains outside this PR.
- Render staging, DigitalOcean resources, and production were not changed.
- Canada staging core runtime readiness is achieved; the independent `/healthz`
  routing issue remains open before treating the service as fully ready.

Next step: document and fix Cloud Run health-check routing separately, then
run an approved PDO/Laravel connection-latency sample before further runtime
optimization.

## 2026-07-10 - Cloud Run health check routing investigation

Environment: Canada staging only. Status: Open pending the focused routing PR.

- In the current Canada staging service, the canonical Cloud Run
  `run.app/healthz` request was observed to return a Google frontend 404 before
  reaching the container. It has no application response headers and does not
  behave like the Laravel response at `/healthz/`.
- The existing exact `/healthz` Nginx rule remains correct for Render and for
  requests that reach the container; adding a Laravel route would not address
  the Cloud Run frontend interception.
- The focused fix adds `/healthz/` as a container-level liveness path while
  preserving Render's existing `/healthz` behavior. Cloud Run probe
  configuration will point to `/healthz/` after the image is deployed.
- No production, Render service, database data, secrets, or business logic was
  changed during the investigation.

## 2026-07-10 - Record Cloud Run health and database latency validation

Environment: Canada staging only. Status: Resolved for the deployed liveness
path and read-only latency measurement; the bare `/healthz` observation remains
open separately.

- Deployed git SHA `2796d2c6` from `Dockerfile.cloudrun` to the Canada Artifact
  Registry image and Cloud Run revision `le-chateau-canada-staging-00010-fh9`.
- Cloud Run liveness probe is `/healthz/`, with 100% traffic on the Ready
  revision. `/healthz/` returned HTTP 200 and appeared in Cloud Run request
  logs. Bare `/healthz` still returns the known Google frontend 404; Render's
  health path was not changed.
- Smoke checks returned HTTP 200 for `/`, `/default/menus`, `/cart`,
  `/default/reservation`, `/admin/login`, and `/livewire/livewire.min.js`.
  The existing staging test media object remained HTTP 200 and `image/png`
  after redeploy.
- Read-only sampling measured: PDO new connection average/p50/max
  `140.85/16.81/349.62 ms`; PDO same connection `select 1`
  `2.23/2.20/2.60 ms`; Laravel reconnect first `select 1`
  `147.87/24.28/356.22 ms`; Laravel same connection `select 1`
  `4.33/4.32/5.11 ms`.
- Compared with the recorded Render averages (328/81/651/162 ms), the
  approximate reductions are 57%, 97%, 77%, and 97%, respectively. These are
  staging samples, not a production SLO.
- The one-time latency Job was deleted after the sample. No migration, seed,
  order, reservation, or other database write was performed. No secret,
  binding, SQL value, or credential was recorded.
- Render staging and DigitalOcean fallback resources remain available.

Next step: repeat authenticated dashboard verification, then handle the bare
Cloud Run `/healthz` routing observation as a separate focused task before
production planning.

## 2026-07-10 - Record Canada staging dashboard acceptance and reservation audit

Environment: Canada staging only. Status: Dashboard acceptance resolved;
reservation requirements audit pending business confirmation.

- Reused the authenticated Canada staging browser session and verified the
  dashboard, Orders, Reservations, Categories, Menus, Media Manager, Settings,
  Extensions, and Staff members pages.
- Pages rendered successfully with no browser console errors, no same-origin
  asset failures, and no localhost or old Render asset URLs. A repeated
  non-blocking `Broadcast is not defined` console warning remains.
- The Cloud Run liveness path remains `/healthz/` with the bare `/healthz`
  Google frontend 404 documented separately. Render staging remains the
  fallback and production was not changed.
- Audited the public reservation form without submitting it. The observed flow
  starts with date, guest count, and time selection followed by `Find Table`.
- Recorded the current backend reservation configuration: reservations and
  automatic table assignment enabled; 15-minute interval; 45-minute stay;
  guest range 2-20; advance window 2-30 days; guest-count limiting disabled;
  cancellation timeout 0; start-time inclusion enabled; location schedule
  24/7; global reservation email/status mappings available.
- No reservation, order, customer, menu, payment, mail, or settings write was
  performed. No runtime code was modified.

Next step: wait for explicit business requirements before proposing a focused
reservation PR. Do not change reservation conflict logic or core/vendor code
without a separately confirmed scope.
## 2026-07-10 - Implement Birthday reservation rules

Environment: local build only; Canada staging not deployed. Status: Pending PR
review.

- Added an app-level Birthday Booking rules slice behind
  `BIRTHDAY_BOOKING_RULES_ENABLED=false` by default.
- Added centralized fixed slots `12:00-16:00` and `16:00-20:00`, venue-local
  plus-2 through plus-60 date validation, and a custom reservation flow that
  displays only those slots before reusing the existing customer form/save
  path.
- Added explicit `birthday_booking`, `birthday_slot_code`, and
  `birthday_slot_key` fields to the existing reservations schema with a unique
  location/key index. Occupancy uses the
  configured default and confirmed status IDs; non-occupying statuses clear the
  key while preserving the reservation record.
- Added server-side availability and conflict checks, a marker-scoped
  table-independent Birthday reservation guard, admin marker field, and an
  additive rollback migration. Status-only maintenance skips the creation date
  window, and unique-key conflicts are converted to validation responses.
- No vendor/core, order, payment, authentication, security, notification,
  takeout, production, or business-data changes were made.
- Validation: all new PHP files linted, Dockerfile.cloudrun built successfully,
  config cache succeeded, and Birthday rules tests passed with 7 tests and 15
  assertions. Staging migration and concurrency testing remain pending.

Next step: review and merge the implementation PR, then enable the flag only on
Canada staging and execute migration, backend conflict, frontend, and smoke
tests. Do not start payment, registration, or add-on work yet.

## 2026-07-11 - Birthday reservation rules staging validation

Environment: Canada staging only. Status: Service-side Birthday rule
validation resolved; end-to-end browser submission remains pending because the
telephone-widget blocked the form.

- Synchronized merged PR #48 at SHA `0a19c37f`, built with
  `Dockerfile.cloudrun`, and deployed revision
  `le-chateau-canada-staging-00014-2kd` in Montréal. No `.env`, secret, or real
  data was included.
- `/healthz/`, homepage, menus, cart, reservation, admin login, Livewire, and
  authenticated admin pages returned successfully. Recent Cloud Run error
  filtering found no new fatal, exception, 500, storage permission, FUSE, or
  cache-directory errors.
- Staging-only service-side QA verified the two fixed slots, Toronto date
  window (+2 through +60), positive guest compatibility, occupancy states, and
  cancellation release. In the two-task concurrent execution, one claim
  succeeded and one lost with the expected unique-conflict path; the final
  occupying-row count was one. No SQL bindings or index details were exposed.
  The browser-facing readable business validation message was not separately
  exercised because the telephone widget blocked form submission.
- Synthetic records were cleaned up and all temporary Birthday QA Jobs were
  deleted. No test order, real customer data, payment, mail, or production
  operation was used.
- Browser slot selection/display passed. Submission was blocked by the
  existing telephone widget rejecting the synthetic number; no browser-created
  reservation remains.
- PR #45 was closed as superseded by PR #46 and subsequent staging
  fixes/validation.

Next step: merge the documentation record PR, then handle the telephone input
widget as an independent small staging-only task if required.

## 2026-07-12 - Fix Birthday reservation telephone input

Environment: local build only; Canada staging not deployed. Status: Pending PR
review.

- Replaced the Birthday flow's dependency on the legacy telephone picker with
  a scoped native telephone field and lightweight client-side formatting.
- Added app-level server validation/normalization for Canada/US NANP numbers,
  including national, punctuation-separated, and `+1` forms. Normalized
  values use the `+15145550100`-style representation without duplicating the
  country code.
- Added focused unit coverage for accepted formats, normalization, optional
  empty values, and invalid/non-NANP inputs.
- No vendor/core, standard reservation, payment, order, authentication,
  notification, production, or real-data changes were made. Render staging
  remains unchanged as fallback.

Next step: review and merge the implementation PR, then build/deploy Canada
staging and complete the browser-only synthetic Birthday reservation test.

## 2026-07-12 - Record Birthday browser submission validation

Environment: Canada staging only. Status: Resolved for the PR #50 validation
gate.

- Built the merged PR #50 image from SHA `fceead8b` and deployed it to Cloud
  Run revision `le-chateau-canada-staging-00015-vnj`; the staging-only log
  mailer configuration was subsequently deployed as revision
  `le-chateau-canada-staging-00016-2tj`.
- Rechecked `/healthz/`, the homepage, menus, cart, reservation entry, admin
  login, and Livewire JavaScript; all returned HTTP 200. Retained test media
  `IMG_2484.png` remained HTTP 200 with `image/png`. No localhost or old Render
  URL appeared in the tested HTML.
- Completed one synthetic browser Birthday reservation. The telephone input
  accepted the test NANP value and server normalization produced the expected
  E.164-style value. The success page and authenticated admin record were
  both verified. The fixed slot was unavailable while Pending, was released
  after Canceled, and the synthetic record was deleted through the admin UI.
- Browser reservation logs had no error entries. Cloud Run error filtering
  found no new fatal, exception, 500, SQL, cache-directory, FUSE, or storage
  permission errors. Existing dashboard Broadcast/configuration warnings are
  recorded as non-blocking.
- No code, migration, vendor, core, production, payment, order, customer,
  notification, or real-data change was made in this documentation update.
  Render staging remains the fallback.

Next step: review this documentation-only validation PR, then continue with
small, explicitly scoped Birthday reservation enhancements. Do not begin
payment, registration, add-ons, or production planning from this record.

## 2026-07-12 - Design shared payment infrastructure and Birthday checkout

Environment: docs/local audit only. Status: Design pending business decisions.

- Synchronized merged PR #51 at
  `45730125929ee8afc5f5cde5cf8a8f7ac867d9c4` and created a design branch from
  current `4.x`.
- Audited the actual installed TastyIgniter Order checkout and PayRegister
  implementation, including gateway registration, Payment/PaymentLog/
  PaymentProfile models, Stripe PaymentIntent/Checkout Session behavior,
  signed webhook handling, delayed webhook job, refund form, Order status
  transitions, Reservation model, and the custom Birthday flow.
- Design conclusion: keep existing Order checkout separate, introduce an
  app-owned polymorphic payable boundary and payment transaction/event/refund
  records, and keep Birthday Booking/slot holds separate from payment and
  Reservation status. Do not repurpose the existing gateway configuration
  `payments` table or Order-only `payment_logs` table.
- Documented idempotency, webhook replay protection, raw-body/signature
  handling, PCI boundary, price snapshots, hold lifecycle, state mapping,
  registration and notification boundaries, refund research, test matrix,
  rollback, and open business choices.
- No PaymentIntent, gateway account, webhook endpoint, secret, migration, code,
  vendor/core change, test order, test reservation, real email, or production
  operation was performed.

Next step: review and merge the documentation-only design PR, then confirm the
open business decisions before creating any implementation PR. No payment
secret is required for this review stage.

## 2026-07-12 - Refine PR #52 payment exception and delivery order design

Environment: docs/local audit only. Status: Design update pending review.

- Added the exception path where a signed webhook verifies payment success but
  the Birthday hold is expired, missing, or reclaimed. The payment remains
  `succeeded`; Booking moves to `payment_exception`/`manual_review`; no valid
  slot is overwritten; a safe reconciliation reason and internal alert are
  required; manual recovery chooses refund or alternate-slot coordination.
- Added this path to the hold lifecycle, state model, payment/webhook sequence,
  webhook idempotency, admin requirements, rollback/reconciliation guidance,
  and integration tests. Added tests for expiry, reclaim, duplicate webhook
  during review, refund retry, and no automatic confirmation.
- Changed the recommended order to A -> B -> C -> D -> F -> E -> G. E before F
  is allowed only as an internal fake-gateway harness with no public customer
  checkout or payment page.
- Added a 20-item Risk and Mitigation Matrix with failure mode, mitigation,
  automated test, staging gate, and rollback/manual recovery.
- This is still a documentation-only update. No runtime code, migration,
  vendor/core, gateway, webhook, secret, production, order, reservation,
  notification, or real-data operation was performed.

## 2026-07-17 - Address PR #53 Birthday catalog review blockers

Environment: local Docker/MySQL validation only. Status: Pending renewed PR
review; no staging deployment or migration execution.

- Converted `abandon.birthday` to a root-required Composer path package,
  committed `composer.lock`, pinned Composer's PHP platform to 8.3, and made
  both runtime Dockerfiles copy the extension manifest before dependency
  installation. Clean Composer install, TastyIgniter discovery, and admin route
  registration now have executable coverage.
- Moved default-package switching out of model side effects and into the
  package service transaction. Candidate rows are locked in deterministic
  primary-key order, the unique `default_guard` remains the database invariant,
  and duplicate-key races become a field-level validation error without SQL
  details.
- Added the shared overflow-safe Birthday price rule with an exact CAD maximum
  of `42949672.95`, then applied it to both admin request classes and service
  saves. Maximum, maximum-plus-one, and huge inputs are tested.
- Corrected FormController integration for package save, archived-record
  lookup, archive/restore, and archived-list filtering. Added functional tests
  for extension discovery, schema/indexes, service behavior, permissions,
  admin create/edit/archive/filter/restore, oversized inputs, and a true
  two-process concurrency barrier.
- Validation passed on PHP 8.3 and MySQL 8.4: Pint on 21 files, PHP syntax
  checks, 39 focused tests with 173 assertions, additive extension migration
  down/up, config/route/view cache commands, two Birthday admin routes,
  Composer strict validation, and a clean `Dockerfile.cloudrun` build. The test
  harness still reports one existing PHPUnit deprecation.
- A full empty-database `igniter:up` remains affected by the pre-existing root
  Birthday reservation migration running before the Reservation extension
  creates its table. PR #53 does not alter that unrelated migration; catalog
  migration validation used a dependency-ordered disposable base schema.
- No payment, webhook, slot hold, Booking, registration, Reservation/Order
  logic, vendor/core, secret, staging data, production, or real-data operation
  was performed. Render and DigitalOcean fallback resources are unchanged.

Next step: request renewed review of Draft PR #53. Do not merge, deploy, or run
the Canada staging migration until the review gate is approved.

## 2026-07-17 - Deploy PR #53 and isolate Birthday navigation label regression

Environment: Canada staging. Status: Deployment and catalog behavior passed;
focused navigation-label fix pending review.

- Built full-SHA image
  `northamerica-northeast1-docker.pkg.dev/le-chateau-canada-staging/tastyigniter-staging/tastyigniter:ac20afa4853694d5fe4572492e55baf12a694035`
  with Cloud Build `aec22814-3f81-46f7-a74c-12aaa7edff7d`. Composer package,
  TastyIgniter discovery, and route build gates passed before deployment.
- Deployed Ready revision `le-chateau-canada-staging-00017-t64` at 100% traffic,
  preserving the prior revision and all service account, Cloud SQL, Secret
  Manager, Cloud Storage, health-check, scaling, and staging-only settings.
- A read-only preflight confirmed the existing Canada schema and no pending
  root migration. The additive Birthday catalog migration completed, and
  postflight checks confirmed catalog schema/indexes/migration tracking while
  existing business-table structures remained unchanged.
- Browser QA covered package/add-on CRUD, exact CAD maximum `42949672.95`,
  invalid and oversized values, integer minor units, deterministic default
  switching, default archival, archive filters, restore, enabled state, and no
  quantity fields. The existing Birthday reservation UI retained its two fixed
  slots and Toronto +2/+60 date window; packages/add-ons were not exposed on
  the public flow.
- Public/admin pages, `/healthz/`, Livewire, retained media, browser console,
  and Cloud Run error/5xx checks passed. Three QA packages and three QA add-ons
  were deleted; temporary preflight, migration, read-only QA, and cleanup jobs
  were deleted. No Order, Reservation, Payment, Customer, real data, secret,
  Render fallback, DigitalOcean fallback, or production change was made.
- Staging exposed one focused regression: Restaurant navigation displayed the
  raw `abandon.birthday` translation keys. The project extension must resolve
  those two titles with `lang(...)`; the runtime container was not hotfixed.

Next step: merge and redeploy the independent navigation-label fix, verify the
labels, then record final Birthday catalog staging acceptance in a pure
documentation PR.

## 2026-07-17 - Finalize Birthday catalog on Canada staging

Environment: Canada staging. Status: Runtime validation resolved; final
documentation PR pending review.

- Confirmed PR #53 merge SHA `ac20afa4853694d5fe4572492e55baf12a694035`
  and its initial Cloud Build `aec22814-3f81-46f7-a74c-12aaa7edff7d` / Ready
  revision `le-chateau-canada-staging-00017-t64` catalog deployment.
- Built PR #54 merge SHA `53960a9e705b271823e375056c2bfce93dcc95d1`
  as
  `northamerica-northeast1-docker.pkg.dev/le-chateau-canada-staging/tastyigniter-staging/tastyigniter:53960a9e705b271823e375056c2bfce93dcc95d1`
  with Cloud Build `50de4c46-c51e-4cd4-86c0-723ce7d712f7`. Composer package
  discovery, TastyIgniter package discovery, and both Birthday admin route
  gates passed.
- Created revision `le-chateau-canada-staging-00018-neb` at 0% traffic, verified
  the tagged revision returned `200 ok` from `/healthz/`, then routed 100% of
  Canada staging traffic to it. The before/after runtime configuration
  fingerprint matched; `00017-t64` and `00016-2tj` remain Ready rollback
  revisions.
- Browser acceptance confirmed resolved `Birthday Packages` and
  `Birthday Add-ons` navigation labels, exact list links, no raw translation
  keys or duplicate entries, working list/create pages, CAD fields, Archived
  filters, no quantity input, and no destructive Delete action. Previous PR
  #53 CRUD, price-boundary, default-switching, archive/restore, enabled-state,
  and permission-isolation results remain the catalog behavior baseline.
- No migration was run. A disposable read-only Job using the application
  runtime account confirmed both catalog tables and the extension migration
  record remain present; the Job was deleted. No schema or catalog data changed.
- `/healthz/`, homepage, menus, cart, reservation entry, admin login and core
  admin pages, Livewire JavaScript, and retained media returned successfully.
  The Birthday form retained two fixed slots, the Toronto plus-2/plus-60 date
  bounds, and telephone input without exposing packages, add-ons, prices,
  Booking, hold, or payment UI. Browser error-level console entries were zero.
- Current-revision log audit covered 275 entries: error severity, HTTP 5xx,
  fatal, unhandled exception, translation/language, package discovery, route,
  Cloud SQL, FUSE/storage/cache permission, and permission-trace counts were
  all zero. Three `/healthz/` requests were observed as container HTTP 200.
- The previously removed three QA packages, three QA add-ons, and four
  validation Jobs remain absent; this phase left no temporary Job or business
  record. Render, DigitalOcean, production, real data, payment, notification,
  secrets, and service-account keys were unchanged.

The pre-existing fresh-install migration ordering issue remains separately
tracked and does not block the already-initialized Canada staging database.
Next step: review and merge this documentation-only record before starting the
separate Birthday Booking domain and immutable price snapshot phase.

## 2026-07-17 - Implement Birthday Booking immutable catalog snapshots

Environment: local Docker/MySQL validation only. Status: Draft PR pending
review; no staging deployment or production change.

- Added additive `birthday_bookings` and `birthday_booking_addons` schema,
  domain models, `catalog_priced` / terminal `cancelled` status constants, and
  model-level immutable-history protections. Current Customer and Location
  primary-key types were verified against the final TastyIgniter schema before
  adding restrictive foreign keys; migration down removes child then parent.
- Added `BirthdayBookingService` and `BirthdayPricingSnapshotService`. One
  transaction validates persisted Customer/Location records, Toronto date and
  fixed slot rules, informational guest count, contact email/telephone, the
  sole available CAD default package, and selected available CAD add-ons. It
  locks catalog rows in deterministic order, computes UTC times, uses
  overflow-checked integer minor-unit addition, and rolls back Booking plus all
  add-on snapshots on any failure.
- Persisted snapshots include contact details, package text/included items,
  selected add-on text/order, source IDs, CAD prices, and pricing version 1.
  Later catalog/customer edits or archives do not change history. No quantity,
  tax, discount, payment fee, tip, or final payable amount is represented;
  `catalog_subtotal_minor` means only package plus selected add-ons.
- Added read-only Birthday Booking admin list/detail pages and the independent
  `Admin.BirthdayBookings` permission. Navigation resolves through `lang()`;
  pages display snapshot values and CAD formatting and expose no create, edit,
  delete, Reservation, confirmation, or collection action.
- Added coverage for valid/invalid package and add-on states, duplicate IDs,
  empty add-ons, immutable history, cancellation, invalid state transitions,
  customer/contact snapshots, Toronto DST and date bounds, both slots,
  same-slot non-occupancy, unrelated-object non-creation, forced partial-row
  rollback, schema/index scope, admin snapshot rendering, permissions, and
  hidden mutation actions. Existing Birthday catalog/rules/concurrency/admin
  regressions also passed in the isolated MySQL schema.
- Validation passed for migration up/down/up, PHP 8.3 syntax, Pint, Composer
  strict validation, config/route/view caches, extension/admin route discovery,
  and clean `Dockerfile.cloudrun` and `Dockerfile.render` builds. Existing npm
  dependency notices and the PHPUnit XML deprecation remain non-blocking and
  were not introduced here.
- Direct query-builder updates bypass Eloquent model events; this is an
  explicit unsupported boundary, not a database-trigger guarantee. Application
  code must create/cancel through the Booking service and must never bulk-edit
  snapshot tables.
- No slot hold, availability lock, payment, webhook, Reservation, Order,
  registration/public-flow integration, tax, coupon, notification, real data,
  secret, Canada staging migration/deployment, or production operation is
  included. Render and DigitalOcean remain available unchanged fallbacks.

The pre-existing full fresh-install migration ordering issue remains a
separate tracked problem; this work used a dependency-ordered isolated schema
and does not modify that root migration. Next step: review the Draft PR, then
merge and perform Canada staging migration/acceptance before starting the
separate 15-minute slot-hold phase.

## 2026-07-17 - Isolate Birthday Booking snapshot hydration blockers

Environment: Canada staging and local Docker/MySQL. Status: Fix PR pending;
Canada staging traffic safely rolled back.

- Confirmed PR #56 merge SHA `8b6ba92c9f27b27e1479f91121379dedfc2e230c`,
  built its full-SHA Cloud Run image, and deployed Ready revision
  `le-chateau-canada-staging-00021-fom`. The additive Birthday Booking
  migration ran successfully once and produced only the expected Booking and
  add-on snapshot schema.
- Public/admin/static/media smoke checks and current-revision error-log audit
  passed. An isolated application-account QA then verified Booking identity,
  catalog status, CAD package/add-on snapshots, integer minor-unit totals,
  pricing version, and intentional same-slot coexistence.
- The QA database round trip revealed Q-007: Eloquent's default `datetime`
  cast treated stored UTC values as Toronto-local time after hydration. The
  record therefore represented an instant four hours later during DST even
  though the pre-persistence service object was correct.
- Stopped the validation immediately, removed all synthetic QA rows, confirmed
  Reservation/Order/Payment/payment-log baselines were unchanged, deleted the
  disposable migration and QA Jobs, and returned 100% traffic to accepted
  revision `le-chateau-canada-staging-00018-neb`. The additive migration was
  retained; no destructive rollback command ran.
- Added an extension-owned `UtcDateTime` cast for `starts_at`, `ends_at`,
  `priced_at`, and `cancelled_at`, plus a Toronto-application-timezone
  persistence/reload regression test. The cast serializes UTC explicitly and
  rejects malformed hydrated values instead of silently changing instants.
- Focused MySQL testing also isolated Q-008: snapshot inserts followed catalog
  order, but the relation reload had no explicit ordering and could follow the
  source-ID unique index instead. Added deterministic hydration by immutable
  `sort_order_snapshot` and snapshot row ID; the existing stable-order service
  regression covers the behavior.
- PHP syntax, Pint, strict Composer validation, a real MySQL/Eloquent UTC
  round-trip check, and clean Cloud Run and Render image builds passed
  locally. The dependency-ordered MySQL 8.4 schema ran all 13 focused Booking
  service tests with 112 assertions, zero errors, and zero failures; the known
  PHPUnit XML deprecation remains non-blocking.
- No slot hold, availability change, payment, webhook, public checkout,
  production operation, secret, real data, or core/vendor modification is
  included. Render and DigitalOcean remain unchanged fallbacks.

Next step: finish the focused regression checks and publish the independent
snapshot hydration PR. After merge, redeploy Canada staging and resume PR #56
snapshot validation; do not start slot-hold work first.

## 2026-07-17 - Validate Birthday Booking snapshots on Canada staging

Environment: Canada staging only. Status: Runtime acceptance complete;
documentation PR pending review.

- Built PR #57 merge SHA `e2ca19d4407064bb9d34d4fe8fe947cd1624c5c2`
  with Cloud Build `2392136c-e2ce-44d7-bced-7b33450958cb` and deployed the
  full-SHA Artifact Registry image as Ready revision
  `le-chateau-canada-staging-00024-dof`. Its tagged `/healthz/` returned 200,
  its runtime fingerprint matched the accepted service configuration, and it
  received 100% traffic. Revisions `00018-neb` and `00021-fom` were retained.
- Did not rerun any migration. A disposable read-only application-account Job
  confirmed the two Birthday Booking tables and the single PR #56 migration
  record, no hold table, no residual QA, and unchanged business baselines.
- Synthetic service-level QA resolved Q-007: persisted summer and standard-time
  start/end values, plus priced/cancelled instants, reloaded in UTC and mapped
  back to Toronto `12:00-16:00` without a second offset.
- Synthetic QA resolved Q-008: an add-on created first with sort 20 reloaded
  after the later-created sort-10 add-on. The relationship and admin detail
  rendered `First`, then `Late`, using snapshot sort order and snapshot-row ID
  as the deterministic tie-break.
- Verified immutable contact/catalog snapshots, 100/75/175-cent CAD subtotals,
  pricing version 1, public-ID uniqueness, model update/delete protection,
  terminal cancellation, date/slot validation, invalid-catalog rollback, and
  forced partial-snapshot rollback.
- Same-location/date/slot catalog-priced Bookings were both accepted as
  designed. No Reservation, Order, Payment, Payment Log, or slot hold was
  created, and the public reservation availability model was not changed.
- Authenticated browser acceptance passed for the Birthday Booking list/detail
  and core admin pages. Historical snapshots remained read-only; browser
  error-level console entries were zero. Public pages, Livewire, retained
  media, and current-revision logs also passed with zero HTTP 5xx or matching
  fatal/UTC/SQL/FUSE/cache errors.
- Removed every synthetic Booking snapshot, Booking, Customer, package, and
  add-on, then deleted all disposable Jobs. Render and DigitalOcean remained
  unchanged fallbacks; production, secrets, real data, payment, and outbound
  notifications were not touched.

The local Windows host does not provide PHP, and PR #57 has no configured
GitHub checks, so no redundant local PHPUnit run was claimed in this phase.
The real Cloud Run/Cloud SQL QA above is the staging acceptance evidence; the
focused MySQL tests recorded on PR #57 remain the automated regression evidence.

Known separate issue: full fresh-install migration ordering remains outside
scope. Next step is review and merge of this documentation-only record, then a
separately approved 15-minute slot-hold phase.

## 2026-07-18 - Implemented 15-minute Birthday slot holds

Environment: local isolated Docker/MySQL 8.4.10. Status: Pending PR review; not
deployed or migrated on Canada staging.

- Added the extension-owned hold migration, status/model/domain exceptions,
  transactional hold service, optional expiry command, and read-only Birthday
  Booking admin integration.
- Enforced a fixed 900-second UTC lifetime, exact `expires_at <= now` expiry,
  one reusable row per location/date/slot, unique public IDs, owner-only
  release, and no renewal for repeated acquisition by the same Booking.
- Combined row locks, database unique constraints, atomic row reuse, and three
  finite retries for duplicate/deadlock/lock-timeout races. Competing Bookings
  receive a non-sensitive availability error; cancellation and hold release
  share one transaction and roll back together.
- Kept Birthday Booking creation non-occupying and rejected hold acquisition
  for cancelled or otherwise ineligible Bookings. No scheduler is required for
  correctness; the cleanup command only marks elapsed active rows expired.
- Added service, migration, real-process concurrency, Booking regression, and
  admin read-only tests. The full Birthday suite passed: 68 tests and 451
  assertions on MySQL 8.4.10. Migration up/down/up passed without removing the
  Birthday Booking table.
- Composer strict validation, PHP syntax checks, Pint, config/route/view cache
  generation, and clean no-cache `Dockerfile.cloudrun` and
  `Dockerfile.render` builds passed. Existing npm and PHPUnit configuration
  deprecation warnings remain non-blocking.
- Did not deploy, migrate, or write Canada staging. Did not change Render,
  DigitalOcean, production, payment, Reservation, Order, authentication,
  public Birthday checkout, vendor, or TastyIgniter core. No secret, `.env`,
  database dump, upload, or real customer/business data is included.

Next step: review and merge the slot-hold PR, then run an explicitly controlled
Canada staging additive migration/deployment and Cloud SQL concurrency/admin
acceptance. The separate fresh-install migration-ordering issue remains open.

## 2026-07-18 - Validate PR #60 Birthday slot-hold display on Canada staging

- Built PR #60 merge SHA `f1d5dc9c8a576e81b8f72f618080e1efb09db6b9`
  with Cloud Build `c41097bb-06b6-45a3-a4ff-a10b5405ff73`. Artifact Registry
  image digest:
  `sha256:4e56e80eaa8c9dcd704ea5ea67255e320817d2c457c064d5893801b289c5ec6c`.
- Deployed Ready revision `le-chateau-canada-staging-slot60-f1d5dc9c`, first
  at 0% with a tagged `/healthz/` check, then at 100% traffic. The accepted
  runtime configuration remained in place; PR #59 and PR #57 revisions remain
  rollback targets.
- Did not run a migration. Read-only Cloud SQL checks confirmed the existing
  hold migration record, schema, required indexes and restrictive foreign
  keys. The service/migration/concurrency diff from PR #59 was empty.
- Verified active, released, expired before cleanup, expired after cleanup,
  and no-hold admin list/detail states. Null timestamps are now blank, non-null
  timestamps remain UTC, raw translation/null values were absent, and the
  pages remained read-only.
- Rechecked 900-second rows, the optional cleanup command, authenticated admin
  pages, public pages, Livewire, and retained media. PR #59 remains the source
  of the completed no-renewal, concurrency, reclaim, release, and cancellation
  acceptance.
- Removed all exact-ID synthetic holds, Bookings, snapshots, Customer, and
  catalog rows. Reservations=0, Orders=0, Payments=6, Payment Logs=0 after
  cleanup. Deleted every temporary PR #60 Job.
- Current revision logs had zero error-severity entries and HTTP 5xx. Reviewed
  cache/FUSE keyword matches were INFO-only startup configuration. No secret,
  real data, payment, notification, Render/DigitalOcean, or production change
  occurred.
- The complete local feature suite was not rerun because PHP is unavailable on
  the host; focused PR tests were not changed and live staging acceptance
  passed. Fresh-install migration ordering remains a separate known issue.

Next step: review this documentation-only validation PR. Do not begin payment,
registration, public checkout, webhook, or production work until a separately
approved phase.

## 2026-07-18 - Implemented Delivery feature flag and server-side gate

Environment: local isolated Docker/PHP 8.3/MySQL 8.4. Status: Pending PR
review; not deployed.

- Added a fail-closed `DELIVERY_ENABLED` project flag with config-cache-safe
  runtime access and explicit boolean parsing. The default and invalid-value
  behavior is false.
- Added an app-owned Delivery availability gate and Location behavior override
  so active order types require both the global flag and existing Location
  Delivery setting. Pickup/Collection remains available independently.
- Added passive server-side stale-session normalization that clears only
  temporary Delivery location/area/timeslot state and preserves cart, Birthday,
  and Reservation data. If Delivery and Collection are both unavailable, the
  global web middleware clears the invalid order type without blocking
  unrelated pages. Missing Location setup remains handled by the upstream
  installation/location flow.
- Added strict server guards for fulfillment changes, cart validation, checkout
  final save, and Orders API writes. The API rejects all Delivery
  creates/updates until it can reuse complete storefront address, area,
  minimum, fee, and totals validation. A disabled Pickup selection is cleared
  and rejected, while enabled Pickup API writes and historical Delivery reads
  remain unchanged.
- Added unit, feature, HTTP storefront, checkout, and full Orders API tests.
  Test Order writes use synthetic values and database transactions only. The
  MySQL integration environment is isolated and does not contain staging or
  production data.
- Delivery coverage passed 48 tests and 124 assertions on PHP 8.3 and MySQL
  8.4.10. Coverage includes real homepage, Birthday, Reservation-account,
  login, and content routes with both fulfillment methods disabled, as well as
  strict order-type, cart, checkout, and Orders API failures. All 78 previously
  passing Birthday regression cases remain unchanged; the existing
  migration-inspection test that assumes an unprefixed schema continues to use
  its matching no-prefix database rather than changing unrelated test code.
- PHP syntax, scoped Pint, Composer strict validation, Laravel config/route/view
  cache generation, and clean no-cache Cloud Run and Render Docker builds
  passed. Only the existing PHPUnit XML and npm dependency deprecation warnings
  remain.
- No migration, schema, Delivery Area, fee, hours, storefront UI, homepage,
  payment, Birthday, Reservation, production, Render, DigitalOcean, vendor, or
  TastyIgniter core change is included. `.codex-tmp/` is excluded from Docker
  build contexts and remains outside version control.

Next step: review and merge the feature PR, then deploy separately to Canada
staging with `DELIVERY_ENABLED=false` for closed-state acceptance. D2 UI and D3
Delivery business parameters remain separately scoped, as does the known
fresh-install migration-ordering issue.

## 2026-07-18 - Validated Delivery D1 on Canada staging

Environment: Canada staging only. Status: Runtime acceptance complete;
documentation PR pending review.

- Built PR #62 merge SHA `6a1ccc1d95e25050abe13e36377a38db7c80e438`
  with Cloud Build `48710aec-3904-46a5-8842-0e8d1aa5a719`. Artifact Registry
  image
  `northamerica-northeast1-docker.pkg.dev/le-chateau-canada-staging/tastyigniter-staging/tastyigniter:6a1ccc1d95e25050abe13e36377a38db7c80e438`
  has digest
  `sha256:724e82849b6fd8d5befd27d213823e78a271349e53ac32758b9cadd4fe772095`.
- Deployed Ready revision `le-chateau-canada-staging-d1-6a1ccc1d`, validated
  tagged `/healthz/` at 0% traffic, then assigned 100% traffic. Runtime
  fingerprint `0f5d7552c062fac4` preserved the Cloud SQL, Secret Manager,
  Cloud Storage, service-account, liveness, resource, scaling, and ingress
  configuration.
- Explicit `DELIVERY_ENABLED=false` survived config caching. The Location's
  Delivery and Collection flags stayed true and Delivery Areas stayed empty;
  active fulfillment exposed only Collection/Pickup. No migration ran.
- Read-only/runtime QA verified stale Delivery-session fallback, targeted
  Delivery-state cleanup, preservation of cart/Birthday/Reservation state,
  safe order-type/cart/checkout failures, and unchanged business baselines.
- Orders API QA returned 422 for Delivery create and Pickup-to-Delivery
  update, while synthetic Pickup create/read/update returned 201/200/200.
  Cleanup removed the exact QA Order, status history, API token, and API user,
  with no Customer or Payment write. The database contained no historical
  Delivery fixture, so live history read was not applicable; PR #62 tests
  retain coverage for unchanged historical reads.
- Browser Pickup regression passed menu access, add, quantity increase,
  decrease, removal, and checkout-page access. Totals contained no Delivery
  fee, no Delivery address was required, no final Order was submitted, and the
  browser cart was cleaned.
- Public/admin routes, Livewire, static assets, and test media passed. Final
  log audit recorded zero error-severity entries, HTTP 5xx, unexpected 422s,
  or fatal/Cloud SQL/FUSE/cache failures; two Delivery API 422s were expected.
  Both disposable D1 Jobs were deleted.
- Render and DigitalOcean remain unchanged fallbacks. Production, secrets,
  real data, payment, mail, and domain configuration were not touched.

Next step: review and merge this documentation-only validation PR. Canada
staging must remain `DELIVERY_ENABLED=false`; D2 UI restoration, D3 business
parameters, and fresh-install migration ordering remain separate phases.

## 2026-07-18 - Restored conditional Delivery storefront presentation

Environment: local isolated Docker/PHP 8.3/MySQL 8.4. Status: Pending PR
review; not deployed.

- Registered a project-owned Orange local-search Livewire component that
  reuses upstream search behavior but checks the D1 Delivery gate before and
  after every search. The homepage conditionally renders the address form while
  always preserving the applicable Pickup and Birthday entry points.
- Updated missing-order-type normalization so Pickup is preferred when both
  methods are available, a valid Delivery session is preserved, and
  Delivery-only Locations still receive a usable default.
- Added project Orange overrides for fulfillment schedule tabs and timeslot
  rendering. Closed methods are removed from the DOM, tab state remains
  accessible, Alpine tuple scoping survives Livewire morphs, and map setup is
  skipped unless a real map target and finite coordinates exist.
- Added exact `en_CA` and `fr_CA` critical UI copy plus Livewire, Blade, gate,
  session, accessibility, and no-fulfillment tests. Local desktop/mobile browser
  acceptance verified both flag states and Pickup/Delivery/Pickup switching
  with no console errors after the same asset-publish step used by deployment
  images.
- Isolated PHP 8.3/MySQL 8.4 verification passed 63 Delivery tests with 190
  assertions and 78 Birthday tests with 520 assertions. PHP syntax, Pint,
  strict Composer validation, config/route/view cache generation, and fresh
  no-cache Cloud Run and Render image builds passed. Build output contained
  only existing npm dependency deprecation notices.
- No vendor/core, migration/schema, Delivery Area, fee, minimum, hours,
  checkout totals, Orders API, Birthday, Reservation, staging runtime,
  production, payment, secret, real address, geocoding, or real Order change is
  included.

Canada staging remains `DELIVERY_ENABLED=false` with zero Delivery Areas, and
Render/DigitalOcean remain unchanged fallbacks. Next step: review and merge the
D2 PR, deploy it separately with Delivery still closed, and complete
Pickup-only runtime acceptance before any D3 business-parameter configuration.

## 2026-07-18 - Fixed Q-009 Pickup checkout Delivery instructions

Environment: Canada staging deployment investigation and local isolated
Docker. Status: Fix PR pending review; Canada staging rolled back to D1.

- Built PR #64 merge SHA `fab804d276f60a05548039f7d39b45a2585ff912`
  with Cloud Build `ad91ad49-dbac-4fd9-8eb1-40c24e0c9970`; image digest
  `sha256:cab7dcaef031e071e8dad086075e81d5bd8e5299dac0ed6bfe81e793a7cfeefc`
  deployed as Ready revision `le-chateau-canada-staging-d2-fab804d2`.
- Tagged 0% validation passed health, startup logs, direct D2 assets, generated
  Laravel config cache, and read-only database baselines. Delivery remained
  explicitly and effectively false, Location Delivery/Collection settings
  stayed enabled, Delivery Areas stayed 0, and no migration ran.
- Temporary browser acceptance at 100% passed the Pickup-only homepage, menu,
  Birthday CTA, schedule tabs, cart quantity changes/removal/re-addition,
  fee-free totals, DOM focusability, overflow, and console checks. It then
  exposed Q-009: Pickup checkout rendered the upstream driver-note textarea.
- Stopped before Order submission, emptied the browser cart, deleted the
  disposable Job, and restored D1 revision
  `le-chateau-canada-staging-d1-6a1ccc1d` to 100% traffic. No Order, Customer,
  Payment, address, Delivery Area, or real data was written.
- Added a project-owned Orange checkout partial that skips only
  `delivery_comment` for non-Delivery orders. Delivery orders retain the
  upstream field. Added explicit Pickup-hidden and Delivery-preserved tests.
- Validation passed 51 Delivery tests with 182 assertions; the final focused
  storefront UI rerun passed 13 tests with 69 assertions. Pint, Blade view
  caching, and local Cloud Run and Render image builds passed. No vendor/core,
  migration, payment, production, Render runtime, DigitalOcean, secret, or D3
  business-parameter change is included.

Next step: merge and deploy the focused Q-009 fix, then repeat the complete D2
Pickup-only browser, server-gate, session, log, and cleanup acceptance.

## 2026-07-19 - Validated Delivery D2 Pickup-only on Canada staging

Environment: Canada staging. Status: D2 runtime acceptance complete;
documentation PR pending review.

- Built PR #65 merge SHA `31821289df9ae4a162cabd0cac7a3ac6fb04cd0c`
  with Cloud Build `7ae74bf0-1943-4f15-a87d-d5fe43dac2af`. Artifact Registry
  digest is
  `sha256:72371b610a2dff66d29dcee09a2095c72c2f6bb0d932d33744db3444c3689102`.
- Deployed new Ready revision `le-chateau-canada-staging-d2fix-31821289` at
  0% for tagged health/page/asset/config/database/log preflight, then routed
  100% traffic after it passed. The failed PR #64 D2 revision remains 0%, and
  D1 remains available for rollback. Redacted runtime configuration matched D1
  with fingerprint `74bfef31792cdfcf`.
- Confirmed explicit and cached `DELIVERY_ENABLED=false`, effective
  Collection/Pickup, retained Location settings, zero Delivery Areas, and no
  migration/schema change.
- Desktop and 390x844 checks found no Delivery fulfillment search, address,
  selector, schedule tab, hidden focusable control, overflow, raw key, asset
  failure, or console error. Pickup and Birthday CTAs, Collection selection,
  public Birthday/Reservation, authenticated admin modules, Livewire, and
  retained media passed.
- Combined the earlier same-D2 live cart lifecycle pass with the exact PR #65
  image verification. The redeploy ran before configured opening time, so the
  existing schedule guard prevented a second synthetic add. No final Order,
  Customer, Payment, address, or Delivery Area was created.
- Verified Q-009 in the deployed source: Pickup omits only
  `delivery_comment`, ordinary `comment` remains, and Delivery rendering is
  preserved by the focused tests. The PR #65 focused suite passed 13 tests/69
  assertions; the full Delivery suite passed 51 tests/182 assertions.
- A disposable production-image Job passed stale-session normalization,
  session/cart preservation, storefront spoof 422, Orders API 422, and
  no-leak checks. Final read-only counts matched the preflight baseline; the
  Job was deleted.
- Current revision logs contained zero error-severity entries, HTTP 5xx,
  unexpected 422, or fatal/SQL/FUSE/cache/permission errors. `/healthz/`
  returned `200 ok` and the retained PNG returned 200 with its cache header.

Q-005 remains open for the `en_CA` to `fr_CA` fallback. Q-010 records the
pre-existing Orange scheduled-time `wire:ignore` synchronization issue as
Deferred and separate from D2; future orders remain disabled. Render,
DigitalOcean, production, payment, real data, D3 settings, and fresh-install
migration ordering were not changed.

Next step: review this documentation-only PR. Keep Canada staging Delivery
closed until D3 is separately approved.

## 2026-07-19 - Audited Delivery D3 business parameters

Environment: Canada staging read-only and repository source audit. Status:
D3A documented; awaiting business decisions.

- Synced `4.x` to PR #66 merge SHA
  `ea5c6b5f263ba93f4bd28b9551435d85c66b7ff7` and inspected the installed
  TastyIgniter Local v4.1.4 and Cart v4.2.3 behavior without modifying vendor.
- Verified the active Canada revision remains Ready at 100% with
  `DELIVERY_ENABLED=false`, stored Delivery/Collection enabled, zero Delivery
  Areas, and Pickup-only storefront behavior.
- Read-only admin inspection recorded the retained Delivery minimum, interval,
  lead time, future-order state, weekly schedule, tax switches, distance unit,
  and geocoder configuration without recording credentials or changing a
  field.
- Documented native address/circle/polygon support, first-priority overlap
  behavior, inclusive boundaries, postal/FSA normalization limits, multiple
  Location selection, server-side area revalidation, and Order totals history.
- Confirmed minimum and free-delivery conditions use `Cart::subtotal()` before
  cart-level coupon and tax; Delivery fee is priority 100, coupon 200, tax 300,
  and Pickup skips the Delivery condition. Distance surcharge remains additive
  even when the base-area condition is free.
- Added `DELIVERY_D3_BUSINESS_PARAMETER_PLAN.md` with options A/B/C, a
  polygon-first recommendation, a complete decision table whose final values
  are all pending, D3B/D3C acceptance plans, rollback, and exact QA cleanup.
- Opened Q-011 as a D3C gate for synthetic geocoder failure log redaction and
  Google/Nominatim quota, identification, attribution, and fallback acceptance.

No runtime code, database, schema, Delivery Area, fee, minimum, hours, tax,
environment, real data, payment, mail, production, Render, or DigitalOcean
change is included. Q-005, Q-010, and fresh-install migration ordering remain
separate. D3B must not start until the user confirms the decision table.

## 2026-08-19 - Configured Delivery D3B on closed Canada staging

Environment: Canada staging and documentation. Status: Approved D3B
configuration complete; D3C enablement remains blocked.

- Synced the task branch from the latest `origin/4.x` baseline and made no
  runtime code, vendor/core, schema, or migration change.
- Validated the supplied KMZ/KML read-only. The linked geometry contains one
  suitable polygon with 36 unique vertices, a closed ring, no consecutive
  duplicates, no self-intersection, and an approximate area of 14.74 square
  kilometres.
- Saved and read back the default Location coordinates from the supplied map,
  one default `D3 Montreal Delivery Area` polygon, CAD 5.00 base Delivery,
  free Delivery at or above CAD 80.00, no distance surcharge, CAD 20.00
  Delivery minimum, and Monday-Friday 12:00-21:00 Delivery hours with weekends
  closed. Pickup and tax settings were unchanged.
- A disposable current-image Cloud Run Job performed the exact transactional
  area write and after-save inside/outside/boundary checks using the active
  staging service configuration and Cloud SQL socket. Admin readback then
  independently confirmed the area type, 36 vertices, fee rules, minimum,
  schedule, and coordinates.
- `DELIVERY_ENABLED=false` remained unchanged and the public storefront stayed
  Pickup-only. No Order, Customer, Reservation, Birthday, Payment, Payment Log,
  real customer data, production service, payment, mail, Render, DigitalOcean,
  or secret was touched.
- Deleted the disposable Cloud Run Job after verification and confirmed the
  resource returned 404. Updated `DELIVERY_D3_BUSINESS_PARAMETER_PLAN.md` and
  `ADMIN_CONFIGURATION_TRACKER.md` with the approved values, non-sensitive
  boundary summary, validation, rollback state, and remaining gates.

Next step: review the documentation diff. Q-011 and any remaining launch
decisions must be resolved before an isolated D3C revision changes the global
Delivery gate.

## 2026-08-20 - Added project-owned Delivery geocoder error redaction

Environment: Canada staging investigation and local isolated tests. Status:
Fix PR pending review; Q-011 remains Open and Delivery remains closed.

- Opened Ready PR #69, `Fix Delivery geocoder error redaction`, against `4.x`.
- Synced from PR #68 merge SHA
  `b653b55b2787700b7dd26edc95b36074a8bbe35f`. The active Canada staging
  revision remained `le-chateau-canada-staging-d2fix-31821289` at 100% with
  `DELIVERY_ENABLED=false`.
- A disposable same-image controlled failure reproduced the encoded synthetic
  address and provider request URL in application/Cloud Run logs. The tested
  log window did not expose a credential, authorization header, geometry,
  SQLSTATE, or internal Location/area ID.
- Source audit traced the log leak to Orange's empty-geocode diagnostics and
  found that autocomplete and suggestion lookup can also surface raw provider
  exception messages in Livewire validation errors.
- Updated the project-owned `DeliveryLocalSearch` wrapper after PR review. It
  now sanitizes exceptions thrown directly by forward and reverse geocoder
  facade calls before a collection is returned, and narrows autocomplete and
  Google place-lookup handling to provider `Exception` boundaries. Logs contain
  only a generic event plus safe operation category; business validation is
  preserved and programming `Error` instances are not converted into address
  validation. No vendor/core code or global error reporting was changed.
- Expanded the focused synthetic suite to cover empty results, autocomplete,
  suggestion lookup, direct forward and reverse exceptions, preserved business
  coordinate validation, and programming-error propagation. PHP syntax and
  Pint checks passed; the focused suite passed 8 tests with 48 assertions in an
  isolated PHP 8.3 container. The added success-path regression proves reverse
  geocoding retains the provider-supplied formatted address rather than a
  formatter reconstruction. Testing used no staging database, migration, or
  seed. A broader Delivery-suite attempt was blocked only by the intentionally
  absent local database/application key; no database was created or migrated.
- Permanently deleted both disposable Q-011 Cloud Run Jobs after testing and
  confirmed that both exact names are absent from the Cloud Run Jobs list. No
  synthetic database row, temporary revision, cache, or configuration was
  retained; synthetic audit logs remain under the platform retention policy.
- Recorded that public Nominatim fallback is not approved for production
  Delivery traffic in its current form: request-derived identity is not a
  stable application identity, there is no shared cross-instance rate limiter,
  Google failure can fan out fallback traffic, and public autocomplete is
  prohibited.

No Delivery gate, traffic, Location, polygon, fee, minimum, schedule, Pickup,
tax, database/schema, Order, Customer, Reservation, Birthday, payment, mail,
production, Render, DigitalOcean, secret, or real-data change is included.
Q-011 remains `Open — staging rerun required`; `DELIVERY_ENABLED=false` remains
unchanged. Next step: review the updated PR #69. Do not merge or deploy until
review passes.

## 2026-08-20 - Resolved Delivery D3 Q-011 in a final clean staging rerun

Environment: Canada staging and docs. Status: Resolved for the current tested
failure path; Delivery remains globally closed.

- Corrected the D3B Delivery schedule from Monday closed and
  Tuesday-Saturday 12:00-21:00 to Monday-Friday 12:00-21:00 with Saturday and
  Sunday closed. The write was scoped and transaction-protected; Pickup and
  other D3B settings were unchanged.
- Tested the merged PR #69 project-owned redaction at commit
  `3fc841d12e65155c445c9c747ee7f97ec3ea0f49` in isolated 0%-traffic revision
  `le-chateau-canada-staging-q011-3fc841d`. The stable revision retained 100%
  traffic and `DELIVERY_ENABLED=false`.
- The authoritative clean same-image execution passed Cloud SQL mount,
  application bootstrap, database preflight, empty-result, forward/reverse,
  autocomplete, place-lookup, business-validation, successful-reverse, and
  fail-closed Delivery API checks.
- Exact-window application and Cloud Run scanning found no full address,
  provider URL, credential, raw provider exception, geometry, SQL/internal
  ID, database diagnostic, or PHP path exposure. Public and Livewire failures
  remained generic and fail-closed.
- Desktop/mobile Pickup-only, Birthday, Reservation, Admin, and health
  regression checks passed. D3B readback confirmed the retained polygon and
  commercial values plus the corrected weekday schedule. Orders, Customers,
  Reservations, Birthday records, slot holds, Payments, and Payment Logs were
  unchanged.
- Permanently deleted all four disposable Jobs after explicit approval and
  confirmed exact resource absence. Removed Cloud Shell test files and local
  temporary read-only clones. Historical audit logs remain under platform
  retention; no temporary configuration or synthetic database row remains.
- Updated `ADMIN_CONFIGURATION_TRACKER.md`, `CHANGELOG_AI.md`,
  `DELIVERY_D3_BUSINESS_PARAMETER_PLAN.md`, and
  `CLOUD_RUN_CANADA_STAGING_RUNTIME.md`. This is a documentation-only record;
  it contains no runtime code, secret, complete synthetic address, provider
  request URL, polygon vertex list, real data, or production change.
- Public Nominatim fallback is not approved for production Delivery traffic.
  Its identity, attribution, shared rate-limiting, and fallback operating model
  remain a separate production decision and do not reopen the tested staging
  redaction result.

Q-011 is `Resolved` for the current Canada staging tested failure path. Review
and merge this validation PR, keep `DELIVERY_ENABLED=false`, and freeze the
project handoff baseline before any separately approved D3C enablement work.

## 2026-08-20 - Prepared the Claude migration handoff freeze

Environment: repository, Canada staging read-only audit, and docs. Status:
docs-only handoff PR pending review; Delivery remains closed.

- Synced clean `4.x` to PR #70 merge SHA
  `f731f775e5d3f069a959641323b544007ca21552` and confirmed no open Delivery
  implementation PR before creating the handoff branch.
- Performed a fresh read-only GCP audit. Main traffic remained 100% on
  `le-chateau-canada-staging-d2fix-31821289`; Q-011 revision
  `le-chateau-canada-staging-q011-3fc841d` remained tagged at 0%; health returned
  `200 ok`; and `DELIVERY_ENABLED=false` remained unchanged.
- Confirmed the runtime identity has no user-managed key, the Cloud SQL
  reference is runnable, the Storage reference is readable, five unique Secret
  Manager references resolve without reading values, the tested Artifact image
  is readable, and the regional Q-011 build status is `SUCCESS`.
- Added `CLAUDE_HANDOFF.md` as the standalone project baseline covering
  infrastructure, Delivery, Birthday, payment, security, known issues,
  production gates, fallbacks, exact D3C next phase, acceptance, and stop
  conditions.
- Added `AGENT_WORKFLOW.md` with Level 0-3 risk classification, internal Review
  A/B, verification, PR/merge gates, destructive approval, secret handling,
  and post-merge continuation. No standing auto-merge permission is assumed.
- This task made no runtime, traffic, Delivery, Pickup, Birthday, Reservation,
  payment, schema, production, Render, DigitalOcean, secret, real-data, or
  fallback-infrastructure change.

The merge commit of the handoff PR becomes the `CLAUDE HANDOFF BASELINE`.
Do not start D3C until that PR is merged.

## 2026-08-20 - Confirmed Delivery D3 business decisions and removed leftover Jobs

Environment: repository docs, Canada staging read-only audit, and an approved
Cloud Run Jobs cleanup. Status: documentation PR pending review; Delivery
remains globally closed.

- Started from the frozen handoff baseline
  `6c9331c1526778e474be2a980831f1e5505955b4` on clean `4.x` with no open pull
  request, and created this focused documentation branch from that mainline.
- Re-verified Canada staging directly instead of copying the freeze record.
  Traffic, image digest, retained tagged revisions, runtime service account,
  zero user-managed keys, Cloud SQL `RUNNABLE` state, media volume mount, the
  five Secret Manager references, the successful regional Q-011 build, and
  health `200 ok` all matched `CLAUDE_HANDOFF.md`. No secret value was read.
- Added evidence the freeze did not contain: all 26 revisions were checked,
  only the four Delivery-era revisions define `DELIVERY_ENABLED`, all four read
  `false`, the other 22 predate the flag, and no revision has ever set it to
  `true`.
- Recorded the user's 2026-08-20 confirmation of the 24 remaining business
  decisions in `DELIVERY_D3_BUSINESS_PARAMETER_PLAN.md`, replacing every
  `Pending confirmation` entry with the confirmed value and its date.
- Documented two accepted operational risks (unaudited admin fee edits, manual
  holiday closure) and two outstanding numeric values (tax rate and Delivery
  fee taxability, Google geocoding daily cap). Neither number blocks D3C
  because staging tax remains disabled and D3C uses synthetic addresses.
- Audited 17 previously undocumented Cloud Run Jobs from the July
  initialization phase, decoding their inline scripts to classify three
  database-writing, three configuration-writing, and eleven read-only jobs.
  Confirmed zero literal credentials: every secret-like environment variable
  resolved through Secret Manager.
- Exported all 17 restorable definitions before deletion, deleted all 17 after
  explicit per-target user confirmation, and verified zero Jobs remain with
  17 of 17 absent on exact readback. Traffic, the Delivery gate, and health
  were unchanged afterwards.
- Corrected an earlier inconclusive observation: the bare `/healthz` 404 seen
  through a generic web fetch is the documented Google frontend behaviour. The
  configured liveness path `/healthz/` returns `200 ok`.

No runtime, traffic, revision, image, environment variable, schema, business
row, Delivery Area, fee, minimum, schedule, Pickup, Birthday, Reservation,
payment, secret, production, Render, or DigitalOcean change is included beyond
the approved Job deletion recorded above.

Next step: review this documentation PR. Keep `DELIVERY_ENABLED=false` until a
separately approved D3C isolated enablement.

## 2026-08-20 - Add CLAUDE.md and record docs-only merge authorization

Environment: repository documentation only. Status: PR pending user merge.

- Added `CLAUDE.md`. Claude Code loads it automatically at session start, while
  `AGENT_WORKFLOW.md` and `CLAUDE_HANDOFF.md` are not, so a fresh session
  previously began without the baseline check, verification standard, merge
  gates, destructive-operation rules, secret handling, or standing boundaries
  in context.
- Kept `CLAUDE.md` a pointer rather than a copy. Both source documents are
  declared authoritative and win any disagreement, so the summary cannot drift
  into silently contradicting them.
- Recorded the standing Level 0 docs-only merge authorization the user granted
  on 2026-08-20, with its four conditions and its exclusions, in
  `CLAUDE_HANDOFF.md` section 15, `AGENT_WORKFLOW.md` section 8, and
  `CLAUDE.md`. The previous text asserting that no such authorization existed
  is replaced.
- Excluded this PR from that authorization and left it for user merge, because
  it is the change that grants the authorization. Self-merging it would mean
  self-certifying a permission expansion.

Documentation only. No code, runtime, schema, secret, business data, or
production change. `DELIVERY_ENABLED` remains `false` and D3C has not started.

## 2026-08-20 - Backfill the CLAUDE handoff baseline SHA

Environment: repository documentation only. Status: PR open; eligible for
auto-merge under the standing Level 0 docs-only authorization. No PR has been
merged under that authorization before this one: PR #72 was merged before the
authorization existed, and PR #73 is the change that granted it and was
explicitly excluded from it, so neither relied on it.

- `CLAUDE_HANDOFF.md` section 2 said the baseline SHA "cannot be recorded
  until the PR is merged". PR #71 merged on 2026-08-20, so that sentence has
  been stale ever since and left the frozen baseline unnamed, which weakened
  the start-of-task containment check every later session depends on.
- Recorded the baseline as `6c9331c1526778e474be2a980831f1e5505955b4`, the
  merge commit of PR #71, `Freeze project baseline for Claude handoff`.
- Verified rather than assumed. That commit has exactly two parents:
  `f731f775e5d3f069a959641323b544007ca21552`, the PR #70 SHA already recorded
  in section 2, and `c66eb10b5abefe14a474394a49f0c696b4e4d705`, the commit
  that added `CLAUDE_HANDOFF.md`. GitHub reports the same merge commit for
  PR #71, and the commit is contained in `origin/4.x`.
- Dropped the now-redundant instruction to "identify that merge commit". The
  fetch, containment, and clean-worktree checks are unchanged.
- Swept every `.md` for comparable placeholders left unfilled after their
  blocking merge. This was the only one. `<INSTANCE_CONNECTION_NAME>` in
  `CLOUD_RUN_CANADA_STAGING_RUNTIME.md`, `ADMIN_CONFIGURATION_TRACKER.md`,
  `CHANGELOG_AI.md`, and `RENDER_RUNTIME_READINESS.md` is a deliberate
  redaction marker and must stay unfilled. The 2026-07-10 Birthday entry
  referring to a flag being set "after the PR is merged" is a dated historical
  record that was accurate when written and is not rewritten.

Documentation only. Every changed path is `.md`. No code, runtime, schema,
secret, business data, or production change. `DELIVERY_ENABLED` remains
`false` and D3C has not started.

## 2026-08-20 - Replace the void runtime fingerprint with FP-1

Environment: repository documentation plus a read-only Canada staging
measurement. Status: PR open; eligible for auto-merge under the standing
Level 0 docs-only authorization.

- `CLAUDE_HANDOFF.md` section 5 recorded a runtime fingerprint and instructed
  future sessions to "recompute it with the same normalized fields", but the
  normalized fields and the algorithm were never written down anywhere in the
  repository. The instruction was impossible to follow, which meant the freeze
  carried a comparison marker that no one could ever compare against.
- Marked
  `b82e3aa41eaba24a1bc54784f9f889209a83b178cda1baeec85f069337e41b1b` void and
  permanently unverifiable. Documenting an algorithm now cannot recover it: any
  algorithm written today would either coincidentally reproduce the digest or,
  far more likely, fail to, and neither outcome establishes anything about the
  August runtime. The wording deliberately does not imply the old value was
  ever checked. It was not, and it never can be.
- Defined FP-1 in `CLOUD_RUN_CANADA_STAGING_RUNTIME.md`: scope, a fixed
  twenty-field list with sources, the algorithm, a reference implementation,
  and an explicit statement of what FP-1 does not cover, which is Cloud SQL
  instance state, IAM bindings, Secret Manager contents, bucket contents,
  image layers behind the digest, and any non-serving revision.
- Stored a per-field digest table, not only a total. A single opaque total
  reports that something changed without saying what; the table names the
  field. Demonstrated against the retained 0% revision
  `le-chateau-canada-staging-q011-3fc841d`, where exactly `revision` and
  `image_digest` differ from the serving revision and the other eighteen
  fields are identical.
- Made the total the SHA-256 of the published table rather than of the
  plaintext, so the table alone re-verifies the total and field plaintext never
  needs to leave memory.
- Chose a digest of the connection name over an instance count for field 16.
  A count cannot detect a database swap. Verified that a different instance
  name and a different project each change the field digest, and that the
  field carries the connection name rather than an empty string.
- Stated the confidentiality limit plainly: per-field digests over low-entropy
  values are confirmation oracles, not secrecy. A keyed hash would close that
  gap but would require reading a secret value to compute a fingerprint, which
  the project rules forbid, so the weaker guarantee is accepted deliberately.
- Recorded the 2026-08-20 FP-1 baseline for
  `le-chateau-canada-staging-d2fix-31821289` at 100% traffic:
  `2127efd6d63de53e6d9fbc5388f9db3fee72d0575eec25a09b9f99e9ad8565d3`.

The measurement was read-only. Documentation only; every changed path is
`.md`. No code, runtime, traffic, revision, image, environment variable,
schema, secret, business data, or production change. `DELIVERY_ENABLED` remains
`false` and D3C has not started.

## 2026-08-20 - Make tools/fp1.py the single FP-1 implementation

Environment: repository, plus a read-only Canada staging re-measurement.
Status: PR open. Level 1, and it adds a non-`.md` path, so it falls outside the
standing docs-only authorization and stops for user merge confirmation. The
user authorized this specific change explicitly and once.

- Committed the FP-1 reference implementation as `tools/fp1.py` and removed the
  110-line copy embedded in `CLOUD_RUN_CANADA_STAGING_RUNTIME.md`. Two copies
  of an implementation drift, and reproducibility is the whole value of a
  fingerprint.
- Declared precedence instead of leaving it implicit: where the document's
  field table and the script disagree, the script wins and the table is the
  bug. The field table stays, because a reader needs it to interpret a digest
  table, and the 2026-08-20 baseline digests are unchanged.
- Verified the committed script rather than assuming the move was lossless.
  Invoked as `tools/fp1.py` it reproduced the recorded baseline
  `2127efd6d63de53e6d9fbc5388f9db3fee72d0575eec25a09b9f99e9ad8565d3`, and its
  output was byte-identical to the copy that produced the original
  measurement. The only difference from that copy is an added argument guard
  that prints usage and exits 1 rather than raising TypeError.
- Confirmed Canada staging had not drifted: FP-1 measured before the move
  equals the value recorded in PR #75.
- Added the FP-1 pre/post gate to the D3C plan in `CLAUDE_HANDOFF.md` sections
  18 and 19. FP-1 for the main-traffic revision is recorded before the first
  D3C action and recomputed after the last. An identical pair is the accepted
  evidence that D3C never touched the live path, and "no change was intended"
  is explicitly not accepted in its place. A differing pair stops work, and the
  per-field digest table names the field that moved.
- Recorded two consequences of that gate. A changed `revision` or
  `traffic_percent` means traffic moved, which is outside D3C scope and needs
  the separate cutover gate. FP-1 refuses to run while traffic is split across
  revisions, so a split part way through D3C surfaces as an error instead of a
  silent pass.

The measurement was read-only. No runtime, traffic, revision, image,
environment variable, schema, secret, business data, or production change.
`DELIVERY_ENABLED` remains `false` and D3C has not started.

## 2026-08-20 - Record the CI gap and clear stale post-merge preconditions

Environment: repository documentation only. Status: PR open; eligible for
auto-merge under the standing Level 0 docs-only authorization.

- Recorded in `CLAUDE_HANDOFF.md` section 10 that continuous integration has
  never run in this repository. Both workflows report `active`, but the run
  history is empty across every branch and pull request, including PRs #74,
  #75, and #76. Actions being disabled for this fork is the likely cause.
- Stated the consequence plainly, because it is a reading trap rather than a
  visible failure: an empty check list means untested, not passing. It is also
  why the section 15 rule against merging on absent checks matters here, and
  why the section 20 stop condition about a blocking CI finding is inert, since
  CI produces no findings at all.
- Framed it as a decision point rather than a defect to fix quietly. After D3C
  and before substantive code work such as Birthday checkout, payment,
  webhooks, or authentication, whether to enable CI must be decided
  explicitly. Documentation-only changes can proceed without it; code changes
  should not do so indefinitely.
- Recorded the expected consequence of enabling it: broad initial failure.
  `vendor/` has never been installed in this working copy and the PHP 8.3, 8.4,
  and 8.5 matrix has never been exercised, so the suite's current state is
  unknown. That is pre-existing state becoming visible, not a regression, and
  it warrants a budgeted triage pass rather than blocking whichever change
  happens to trigger the first red run.
- Added a matching note to `CLAUDE.md` under current state, so a fresh session
  cannot read an empty check list as a green one. The note is deliberately
  placed in the state section rather than the merge-gate section, because the
  standing docs-only authorization does not cover changes to the merge rules
  and no merge rule is being changed.
- Cleared the stale post-merge preconditions in `CLAUDE_HANDOFF.md`. Sections
  1, 18, and 20 still said the next phase waits for a handoff PR that merged on
  2026-08-20 as PR #71. Section 20's stop condition now turns on whether the
  section 2 baseline verifies and is contained in `HEAD`, which is the half of
  it that remains live.
- Re-swept every `.md` with wider patterns after the previous sweep missed
  these. The earlier regex required "the PR"/"this PR" immediately after
  "after", so "after the handoff PR is merged" did not match. The new sweep
  allowed up to sixty characters between the conjunction and "merged", and
  separately searched deferred-value phrasing, pending markers, and
  angle-bracket markers.
- Triaged and deliberately left unchanged: `<INSTANCE_CONNECTION_NAME>` and
  `<from Secret Manager>` are intentional redaction markers; the 2026-07-10
  Birthday entry and the Q-007/Q-008 note in
  `ADMIN_CONFIGURATION_TRACKER.md` are dated historical statements that were
  accurate when written; the section 11 production gate referring to decisions
  "still marked pending" is a forward-looking gate rather than an unfilled
  value; and the outstanding tax rate and geocoding cap are genuinely open
  items correctly recorded as open.

Documentation only. Every changed path is `.md`. No code, runtime, schema,
secret, business data, or production change. `DELIVERY_ENABLED` remains `false`
and D3C has not started.

## 2026-08-20 - FP-1 gains an explicit REVISION target

Environment: repository, plus read-only Canada staging measurements. Status: PR
open. Level 1 and non-`.md`, so outside the standing docs-only authorization;
the user approved it with a mandatory regression condition.

- D3C surfaced the gap. `CLAUDE_HANDOFF.md` section 19 requires a configuration
  fingerprint for the tagged revision, but `tools/fp1.py` could only target the
  revision holding main traffic, and deliberately refused anything else.
- Added an optional third argument. `tools/fp1.py SERVICE REGION REVISION`
  fingerprints the named revision. Its `traffic_percent` is whatever that
  revision holds, which is 0 for a revision serving no traffic.
- Left the default path untouched, as required. With no `REVISION` the target
  is still the traffic-holding revision and the split-traffic guard still
  fires. The guard was not weakened, relaxed, or made conditional in that mode.
- Met the mandatory regression condition before anything else. Re-fingerprinting
  `le-chateau-canada-staging-d2fix-31821289` in default mode reproduced the
  recorded baseline
  `2127efd6d63de53e6d9fbc5388f9db3fee72d0575eec25a09b9f99e9ad8565d3`, and the
  full twenty-one-line output was byte-identical to the recorded table, not
  merely the total. The baseline was not adjusted to accommodate the code, and
  would not have been; a mismatch was to mean revert and redo.
- Verified the guard with stubbed service responses rather than by inspection.
  Default mode exits with the unchanged message on a two-way split and on zero
  traffic holders, and still succeeds with a single holder. Explicit mode
  returns the named revision under a split, reports 0 for a non-serving
  revision, and matches default mode exactly when it names the serving one.
  Argument arity accepts three and four and rejects two and five.
- Confirmed the two modes agree. Naming the serving revision explicitly
  produced output byte-identical to the default invocation.
- Documented that a non-serving revision never matches the serving one.
  `traffic_percent` and `revision` differ by construction, so a tagged
  revision's fingerprint is necessarily different even when every other field
  is identical. `CLOUD_RUN_CANADA_STAGING_RUNTIME.md` now states plainly that
  this is expected and must not be recorded as drift, and that comparisons must
  be like with like.
- Exercised the new mode against the D3C tagged revision as part of that
  verification. The D3C acceptance record itself is deliberately not written
  here; it is written once when the section 19 matrix completes.
- Added `scratchpad/` to `.gitignore`, carried on this change as agreed rather
  than as a pull request of its own. The session scratchpad lives outside the
  working tree, so nothing from it could have been committed; the rule is
  defensive.

No runtime, traffic, revision, image, environment variable, schema, secret,
business data, or production change. The measurements were read-only.
`DELIVERY_ENABLED` remains `false` on the revision serving main traffic.

## 2026-08-20 - Redact URLs from log records

Environment: repository, plus a read-only Canada staging observation. Status:
PR open. Level 1, stops for user merge confirmation.

- D3C's first functional case exposed the leak. The Canada staging tagged
  revision logged, at `staging.ERROR`, a raw provider exception containing a
  Google console URL. Section 20 lists a provider URL appearing in logs as a
  stop condition, so D3C stopped there and the user classified it as blocking.
- Established where it comes from before fixing it. Project-owned code does not
  log raw provider exceptions: `App\Livewire\DeliveryLocalSearch` catches
  provider failures without binding the exception and logs only an event name
  and operation. The entry originates in the upstream geocoder provider, and
  vendor code is inspection-only.
- Added `App\Logging\RedactUrlProcessor`, a Monolog processor that strips
  absolute URLs from a record's message, context strings, and extra strings,
  leaving non-string values and record identity untouched. The log record is the
  last project-owned point before a record reaches a handler, so the redaction
  belongs there rather than in vendor code.
- Installed it through `App\Logging\RedactSensitiveLogData`, a channel tap
  referenced from the `stderr` and `single` channels in `config/logging.php`.
  The runtime uses `LOG_CHANNEL=stderr`; `single` is covered too so the same
  leak cannot reappear under a different channel.
- Verified the fix rather than asserting it, despite there being no CI and no
  local PHP. A container running PHP 8.3.32 and PHPUnit 11.5.56 executed
  `tests/Unit/Logging/RedactUrlProcessorTest.php`: 12 tests, 19 assertions, all
  passing. The test asserts against the exact message observed on the D3C
  revision, not a paraphrase.
- Recorded the residual limitation in `CLAUDE_HANDOFF.md` section 10 and
  asserted it in the test. A Throwable placed in log context is normalized after
  processors run, so a URL inside an exception object is not redacted. Closing
  that needs formatter-level redaction and is a separate decision.
- Noted for the pending continuous integration decision: the suite can be run in
  a container today. What blocked verification was tooling, not the code.
- Not yet verified end to end. That the processor works is established; that the
  tap is installed in the deployed application is not, and is the purpose of the
  authorized Cloud Build reproduction on the live failure path.

Q-011's acceptance covered the failure paths tested at the time. This failure
mode, an unactivated provider API, was not among them, so this extends that work
rather than contradicting it.

No runtime, traffic, revision, image, environment variable, schema, secret,
business data, or production change. `DELIVERY_ENABLED` remains `false` on the
revision serving main traffic.

## 2026-08-20 - Geocoding daily cap and container-based testing

Environment: repository documentation only. Status: PR open; eligible for
auto-merge under the standing Level 0 docs-only authorization.

- Backfilled the Google geocoding daily usage cap at 500 requests per day with
  a budget alert, closing the last open numeric value in the geocoder decision.
  Recorded the reasoning rather than the number alone, because the number is
  not derived from expected usage. Geocoding is free for the first 10,000
  requests a month and USD 5.00 per 1,000 after that; D3C costs a few hundred
  requests and ordinary trading is around 2,700 a month, so both are free. The
  cap exists for a looping defect, which can burn a month of free allowance in
  an hour. At 500 a day the worst case is roughly USD 25.00 a month and the
  anomaly surfaces early.
- Corrected the row reference while making the change. The pending cap value
  was in decision 24, `Geocoder provider`, not decision 23, `Provider
  unavailable`, which concerns fail-closed behaviour and carries no cap. The
  described text identified the row unambiguously, so the intent was clear; the
  index was not.
- Recorded that the PHP suite can be run in a container. On 2026-08-20 a
  container running PHP 8.3.32 and PHPUnit 11.5.56 executed a project test file
  with no local PHP toolchain and no CI. This changes the continuous
  integration decision from a bet on whether enabling it would fail broadly
  into something measurable beforehand: run the suite in a container, triage
  what it reports, then decide.
- Recorded a deferred static check for the Monolog redaction gap. The existing
  test asserts that the gap exists but cannot stop new code from passing a
  Throwable into log context, which is how the gap would begin to matter. A
  check that fails when project-owned code does so would close that route.

Documentation only. Every changed path is `.md`. No code, runtime, schema,
secret, business data, or production change. `DELIVERY_ENABLED` remains `false`
on the revision serving main traffic.

## 2026-08-20 - Two billing production gates

Environment: repository documentation only, plus a read-only billing lookup.
Status: PR open; eligible for auto-merge under the standing Level 0 docs-only
authorization.

- Recorded that the confirmed Geocoding daily cap of 500 requests is not in
  place, because a free-trial billing account cannot edit quota overrides. The
  value is decided but unenforced, and the document now says so rather than
  implying the cap protects anything today.
- Recorded the coupling that makes this dangerous, as a production gate. What
  protects the project now is the trial itself: trial usage draws down trial
  credit and a real bill requires a manual upgrade. That protection ends at the
  instant of upgrade, which is the same instant the quota becomes settable. The
  gate is written as "in the same act", because a gap between upgrading and
  capping is exactly the window a looping defect would spend through.
- Recorded the trial expiry as a second production gate. Google states that
  resources created under a free trial may be removed and not recovered if the
  trial ends without an upgrade. The whole of Canada staging sits in that
  project, so the expiry is a dated operational risk that belongs in the launch
  schedule.
- Did not record the remaining credit or the expiry date, and said why rather
  than leaving a bare gap. Neither is exposed by the Cloud Billing API;
  `gcloud billing accounts describe` returns only currency, display name, and
  open state. Both are readable from the console credits page, and the document
  names that page and its owner.
- Noted that the billing account currency is CAD, so the trial amount must be
  read rather than assumed from a figure quoted in another currency.

Documentation only. Every changed path is `.md`. No code, runtime, schema,
secret, business data, or production change. No billing account identifier is
recorded in the repository.

## 2026-08-21 - Record the actual free-trial figures and the working deadline

Environment: repository documentation only. Status: PR open; eligible for
auto-merge under the standing Level 0 docs-only authorization.

- Replaced the trial placeholder recorded a day earlier with figures read from
  the console: CA$425.27 granted, CA$204.13 remaining at 48%, trial period
  2026-07-09 to 2026-10-08 with 48 days left, and CA$103 gross against CA$0.00
  net for 2026-08-01 to 2026-08-21. The placeholder existed for one PR because
  the Cloud Billing API does not expose either figure.
- Recorded that the effective deadline is earlier than the period end. The
  console states the trial ends at whichever comes first, credit spent or
  period elapsed, and cannot be paused or extended. August ran near CA$5.00 a
  day, so the remaining balance covers roughly 41 days and lands about
  2026-10-01, around a week early.
- Labelled that date as an estimate from an observed rate rather than a date
  Google has given, and said to re-read the balance rather than trust the
  projection, because D3C and later phases will change the rate.
- Recorded the console's stated consequence: every resource created during the
  trial stops when it ends, which is the whole of Canada staging including the
  Cloud SQL data.
- Recorded the scheduling decision: upgrade in mid-September 2026 rather than
  the week before the deadline, with the upgrade, the 500-per-day quota, and
  the budget alert performed as one operation.
- Recorded that the budget alert does not exist yet.
- The billing account identifier is deliberately still not written into the
  repository. It is a financial identifier and the gate does not need it.

Documentation only. Every changed path is `.md`. No code, runtime, schema,
secret, business data, or production change.

## 2026-08-21 - Verify the redaction wiring through the real channel

Environment: repository, container-run tests. Status: PR open. Level 1, stops
for user merge confirmation.

- Added `tests/Feature/Logging/RedactUrlChannelTest.php`. The unit test proves
  the processor redacts; this proves the configured channel installs it, which
  is the part that could only be guessed before.
- Verified the wiring rather than assuming it. Booting the application shows
  the `stderr` channel carrying
  `Illuminate\Log\Context\ContextLogProcessor` and
  `App\Logging\RedactUrlProcessor`. The `single` channel carries it too.
- Established where the observed leak actually comes from, which is not where
  it looked. `AbstractProvider::log()` in `tastyigniter/core` never writes to
  Laravel; it appends to an in-memory array. The `staging.ERROR` entry is
  written by `Igniter\Orange\Livewire\Concerns\SearchesNearby`, which calls
  `Log::error(implode(PHP_EOL, Geocoder::getLogs()))`.
- That resolves the open question about coverage. The provider's raw text lands
  in the record message, not in context, so `RedactUrlProcessor` does cover the
  path that leaked on Canada staging. A test asserts it using the observed
  message shape.
- Demonstrated the residual gap instead of describing it. A record carrying an
  exception in context, formatted through the configured handler's real
  `LineFormatter`, still contains the URL inside the serialized exception, and
  also carries the PHP file path and a stack trace. Section 20 lists a PHP path
  as its own leak class, so an exception reaching the log costs more than the
  URL alone.
- Ran the tests: PHP 8.3.32, PHPUnit 11.5.56, 18 tests and 29 assertions across
  the unit and feature files, all passing.
- These ran in a container against a real `composer install`, which required
  `--prefer-source`. Every `tastyigniter/*` dist artifact fails checksum
  verification against `composer.lock`; `Dockerfile.cloudrun` already sidesteps
  this by setting `preferred-install` to source for those packages, which is
  why builds succeed while a plain local install does not.

No runtime, traffic, revision, image, environment variable, schema, secret,
business data, or production change.

## 2026-08-21 - Pin the geocoder chain per deployment

Environment: repository, container-run tests. Status: PR open. Level 1, stops
for user merge confirmation.

- Added `App\Delivery\GeocoderChainOverride` with two environment variables,
  `DELIVERY_GEOCODER_DRIVER` and `DELIVERY_GEOCODER_PROVIDERS`, read through
  `config/delivery.php`. Neither is set anywhere today, and when neither is set
  the stored configuration stands exactly as before.
- The reason is coordinate attributability, not convenience. Google and
  Nominatim can place the same address tens of metres apart, and Delivery Area
  boundaries are decided at that scale. A transient Google failure falls
  through to Nominatim silently, so a boundary result taken against the stored
  chain
  cannot be attributed to a known coordinate source. Decision 24 also fixes
  production on Google alone, so a matrix run against the stored chain would
  validate a configuration production will not have.
- Registered with `afterResolving` rather than `resolving`. The package's own
  resolving callback rewrites the same keys from the settings table, so a
  resolving registration would have depended on provider registration order to
  win. `afterResolving` runs once every resolving callback has.
- Selection only. There is no flag that makes a provider fail. Naming no
  providers leaves a deployment with none configured, which is a configuration
  rather than a trapdoor, and is how the `Provider unavailable` acceptance item
  will be reached.
- Established why a configuration file alone would not have worked.
  TastyIgniter overwrites `igniter-geocoder.default` and the Google key at
  resolve time from the settings table, so a published config file is
  clobbered. This also
  confirms the earlier finding that the key cannot be invalidated per revision.
- Confirmed the cache timing this depends on. `config:cache` runs in
  `docker/cloudrun/start.sh` at container start, not at image build, so
  deploy-time environment variables are read correctly.
- Tests: 16 tests and 24 assertions across the unit and feature files, all
  passing on PHP 8.3.32 with PHPUnit 11.5.56.
- Stated the gap rather than papering over it. The resolve-and-apply path has
  no automated test, because resolving the geocoder needs the settings table
  and the default country row. The unit test covers the logic including the
  untouched case; the feature test covers the registration and the
  configuration shape; the rest is verified on a deployed revision.
- Ran the full suite while here and recorded the result against the continuous
  integration decision: 187 tests, 301 assertions, 82 errors, zero failures. 78
  of the errors are `PDOException: could not find driver`, because the
  `php:8.3-cli` image has no `pdo_mysql`. That is a missing extension in the
  runner, not a defect, and it moderates the earlier "expect broad failure"
  expectation recorded in section 10.

No runtime, traffic, revision, image, environment variable, schema, secret,
business data, or production change. `DELIVERY_ENABLED` remains `false` on the
revision serving main traffic.

## 2026-08-21 - Layer the documents and record the exposed Maps key

Environment: repository documentation only, plus read-only checks against GCP.
Status: PR open; eligible for auto-merge under the standing Level 0 docs-only
authorization.

- Split the documents by rate of change, and recorded the rule in
  `AGENT_WORKFLOW.md` section 8a so it does not depend on conversation memory.
  `CLAUDE.md` is the rule layer and changes rarely. `D3C_PROGRESS.md` is the
  state layer and is a snapshot. `CLAUDE_HANDOFF.md` is the reference layer.
  One fact is maintained in one file; other documents point rather than repeat.
- Cleared progress out of the `CLAUDE.md` current-state section. It no longer
  says which phase is next or how far the work has got. What stays is what
  outlives the current work: the gate semantics of `DELIVERY_ENABLED`, the 24
  confirmed decisions, that CI has never run, and pointers.
- Added `D3C_PROGRESS.md`. It carries the acceptance table, which doubles as
  the acceptance record, the deployed test revisions and their purposes, the
  outstanding items with owners, and the next concrete action. It is written
  for the project owner as well as for an agent, so checks are described by
  what they verify rather than by clause number.
- Recorded that `CLAUDE.md` is kept under 150 lines because it loads in full
  every session. It is 133 lines after this change.
- Recorded the exposed Maps key as a production gate.
  `Igniter\Orange\Livewire\Concerns\SearchesNearby` renders `$mapKey` from
  `setting('maps_api_key')`, the same setting that supplies the server-side
  geocoder key, and that key has no referrer or IP restriction while its
  allow-list includes billable Geocoding.
- Recorded explicitly that this is pre-existing and not a D3C regression. The
  revision serving main traffic exposes the key with `DELIVERY_ENABLED=false`,
  so a later reader cannot mistake it for something the phase introduced.
- Recorded why removing Places from the key's allow-list was rejected rather
  than done. Neither Places API is enabled on the project, so those calls
  already fail for any holder of the key; project-level enablement, not the
  allow-list, is the operative control. The live exposure is Geocoding, which
  is enabled and required.
- Extended the September upgrade to four things done in one sitting: upgrade,
  set the 500-per-day cap, create the budget alert, and split the browser key
  from the server key. The reason is recorded: their risks all become real at
  the same instant, so separating them leaves a window where one protection has
  ended and its replacement does not yet exist.

Documentation only. Every changed path is `.md`. No code, runtime, schema,
secret, business data, or production change. No key value and no billing
identifier is recorded in the repository.

## 2026-08-21 - D3C acceptance methodology and the first matrix findings

Environment: Canada staging, 0%-traffic revisions only. Status: PR open;
eligible for auto-merge under the standing Level 0 docs-only authorization.

- Recorded that rendering is not execution, and applied it backwards. An item
  worded as offering or preserving something is not passed by seeing the
  element. Two rows previously counted as passed were downgraded: pickup being
  offered while the provider was unavailable, where the link was never
  followed, and delivery being closed outside hours, where a label was read but
  no order was attempted and refused.
- Recorded that a tagged 0%-traffic URL is not isolated until `APP_URL`,
  `ASSET_URL`, and `CLOUD_RUN_SERVICE_URL` are pinned to it. The application
  builds redirects from `APP_URL`, so the first server-issued redirect lands on
  the revision serving main traffic, where the Delivery gate is closed and the
  selection is normalised away. The failure is silent: the page looks normal
  and only the hostname differs. One in-area address result taken before the
  fix was discarded and re-run rather than reasoned about.
- Recorded a blocking defect. On Friday 2026-08-21 at 12:04 Montreal, inside
  configured delivery hours of Monday to Friday 12:00-21:00, the storefront
  reported delivery closed and the server offered no delivery time slots at
  all, while pickup at the same moment was available. The 25-minute delivery
  preparation time does not explain it; the earliest slot would fall inside the
  window. Root cause is not yet identified, and it blocks fee, threshold, and
  checkout verification.
- Recorded that time-dependent checks wait for their real window rather than
  having conditions manufactured, with the approval and restoration protocol
  for the case where waiting genuinely cannot work.
- Recorded the shared database as a structural limit on isolation. It has now
  blocked two per-revision changes: an invalid provider key, and different
  opening hours. A separate database for isolated testing is noted as a future
  direction and not undertaken.
- Recorded that address autocomplete does not function because the Places API
  is not enabled on the project, along with what a customer experiences as a
  result, so that enabling it can be decided as a business question with
  costing rather than assumed.
- Recorded that the one-hour gap between delivery closing at 21:00 and the shop
  closing at 22:00 is deliberate and achieved through the two schedules
  differing. No document claimed a missing cut-off, so nothing was corrected;
  the fact is recorded so that none does later.

No runtime change beyond deploying 0%-traffic revisions. Main traffic remained
on its revision throughout, and its fingerprint was unchanged before and after
every deployment.

## 2026-08-21 - Weekday schedules are shifted by one under a Canadian locale

Environment: repository documentation, plus code inspection and a container
probe. Status: PR open; eligible for auto-merge under the standing Level 0
docs-only authorization.

- Found the cause of delivery reporting itself closed on a Friday inside its own
  hours, from code and a reproducible probe, with no database access and no
  waiting. `Igniter\Flame\Translation\Localization` calls `Carbon::setLocale()`
  on every request; the site runs `fr_CA`; Carbon 3.13.1 resolves the first day
  of the week from the locale and returns Sunday for `fr_CA` and `en_CA` while
  returning Monday for the default, `en`, and `fr`. `WorkingHour::$weekDays`
  numbers rows from Monday, but `WorkingHour::getDay()` resolves them through
  `startOfWeek()`. Hours entered as Monday to Friday are applied as Sunday to
  Thursday.
- Recorded a false lead deliberately, because discarding it was the step that
  mattered. `HasWorkingHours::getWorkingHourByDateAndType` uses `format('N')`,
  Monday=1, against Monday=0 storage. It fits the symptom exactly and is a real
  off-by-one, but it has no callers anywhere and never runs. Checking callers
  before believing it prevented a repair to dead code and a false claim of a fix.
- Surveyed what else is affected. Delivery, restricted to five days, is the only
  schedule that exposes it. Collection and opening are open every day, so the
  shift lands on an open day and stays invisible; reservations use the opening
  schedule and inherit that. Birthday is structurally immune: it builds
  availability from fixed slots holding only code, start, end, label, and
  capacity, and never constructs a weekly schedule.
- Recorded that the latent cases are the dangerous ones. Restricting any of those
  schedules by day, for a holiday or a single closure, brings the same failure
  back.
- Recorded a forbidden repair. Entering Sunday to Thursday in the admin to obtain
  Monday to Friday behaviour works immediately and permanently decouples what the
  screen says from what the system means. Anyone later correcting the screen to
  read correctly breaks it again, with nothing to explain why. The correct
  direction is to fix the first day of the week on the project side, with the
  blast radius on anything resting on week boundaries assessed before proposing
  it.
- Recorded locale-dependent behaviour as a standing risk category, this being the
  second such defect after the Q-005 language fallback, and added a money
  handling check under `fr_CA` to the acceptance table because the decimal
  separator differs and the fee, free threshold, and minimum are compared
  numerically.
- Recorded the delivery hours change confirmed on 2026-08-21, from Monday to
  Friday 12:00-21:00 to every day 12:00-21:00, keeping the original value and the
  change date rather than overwriting them. The stored schedule is deliberately
  not updated yet: opening it to seven days would hide the defect exactly as
  pickup already does, and would destroy the Sunday observation that is the
  remaining end-to-end confirmation.
- Removed the weekend-closed acceptance item, which is no longer expected
  behaviour, and restated the hours item as delivery being unorderable before
  12:00 and after 21:00 on any day.
- Added four acceptance checks for the owner's delivery on/off switch, which
  decision 6's holiday plan depends on and which had never been tested: how long
  it takes to take effect, what happens to a customer already mid-order, whether
  delivery disappears or only blocks at checkout, and whether switching back on
  fully restores. They require writing to a shared setting and will stop for
  approval first.

Documentation only. Every changed path is `.md`. No stored value was changed.

## 2026-08-21 - Weekday schedule correction deployed, awaiting the Sunday reading

Environment: repository plus a 0%-traffic Canada staging deployment. Status: PR
open; eligible for auto-merge under the standing Level 0 docs-only
authorization.

- Recorded that the weekday defect is fixed and deployed to
  `d3c-fix-be6835a9` at 0% of traffic, and that it is **not yet confirmed in the
  deployed environment**. The distinction is kept explicit rather than letting
  "fixed" imply "verified in place".
- Recorded the Sunday window as one-shot and high priority. Only Friday and
  Sunday distinguish the fixed from the unfixed behaviour; Friday's window
  closed at 21:00 on 2026-08-21 and the next is a week away. Changing the
  delivery hours to every day would make both versions open every day and
  destroy the difference permanently, so the hours must not change before the
  reading is taken.
- Recorded what happens if the window is missed: the fix stands on unit tests
  alone and the record must say it was never confirmed in the deployed
  environment.
- Recorded that no same-day comparison is possible, with the reason rather than
  the conclusion alone. The hours display is built from `ScheduleItem::
  getHours()`, the saved settings, not from the corrected schedule, so it does
  not move. The only storefront path that consults the corrected schedule is
  `Location::checkOrderTime()`, and at line 360 it returns early when future
  orders are disabled and the shop is closed, before `isOpenAt()` is reached,
  identically on both copies.
- Recorded the `getType()` hardening as a methodology case in
  `AGENT_WORKFLOW.md` section 8b. The first draft threw out of an event
  listener, which would have broken page rendering. Review did not catch it; the
  test did, and only because the test built a real schedule and ran the real
  listener rather than asserting against a double.

No runtime change beyond deploying a 0%-traffic revision. Main traffic stayed on
its revision and its fingerprint was unchanged before and after.

## 2026-08-22 - Saturday acceptance results

Environment: Canada staging, 0%-traffic copies only. Status: PR open; eligible
for auto-merge under the standing Level 0 docs-only authorization.

- Cleared both rows that had been downgraded to rendered-only or left unstarted
  because the shop was closed, this time to the executed standard.
- Pickup reachable after an address-provider failure: passed. An item was added
  during opening hours, a delivery address was submitted and failed for want of
  a provider, and the basket was then followed through to a working pickup
  checkout showing "Cueillette, in 25 minutes" with the basket intact. Stopped
  at the confirm step; no order was placed.
- Basket kept through a failed address lookup: passed. The item and the totals
  survived unchanged at CA$13.99, and no delivery fee was added, which confirms
  the order stayed in pickup rather than half-entering delivery.
- Answered the question of whether a basket can be filled while closed. It
  cannot: the refusal happens at the moment of adding rather than at checkout,
  the basket stays empty, and the customer is told the order time is outside
  opening hours. The opposite case was confirmed during opening hours on
  Saturday, so the refusal is attributable to the hours rather than to anything
  else.
- Recorded that the refusal message is English on a French site and mixes in the
  French word Cueillette. That is a third locale-related defect after the
  language fallback and the weekday shift.
- Marked the pickup 22:00 cut-off as executed rather than rendered: an order was
  attempted at 22:31 and refused, rather than a closed label being read.
- Scanned the checkout page, a surface not previously examined. Everything is
  absent except the already-recorded Maps key, which appears on every page
  including the live one. No new leak class.

Saturday gives no information about the weekday fix: delivery is closed by
configuration on Saturday on both the fixed and the unfixed copy. The Sunday
window remains the only runtime confirmation.

No runtime change. Main traffic stayed on its revision, fingerprint unchanged.

## 2026-08-22 - Three locale-adjacent symptoms, three separate causes

Environment: repository documentation, plus code and configuration inspection.
Status: PR open; eligible for auto-merge under the standing Level 0 docs-only
authorization.

- Tested the proposal that the English refusal message, the money format, and
  Q-001 share a root cause. One of the three merges; the other does not.
- Corrected the framing first. The locale is not failing to apply. Localization
  sets the application locale and `Carbon::setLocale()` from the same value, and
  the weekday shift is itself proof that Carbon received a Canadian locale.
  Recording these as "localisation not in effect" would have been wrong and
  would have pointed two of the three fixes in the wrong direction.
- English text on a French page merges into Q-001. The key
  `alert_outside_hours` exists only in the package's `lang/en/default.php`. The
  project already carries local overrides under `lang/vendor/`, including
  `igniter-cart/fr_CA/default.php`, but not this key, so it falls back to
  English. The French word inside the English sentence is the order type name,
  which comes from stored configuration rather than a language file.
- The money format does not merge. `config/currency.php` in the package sets
  `formatter` to null and the project publishes no override, so amounts are
  formatted by `number_format()` from the separators held on the currency
  record. The locale-driven `PHPIntl` formatter exists but is not in use.
  Changing translations would not change how money reads.
- The weekday shift merges with neither. It is caused by the locale working.
- Recorded Quebec's French-language requirements as a production gate rather
  than a defect, because it is not a technical judgement. The observable fact is
  recorded, that customer-facing English is served from a Montreal restaurant's
  checkout, along with the questions professional advice needs to answer. No
  legal conclusion is stated, and none should be inferred from this record.
- Recorded that translation work must not begin on an assumed reading of the
  requirement, because what needs translating depends on the answer.

Documentation only. Every changed path is `.md`. No stored value was changed.

## 2026-08-22 - Saturday regression, mobile, and accessibility

Environment: Canada staging, 0%-traffic copies only. Status: PR open; eligible
for auto-merge under the standing Level 0 docs-only authorization.

- Regression smoke across ten pages that do not depend on Delivery: health, home,
  menu, reservation, locations, contact, about, policy, login, register. All
  served, all with plausible sizes.
- At 390 pixels wide: no sideways scrolling, no element wider than the viewport,
  no broken images. Browser console carried no messages at all.
- Focus is visibly indicated, and no element carries a positive tabindex, so tab
  order is not distorted.
- Recorded a verified accessibility failure. Each menu item's add button
  contains only a decorative icon, with no text, no aria-label, and no title, so
  it has no accessible name. A screen reader announces "button" with no
  indication of the dish or the action.
- Recorded whether the keyboard can add an item as inconclusive rather than
  guessing. Enter on the focused button through the tooling produced no request
  to the server, which would be severe if real, but clicking the same button
  directly works and a browser turns Enter on a focused button into a click. The
  likelier reading is that the tooling never delivered the keypress as an
  activation. It needs a person at a real keyboard to settle, which is recorded
  as the next step rather than assumed either way.
- Recorded six text links below the 24-pixel guidance height. Widths are fine.
- All of the above are theme-level and present on the live site. None was
  introduced by this phase, and the record says so, so that a later reader does
  not treat them as regressions from enabling Delivery.
- Split the admin regression into its own row and left it unstarted: there is no
  admin access from here.

No runtime change. Main traffic stayed on its revision, fingerprint unchanged.
