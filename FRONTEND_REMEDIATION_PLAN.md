# FRONTEND_REMEDIATION_PLAN

> **2026-08-24 状态说明**:本文写于旧版主题时期,其中 Q-002 的"未发现可见
> 语言切换组件、/en_CA 路由 404"分析与 Q-004 的"第一版 Pickup only"前提
> 均已过时:当前主题页头已渲染 `Français | English`(`/language/{locale}`
> 路由),但英文切换尚不生效;配送正随 D3C 启用,首页配送组件属有意为之。
> 语言与本地化工作的现行规划见 `LOCALIZATION_WORKSTREAM_PLAN.md`(板块二);
> 本文余下内容作为背景分析与安全规则(Carté Key 规则仍然有效)保留。

本文件记录第五阶段前台问题修复方案设计。当前阶段只做调查和方案设计，不直接实施主题改造，不写功能代码，不写入数据库，不登录后台，不提交订单或预约。

## 目标

本阶段目标是为第一批配置后的前台问题制定最小、非侵入式修复方案。

本阶段不做这些事：

- 不修改 TastyIgniter core。
- 不修改 `vendor/`。
- 不直接修改 `vendor/tastyigniter/ti-theme-orange`。
- 不修改订单、支付、预约冲突、登录认证或安全逻辑。
- 不写入数据库。
- 不登录后台。
- 不提交真实订单或真实预约。
- 不提交密码、密钥、token、Carté Key、真实顾客信息或真实支付信息。

## 当前前台问题摘要

| 问题编号 | 摘要 | 当前状态 | 是否影响上线 |
| --- | --- | --- | --- |
| Q-001 | `fr_CA` 已创建、启用并设为默认，但翻译数量为 `0/2992`；导入翻译需要 TastyIgniter Carté Key。 | Open | Yes |
| Q-002 | `fr_CA` 和 `en_CA` 已启用，但前台没有可见语言切换入口。 | Open | Yes |
| Q-004 | 第一版已关闭或后置 Delivery，但首页仍显示 delivery address 搜索入口，可能误导顾客。 | Open | Yes |
| Q-005 | `<html lang>` 已是 `fr_CA`，但前台仍大量英文-only。 | Open | Yes |

## Q-004：首页 Delivery address 搜索入口分析

### 来源文件或组件

首页来源：

- `vendor/tastyigniter/ti-theme-orange/resources/views/_pages/home.blade.php`

首页中加载了 Orange 主题的本地搜索组件：

- `vendor/tastyigniter/ti-theme-orange/src/Livewire/LocalSearch.php`
- `vendor/tastyigniter/ti-theme-orange/resources/views/livewire/local-search.blade.php`

该组件使用 `SearchesNearby` trait：

- `vendor/tastyigniter/ti-theme-orange/src/Livewire/Concerns/SearchesNearby.php`

可见文案主要来自语言 key：

- `igniter.local::default.text_order_summary`
- `igniter.local::default.label_search_query`
- `igniter.local::default.button_search_location`

这些 key 的英文源文件在：

- `vendor/tastyigniter/ti-ext-local/resources/lang/en/default.php`

当前英文源文案包括：

- `Find a restaurant near you`
- `Enter delivery address`
- `GO`

### 是否可后台配置

代码层面确认 `LocalSearch` 组件有 `hideSearch` 属性：

- `hideSearch`：隐藏搜索框，并显示 View Menu / Find 菜单按钮。

Orange 主题文档也说明 `igniter-orange::local-search` 支持：

- `hideSearch`
- `menusPage`

当前首页 page front matter 中组件配置为空：

```text
'[igniter-orange::local-search]': []
```

因此最小配置方向是把首页 `local-search` 组件的 `hideSearch` 设为 `true`。这可能可以通过后台页面/主题编辑器配置；具体后台入口和按钮名称需要登录后台实际确认。当前阶段不登录后台，因此不能确认按钮名称。

### 是否需要主题改造

优先级判断：

1. 先尝试后台页面组件配置，把 `hideSearch` 设为 `true`。
2. 如果后台不能编辑该组件属性，下一步应通过自定义主题或主题覆盖修改首页页面配置。
3. 不应直接修改 `vendor/tastyigniter/ti-theme-orange`。

