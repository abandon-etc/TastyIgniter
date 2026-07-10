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
- Added `birthday_slot_code` and `birthday_slot_key` to the existing
  reservations schema with a unique location/key index. Occupancy uses the
  configured default and confirmed status IDs; non-occupying statuses clear the
  key while preserving the reservation record.
- Added server-side availability and conflict checks, a table-independent
  Birthday reservation guard, and an additive rollback migration.
- No vendor/core, order, payment, authentication, security, notification,
  takeout, production, or business-data changes were made.
- Validation: all new PHP files linted, Dockerfile.cloudrun built successfully,
  config cache succeeded, and Birthday rules tests passed with 4 tests and 11
  assertions. Staging migration and concurrency testing remain pending.

Next step: review and merge the implementation PR, then enable the flag only on
Canada staging and execute migration, backend conflict, frontend, and smoke
tests. Do not start payment, registration, or add-on work yet.