### 推荐修复方案

推荐最小安全方案：

1. 优先在后台或自定义主题中设置首页 `Local Search` 组件的 `hideSearch = true`。
2. 首页只显示“View Menu / Menu”入口，不显示 delivery address 搜索框。
3. 后续用法语和英语翻译该按钮，例如：
   - French: `Voir le menu`
   - English: `View Menu`
4. 菜单页继续保留当前 Pickup 显示：`Pick-up · in 30 min`。

如果仍需要首页说明文字，推荐使用后台可配置页面内容或主题覆盖添加双语说明：

- French: `Commande en ligne pour cueillette seulement.`
- English: `Online ordering is pickup only.`

### 风险

- 如果只翻译 `Enter delivery address` 为法语，但仍保留地址搜索框，顾客仍可能误以为支持配送。
- 如果直接删组件，可能影响首页跳转到菜单页的入口。
- 如果直接改 `vendor/tastyigniter/ti-theme-orange`，未来升级会被覆盖。

### 是否影响上线

Yes。第一版业务策略是 Pickup only。首页显示 delivery address 搜索入口会误导顾客，因此 Q-004 应优先修复。

## Q-002：语言切换入口分析

### 当前语言机制

代码层面确认 TastyIgniter 有 localization middleware：

- `vendor/tastyigniter/core/src/Flame/Translation/Middleware/Localization.php`
- `vendor/tastyigniter/core/src/Flame/Translation/Localization.php`

当前加载顺序：

1. 从 URL 第一段读取 locale。
2. 从浏览器语言读取 locale。
3. 从 session 读取 locale。
4. 使用后台默认语言。

当前本地只读测试结果：

- `/fr_CA` 返回 `404`。
- `/en_CA` 返回 `404`。
- `/fr_CA/default/menus` 返回 `404`。
- `/en_CA/default/menus` 返回 `404`。

这说明当前站点不能简单使用 `/fr_CA/...` 或 `/en_CA/...` 作为前台切换链接，除非后续同时补齐路由支持。

Session locale key：

- `igniter.translation.locale`

代码中有 `setSessionLocale($locale)` 方法，但当前未发现前台已有可见语言切换 UI 或现成切换路由。

### 是否已有组件

当前未发现 Orange 主题有可见语言切换组件。相关导航文件是：

- `vendor/tastyigniter/ti-theme-orange/resources/views/includes/header.blade.php`
- `vendor/tastyigniter/ti-theme-orange/resources/views/includes/navs/main-menu.blade.php`

当前主导航来自 meta menu：

- `vendor/tastyigniter/ti-theme-orange/resources/meta/menus/main-menu.php`

菜单文字大多使用语言 key，例如：

- `igniter.orange::default.menu_menu`
- `igniter.orange::default.menu_reservation`
- `igniter.orange::default.menu_login`
- `igniter.orange::default.menu_register`

没有发现现成 `Français | English`、语言下拉框、国旗或 globe 图标组件。

### 推荐语言切换形式

推荐第一版使用文字链接：

```text
Français | English
```

原因：

- 对非程序员和顾客最清楚。
- 不依赖国旗，避免语言和国家混淆。
- 移动端也容易排版。

### 推荐放置位置

推荐位置：

1. 桌面端：页头导航右侧，靠近 Login / Register。
2. 移动端：折叠菜单内，放在主导航项下方。
3. 页脚可放第二个入口，但不能只放页脚。

### 是否需要主题改造

需要。当前未发现后台配置可直接打开语言切换入口，也未发现 Orange 主题已有可见组件。

推荐非侵入式实现方向：

- 创建自定义主题或 Orange 子主题。
- 覆盖 header 或 nav partial。
- 增加一个小型前台语言切换入口。
- 切换时设置 session locale，然后返回当前页面。

后续实现时需要一个非常小的、安全的切换动作。它只应设置 locale，不应修改订单、支付、预约、认证或安全逻辑。

### 语言切换后如何返回当前页面

推荐行为：

1. 顾客在任意页面点击 `Français` 或 `English`。
2. 系统只更新 session locale。
3. 系统重定向回当前页面。

不建议第一版直接使用 URL prefix，因为当前本地测试 `/fr_CA` 和 `/en_CA` 返回 `404`。

### 风险

- 如果只加入链接但不设置 session，页面不会真正切换。
- 如果直接使用 URL prefix，当前路由会 404。
- 如果语言包缺失，切换到法语后仍可能显示英文，见 Q-005。

## Q-005：英文-only 文案分析

### 可以通过语言包解决

大量系统文案使用 `@lang(...)` 或 `lang(...)`，理论上可以通过 TastyIgniter Marketplace 语言包解决。

优先覆盖的 namespace：

- `igniter.local::default`：首页搜索、菜单页、门店、营业状态、Pickup / Delivery 相关文案。
- `igniter.orange::default`：Orange 主题标题、导航、按钮、账号、newsletter、cookie 等主题文案。
- `igniter.cart::default`：购物车、结账、订单、自取/配送时间相关文案。
- `igniter.reservation::default`：预约页、预约表单、预约成功/失败提示。
- `igniter.user::default`：登录、注册、顾客账户文案。
- `igniter::system` 和 `igniter::main`：通用系统时间格式、版权、部分通用文案。

如果有 Carté Key，优先用后台 Import translations 或 `igniter:language-install fr_CA` 路线安装官方/社区翻译包，再人工校对魁北克语境。

### 可以通过 `lang/vendor` 覆盖解决

如果暂时没有 Carté Key，可以先用本地翻译覆盖关键前台文案。TastyIgniter 的 FileLoader 支持这些 override 路径形式：

- `lang/vendor/<namespace-with-slash>/<locale>/<file>.php`
- `lang/vendor/<namespace-with-hyphen>/<locale>/<file>.php`

推荐第一版优先使用 hyphen 路径，例如：

- `lang/vendor/igniter-local/fr_CA/default.php`
- `lang/vendor/igniter-orange/fr_CA/default.php`
- `lang/vendor/igniter-cart/fr_CA/default.php`
- `lang/vendor/igniter-reservation/fr_CA/default.php`
- `lang/vendor/igniter-user/fr_CA/default.php`
- `lang/vendor/igniter/fr_CA/system.php`
- `lang/vendor/igniter/fr_CA/main.php`

短期不要一次性翻译 2992 条。只覆盖上线关键前台文案。

### 可以通过后台内容配置解决

这些内容应优先通过后台配置，不应写死在主题里：

- 店铺名称。
- 店铺公开地址、电话、邮箱。
- 菜单分类。
- 商品名称。
- 商品描述。
- 商品选项。
- 静态页面，例如 About Us、Privacy Policy。
- 首页/说明页文案，如果后台页面编辑器可配置。
- 邮件模板。

菜单页当前显示 demo content，这是示例数据和未完成菜单配置，不属于主题语言问题。后续应通过后台清理 demo 菜单，并配置真实冰淇淋菜单。

### 需要主题改造解决

这些问题很可能需要主题改造：

- 前台语言切换入口。
- 首页 Delivery address 搜索框隐藏或替换。
- 如果后台不能改首页说明文案，需要主题覆盖首页页面。
- 如果某些按钮或布局中的文案不是语言 key，而是主题硬编码，需要主题覆盖。
- 移动端语言切换位置和样式。

当前代码中页脚 meta menu 里存在直接英文项：

- `About Us`
- `Privacy Policy`

这些可能可通过后台静态页面/菜单配置处理；如果后台不能处理，则进入主题或菜单配置调整。

### 暂时可接受的 demo content

以下内容第一版配置前可以暂时接受，但上线前必须处理：

- 菜单页示例商品。
- 示例分类。
- 示例价格。
- 示例评论数量。

这些不建议在本阶段通过代码处理。后续 PR D 应通过后台内容配置或清理 demo data 处理。

## Q-001：Carté Key / 翻译方案分析

### Carté Key 用途

Carté Key 用于访问 TastyIgniter Marketplace，安装或更新 Marketplace 项目，包括语言包。

代码确认：

- `vendor/tastyigniter/core/config/system.php` 读取 `IGNITER_CARTE_KEY`。
- `vendor/tastyigniter/core/src/System/Traits/ManagesUpdates.php` 在安装/更新前检查 Carté Key。
- `vendor/tastyigniter/core/src/System/Console/Commands/LanguageInstall.php` 用于从 Marketplace 拉取语言包。
- `vendor/tastyigniter/core/src/System/Classes/LanguageManager.php` 会把下载的语言包写入 `lang/vendor/.../<locale>/<file>.php`。

### 安全配置方式

可以选择以下安全方式之一：

1. 本机 `.env` 中配置：

```text
IGNITER_CARTE_KEY=<your-local-carte-key>
```

2. 后台 `Updates / Marketplace` 页面 Attach Carté Key。

安全规则：

- 不要把 Carté Key 发到聊天窗口。
- 不要写入 GitHub。
- 不要写入 `PROJECT_NOTES.md`、`CHANGELOG_AI.md` 或其他仓库文档。
- 不要提交 `.env`。
- 不要截图包含 Carté Key 的页面。

### 不使用 Carté Key 时的替代方案

可以先用本地 `lang/vendor` 覆盖关键前台文案。

优点：

- 不需要 Marketplace。
- 不需要 Carté Key。
- 只覆盖第一版上线关键文案。
- 不改 core、不改 vendor。

缺点：

- 需要人工维护翻译 key。
- 未来 TastyIgniter 或扩展升级后，新增 key 需要补充。
- 如果上游语言包后来安装，可能需要合并本地定制文案。

### 推荐短期方案

短期建议：

1. 先不要强行解决全部 2992 条。
2. 优先通过 `lang/vendor` 覆盖首页、菜单页、预约页、购物车和导航关键文案。
3. 所有法语文案由懂魁北克法语的人审核。
4. 保留 Q-001，等有 Carté Key 时再尝试 Marketplace 导入。

### 推荐长期方案

长期建议：

1. 安全配置 Carté Key。
2. 尝试导入 `fr_CA` 语言包。
3. 如果 Marketplace 不支持 `fr_CA`，评估 `fr_FR` 作为基础，再改成魁北克语境。
4. 保留项目本地 `lang/vendor` 覆盖，用于品牌语气、魁北克表达和关键业务文案。
5. 升级 TastyIgniter 后重新检查 untranslated keys。

## 修复优先级

建议顺序：

1. 先解决 Q-004，避免顾客误以为支持 Delivery。
2. 再解决 Q-002，增加语言切换入口。
3. 再解决 Q-005 的关键前台文案。
4. Q-001 根据是否有 Carté Key，决定走 Marketplace 导入还是本地翻译文件。

理由：

- Q-004 直接影响顾客是否理解第一版是 Pickup only。
- Q-002 是魁北克英法双语体验的入口。
- Q-005 是实际翻译质量问题，需要分批处理。
- Q-001 是翻译来源问题，不应阻塞先修复最明显的顾客误导。

## 推荐实施方式

推荐最小非侵入式方案：

- 不修改 core。
- 不修改 `vendor/`。
- 不直接改 `vendor/tastyigniter/ti-theme-orange`。
- 如需改主题，应创建自定义主题或 Orange 子主题。
- 优先使用后台页面组件配置。
- 优先使用 `themes/` 放主题覆盖。
- 优先使用 `lang/vendor/` 放翻译覆盖。
- 只改前台展示，不改订单、支付、预约冲突、登录认证或安全逻辑。

建议技术路线：

1. Q-004：先尝试后台把首页 `local-search` 组件设置为 `hideSearch = true`；如果后台不能配置，则通过自定义主题覆盖首页页面。
2. Q-002：在自定义主题中覆盖 header/nav，增加 `Français | English`；使用 session locale 切换并返回当前页面。
3. Q-005：先补 `lang/vendor` 关键文案；再清理后台 demo content；最后处理主题硬编码。
4. Q-001：有 Carté Key 时导入语言包；没有 Carté Key 时继续本地覆盖。

## 下一阶段可执行任务拆分

### PR A：禁用或改写首页 Delivery 搜索入口

目标：

- 首页不再显示 delivery address 搜索入口。
- 顾客明确看到第一版是 Pickup only。

预计改哪些文件：

- 如果后台可配置：只更新 `ADMIN_CONFIGURATION_TRACKER.md` 和 `CHANGELOG_AI.md` 记录操作结果。
- 如果需要主题覆盖：新增自定义主题或子主题文件，覆盖首页 page 或相关 partial。
- 可能新增 `themes/<custom-theme>/resources/views/_pages/home.blade.php` 或等价主题页面文件。

不应改哪些文件：

- 不改 `vendor/tastyigniter/ti-theme-orange`。
- 不改 `vendor/`。
- 不改 core。
- 不改订单、支付、预约、认证、安全逻辑。

验收方式：

- 首页返回 `200`。
- 首页不显示 `Enter delivery address`。
- 首页不显示误导性 Delivery 搜索框。
- 首页有清晰菜单入口。
- 菜单页仍显示 `Pick-up · in 30 min`。
- 移动端首页没有明显布局破损。

### PR B：添加前台语言切换入口

目标：

- 前台页头和移动端菜单显示 `Français | English`。
- 顾客可以切换法语和英语。

预计改哪些文件：

- 自定义主题或子主题的 header/nav partial。
- 可能新增一个小型前台 route/controller 或 Livewire/action，用于设置 session locale。
- 可能新增 `lang/vendor/igniter-orange/fr_CA/default.php` 和 `en_CA` 文案覆盖。

不应改哪些文件：

- 不改 `vendor/tastyigniter/ti-theme-orange`。
- 不改 core。
- 不改登录认证逻辑。
- 不改顾客账号、订单、支付或预约逻辑。

验收方式：

- 首页、菜单页、预约页可见 `Français | English`。
- 点击语言后返回当前页面。
- `<html lang>` 随选择变为对应 locale。
- 移动端也能看到语言切换入口。
- 不提交表单、不创建订单、不创建预约。

### PR C：补充关键前台法语翻译或 `lang/vendor` 覆盖

目标：

- 先覆盖第一版上线关键前台文案，不一次性翻译全部 2992 条。

预计改哪些文件：

- `lang/vendor/igniter-local/fr_CA/default.php`
- `lang/vendor/igniter-orange/fr_CA/default.php`
- `lang/vendor/igniter-cart/fr_CA/default.php`
- `lang/vendor/igniter-reservation/fr_CA/default.php`
- 需要时增加 `en_CA` 覆盖，确保英语也可控。

不应改哪些文件：

- 不改 `vendor/` 原始语言文件。
- 不改 core。
- 不改业务逻辑。

验收方式：

- 首页、菜单页、预约页、购物车关键按钮和提示不再英文-only。
- 法语文案由店主或懂魁北克法语的人审核。
- 英语切换后仍可读。
- 页面不出现 missing translation key。

### PR D：清理 demo content / 准备菜单配置

目标：

- 移除或替换当前示例菜单内容。
- 准备冰淇淋店真实菜单配置。

预计改哪些文件：

- 如果只通过后台配置：不需要代码文件，只更新 tracker / changelog。
- 如果需要导入模板数据：应先设计安全的数据导入方案，不能直接混入主题修复 PR。

不应改哪些文件：

- 不改 core。
- 不改 vendor。
- 不改订单、支付、预约冲突、认证或安全逻辑。

验收方式：

- 菜单页不再显示示例非冰淇淋商品。
- 菜单分类符合 `BUSINESS_CONFIGURATION_PLAN.md`。
- 商品价格由店主确认后录入。
- 不使用真实顾客订单测试。

## 哪些事情不要现在做

- 不接入真实支付。
- 不提交 Carté Key。
- 不修改 core。
- 不修改 `vendor/`。
- 不做复杂预约字段。
- 不提交真实顾客信息。
- 不提交真实支付信息。
- 不一次性翻译 2992 条。
- 不直接上线机器翻译。
- 不开发自定义预约冲突检测。
- 不把语言切换做成会破坏当前路由的 URL prefix。
- 不把 Delivery 相关文案简单翻译成法语后继续保留误导性搜索框。

