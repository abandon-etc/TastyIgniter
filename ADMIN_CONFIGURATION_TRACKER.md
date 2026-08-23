# ADMIN_CONFIGURATION_TRACKER

本文件用于记录后台实际配置进度、未完成事项和遇到的问题。它是给不会编程的店主填写的表格，不需要写代码。

## 使用说明

- 这个文件用于记录后台配置进度。
- 不记录密码、密钥、token、真实顾客信息或真实支付信息。
- 可以记录公开店铺信息，例如公开电话、公开地址、公开邮箱。
- 如果某个后台字段不确定，先写“待确认”。
- 如果某个界面和指南不一致，记录问题，不要猜。
- 截图文件名不要包含管理员邮箱、密码、顾客姓名、电话、订单号或支付信息。
- 如果某个配置会影响上线，请在问题跟踪表中标记。

## 第一批配置目标

第一批只配置这四类内容：

1. 语言设置。
2. 店铺基础信息。
3. 营业时间。
4. 自取设置。

第一批不要配置：

- 菜单分类。
- 商品选项。
- 商品。
- 生日派对预约。
- 支付方式。
- 真实邮件服务。

这些内容放到后面批次。第一批的目标是先确认网站基础信息和法语默认、英语可切换的基础方向。

## 第一批配置前准备清单

| 项目 | 我准备填写的值 | 是否可以写入后台 | 是否可以写入 GitHub 文档 | 备注 |
| --- | --- | --- | --- | --- |
| 店铺名称 | 待填写 | Yes | Yes, 如果是公开店名 | 公开品牌名称可以记录。 |
| 公开地址 | 待填写 | Yes | Yes | 公开营业地址可以记录。 |
| 城市 | 待填写 | Yes | Yes | 例如 Quebec 城市或实际城市。 |
| 省份 | Quebec | Yes | Yes | 可写公开省份。 |
| 邮编 | 待填写 | Yes | Yes, 如果是公开营业地址邮编 | 如果不想公开，可只写“已配置”。 |
| 公开电话 | 待填写 | Yes | Yes | 公开店铺电话可以记录。 |
| 公开邮箱 | 待填写 | Yes | Yes | 公开联系邮箱可以记录。 |
| 时区 | 待确认 | Yes | Yes | 使用店铺所在地对应时区，后台选项需要确认。 |
| 默认货币 | CAD | Yes | Yes | 上线前仍需检查税费。 |
| 默认语言 | French | Yes | Yes | 第一版要求法语默认。 |
| 第二语言 | English | Yes | Yes | 英语作为可切换语言。 |
| 每周营业日 | 待填写 | Yes | Yes | 可记录公开营业安排。 |
| 每天开门时间 | 待填写 | Yes | Yes | 可记录公开营业时间。 |
| 每天关门时间 | 待填写 | Yes | Yes | 可记录公开营业时间。 |
| 节假日规则 | 待确认 | Yes | Yes | 先记录规则，不需要一次配置完。 |
| 是否第一版只做自取 | Yes | Yes | Yes | 建议第一版只启用 Pickup。 |
| 普通订单提前准备时间 | 待填写 | Yes | Yes | 例如准备时间分钟数，但由店主决定。 |
| 冰淇淋蛋糕提前准备时间 | 待填写 | Yes | Yes | 例如 24 或 48 小时，由店主决定。 |
| 生日派对提前预约时间 | 待填写 | Later | Yes | 第一批先准备，不在后台配置生日预约。 |
| 管理员密码 | 不记录 | Yes, 只在后台登录时输入 | No | 不要写入 GitHub 文档。 |
| 数据库真实密码 | 不记录 | No | No | 不要写入 GitHub 文档。 |
| 邮件服务密码 | 不记录 | Later | No | 上线前配置，不记录在文档。 |
| 支付密钥 | 不记录 | Later | No | 第一批不配置支付。 |
| 真实顾客信息 | 不记录 | No | No | 不上传、不记录。 |
| 真实支付信息 | 不记录 | No | No | 不上传、不记录。 |

## 语言设置记录表

| 配置项 | 目标值 | 后台实际选项 | 是否已完成 | 遇到的问题 | 截图文件名，可选，不要包含敏感信息 |
| --- | --- | --- | --- | --- | --- |
| 法语是否可添加 | Yes | Yes, `Français` | Yes | Q-001：翻译导入失败，翻译数量为 `0/2992`。 |  |
| 英语是否可添加 | Yes | Yes, `English` | Yes |  |  |
| 法语 locale | `fr_CA` 优先；不支持则记录实际可用值 | `fr_CA` | Yes | Q-001：需要 Carté Key 或本地翻译方案。 |  |
| 英语 locale | `en_CA` 优先；不支持则记录实际可用值 | `en_CA` | Yes |  |  |
| 默认语言是否设为法语 | Yes | Yes, `fr_CA` is default | Yes |  |  |
| 前台是否能看到语言切换 | Yes | No | No | Q-002：Orange theme 当前未显示语言切换入口。 |  |
| 语言切换是否影响首页 | Yes | 待填写 | No | 待确认 |  |
| 语言切换是否影响菜单页 | Yes | 待填写 | No | 待确认 |  |
| 语言切换是否影响预约页 | Yes | 待填写 | No | 待确认 |  |
| 是否需要后续主题改造 | Unknown | Yes, likely needed | No | Q-002：需要后续主题改造或主题覆盖确认。 |  |

填写提示：

- 如果后台没有 `fr_CA`，请不要强行猜。记录实际可选项。
- 如果没有语言切换按钮，问题分类通常是“主题改造”。
- 如果语言切换存在但某些页面不变，请记录具体页面。

## 店铺基础信息记录表

| 配置项 | 目标值 | 后台实际字段名 | 是否已完成 | 遇到的问题 | 备注 |
| --- | --- | --- | --- | --- | --- |
| 店铺名称 | 已配置 | `locations.location_name` | Yes |  | 已写入本地开发数据库；文档不记录完整值。 |
| 地址 | 已配置 | `locations.location_address_1` | Yes |  | 已写入本地开发数据库；文档不记录完整值。 |
| 城市 | 已配置 | `locations.location_city` | Yes |  | 已写入本地开发数据库。 |
| 省份 | Quebec | `locations.location_state` | Yes |  | 已写入本地开发数据库。 |
| 邮编 | 已配置 | `locations.location_postcode` | Yes |  | 已写入本地开发数据库；文档不记录完整值。 |
| 电话 | 已配置 | `locations.location_telephone` | Yes |  | 已写入本地开发数据库；文档不记录完整值。 |
| 邮箱 | 已配置 | `locations.location_email` / `settings.site_email` | Yes |  | 已写入本地开发数据库；文档不记录完整值。 |
| 时区 | `America/Toronto` | `settings.timezone` | Yes |  | 已写入本地开发数据库。 |
| 货币 | CAD | CAD | Yes | Q-003：CAD rate 已修复为 `1.00000000`。 | 税费上线前再确认。 |
| 默认语言 | French | `fr_CA` | Yes |  | 第一版必须法语默认。 |

## 营业时间记录表

| 星期 | 是否营业 | 开门时间 | 关门时间 | 是否已配置 | 备注 |
| --- | --- | --- | --- | --- | --- |
| Monday | Yes | 12:00 | 22:00 | Yes | 已写入 `opening` 和 `collection` 时间。 |
| Tuesday | Yes | 12:00 | 22:00 | Yes | 已写入 `opening` 和 `collection` 时间。 |
| Wednesday | Yes | 12:00 | 22:00 | Yes | 已写入 `opening` 和 `collection` 时间。 |
| Thursday | Yes | 12:00 | 22:00 | Yes | 已写入 `opening` 和 `collection` 时间。 |
| Friday | Yes | 12:00 | 22:00 | Yes | 已写入 `opening` 和 `collection` 时间。 |
| Saturday | Yes | 12:00 | 22:00 | Yes | 已写入 `opening` 和 `collection` 时间。 |
| Sunday | Yes | 12:00 | 22:00 | Yes | 已写入 `opening` 和 `collection` 时间。 |

### 节假日/特殊营业时间问题记录表

| 日期或规则 | 是否营业 | 特殊开门时间 | 特殊关门时间 | 后台是否支持 | 备注 |
| --- | --- | --- | --- | --- | --- |
| 待填写 | 待填写 | 待填写 | 待填写 | 待确认 | 例如节假日、学校假期、冬季营业时间。 |
| 待填写 | 待填写 | 待填写 | 待填写 | 待确认 |  |
| 待填写 | 待填写 | 待填写 | 待填写 | 待确认 |  |

记录提示：

- 如果生日派对预约时间和普通营业时间不一致，先记录，不要在第一批配置。
- 如果后台没有节假日设置入口，记录到问题跟踪表。

## 自取设置记录表

| 配置项 | 目标值 | 后台实际字段名 | 是否已完成 | 遇到的问题 | 备注 |
| --- | --- | --- | --- | --- | --- |
| Pickup 是否启用 | Yes | `location_settings.collection.is_enabled` | Yes |  | 已写入本地开发数据库。 |
| Delivery 是否关闭或后置 | Closed / Later | `location_settings.delivery.is_enabled` | Yes |  | 已写入本地开发数据库，第一版不启用配送。 |
| 普通订单提前准备时间 | 30 minutes | `location_settings.collection.lead_time` | Yes |  | 已写入本地开发数据库。 |
| 冰淇淋蛋糕提前准备时间 | Not configured in first batch | 不写入 | Deferred |  | 第一版暂不做冰淇淋蛋糕预订，仅保留后续规划。 |
| 前台是否显示自取说明 | Yes | 菜单页显示 `Pick-up · in 30 min` | Yes |  | 非登录前台只读检查已确认。 |
| 前台是否误显示配送 | No | 首页已隐藏 delivery address 搜索 | Yes | Q-004 已通过项目级 Orange 视图覆盖解决。 | 菜单页仍显示 Pickup，首页改为点单和预约入口。 |
| 是否需要后续主题改造 | Yes | 项目级 Orange 视图覆盖 | Yes | Q-005 仍需后续处理。 | 首页搜索模块和语言切换入口已非侵入式覆盖；完整翻译仍未完成。 |

## 问题跟踪表

| 问题编号 | 发现位置 | 问题描述 | 是否影响上线 | 分类：后台配置 / 翻译配置 / 主题改造 / 扩展开发 / 待确认 | 建议下一步 | 状态：Open / Resolved / Deferred |
| --- | --- | --- | --- | --- | --- | --- |
| Q-001 | Settings → Languages → `fr_CA` → Import translations | `fr_CA` 已创建、启用并设为默认，但翻译数量为 `0/2992`；导入翻译时报错：`A carte key is required to install/update from the TastyIgniter marketplace.` | Yes | 翻译配置 | 安全配置 TastyIgniter Carté Key 后重试导入；如果暂时没有 Carté Key，后续可评估本地 `lang/` 或 `lang/vendor/` 翻译文件方案。不要把 Carté Key 写入聊天、GitHub 或文档。 | Open |
| Q-002 | 前台首页 | `fr_CA` 和 `en_CA` 都已启用，但前台没有可见语言切换入口。代码检查未发现 Orange theme 内置可见语言切换组件。 | Yes | 主题改造 | 已新增项目级 Orange header view override，前台显示 `Français | English`；点击后通过 `/language/fr_CA` 或 `/language/en_CA` 设置 session locale 并返回当前页面。未使用 `/fr_CA` 或 `/en_CA` URL prefix。 | Resolved |
| Q-003 | Settings / Currencies | 删除币种后 Currency / Currencies 页面报错。数据库中 CAD 仍存在且为默认币种，但 `currency_rate` 为 `0.00000000`；日志还显示 Currency 列表页渲染 `currency_rate` 浮点值时触发 TastyIgniter core 类型错误。 | Yes | 后台配置 / 待确认 | 已在本地开发数据库将 CAD 设为唯一默认币种、启用状态，并将 rate 修复为 `1.00000000`。店主已在后台确认 Currencies 页面恢复，CAD 存在、为默认币种且 rate 为 1。 | Resolved |
| Q-004 | 前台首页 | 第一批已关闭或后置 Delivery，但首页仍显示 delivery address 搜索入口；菜单页显示 Pickup，不显示 Delivery。 | Yes | 主题改造 | 已通过项目级 Orange 视图覆盖隐藏首页 local search / delivery address 搜索区域，并替换为按当前语言显示的点单和预约入口按钮；未修改 core、`vendor/` 或数据库。 | Resolved |
| Q-005 | 前台首页、菜单页、预约页、购物车入口 | `<html lang>` 已可在 `fr_CA` / `en_CA` 间切换，但前台仍有未完整本地化内容。 | Yes | 翻译配置 / 主题改造 | 已新增少量 `lang/vendor` 覆盖并将首页 CTA 改为语言 key：`fr_CA` 显示法语单语，`en_CA` 显示英语单语；已部分覆盖导航、Pickup、预约和购物车关键文案。完整站点翻译、后台 demo content 和未覆盖主题文案仍需后续处理。 | Open |
| Q-006 | 菜单页 / 购物车 / 结账入口 | 初次验证时当前本地时间不在营业时间内，前台显示 `CLOSED`，导致无法完成加入购物车和 checkout 验证。临时打开本地营业时间后，测试商品可加入购物车，数量可增加 / 减少，可移除商品，并可进入 checkout 表单。 | No | 前台流程 / 后台配置 | 已确认原因是测试时段不在营业时间内；测试后已从 `.local/backups/q006-before-temp-open-20260708-102353.json` 恢复原始营业时间和 collection 配置。未提交订单，未提交预约，未配置真实支付。 | Resolved |
| Q-007 | Canada staging / Birthday Booking snapshot validation | UTC booking instants were stored correctly, but the default Eloquent `datetime` cast reinterpreted them in the Toronto application timezone after reload. | No | Birthday extension / datetime persistence | Resolved by PR #57 with the extension-owned `UtcDateTime` cast. Canada staging database round trips verified UTC hydration plus correct Toronto daylight-saving and standard-time display for start/end, priced, and cancelled instants. | Resolved |
| Q-008 | Birthday Booking add-on snapshot hydration | Add-on snapshots could reload in source add-on ID order because the Booking relationship lacked an explicit historical ordering. | No | Birthday extension / snapshot presentation | Resolved by PR #57. The relationship now orders by immutable `sort_order_snapshot` and then snapshot row ID; Canada staging reloaded `First` before `Late` even though `Late` was created first. | Resolved |
| Q-011 | Canada staging Delivery geocoding failure path | The PR #69 project-owned redaction wrapper was exercised in a same-image, 0%-traffic isolated revision. The final clean synthetic matrix covered empty results, forward/reverse failures, autocomplete, place lookup, business validation, successful reverse geocoding, and the closed Delivery API gate. Exact-window application and Cloud Run log scans found no full address, provider URL, credential, raw provider exception, geometry, SQL/internal ID, database diagnostic, or PHP path exposure. | No; resolved for the tested Canada staging failure path | Keep `DELIVERY_ENABLED=false` until a separately reviewed D3C enablement. Public Nominatim remains not approved for production Delivery traffic without stable identity, attribution, shared cross-instance rate limiting, and an accepted operating model. | Resolved |

分类说明：

- 后台配置：后台可以直接解决。
- 翻译配置：语言包导入、本地翻译文件、翻译数量或 Marketplace Carté Key 相关问题。
- 主题改造：后台不能改，但属于前台显示、文案、按钮、布局或语言切换问题。
- 扩展开发：需要新字段、复杂规则、自动化流程或系统行为变化。
- 待确认：现在还不知道原因，需要先截图和描述。

状态说明：

- Open：还没解决。
- Resolved：已经解决。
- Deferred：暂时接受，放到后续阶段。

## 第一批配置完成后的验收清单

| 检查项 | 是否完成 | 备注 |
| --- | --- | --- |
| 后台可以登录 | No | 不记录账号和密码。 |
| 法语已启用 | Yes | 实际 locale：`fr_CA`；翻译数量仍为 `0/2992`，见 Q-001。 |
| 英语已启用 | Yes | 实际 locale：`en_CA`。 |
| 法语为默认语言 | Yes | `fr_CA` 已设为默认语言。 |
| 前台可访问 | Yes | `http://127.0.0.1:8000` 返回 `200`。 |
| 前台语言切换情况已记录 | Yes | 前台已显示 `Français | English`，见 Q-002。 |
| 店铺基础信息已配置 | Yes | 店铺名称、地址、城市、省份、邮编、电话、邮箱、时区、CAD 已写入本地开发数据库；不在文档记录完整联系方式。 |
| 营业时间已配置 | Yes | 每天 12:00-22:00，已写入 `opening` 和 `collection` 时间。 |
| Pickup 设置已检查 | Yes | Pickup 已启用，普通订单准备时间为 30 minutes。 |
| Delivery 是否后置已确认 | Yes | Delivery 已关闭或后置，第一版不启用配送。 |
| 没有录入密码、密钥、真实顾客信息或真实支付信息 | Yes | 本次只写入公开店铺信息和本地开发配置。 |
| 遇到的问题已记录到问题跟踪表 | Yes | 已记录 Q-001、Q-002、Q-003、Q-004、Q-005。 |

## 非登录前台只读检查记录

检查日期：2026-07-07

检查方式：只读 HTTP 检查和浏览器检查。未登录后台，未提交订单，未提交预约，未输入真实顾客信息，未测试真实支付。

| 检查项 | 结果 | 备注 |
| --- | --- | --- |
| 首页是否返回 200 | Yes | `http://127.0.0.1:8000` 返回 `200`。 |
| 首页是否正常显示 | Yes | 未发现系统错误或异常页面。 |
| 首页是否显示店铺信息 | Yes | 店铺名称可见；文档不记录完整地址、电话或邮箱。 |
| 首页是否有语言切换入口 | Resolved | 后续语言切换实施已显示 `Français | English`，见 Q-002。 |
| `<html lang>` 是否为法语 locale | Yes | 当前为 `fr_CA`。 |
| 首页是否明显英文-only | Yes | 首页导航、搜索和 cookie 文案仍为英文，见 Q-005。 |
| 菜单页是否能打开 | Yes | 浏览器可打开 `http://127.0.0.1:8000/default/menus`；HTTP 直访会先重定向。 |
| 菜单页是否出现系统错误 | No | 未发现系统错误。 |
| 菜单页内容状态 | Demo content | 菜单仍是示例内容；第一批配置未包含菜单和商品，所以暂不作为新问题。 |
| 预约页是否能打开 | Yes | `http://127.0.0.1:8000/default/reservation` 可打开，未提交预约。 |
| 预约页是否出现系统错误 | No | 未发现系统错误。 |
| 购物车入口是否能打开 | Yes | `http://127.0.0.1:8000/cart` 可打开，空购物车状态未提交订单。 |
| 结账入口是否能访问 | Partial | 空购物车访问 `http://127.0.0.1:8000/checkout` 会跳回菜单页；未提交订单。 |
| Pickup / Collection 是否显示 | Yes | 菜单页显示 `Pick-up · in 30 min`。 |
| Delivery 是否仍显示 | Resolved on homepage | 原首页 delivery address 搜索入口已在后续首页 CTA 实施中隐藏，见 Q-004。菜单页未显示 Delivery。 |
| 移动端导航是否明显破损 | No | 390px 宽度下未发现明显横向溢出；导航可见。 |
| 移动端是否有语言切换入口 | Resolved | 后续语言切换实施已确认 390px 宽度下 `Français` 和 `English` 均可见，见 Q-002。 |

## 首页单店 CTA 实施检查记录

检查日期：2026-07-08

检查方式：只读 HTTP 检查和浏览器检查。未登录后台，未提交订单，未提交预约，未写入数据库，未输入真实顾客信息，未测试真实支付。

| 检查项 | 结果 | 备注 |
| --- | --- | --- |
| 首页是否返回 200 | Yes | `http://127.0.0.1:8000` 可访问。 |
| 首页是否仍显示 `Enter delivery address` | No | 首页 HTML 未发现该文案。 |
| 首页是否仍显示 `Find a restaurant near you` | No | 首页 HTML 未发现该文案。 |
| 首页是否仍显示地址搜索框 | No | 未发现 `#search-query`、`#local-search-form` 或 `location-search`。 |
| 首页是否显示点单按钮 | Yes | 显示 `Commander / Order Now`。 |
| 首页是否显示生日派对预约按钮 | Yes | 显示 `Réserver une fête / Book a Party`。 |
| 点单按钮跳转 | Yes | 点击后打开 `http://127.0.0.1:8000/default/menus`。 |
| 预约按钮跳转 | Yes | 点击后打开 `http://127.0.0.1:8000/default/reservation`。 |
| 菜单页是否仍显示 Pickup | Yes | 菜单页显示 `Pick-up` 和 `in 30 min`。 |
| 菜单页是否出现系统错误 | No | 浏览器检查未发现系统错误。 |
| 预约页是否出现系统错误 | No | 浏览器检查未发现系统错误。 |
| 移动端 390px 是否可见 | Yes | 两个按钮在 390px 宽度下可见并未超出屏幕。 |
| Q-004 状态 | Resolved | 首页误导性的 Delivery / 地址搜索入口已隐藏。 |
| Q-002 状态 | Resolved | 后续语言切换实施已显示 `Français | English`。 |
| Q-005 状态 | Open | 后续关键文案本地化已将首页 CTA 改为按当前语言显示；完整站点翻译仍未完成。 |

## 前台语言切换实施检查记录

检查日期：2026-07-08

检查方式：只读 HTTP 检查和浏览器检查。未登录后台，未提交订单，未提交预约，未写入数据库，未输入真实顾客信息，未测试真实支付。

| 检查项 | 结果 | 备注 |
| --- | --- | --- |
| 首页是否返回 200 | Yes | `http://127.0.0.1:8000` 可访问。 |
| 后台登录页是否返回 200 | Yes | `http://127.0.0.1:8000/admin/login` 可访问，未登录后台。 |
| 前台是否显示语言切换入口 | Yes | 显示 `Français | English`。 |
| 默认语言是否仍为法语 | Yes | 初始 `<html lang>` 为 `fr_CA`。 |
| 点击 English 是否可切换 | Yes | 点击后 session locale 变为 `en_CA`，并返回当前页面。 |
| 点击 Français 是否可切换 | Yes | 点击后 session locale 变为 `fr_CA`，并返回当前页面。 |
| 是否使用 `/fr_CA` 或 `/en_CA` URL prefix | No | 本次使用 `/language/fr_CA` 和 `/language/en_CA`，不使用 locale prefix。 |
| 是否限制 locale | Yes | 只允许 `fr_CA` 和 `en_CA`；其他 locale 返回 `404`。 |
| 是否避免 open redirect | Yes | 外部 `Referer` 会回到首页，站内 `Referer` 才返回原页面。 |
| 移动端 390px 是否可见 | Yes | `Français` 和 `English` 均可见且未超出屏幕。 |
| Q-002 状态 | Resolved | 前台可见语言切换入口已完成。 |
| Q-001 状态 | Open | Carté Key / 法语翻译导入仍未处理。 |
| Q-005 状态 | Open | 完整法语翻译和英文-only 文案仍未处理。 |

## 关键前台文案本地化检查记录

检查日期：2026-07-08

检查方式：只读 HTTP 检查、Docker 容器内 PHP 语法检查和页面内容检查。未登录后台，未提交订单，未提交预约，未写入数据库，未输入真实顾客信息，未测试真实支付。

| 检查项 | 结果 | 备注 |
| --- | --- | --- |
| 首页是否返回 200 | Yes | `http://127.0.0.1:8000` 可访问。 |
| 菜单页是否返回 200 | Yes | `http://127.0.0.1:8000/default/menus` 可访问。 |
| 预约页是否返回 200 | Yes | `http://127.0.0.1:8000/default/reservation` 可访问。 |
| 购物车页是否返回 200 | Yes | `http://127.0.0.1:8000/cart` 可访问，未提交订单。 |
| 后台登录页是否返回 200 | Yes | `http://127.0.0.1:8000/admin/login` 可访问，未登录后台。 |
| 语言切换入口是否仍显示 | Yes | `Français | English` 仍可见。 |
| `fr_CA` 首页 CTA 是否为法语单语 | Yes | 显示 `Commandez en ligne pour cueillette...`、`Commander`、`Réserver une fête`；不再显示 `Order Now` 或 `Book a Party`。 |
| `en_CA` 首页 CTA 是否为英语单语 | Yes | 显示 `Order online for pickup...`、`Order Now`、`Book a Party`。 |
| 首页是否仍隐藏 Delivery / Find Location 搜索 | Yes | 未发现 `Enter delivery address` 或 `Find a restaurant near you`。 |
| 菜单页 Pickup 文案是否部分本地化 | Yes | `fr_CA` 下可见 `Cueillette` / `dans 30 min` 相关文案。 |
| 预约页关键文案是否部分本地化 | Yes | `fr_CA` 下可见预约标题、人数、日期、时间和操作按钮的法语覆盖。 |
| 购物车空状态是否部分本地化 | Yes | `fr_CA` 下可见 `Panier` 或空购物车提示的法语覆盖。 |
| 移动端布局风险 | Low | 本次未修改 CTA 的 Bootstrap 响应式布局类，沿用前一阶段已验证的按钮结构；本地 Chrome 只读工具不能可靠切换到 390px 视口。 |
| Q-001 状态 | Open | 本次未处理 Carté Key，也未导入 Marketplace 完整语言包。 |
| Q-002 状态 | Resolved | 前台语言切换入口仍可用。 |
| Q-004 状态 | Resolved | 首页地址搜索入口仍保持隐藏。 |
| Q-005 状态 | Open | 关键首页、Pickup、预约和购物车文案已部分覆盖；完整站点翻译、后台 demo content 和剩余英文文案仍需后续处理。 |

## 配置完成后反馈给 Codex 的内容

请把以下内容反馈给 Codex，不要提供密码、密钥、token、真实顾客信息或真实支付信息：

- 法语是否添加成功。
- 法语实际 locale。
- 英语是否添加成功。
- 英语实际 locale。
- 法语是否已经设为默认语言。
- 前台是否有语言切换按钮。
- 店铺基础信息是否已配置。
- 营业时间是否已配置。
- Pickup 是否启用。
- Delivery 是否关闭或后置。
- 第一批遇到的问题编号和描述。
- 如果有截图，只提供不含敏感信息的截图文件名或截图内容描述。

## 第一版前台视觉系统检查记录

检查日期：2026-07-08

检查方式：只读 HTTP 检查和浏览器渲染检查。未登录后台，未提交订单，未提交预约，未写入数据库，未输入真实顾客信息，未测试真实支付。

| 检查项 | 结果 | 备注 |
| --- | --- | --- |
| 首页是否返回 200 | Yes | `http://127.0.0.1:8000` 可访问。 |
| 首页是否隐藏 hero slider / 大图 / 装饰图 | Yes | `#slider-home-slider` 已隐藏，首页非菜单图片区域不显示。 |
| 首页是否保留原图片区域空白 | No | Header 下方直接进入粉色背景和居中 CTA。 |
| 首页背景是否为 solid `#FAC8D5` | Yes | 浏览器检查背景为 `rgb(250, 200, 213)`。 |
| 首页 CTA 是否居中 | Yes | 桌面端和 390px 移动端均可见且居中。 |
| 点单按钮是否仍打开菜单页 | Yes | 按钮仍跳转到 `http://127.0.0.1:8000/default/menus`。 |
| 预约按钮是否仍打开预约页 | Yes | 按钮仍跳转到 `http://127.0.0.1:8000/default/reservation`。 |
| 语言切换是否仍可见 | Yes | `Français | English` 仍可见。 |
| `fr_CA` / `en_CA` 是否仍可切换 | Yes | `<html lang>` 可在 `fr_CA` 和 `en_CA` 间切换。 |
| 菜单页是否返回 200 | Yes | `http://127.0.0.1:8000/default/menus` 可访问。 |
| 菜单页商品图片是否保留 | Yes | 本次 CSS 未匹配或隐藏 `.menu-item-image`；当前本地 demo 数据未渲染商品缩略图元素。 |
| 菜单页商品卡片布局是否正常 | Yes | 桌面端和 390px 移动端商品卡片均正常显示。 |
| 菜单页 Pickup / Cueillette 是否正常 | Yes | `Cueillette` / Pickup 状态仍可见。 |
| 预约页是否返回 200 | Yes | `http://127.0.0.1:8000/default/reservation` 可访问，表单卡片风格统一。 |
| 购物车页是否返回 200 | Yes | `http://127.0.0.1:8000/cart` 可访问，空购物车状态正常。 |
| 结账页是否可访问 | Yes | 空购物车状态访问 `http://127.0.0.1:8000/checkout` 会按系统现有流程跳回菜单页，未修改订单流程。 |
| 登录页 / 注册页是否可访问 | Yes | `/login` 和 `/register` 可访问，表单卡片使用统一视觉风格。 |
| 后台登录页是否返回 200 | Yes | `http://127.0.0.1:8000/admin/login` 可访问，未登录后台。 |
| 是否使用渐变 | No | CSS 未使用 `linear-gradient`、`radial-gradient` 或 `background-image` 渐变。 |
| 是否使用危险图片隐藏规则 | No | 未使用全局 `img { display: none; }`、`.carousel { display: none; }` 或 `.card img { display: none; }`。 |
| Q-001 状态 | Open | 本次未处理 Carté Key 或 Marketplace 翻译导入。 |
| Q-002 状态 | Resolved | 语言切换入口仍正常。 |
| Q-004 状态 | Resolved | 首页 Delivery / Find Location 搜索入口仍隐藏。 |
| Q-005 状态 | Open | 本次只统一视觉，不完成全站翻译；关键本地化仍保持前一阶段部分覆盖状态。 |

## 菜单真实数据录入决策记录

记录日期：2026-07-08

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| 大量真实菜单数据录入 | Deferred | 本地 Docker 后台响应慢，暂时不在本地大量录入真实菜品。 |
| 本地测试菜单数据 | Keep minimal | 本地只保留少量测试分类、测试商品、测试价格和可选测试图片。 |
| 菜单真实数据录入位置 | Later | 等正式或准生产环境准备好后，再通过管理员后台录入真实菜单。 |
| 性能判断 | Local-only concern | 当前慢主要来自 Windows bind mount、Laravel / TastyIgniter 小文件读取、`php artisan serve`、CLI OPcache 未生效、`APP_DEBUG=true` 和缓存未启用。 |
| 生产环境判断 | Pending validation | Linux + Nginx / PHP-FPM + OPcache + `APP_DEBUG=false` + Laravel 缓存后，后台性能预计会明显改善，但仍需准生产验证。 |
| 数据保护 | Required | 部署后录入真实菜单前，必须确认生产数据库、`storage` / uploads 和 `.env` 不会被本地配置或部署脚本覆盖。 |

详细计划见 `MENU_DATA_ENTRY_PLAN.md`。

## 部署准备 checklist 记录

记录日期：2026-07-08

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| 部署准备 checklist | Done | 已新增 `DEPLOYMENT_READINESS_CHECKLIST.md`。 |
| 真实菜单数据保护 | Pending | 部署前必须确认生产数据库不会被重建，`storage` / uploads 会持久化并备份。 |
| 生产配置保护 | Pending | 生产 `.env`、Carté Key、邮件配置和支付密钥不得进入 GitHub，也不能被本地配置覆盖。 |
| 备份流程 | Pending | 真实菜单录入前后都需要数据库和上传目录备份。 |

## Render 部署方案记录

记录日期：2026-07-08

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| Render 部署方案 | Done | 已新增 `RENDER_DEPLOYMENT_PLAN.md`，仅作为方案设计，不直接部署。 |
| Render Web Service 路线 | Recommended | 第一版建议使用 Render Docker Web Service，但当前 Dockerfile 仍是本地开发方案，后续需要单独准备生产 Docker / Nginx / PHP-FPM / OPcache。 |
| 数据库路线 | External MySQL / MariaDB recommended | 不建议第一版直接切换到 Render PostgreSQL；当前项目和本地验证均基于 MySQL / MariaDB。 |
| Persistent Disk | Required | `storage`、uploads / media 和菜品图片必须持久化；`public/media` 需要后续通过安全 symlink 或等价方案接入持久磁盘。 |
| 生产环境变量 | Pending | Render Environment Variables 需要人工配置；`.env`、Carté Key、邮件密码和支付密钥不得进入 GitHub。 |
| 自动初始化风险 | Blocked until confirmed | 生产环境不得自动运行会清空、重建或写入 demo 数据的命令；数据库迁移必须先备份并人工确认。 |

## Render 方案 A 架构决策记录

记录日期：2026-07-08

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| 部署架构 | Decided | 已正式选择方案 A：Render Docker Web Service 跑应用，外部托管 MySQL / MariaDB 跑数据库。 |
| Render PostgreSQL | Deferred | 第一版暂不使用；当前项目、本地 Docker、配置验证和 Q-006 流程验证均基于 MySQL / MariaDB。 |
| Render Persistent Disk 自托管数据库 | Rejected | 不在 Render Persistent Disk 上自托管 MySQL / MariaDB。 |
| Render Persistent Disk 用途 | Required | 只用于 `storage`、uploads / media、菜品图片和必要运行时文件。 |
| Cloudflare 域名 | Pending setup | 域名已购买；后续 Render 创建 Web Service 后，在 Render 添加 custom domain，再到 Cloudflare 配置 DNS。 |
| 生产密钥保护 | Required | 不提交真实域名私密配置、Cloudflare API token、数据库密码、Render secret、Carté Key、支付密钥或邮件密码。 |
| 下一阶段 | Recommended | 创建 Render production runtime PR，准备 Nginx + PHP-FPM + OPcache + Render `$PORT` + Persistent Disk symlink / directory setup + `.dockerignore` + safe startup script。 |

## Render Production Runtime 记录

记录日期：2026-07-08

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| Render production runtime 文件 | Added | 已新增 `Dockerfile.render`、`docker/render/nginx.conf.template`、`docker/render/php-production.ini`、`docker/render/start.sh` 和 `RENDER_RUNTIME_READINESS.md`。 |
| 本地 Docker baseline | Preserved | 未修改现有 `Dockerfile` 和 `docker-compose.yml`。 |
| Nginx / PHP-FPM | Added | Render runtime 使用 Nginx + PHP-FPM，不使用 `php artisan serve`。 |
| OPcache | Added | 已新增生产 PHP / OPcache 配置。 |
| Render `$PORT` | Added | 启动脚本会渲染 Nginx template，优先使用 Render 提供的 `$PORT`。 |
| Persistent Disk setup | Added | 启动脚本会创建 `storage` 运行时目录，并安全处理 `public/storage` 和 `public/media` symlink。 |
| Destructive commands | Blocked | 启动脚本不自动运行 `migrate:fresh`、`migrate:refresh`、`db:seed` 或 `igniter:install`。 |
| Docker build 验证 | Pass | `docker build -f Dockerfile.render -t tastyigniter-render-test .` 已成功。 |
| PHP 扩展检查 | Pass | 已确认 `bcmath`、`curl`、`exif`、`gd`、`intl`、`mbstring`、`pdo_mysql`、`zip` 和 `Zend OPcache` 存在。 |
| Nginx template 检查 | Pass | 使用 `PORT=10000` 渲染后，`nginx -t` 通过。 |
| 镜像敏感文件检查 | Pass | 已确认测试镜像内没有 `/var/www/html/.env` 或 `/var/www/html/.local`。 |
| Linux 行尾保护 | Added | `.gitattributes` 已固定 Render runtime 文件使用 LF 行尾。 |
| 真实环境配置 | Pending | 尚未配置真实数据库、真实域名 DNS、真实支付、真实邮件或 Render secrets。 |
| 下一步 | Pending | 创建 Render staging Web Service，并配置外部 MySQL / MariaDB、Persistent Disk 和 Render Environment Variables。 |

## Render composer.lock Build 修复记录

记录日期：2026-07-08

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| Render Dockerfile | Confirmed | Render staging 已确认使用 `Dockerfile.render`。 |
| Build 失败原因 | Identified | `Dockerfile.render` 强制 `COPY composer.json composer.lock ./`，但 GitHub 仓库当前没有 `composer.lock`。 |
| 修复方式 | Applied | 已改为 `COPY composer.* ./`，兼容存在或不存在 `composer.lock` 的情况。 |
| Composer install 逻辑 | Preserved | 后续 `composer install` 逻辑保持不变。 |
| 部署状态 | Pending | 本次只修复 build 输入文件复制问题，Render staging 仍需重新 build / deploy 验证。 |
| 生产数据 | Not touched | 未连接或写入生产数据库，未配置真实密钥或真实业务数据。 |

## Render staging 访问问题排查记录

记录日期：2026-07-08

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| 测试地址 | Checked | `https://le-chateau-des-enfants.onrender.com`。 |
| 静态资源 | Pass | `favicon.svg` 返回 200，说明域名、HTTPS、Render 入口和 Nginx 静态文件服务初步正常。 |
| 首页动态页面 | Failing | `/` 约 60 秒后返回 504。 |
| 后台登录页动态页面 | Failing | `/admin/login` 约 60 秒后返回 504。 |
| 菜单页动态页面 | Failing | `/default/menus` 在 20 秒内没有返回首字节。 |
| 初步判断 | Database connection likely | 动态页面卡住但静态文件正常，最可能是 Laravel / TastyIgniter 请求期间等待外部 MySQL / MariaDB 连接。 |
| 低风险修复 | Applied | MySQL 连接新增 `DB_CONNECT_TIMEOUT` 环境变量支持，默认 5 秒，避免数据库不可达时一直等待到 Render 504。 |
| 启动缓存默认值 | Adjusted | Render 启动脚本中 `RUN_CONFIG_CACHE` 默认值改为 `false`，避免 staging 数据库未确认时卡在 `php artisan package:discover` / `config:cache`。 |
| PHP socket 超时 | Adjusted | 生产 PHP 配置中 `default_socket_timeout` 设为 10 秒，作为外部服务网络等待的辅助保护；本地黑洞型数据库地址模拟中动态请求仍可能超过 20 秒。 |
| 健康检查端点 | Added | Nginx 新增 `/healthz` 静态健康检查端点，并让根路径 `HEAD /` 直接返回 200，避免 Render 健康探测占满 Laravel / PHP-FPM worker。 |
| 仍需确认 | Pending | Render Environment Variables 中的 `DB_*` 配置、外部 MySQL / MariaDB 防火墙和数据库初始化状态仍需在 Render / 数据库服务商后台确认。 |
| 生产数据 | Not touched | 未读取、连接或写入真实数据库，未提交密钥或真实业务数据。 |

### Render staging 数据库连接复查记录

记录日期：2026-07-08

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| `/healthz` | Pass | 返回 200 和 `ok`。 |
| 静态资源 | Pass | `/favicon.svg` 返回 200。 |
| 首页 `/` | Database Error | 返回 200，但页面标题为 `Database Error Was Encountered`，首字节约 10 秒。 |
| 后台登录页 `/admin/login` | Database Error | 返回 200，但页面标题为 `Database Error Was Encountered`，首字节约 10 秒。 |
| 菜单页 `/default/menus` | Database Error | 返回 200，但页面标题为 `Database Error Was Encountered`，首字节约 10 秒。 |
| 购物车 `/cart` | Database Error | 返回 200，但页面标题为 `Database Error Was Encountered`。 |
| 预约页 `/default/reservation` | Database Error | 返回 200，但页面标题为 `Database Error Was Encountered`。 |
| 当前判断 | Database configuration / initialization | Docker build、Nginx、PHP-FPM、静态资源和 health check 已基本正常；下一步应检查 Render `DB_*`、DigitalOcean Public host / port、Trusted Sources 和 staging 数据库是否初始化。 |
| Render Environment Variables | Not verified | 无 Render 后台权限，未读取或记录真实值。 |
| DigitalOcean Trusted Sources | Not verified | 无 DigitalOcean 后台权限，未读取或记录真实值。 |
| Render Shell `mysql select 1` | Not executed | 需要在 Render Shell / Console / one-off command 中执行，不要把密码写入文档或聊天。 |
| `show tables` | Not executed | 需要在 `select 1` 成功后执行，判断 staging 数据库是否为空或缺表。 |
| 敏感信息 | Not touched | 未提交 `.env`、`.local`、密码、密钥、token、APP_KEY、Carté Key、Render secret、DigitalOcean token、数据库密码、真实顾客信息或真实支付信息。 |

### DigitalOcean Managed MySQL primary key 限制记录

记录日期：2026-07-08

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| Render Shell `mysql select 1` | Pass | 已由用户确认成功；未记录真实 host、用户名或密码。 |
| `show tables` | Empty | 已由用户确认执行成功但没有返回业务表，当前数据库为 staging 空数据库。 |
| 初始化失败原因 | Identified | TastyIgniter 初始化遇到 `sql_require_primary_key=ON`，MySQL 报错要求建表必须有 primary key。 |
| DigitalOcean 全局设置 | Confirmed | DigitalOcean Managed MySQL 全局 `sql_require_primary_key=ON`。 |
| `SET GLOBAL` | Not allowed | 普通数据库用户不能执行全局修改。 |
| `SET SESSION` | Allowed | 普通数据库用户可以执行 `SET SESSION sql_require_primary_key = OFF`。 |
| 应用配置支持 | Added | `config/database.php` 已新增 `MYSQL_ATTR_INIT_COMMAND` 支持，可通过 Render Environment Variable 设置 session 初始化命令。 |
| 默认行为 | Preserved | 不设置 `MYSQL_ATTR_INIT_COMMAND` 时保持原行为，不默认关闭 primary key 要求。 |
| 数据库写入 | Not performed | 本次未写入数据库，未运行 `igniter:install`、`migrate:fresh`、`migrate:refresh` 或 `db:seed`。 |
| 敏感信息 | Not touched | 未提交 `.env`、`.local`、APP_KEY、DB_HOST、DB_USERNAME、DB_PASSWORD、Render secret、DigitalOcean token、Carté Key、支付密钥、真实顾客信息或真实支付信息。 |

## 前台流程低风险验证记录

记录日期：2026-07-08

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| 基础访问 | Pass | 首页、菜单页、预约页、购物车页和后台登录页均返回 200。 |
| 语言切换 | Pass | `Français | English` 可见，`fr_CA` / `en_CA` 可切换。 |
| 首页 CTA | Pass | 点单按钮打开菜单页，预约按钮打开预约页。 |
| 菜单展示 | Pass | 当前 demo 商品和商品卡片正常显示，Pickup / Cueillette 正常。 |
| 商品图片 | Not applicable | 当前本地 demo 数据未渲染 `.menu-item-image` 商品图片元素；未发现图片破损。 |
| 购物车加入商品 | Pass | Q-006 复测中临时打开本地营业时间后，测试商品可加入购物车。 |
| 数量修改和移除商品 | Pass | Q-006 复测中已验证数量可增加、减少，并可移除商品；空购物车状态可恢复。 |
| 结账入口 | Pass | Q-006 复测中有测试商品时可进入 checkout 表单，显示顾客信息、Pickup 和 payment area；未点击最终 `Confirm`。 |
| 预约入口 | Pass | 预约页显示日期、人数和时间选择；未提交预约。 |
| 移动端 390px | Pass | 首页、菜单页、购物车页、预约页和语言切换无明显破版。 |

### Q-006 临时营业时间复测记录

记录日期：2026-07-08

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| 备份是否创建 | Yes | 备份文件为 `.local/backups/q006-before-temp-open-20260708-102353.json`，不提交 GitHub。 |
| 临时修改范围 | Done | 仅临时修改本地开发数据库中的当前星期 `opening` / `collection` 时间，并确认 Pickup 启用、Delivery 关闭。 |
| 商品加入购物车 | Pass | 临时营业状态下，当前 demo 商品可加入购物车。 |
| 购物车商品显示 | Pass | 可显示商品名称、数量、价格、小计和订单总计。 |
| 数量修改 | Pass | 已验证数量可增加和减少。 |
| 移除商品 | Pass | 已验证商品可移除，空购物车状态可恢复。 |
| checkout 表单入口 | Pass | 有商品时可进入 checkout，显示顾客信息、Pickup 和 payment area。 |
| 是否提交订单 | No | 未点击最终 `Confirm` / `Place Order` / `Pay`。 |
| 是否提交预约 | No | 未提交预约。 |
| 是否配置真实支付 | No | 未配置真实支付，未测试真实支付。 |
| 是否恢复原配置 | Yes | 已按备份恢复原始营业时间和相关配置；当前星期 `opening` / `collection` 恢复为 `12:00-22:00`，Delivery 仍关闭。 |
| Q-006 状态 | Resolved | 原因确认为测试时段不在营业时间内。 |

详细检查见 `FRONTEND_FLOW_READINESS_CHECK.md`。

## Render staging 前台交互 smoke test 记录

记录日期：2026-07-09

环境：Render staging

当前阶段：前台交互 smoke test

| 检查项 | 结果 | 备注 |
| --- | --- | --- |
| `/healthz` | Pass | 返回 200，内容为 `ok`。 |
| 首页 `/` | Pass | 返回 200，前台页面可访问。 |
| 菜单页 `/default/menus` | Pass | 返回 200，`Test Category`、`Test Item` 和测试价格可见。 |
| 测试图片 URL | Pass | `/storage/media/uploads/staging-test-upload.png` 返回 200。 |
| 测试图片是否显示在菜单商品卡片 | Not shown | 当前 `Test Item` 卡片未渲染商品图片元素；测试上传文件本身存在且可访问。此项属于测试内容绑定问题，不是资源或上传持久化 blocker。 |
| Livewire JS | Pass | `/livewire/livewire.min.js?id=42cd7fd5` 返回 200，content type 为 JavaScript。 |
| 前台 CSS / JS | Pass | 首页、菜单页、购物车、预约页和 checkout 相关 CSS / JS 均返回 200；未发现 public `localhost` 资源 URL。 |
| 菜单页加入购物车 | Pass | 点击 `Test Item` 后购物车金额更新，Livewire / AJAX 请求正常，无控制台 error。 |
| 购物车数量修改 | Pass | 可增加 / 减少测试商品数量，金额随测试数量变化。 |
| 购物车删除商品 | Pass | 可将测试商品移除，空购物车状态可恢复。 |
| 空车后再次添加商品 | Pass | 再次点击 `Test Item` 可重新加入购物车。 |
| checkout 入口 | Pass | 有测试商品时从购物车点击 `Checkout` 可进入 checkout 表单；未点击最终 `Confirm`。 |
| checkout 表单 | Pass | 顾客字段、备注字段、条款勾选和 Cash On Delivery 选项可见。 |
| checkout 表单校验提交 | Skipped | 未提交 checkout 表单，避免创建测试订单、顾客记录或地址记录。 |
| 预约页 `/default/reservation` | Pass | 返回 200，页面正常显示。 |
| 预约控件 | Pass | 人数和时间下拉控件可选择测试值，无控制台 error。 |
| 预约提交 / 校验 | Skipped | 未点击 `Find Table` 或后续提交按钮，避免创建测试预约。 |
| 是否创建测试订单 | No | 本次未提交订单。 |
| 是否创建测试预约 | No | 本次未提交预约。 |
| 是否使用真实顾客数据 | No | 未填写真实顾客姓名、电话、邮箱、地址或支付信息。 |
| Render / Laravel / TastyIgniter 日志 | Pass | 最近日志未发现新的 HTTP 404 / 500、PHP fatal、Laravel exception、Livewire error、payment error 或 storage permission error。 |
| 非阻塞日志提示 | Noted | 发现 Nginx 对较大 `_assets` 响应有 upstream buffering warning，但对应资源返回 200 / 304；当前不影响 smoke test。 |
| 是否影响上线 | No blocker found | 目前未发现阻止进入下一阶段的 blocker。 |

下一步建议：可以进入 staging 性能基线测试；同时在正式内容录入阶段把测试图片或正式图片明确绑定到真实商品后，再复查菜单商品图片展示。

## Render staging 第一阶段性能优化记录

记录日期：2026-07-09

环境：Render staging

当前阶段：第一阶段性能优化：asset cache headers + buffering

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| 修改范围 | Prepared | 本阶段只调整 Render Nginx runtime 配置和项目记录文件。 |
| `_assets` / `admin/_assets` 当前来源 | Dynamic via Laravel | 当前仓库没有可提交的 `public/_assets` 或 `public/admin/_assets` 文件；Render 日志显示这些请求走 `fastcgi://127.0.0.1:9000`。 |
| `_assets` / `admin/_assets` 修改 | Prepared | 增加专用 named FastCGI location，保留 Laravel fallback，并设置 `Cache-Control: public, max-age=86400`。 |
| `/storage` / `/media` 修改 | Prepared | 增加 `Cache-Control: public, max-age=604800`。 |
| FastCGI buffering | Prepared | 只针对 TastyIgniter combined asset fallback 增大 FastCGI buffer，减少大 CSS / JS 写入临时文件触发 warning。 |
| `/livewire/` fallback | Preserved | 未修改 Livewire location，避免再次出现 Livewire JS 404。 |
| `/healthz` | Preserved | 健康检查仍为 Nginx 静态返回，不依赖 Laravel。 |
| Laravel route cache | Not enabled | 本 PR 不默认启用 route cache，避免影响 TastyIgniter extension / admin 动态路由。 |
| Laravel config / view cache | Not enabled | 本 PR 只记录后续可评估，不改变默认启动策略。 |
| 动态 HTML TTFB | Not addressed | 本 PR 不处理 DB 查询、TastyIgniter boot 或主题渲染。 |
| 测试订单 / 测试预约 | Not created | 未提交订单或预约。 |
| 生产配置 | Not touched | 未修改 production、Cloudflare、支付、邮件或真实菜单配置。 |
| 部署后复测 | Pending | 合并并部署到 staging 后，需要复测资源 headers、upstream buffering warning 和关键页面。 |

部署前验证要求：

- Nginx 配置语法检查通过。
- Docker build 成功。
- `/healthz`、前台页面、后台登录页、dashboard、Livewire JS、测试图片和测试内容在 staging 部署后复查。
- 复查 `_assets` / `admin/_assets` 是否有 `max-age=86400`。
- 复查 `/storage/media/uploads/staging-test-upload.png` 是否有 `max-age=604800`。
- 复查日志是否仍有 upstream buffering warning；如仍存在，记录后再进入下一轮优化。

剩余性能问题：

- 前台动态 HTML warm TTFB 仍需单独拆分 DB 查询、TastyIgniter boot、theme rendering 和 Laravel cache 影响。
- 后台 dashboard 主 JS 体积较大，后续可评估资源构建 / combiner 缓存策略。
- Laravel config / view cache 可作为下一阶段评估项；route cache 不应盲目默认启用。

## Render staging 第二阶段性能诊断记录

记录日期：2026-07-09

环境：Render staging

当前阶段：第二阶段性能诊断：动态 HTML TTFB 拆分

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| 本地分支 | Synced | 已同步最新 `4.x`，当前包含 PR #30：`6cb1e629 Improve Render asset caching and buffering (#30)`。 |
| Render live commit | Confirmed | Render Events 显示 `6cb1e62` 已 live，对应 PR #30。 |
| 诊断方式 | Read-only | 使用外部 HTTP timing、Render Shell 只读命令、PHP-FPM OPcache 检查、Laravel query event 统计和内部 `curl` timing。 |
| 代码修改 | No runtime change | 本阶段未修改运行代码、业务逻辑、TastyIgniter core 或 `vendor/`。 |
| 订单 / 预约 | Not created | 未提交测试订单，未提交测试预约。 |
| OPcache | Confirmed enabled | PHP-FPM `Server API => FPM/FastCGI`，`opcache.enable => On`，`opcache.validate_timestamps => Off`，`opcache.memory_consumption => 192`，`opcache.max_accelerated_files => 20000`。 |
| Laravel config cache | Disabled | 运行时 `RUN_CONFIG_CACHE=false`，`bootstrap/cache/config.php` 不存在。 |
| Laravel route cache | Disabled | 运行时 `RUN_ROUTE_CACHE=false`，未发现 route cache 文件；route cache 仍不建议默认启用。 |
| Laravel view cache | Disabled by startup flag | 运行时 `RUN_VIEW_CACHE=false`，但 `storage/framework/views` 已有约 128 个编译视图文件。 |
| APP_DEBUG | Pass | staging 运行时 `APP_DEBUG=false`。 |
| Cache / session | File-based | 运行时 `CACHE_DRIVER=file`，`SESSION_DRIVER=file`，`QUEUE_CONNECTION=sync`。 |
| 数据库基础延迟 | Slow enough to matter | Render Shell 中 Laravel bootstrap 后连续 `select 1` 平均约 151ms，说明每次数据库往返成本较高。 |
| 首页 `/` | DB-bound | 内部 HTTP TTFB 约 5.05-5.61s；Laravel query event 统计约 30 次查询，累计约 5.52s，全部查询超过 100ms。 |
| 菜单页 `/default/menus` | DB-bound | 内部 HTTP TTFB 约 7.28-8.06s；约 44 次查询，累计约 7.20s。抽样显示包含多次 schema / settings / extension / pages 查询。 |
| 购物车 `/cart` | DB-bound | 内部 HTTP TTFB 约 6.17-6.88s；约 37 次查询，累计约 6.05s。 |
| 预约页 `/default/reservation` | DB-bound | 内部 HTTP TTFB 约 6.27-6.83s；约 37 次查询，累计约 6.79s。 |
| 后台登录页 `/admin/login` | DB-bound | 内部 HTTP TTFB 约 2.81-3.11s；约 15 次查询，累计约 2.78s。 |
| Dashboard | Partially diagnosed | 已登录浏览器下 dashboard 可打开且无控制台 error；此前体感约 14s。CLI 未携带后台 session，`/admin/dashboard` 只验证到未登录 302，不作为 dashboard 查询拆分依据。 |
| 已排除因素 | Confirmed | 当前主要瓶颈不是 Cloudflare / 公网链路、Nginx buffering、静态资源缓存头、Livewire 404、上传图片或 OPcache 未启用。 |
| 主要瓶颈 | Database round trips | 内部容器请求与外部请求耗时接近，且 query 累计时间接近页面总耗时，当前最大耗时来源是远程数据库多次往返和 TastyIgniter 启动 / 页面渲染过程中重复查询。 |
| 低风险优化机会 | Recommended | 优先创建独立 PR 评估启用 Render 上的 config cache；view cache 可小范围验证；route cache 暂不默认启用。 |
| 进一步诊断机会 | Recommended | 如需更精确拆 dashboard 或具体重复 query 来源，建议单独做 staging-only、环境变量控制的轻量性能诊断 PR。 |
| 是否影响上线 | Performance risk | 当前不是功能 blocker，但 5-8s 动态 HTML TTFB 和 dashboard 慢会影响上线体验，production readiness 仍需性能优化或架构决策。 |

推荐后续 PR 拆分：

- PR A：`Enable safe Laravel config cache on Render`，先只启用 config cache，提供环境变量 fallback，不启用 route cache。
- PR B：`Add lightweight staging performance diagnostics`，仅在需要进一步定位 dashboard / query 来源时启用，必须由环境变量控制。
- PR C：`Evaluate Render database latency options`，评估数据库区域、连接方式、缓存或 Redis / persistent cache 策略。
- PR D：`Assess dashboard loading bottlenecks`，仅在拿到后台 session 下的 dashboard profile 后处理，不改订单、预约、支付或认证逻辑。

## Render staging config cache 启用记录

记录日期：2026-07-09

环境：Render staging

当前阶段：Enable safe Laravel config cache on Render

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| PR #31 合并后 smoke check | Pass | `/healthz`、首页、菜单页、购物车、预约页和后台登录页均返回 200；文档 PR 未引入运行变化。 |
| `RUN_CONFIG_CACHE` 默认值 | Updated | Render 启动脚本默认启用 config cache；如 staging 部署后异常，可设置 `RUN_CONFIG_CACHE=false` 回滚。 |
| 启动顺序 | Checked | `APP_URL` / `ASSET_URL` 的 Render fallback、runtime 目录和权限准备完成后，再运行 `package:discover` 和 `config:cache`。 |
| 关键配置来源 | Checked | `APP_URL`、`DB_*`、`MYSQL_ATTR_INIT_COMMAND`、`CACHE_DRIVER`、`SESSION_DRIVER`、`QUEUE_CONNECTION` 均通过 Laravel config 读取，会被 config cache 捕获。 |
| Route cache | Not enabled | `RUN_ROUTE_CACHE` 仍默认 `false`，不在本阶段启用。 |
| View cache | Not enabled | `RUN_VIEW_CACHE` 仍默认 `false`，不在本阶段启用。 |
| 业务逻辑 | Not touched | 未修改订单、支付、预约、认证、安全逻辑或 TastyIgniter core。 |
| 数据操作 | Not performed | 未运行 `migrate:fresh`、`migrate:refresh`、`db:seed`，未提交测试订单或测试预约。 |
| 敏感信息 | Not touched | 未提交 `.env`、`.local`、数据库 dump、真实上传文件、密码、密钥、token、APP_KEY、DB_PASSWORD、Render secret、DigitalOcean token、Cloudflare token、Carté Key、支付密钥、邮件密码或真实顾客信息。 |
| 部署后验证 | Pending | 合并并部署到 staging 后需复测前后台、Livewire、媒体、日志和动态 HTML TTFB。 |

下一步建议：合并本 PR 并部署 staging 后，验证 config cache 是否生成、页面功能是否正常、TTFB 是否有改善；如改善有限，继续评估数据库区域 / 连接路径和轻量 query diagnostics。

## Render staging 数据库延迟 / query 来源定位记录

记录日期：2026-07-09

环境：Render staging

当前阶段：database latency / query source diagnostics

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| 最新代码 | Confirmed | 本地 `4.x` 已同步到 `9cbbea34 Enable Render config cache (#32)`。 |
| Render live commit | Confirmed | Render Events 显示 `9cbbea3` 已 live。 |
| Config cache | Confirmed | 前序验证已确认 `RUN_CONFIG_CACHE=true`，`bootstrap/cache/config.php` 已生成。 |
| 现有公开页面诊断 | DB-bound | 已知 `/`、`/default/menus`、`/cart`、`/default/reservation`、`/admin/login` 的 query 累计耗时接近页面 TTFB。 |
| 数据库 RTT | Slow enough to dominate | 前序 `select 1` 平均约 151ms；页面 15-44 次查询足以解释 3-8s 动态 HTML TTFB。 |
| 已登录 dashboard | Needs instrumentation | 浏览器可打开且无控制台 error，但现有 CLI / curl 不携带后台 session，无法定位 dashboard 内部 query 来源。 |
| 诊断 PR | Prepared | 新增默认关闭、环境变量控制的 staging-only query 指纹 / timing 诊断能力。 |
| 诊断开关 | Safe default | `ENABLE_STAGING_PERF_DIAGNOSTICS=false` 默认关闭；只在 `APP_ENV` 非 `production` 的 staging 环境需要采样时设为 `true` 并重新部署。 |
| Production guard | Enforced | `APP_ENV=production` 时即使误设 `ENABLE_STAGING_PERF_DIAGNOSTICS=true`，诊断仍保持关闭。 |
| 输出内容 | Sanitized | 只记录 path、状态码、请求耗时、query count、query total、query fingerprint、分类和来源文件摘要；不记录 bindings、请求 body、cookie、session、CSRF token、用户 ID 或真实业务数据。 |
| Route / view cache | Not changed | 未启用 route cache 或 view cache。 |
| 业务逻辑 | Not touched | 未修改订单、支付、预约、认证、安全逻辑或 TastyIgniter core。 |
| 数据操作 | Not performed | 未运行 `migrate:fresh`、`migrate:refresh`、`db:seed`，未提交测试订单或测试预约。 |
| 敏感信息 | Not touched | 未提交 `.env`、`.local`、数据库 dump、真实上传文件、密码、密钥、token、APP_KEY、DB_PASSWORD、Render secret、DigitalOcean token、Cloudflare token、Carté Key、支付密钥、邮件密码或真实顾客信息。 |

下一步建议：合并并部署诊断 PR 后，在 Render staging 临时设置 `ENABLE_STAGING_PERF_DIAGNOSTICS=true`，采样公开页面和已登录 dashboard；完成采样后立即改回 `false` 并重新部署。

## Render staging performance diagnostics 采样记录

记录日期：2026-07-09

环境：Render staging

当前阶段：PR #33 部署与 staging performance diagnostics 采样

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| PR #33 | Merged / deployed | Render Events 显示 `bbd9376` 已 live。 |
| `APP_ENV` | Confirmed | Render Shell 显示 `APP_ENV=staging`，不是 `production`。 |
| Diagnostics 启用 | Completed | 短时间设置 `ENABLE_STAGING_PERF_DIAGNOSTICS=true` 并重新部署后采样。 |
| Diagnostics 关闭 | Completed | 采样后设置 `ENABLE_STAGING_PERF_DIAGNOSTICS=false` 并重新部署；config cache 中 `DIAG_ENABLED=false`。 |
| 关闭后验证 | Pass | 关闭后访问首页仍返回 200，`staging_perf_diagnostics` 日志计数未增加。 |
| `/` | DB-bound | duration 3018.26ms；query_count 19；query_total 2893.4ms；top 类别为 theme / pages / other。 |
| `/default/menus` | DB-bound | duration 5114.76ms；query_count 33；query_total 4978.89ms；top 类别为 settings / other / menus。 |
| `/cart` | DB-bound | duration 4377ms；query_count 26；query_total 4315.51ms；top 类别为 theme / pages / settings。 |
| `/default/reservation` | DB-bound | duration 4468.38ms；query_count 26；query_total 4372.31ms；top 类别为 theme / pages / settings。 |
| `/admin/login` | DB-bound | duration 710.24ms；query_count 4；query_total 688.27ms；top 来源包含 user login、settings 和 cart status middleware。 |
| `/admin/dashboard` | DB-bound / widget queries | POST duration 4739.67ms；query_count 24；query_total 3672.89ms；top 类别包含 users、orders aggregate 和 reservation aggregate。 |
| Smoke check | Pass | `/healthz`、首页、菜单页、购物车、预约页、后台登录页、Livewire JS 和已登录 dashboard 均返回 / 显示正常。 |
| 新错误日志 | None from sampling | 采样期间未发现新的 PHP fatal、Laravel exception、500 或 storage permission error；日志中两条 ERROR 为早前旧记录。 |
| 敏感信息 | Not touched | 未记录 SQL bindings、请求 body、cookie、session、CSRF token、用户 ID、真实顾客、真实订单、真实预约或真实支付数据。 |
| 数据操作 | Not performed | 未提交测试订单或测试预约；未运行 `migrate:fresh`、`migrate:refresh`、`db:seed`。 |

主要结论：动态 HTML TTFB 仍主要由远程数据库多次往返叠加造成；每个页面的 query_total_ms 基本覆盖 duration_ms。公开页面重复来源集中在 theme / pages / settings / menus；dashboard 额外包含订单、预约、客户和用户偏好等 widget / aggregate 查询。

下一步建议：优先创建 `Evaluate database latency options`，评估数据库区域、Render 到数据库连接路径和缓存策略；随后再评估 `Reduce repeated settings and schema queries` 与 `Assess cache backend for Render staging`。Cloudflare / custom domain / production 前置规划可以并行，但 production readiness 仍受动态 HTML TTFB 风险影响。

## Render staging database latency options 评估记录

记录日期：2026-07-09

环境：Render staging

当前阶段：Evaluate database latency options

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| 最新代码 | Confirmed | 本地 `4.x` 已同步到 PR #34 合并提交 `bd1c4fe0`。 |
| Render staging | Pass | PR #34 已自动部署；`/healthz`、首页、菜单页、购物车、预约页和后台登录页均返回 200。 |
| Render app 位置 | Inferred Oregon | Render Dashboard 未在普通文本中暴露 region 值；运行容器出口地理摘要为 Boardman, Oregon / AWS。 |
| DigitalOcean DB 位置 | Inferred New Jersey | 数据库主机未输出；解析 IP 的地理摘要为 Clifton, New Jersey / DigitalOcean。 |
| 连接类型 | Public / cross-cloud | 当前数据库连接为 public / external host，不是 Render private network。 |
| 架构判断 | Cross-cloud / cross-region | 当前路径为 Render Oregon / AWS 到 DigitalOcean New Jersey，跨云且跨美国东西部。 |
| PDO 新连接 | Slow | 8 次新建 PDO 连接平均 328.08ms，p50 332.93ms。 |
| PDO 同连接 `select 1` | Slow enough | 40 次同连接 `select 1` 平均 80.94ms，p50 82.68ms。 |
| Laravel 重连后首查 | Very slow | 8 次 Laravel `DB::purge()` 后首个 `select 1` 平均 651.35ms，p50 665.33ms。 |
| Laravel 同连接查询 | Dominant | 40 次 Laravel 同连接 `select 1` 平均 161.89ms，p50 166.3ms。 |
| 持久连接 | Not enabled | 当前 `config/database.php` 未启用 `PDO::ATTR_PERSISTENT`；本阶段未修改配置。 |
| Cloudflare / custom domain | Not primary fix | 可改善边缘 TLS、DNS、静态缓存和前端缓存，但不能降低 Render 到数据库的 RTT。 |

方案比较：

| 方案 | 预期收益 | 复杂度 / 风险 | 适合 staging 先试 | 结论 |
| --- | --- | --- | --- | --- |
| A. 保持 Render app，创建更靠近 Oregon 的 DigitalOcean Managed MySQL staging test DB | 中到高；可减少跨美国东西部 RTT，但仍跨云 / public internet | 需要新 DB、数据迁移或空库重装；有新增费用；需用户在 DO 创建资源并输入 secret | 是 | 推荐第一优先级实验。 |
| B. 保持当前 DigitalOcean DB，创建更靠近 New Jersey / NYC3 的 Render staging app | 中到高；Render Virginia / Ohio 到 NYC3 预计比 Oregon 到 New Jersey 更近 | Render 现有服务不能直接改 region，需要新 service；需重新配置 env、disk、custom staging URL | 是 | 推荐第二优先级，尤其当用户不想重建 DB。 |
| C. App 和 DB 放同一平台 / 同一区域 | 高；可消除跨云 RTT，若使用同平台 private network 收益更大 | 迁移复杂度较高；Render PostgreSQL 不兼容当前 MySQL 假设；DO App Platform 需重新评估 Docker/runtime | 是，但应新建 staging 试验 | 中期架构候选，不建议直接切 production。 |
| D. 增加 cache backend | 中；可减少 settings / pages / theme / menus 重复查询 | 需要确认 TastyIgniter cache 使用路径；Redis / Valkey 有费用和运维策略；不能消除所有 DB 查询 | 是 | 与区域实验并行评估。 |
| E. 优化应用重复查询 | 中；可减少 query_count | 不能改 vendor / core；需定位可扩展层缓存点，避免影响订单、支付、预约、认证 | 是，小 PR | 在区域/RTT验证后再拆小 PR。 |
| F. Cloudflare / custom domain | 低；改善边缘与静态资源体验 | 不会改善服务器到 DB 的 RTT | 是，但非性能主解法 | 可并行规划，不作为当前 DB-bound 慢的主修复。 |

推荐顺序：

1. 创建 `Create same-region staging database test`：优先测试更靠近 Render Oregon 的 DO MySQL 区域，或创建更靠近当前 DO DB 的 Render staging service，二选一先做成本更低的一项。
2. 创建 `Assess cache backend for Render staging`：评估 Redis / Valkey / persistent cache 是否减少 settings / pages / theme / menus 查询。
3. 创建 `Reduce repeated settings and schema queries`：只在确认可扩展层缓存点后做，不修改 vendor / TastyIgniter core。
4. Cloudflare / custom domain / production 前置规划可以并行，但 production readiness 仍受 DB RTT 风险影响。

阻塞：需要用户确认是否愿意为 same-region staging DB 或新 Render staging service 产生额外费用，并在 Render / DigitalOcean UI 中配置或输入 secret；不需要把 secret 发到聊天。

## Canada unified hosting architecture 评估记录

记录日期：2026-07-09

环境：planning / staging only

当前阶段：Evaluate Canada unified hosting architecture

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| 当前决策 | Updated | 用户已决定长期方向改为方案 C：app、database、media storage、cache、backup 尽量位于加拿大同一区域。 |
| PR #35 | Merged | `Evaluate database latency options` 已合并；其 Render + DigitalOcean 局部优化实验不再是第一优先级。 |
| 首选候选 | Recommended | Google Cloud Run + Cloud SQL for MySQL + Cloud Storage + 可选 Memorystore / Redis。 |
| 首选区域 | Recommended | Montréal `northamerica-northeast1`，因为目标用户在 Montréal，且 Google Cloud 官方文档列出 Cloud Run、Cloud SQL MySQL、Cloud Storage 和 Memorystore 均支持该区域。 |
| 备选区域 | Viable | Toronto `northamerica-northeast2`，同样支持核心组件；对 Montréal 用户略远，但仍可实现加拿大同区 app / DB / storage / cache。 |
| AWS Canada | Candidate with more ops | ECS / Fargate + RDS MySQL + S3 / EFS 在加拿大区域可行；App Runner 不建议作为新方案，因为 AWS 已公告 App Runner 将不再接受新客户。 |
| DigitalOcean Canada | Candidate if simplifying | DigitalOcean TOR1 支持 App Platform、Managed MySQL、Spaces 和 cache 类资源；更简单，但长期平台能力、private networking、备份和对象存储适配需单独验证。 |
| Storage 策略 | Needs validation | Render Persistent Disk 不能照搬到 Cloud Run；需评估 Cloud Storage bucket、Cloud Storage FUSE volume mount 或 Laravel / TastyIgniter media disk 适配。 |
| Database 策略 | MySQL required | 继续 MySQL 兼容优先；不切 PostgreSQL，除非单独验证 TastyIgniter 兼容性。 |
| Security boundary | Enforced | 不创建付费资源、不输入或记录 secret、不碰 production、不导入真实数据。 |

迁移对象清单：

- Docker app runtime：验证 `Dockerfile.render` 是否可作为 cloud-agnostic Dockerfile 基础。
- Startup：评估 `docker/render/start.sh` 是否需要抽象出 Render 专用 `$PORT`、persistent disk 和 Nginx template 逻辑。
- Database：新 Canada Cloud SQL for MySQL staging DB，只用测试数据或空库初始化；不使用 production 数据。
- Media：将当前 `storage` / uploads / media 从 Render Persistent Disk 迁移到 Cloud Storage / S3 / Spaces 或挂载方案。
- Env / secrets：迁移 `APP_URL`、`ASSET_URL`、`APP_KEY`、DB credentials、mail、cache、session 等；secret 只能在平台 UI / Secret Manager 输入。
- Cache / session / queue：先保持低风险默认值；再评估 Memorystore / Redis 对 settings / pages / theme / menus 查询的收益。
- Logs：Cloud Logging / CloudWatch / DO logs 需要纳入验收。
- Backup：Cloud SQL automated backup + on-demand export；media bucket lifecycle / versioning / cross-region backup 需要单独决策。
- Custom domain / HTTPS：Cloud Run 加拿大区域的直接 domain mapping 需要验证；可能需要 external HTTPS Load Balancer 或 Cloudflare。
- Rollback：保留 Render staging，直到 Canada staging 完成前后台、媒体、Livewire、性能和重新部署持久性验证。

候选方案比较：

| 方案 | 预期收益 | 复杂度 | Docker | MySQL | 媒体存储 | 加拿大区域 | Private networking | 备份 | Rollback | 结论 |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Google Cloud Canada | 高 | 中 | Cloud Run 支持容器 | Cloud SQL MySQL | Cloud Storage / FUSE / Filestore | Montréal / Toronto | Cloud SQL connector 或 private IP + VPC | Cloud SQL backup + bucket lifecycle | 保留 Render staging | 首选。组件齐全，最符合“一劳永逸”同区架构。 |
| AWS Canada | 高 | 中到高 | ECS / Fargate 支持容器 | RDS MySQL / Aurora MySQL | S3 / EFS | Canada Central / Canada West | VPC 内连接成熟 | RDS backup + S3 lifecycle | 保留 Render staging | 可行但运维复杂度高；App Runner 不建议新采用。 |
| DigitalOcean TOR1 | 中到高 | 低到中 | App Platform / DOKS / Droplet | Managed MySQL | Spaces TOR1 / Volumes | Toronto | VPC / trusted sources 需验证 | Managed DB backup + Spaces | 保留 Render staging | 简化候选；若用户偏好低复杂度，可作为备选实验。 |
| Azure Canada | 高 | 中到高 | Container Apps / App Service | Azure Database for MySQL | Blob Storage / Files | Canada Central / East | VNet integration | Managed backup | 保留 Render staging | 可行但当前项目上下文少，暂列第三方备选。 |

推荐路径：

1. 首选平台：Google Cloud。
2. 首选区域：Montréal `northamerica-northeast1`；如账号、配额、价格或 Cloud Run / Cloud SQL 限制不合适，再改 Toronto `northamerica-northeast2`。
3. 第一个 experiment：新建 Canada staging Cloud Run + Cloud SQL for MySQL + Cloud Storage bucket，使用测试数据库和测试图片验证 app / DB / media / cache / logs / backup 基线。
4. 用户需先确认：Google Cloud project、billing、预算上限、目标区域、是否允许创建 Cloud SQL / Cloud Run / Cloud Storage / Secret Manager / 可选 Memorystore。
5. 用户需要在平台 UI 输入：APP_KEY、DB password、Cloud SQL credentials、mail/payment placeholder secrets、Cloudflare/custom domain 相关 secret；不要发到聊天。
6. Render staging 保留为 fallback，直到 Canada staging 完整通过初始化、登录、前后台 smoke、媒体上传、重新部署持久性和性能基线。

## Google Cloud Canada staging experiment 规划记录

记录日期：2026-07-09

环境：planning / staging only

当前阶段：Plan Google Cloud Canada staging experiment

| 项目 | 状态 | 备注 |
| --- | --- | --- |
| 当前代码 | Confirmed | 本地 `4.x` 已同步到 PR #36 合并提交 `b4710f22`。 |
| 首选区域 | Recommended | Montréal `northamerica-northeast1`。 |
| 备选区域 | Viable | Toronto `northamerica-northeast2`。 |
| 当前动作 | Planning only | 不创建 Google Cloud 资源，不产生费用，不迁 production。 |
| Fallback | Required | Render staging 必须保留，直到 Canada staging 完整验收通过。 |
| Secret handling | UI only | 用户在 Google Cloud Console / Secret Manager 输入真实值；不要发到聊天或提交到 GitHub。 |

Google Cloud 前置条件：

- Google Cloud project：建议独立 staging project，例如 `le-chateau-staging-ca`，避免与未来 production 混用。
- Billing：用户确认启用 billing，并设置预算提醒。
- Budget：建议先设置月度预算和 alerts，例如 50%、80%、100%；具体金额由用户决定。
- IAM：用户确认谁可以管理 Cloud Run、Cloud SQL、Cloud Storage、Artifact Registry、Secret Manager、Cloud Logging。
- APIs：Cloud Run、Cloud SQL Admin、Cloud Build、Artifact Registry、Secret Manager、Cloud Storage、Cloud Logging。
- Region：默认 Montréal；如配额、价格或服务限制不合适，再改 Toronto。

计划资源清单：

| 资源 | 建议名称 | 区域 | 用途 | 费用确认 |
| --- | --- | --- | --- | --- |
| Artifact Registry repository | `tastyigniter-staging` | Montréal | 保存 Docker image | 需要 |
| Cloud Run service | `le-chateau-staging` | Montréal | 运行 TastyIgniter app | 需要 |
| Cloud SQL for MySQL | `le-chateau-staging-mysql` | Montréal | staging MySQL DB | 需要，通常是主要成本 |
| Cloud Storage bucket | 唯一 bucket 名，例如 `le-chateau-staging-media-ca` | Montréal | media / uploads / public files | 需要 |
| Secret Manager secrets | 见 secret 清单 | Global control plane / regional access 需后续确认 | 保存 env secrets | 需要 |
| Optional Memorystore / Redis | `le-chateau-staging-redis` | Montréal | cache / session 评估 | 可选，先不创建 |
| Logs / Monitoring | Cloud Logging / Monitoring | Montréal service context | logs / alerting | 需要 |

Secret Manager 名称清单，不包含真实值：

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

Cloud Run runtime planning：

- 先评估复用 `Dockerfile.render`，因为它已经包含 Nginx + PHP-FPM + TastyIgniter asset handling。
- `docker/render/start.sh` 需要确认：
  - Cloud Run 是否提供 `$PORT`，以及 Nginx template 是否可直接使用。
  - Render Persistent Disk setup 是否需要跳过或抽象。
  - `/healthz` 是否继续由 Nginx 静态返回。
  - config cache 是否继续通过 `RUN_CONFIG_CACHE=true` 控制。
  - storage symlink 是否指向 Cloud Storage mount 或本地 ephemeral path。
- 不在本规划 PR 中修改 runtime；后续创建单独 PR 做 Cloud Run compatibility changes。

Cloud SQL 连接规划：

- DB engine：MySQL。
- Staging DB：新建空 staging DB 或导入非真实测试数据；不使用 production 数据。
- 连接方式第一阶段优先 Cloud SQL connector / Cloud Run integration，避免 public DB host。
- 如使用 private IP，需要 VPC / Serverless VPC Access 或 Direct VPC egress 方案，单独验证。
- 保留 `MYSQL_ATTR_INIT_COMMAND=SET SESSION sql_require_primary_key = OFF` 的兼容检查，但 Cloud SQL 是否需要该设置需在 staging 初始化前确认。
- 不运行 `migrate:fresh`、`migrate:refresh` 或 `db:seed`；初始化流程需沿用 TastyIgniter install 路径。

Cloud Storage media planning：

- Render Persistent Disk 不能照搬。
- 第一阶段建议试 Cloud Storage FUSE volume mount，尽量保持 TastyIgniter 现有 local filesystem 语义。
- 验证点：media manager、上传、缩略图、公开 URL、重新部署持久性、并发写入、权限、缓存头。
- 如果 FUSE 不稳定，再评估 Laravel / TastyIgniter filesystem disk 到 Cloud Storage adapter。
- 不上传正式图片，不提交上传文件。

Canada staging 验收清单：

- `/healthz` 返回 200。
- 首页、`/default/menus`、`/cart`、`/default/reservation` 返回 200。
- Livewire JS 返回 200。
- 后台登录页和 dashboard 正常。
- TastyIgniter admin assets 正常。
- 测试分类 / 测试菜品可创建或迁移，不录真实菜单。
- 测试图片上传和重新部署持久性通过。
- Cloud SQL query RTT 明显低于当前 Render + DO 路径。
- 动态 HTML TTFB 重新建立 baseline。
- 日志无新的 PHP fatal、Laravel exception、500、storage permission error。
- Backup / restore 基线有记录。
- 删除或停止 staging 资源的回滚 / 成本控制路径明确。

Rollback / fallback：

- Render staging 保持不变，不删除 service、disk、database 或 env。
- Canada staging 使用独立 URL、独立 DB、独立 media bucket、独立 secrets。
- 如 Cloud Run experiment 失败，停止或删除 Cloud Run / Cloud SQL / bucket 前先确认是否有需要保留的测试数据。
- 不迁 production DNS，不改正式域名，不启用正式支付。

下一步建议：合并本规划 PR 后，进入 `Create Google Cloud Canada staging resources` 前必须由用户确认 billing、预算上限、Montréal / Toronto、允许创建的资源，以及在 Google Cloud UI / Secret Manager 中输入 secrets。

## 2026-07-09 - Prepare Cloud Run Canada staging runtime

Status: PR planned for review.

Environment: Canada staging only.

Scope:

- Added a separate Cloud Run staging runtime entry point.
- Kept Render staging runtime unchanged as fallback.
- Recorded Cloud Run, Cloud SQL, Secret Manager, Cloud Storage media, Artifact Registry, acceptance, and rollback checklists.
- Did not create a Cloud Run service.
- Did not create or download a service account key.
- Did not add secrets or production configuration.

Key runtime decisions:

- Cloud Run image listens on `$PORT`, defaulting to `8080` only if absent.
- Cloud SQL uses the connector socket path `/cloudsql/<INSTANCE_CONNECTION_NAME>`.
- Secret values remain in Google Cloud Secret Manager and are not recorded in git.
- The first Cloud Storage media experiment mounts `le-chateau-canada-staging-media` at `/var/www/html/storage/app/media`.
- Current TastyIgniter media symlinks are preserved.
- Render staging remains active for rollback.

Next gate:

- Build and push the Cloud Run image only after user confirmation.
- Create the Cloud Run service only after user confirms billable resource creation.
- Bind secrets and Cloud SQL only through Google Cloud UI or approved deployment tooling.
## 2026-07-09 - Cloud Run Canada staging initialization blocker

Status: Blocked pending PR #39 and a new Cloud Run image.

Environment: Canada staging only.

- Cloud SQL staging schema was initialized without demo data.
- A staging-only administrator account was created using a Secret Manager value; credentials are not recorded here.
- Cloud Run static assets and Livewire JavaScript returned successfully.
- Dynamic pages returned HTTP 500 because Laravel's file cache data directory was missing at startup.
- PR #39 adds idempotent creation of `storage/framework/cache/data` to the Cloud Run start script.
- Render staging remains unchanged and available as fallback.

Next step: merge PR #39, rebuild/push the Canada staging image, deploy a new revision, and repeat dynamic page and admin smoke tests.

## 2026-07-10 - Cloud Storage FUSE visibility blocker

Status: Blocked pending a focused runtime/configuration PR.

Environment: Canada staging only.

- After PR #39, menu, cart, reservation, admin login, and Livewire returned 200.
- The homepage still returned HTTP 500 because the local filesystem adapter attempted chmod on a Cloud Storage FUSE media file.
- PR under review adds FILESYSTEM_SKIP_VISIBILITY, disabled by default and intended only for the Cloud Run mounted media runtime.
- Render staging keeps the existing public filesystem visibility behavior.
- /healthz remains a separate Cloud Run frontend 404 observation.

Next step: merge the visibility fix, redeploy the new image with the Cloud Run-only flag, then repeat homepage and media smoke tests.

## 2026-07-10 - Cloud Run Canada staging validation after PR #40

Status: Resolved for the FUSE visibility issue. Environment: Canada staging
only.

- PR #40 is merged and deployed from git SHA `44940004`.
- Cloud Run-only `FILESYSTEM_SKIP_VISIBILITY=true` is enabled. Render staging
  configuration is unchanged.
- Cloud Build completed successfully and pushed the SHA-tagged image to the
  Canada Artifact Registry repository.
- Revision `le-chateau-canada-staging-00008-tsd` first carried the fix. A
  same-image redeploy created revision `le-chateau-canada-staging-00009-tvs`
  with 100% traffic.
- Homepage, menus, cart, reservation, admin login, and Livewire returned HTTP
  200 after the final redeploy.
- The Canada staging administrator login was verified in the browser. No
  credentials are recorded here.
- A non-business test image `IMG_2484.png` was uploaded. The object remained
  available after redeploy, returned `image/png` and HTTP 200, and was recorded
  at 109065 bytes. The image is not committed to git.
- Cloud Run logs showed normal GCSFuse mount messages and no new visibility,
  chmod, permission, cache-directory, Cloud SQL, PHP-FPM, Nginx, fatal, or
  Laravel exception errors.
- Warm HTTP TTFB after the final redeploy was approximately: `/` 0.66s,
  `/default/menus` 0.60s, `/cart` 0.60s, `/default/reservation` 0.62s,
  `/admin/login` 0.39s, and Livewire JavaScript 0.34s. These are HTTP smoke
  measurements, not a replacement for PDO-level latency sampling.
- `/healthz` remains a separate Cloud Run frontend HTTP 404 and is not mixed
  into the FUSE fix. Render staging remains the fallback.

Next steps:

1. Retain `staging-inspector` as a Canada staging maintenance account. It is
   not the Cloud Run application runtime account and must not be used for
   production. Its password is not recorded here.
2. Create a focused `Fix Cloud Run health check routing` task.
3. Run a separate approved PDO/Laravel connection-latency measurement before
   making a final database architecture decision.

## 2026-07-10 - Cloud Run health and database latency validation

Status: Resolved for the Cloud Run liveness path and read-only latency sample.
Environment: Canada staging only.

- PR #42 was deployed from git SHA `2796d2c6`. Revision
  `le-chateau-canada-staging-00010-fh9` is Ready and serves 100% of traffic.
- Cloud Run liveness uses `/healthz/`. It returned HTTP 200 with
  `text/plain`, and Cloud Run request logs confirmed the request reached the
  deployed revision. Bare `/healthz` remains the known Google frontend 404;
  Render `/healthz` was not changed.
- Homepage, menus, cart, reservation, admin login, Livewire JavaScript, and
  retained staging test media returned HTTP 200. No test image was committed.
- The approved one-time read-only latency Job used the Cloud Run application
  database account, not `staging-inspector`, and was deleted after sampling.
  No database writes were performed.
- Latency averages were: PDO new connection 140.85 ms, PDO same connection
  `select 1` 2.23 ms, Laravel reconnect first query 147.87 ms, and Laravel
  same-connection `select 1` 4.33 ms. These are materially below the former
  Render baseline.
- `staging-inspector` remains a Canada staging maintenance account only. It is
  not the Cloud Run runtime account and is not a production credential.
- Render staging remains available as fallback; production was not changed.

Next steps: repeat authenticated dashboard verification and continue the
separate `/healthz` public-routing investigation before any production work.

## 2026-07-10 - Canada staging dashboard acceptance and reservation audit

Environment: Canada staging only. Status: Dashboard acceptance resolved;
reservation requirements audit remains open pending business requirements.

Dashboard acceptance:

- The authenticated Canada staging session remained active while opening the
  dashboard, Orders, Reservations, Categories, Menus, Media Manager, Settings,
  Extensions, and Staff members pages.
- Each page rendered its expected title/layout and no page showed a 500/502/503/
  504 or Laravel error screen.
- Same-origin scripts, stylesheets, and images observed on the tested pages
  returned HTTP 200. No localhost or old Render asset URL was observed.
- No browser console errors were observed. A repeated non-blocking warning
  states that Broadcast is not defined; it is not a failed request and did not
  prevent navigation or widget rendering.
- Dashboard navigation was about 1.24 seconds on the first observed load and
  about 0.54 seconds on a warm navigation in this browser session. No order,
  reservation, menu, or settings write was performed.
- The Cloud Run revision and `/healthz/` liveness result remain those recorded
  in the previous validation. The bare `/healthz` Google frontend 404 remains
  a known separate observation. Render staging and DigitalOcean fallback
  resources remain unchanged.

Reservation behavior audit:

- The public `/default/reservation` page is reachable and shows a date control,
  guest selector, time selector, and `Find Table` action. The form is a POST
  availability lookup; it was not submitted.
- The observed guest range is 2 through 20. The observed time selector contains
  288 entries in five-minute increments. The date selected by the page reflects
  the configured minimum advance window.
- Location booking settings currently show reservations enabled, automatic
  table assignment enabled, a 15-minute reservation interval, 45-minute stay
  time, minimum/maximum guest size 2/20, minimum/maximum advance window 2/30
  days, guest-count limiting disabled, cancellation timeout 0, and inclusion of
  the schedule start time enabled.
- The current location opening, delivery, and pickup schedules are 24/7 for all
  seven days. This is a configuration state to review before business launch.
- Global reservation settings expose customer, restaurant, and location email
  notifications plus default, confirmed, and canceled status mappings. The
  protected reservations list is reachable and currently contains no records.
- Availability exhaustion, duplicate reservations, conflict handling, customer
  detail/notes collection after table lookup, bilingual validation copy, and
  mobile interaction were not submitted or changed in this audit.

No runtime code, vendor, core, payment, order, authentication, or reservation
conflict logic was modified. No real or test reservation was created.

Next step: obtain the required reservation behavior from the business owner,
then split any confirmed change into a small, reversible staging-only PR.
## 2026-07-10 - Birthday reservation rules implementation

Environment: local build only; Canada staging not deployed. Status: Pending PR
review and staging migration.

- Implemented the first Birthday Booking rules slice behind the non-secret
  `BIRTHDAY_BOOKING_RULES_ENABLED` flag. The default remains disabled so Render
  fallback behavior is unchanged.
- Added centralized fixed slots `12:00-16:00` and `16:00-20:00`, venue-local
  date validation for plus 2 through plus 60 days, and a frontend flow that
  does not render fine-grained time options and reuses the existing reservation
  customer form/save path.
- Added nullable Birthday slot code/key fields and a unique
  `(location_id, birthday_slot_key)` index plus an explicit
  `birthday_booking` marker to the existing reservations table.
  The key is populated only for the configured default and confirmed statuses;
  canceled, expired, rejected, and other non-occupying statuses release it.
- Added server-side availability checks, explicit slot validation, and removal
  of table assignments for Birthday reservations. Only marked Birthday records
  are processed; status-only maintenance does not reapply the creation window.
  The database unique index is the final duplicate-prevention guard and its
  conflict is converted to a readable validation response.
- Automated Birthday rules tests pass: 7 tests, 15 assertions. Dockerfile.cloudrun
  build and config cache validation pass. No staging database or business data
  was changed.

Next step: review the implementation PR, then run the additive migration and
enable the feature only on Canada staging before browser/concurrency smoke
tests. Render remains disabled and available as fallback.

## 2026-07-11 - Birthday reservation rules staging validation

Environment: Canada staging only. Status: Service-side Birthday rule
validation resolved; end-to-end browser reservation submission remains pending
because the telephone input widget blocked the form.

- PR #48 was deployed as SHA `0a19c37f` to Cloud Run revision
  `le-chateau-canada-staging-00014-2kd`. `/healthz/` and the public/admin smoke
  pages returned HTTP 200.
- Authenticated navigation verified dashboard, Orders, Reservations,
  Categories, Menus, Media Manager, Settings, Extensions, Staff, and the
  public reservation page without a page-level 5xx or same-origin asset
  failure.
- The Birthday flow displayed only `12:00-16:00` and `16:00-20:00`; the
  Toronto-local date window was current date +2 through +60. Positive guest
  counts remained compatible and did not control availability.
- Service-side QA covered date boundaries, slot occupancy, occupying and
  non-occupying status behavior, cancellation release, and the database unique
  conflict guard. The two-task concurrent execution produced one successful
  claim and one expected unique-conflict failure; the final occupying-row count
  was one. The harness classified the losing write through the expected
  service-side conflict path without exposing SQL bindings or index details.
  Synthetic QA records and temporary Cloud Run Jobs were removed afterward.
- The browser telephone widget rejected the synthetic number before
  submission. No browser-created reservation remains; this is a separate
  input-widget follow-up, not a Birthday availability failure.
- No real customer, order, reservation, payment, mail, menu, or production
  data was used. Render staging and the DigitalOcean fallback remain unchanged.
- PR #45 was closed as superseded by PR #46 and subsequent staging
  fixes/validation.

Next step: review and merge the documentation PR, then decide separately
whether to fix the telephone widget before further reservation UX work.

## 2026-07-12 - Birthday reservation telephone input follow-up

Environment: local build only; Canada staging not deployed. Status: Pending PR
review.

- Added a Birthday-specific telephone input so the birthday flow no longer
  depends on the legacy Orange `intl-tel-input` browser widget.
- Added server-authoritative Canada/US NANP validation and normalization for
  common national and `+1` formats. Valid values are stored in normalized
  `+1##########` form; invalid values remain rejected.
- Kept the standard reservation telephone flow unchanged. No vendor, core,
  payment, order, authentication, notification, or production configuration
  was modified.
- No staging deployment, reservation submission, notification, or real/test
  customer data change was performed in this step. Render staging remains the
  fallback.

Next step: review and merge the telephone-input PR, then deploy only to
Canada staging and complete one synthetic browser reservation validation.

## 2026-07-12 - Birthday browser submission validation after PR #50

Environment: Canada staging only. Status: Resolved for the current Birthday
reservation scope; production readiness remains deferred.

- PR #50 was deployed from merge SHA `fceead8b` as Cloud Run revision
  `le-chateau-canada-staging-00015-vnj`. A staging-only `MAIL_MAILER=log`
  revision was then created as `le-chateau-canada-staging-00016-2tj` so the
  synthetic validation could not contact an external SMTP server.
- The public Birthday form displayed only the two fixed slots. A synthetic
  browser submission reached the success page, and the admin Reservations
  list displayed the new Birthday record with Pending status, no table, and
  the expected fixed-slot duration. The telephone value was normalized by the
  server to the expected E.164-style representation.
- The occupied slot became unavailable in a fresh public form. After changing
  the synthetic record to Canceled, the slot became available again. The
  synthetic reservation was then deleted from the staging admin UI; no real
  customer, order, payment, notification, or production data was used.
- `/healthz/`, homepage, menus, cart, reservation, admin login, Livewire, and
  retained test media remained available. Dashboard navigation and the logged-
  in admin session remained usable. Recent Cloud Run error filtering found no
  new fatal, exception, 500, SQL, cache-directory, FUSE, or storage-permission
  error. Existing Broadcast/configuration warnings remain non-blocking.
- Render staging and the DigitalOcean fallback remain unchanged.

Next step: keep the synthetic validation record as documentation only, then
move to separately scoped reservation enhancements. Payment, registration,
add-ons, notifications, bilingual copy, and production work remain out of
scope.

## 2026-07-12 - Shared payment and Birthday checkout architecture audit

Environment: docs/local audit only. Status: Design pending business decisions.

- Confirmed PR #51 is merged on `4.x` at SHA `45730125`. No runtime code,
  migration, payment gateway, webhook, order, reservation, customer, or
  production change was made.
- Audited the installed Order checkout, Reservation model, PayRegister
  gateway registry, payment configuration, PaymentLog, PaymentProfile, Stripe
  return/webhook flow, refund widget, and current Birthday Reservation flow.
- Current `payments` is a gateway configuration table and current
  `payment_logs` is Order-specific. Neither should be reused as the generic
  Birthday transaction ledger.
- Recommended separate app-level Birthday Booking, shared payment transaction
  and webhook event records, durable idempotency, and a slot lock/hold layer.
  Existing Reservations remain visible in the backend as the operational
  record; payment state must remain separate from Reservation `status_id`.
- Added `BIRTHDAY_PAYMENT_ARCHITECTURE_DESIGN.md` with boundaries, proposed
  schema, state models, hold lifecycle, webhook/security design, tests,
  rollback, and open business decisions.

Next step: review the design PR and decide package, price, hold, account,
gateway test-mode, tax, cancellation/refund, notification, and Reservation
creation timing requirements. Do not enter payment secrets or connect a real
gateway at this stage.

## 2026-07-12 - PR #52 payment exception and implementation-order clarification

Environment: docs/local audit only. Status: Design update pending review.

- Extended the design for verified payment success after an expired, missing,
  or reclaimed Birthday slot hold. The Payment Transaction remains
  `succeeded`; the Booking enters `payment_exception` or `manual_review`, no
  other hold/Reservation is changed, and operations must choose refund,
  alternate-slot coordination, or another approved remedy.
- Added duplicate-webhook behavior during manual review, refund retry rules,
  admin reconciliation fields, rollback/reconciliation guidance, and the
  required integration cases.
- Confirmed implementation order is A -> B -> C -> D -> F -> E -> G. E may
  precede F only as an internal fake-gateway harness with no public payment
  page or customer payment flow.
- Added the independent 20-item Risk and Mitigation Matrix covering webhook,
  hold, payment consistency, refunds, secrets, notification isolation,
  multi-instance concurrency, tax/legal uncertainty, and metadata leakage.
- This remains documentation only. No runtime code, migration, vendor/core,
  payment gateway, webhook, secret, production, order, reservation, or real
  data change was made.

Next step: request review of PR #52, then confirm the business decisions before
starting packages, migrations, slot holds, payment gateway, or registration
implementation.

## 2026-07-17 - PR #53 Birthday catalog review fixes

Environment: local Docker/MySQL validation only. Status: Pending renewed PR
review; no Canada staging migration or deployment performed.

- Registered the app-owned Birthday extension as a Composer path package,
  committed the reproducible lock file, and updated both runtime Dockerfiles so
  clean builds can install the package before the full source copy. Composer
  and TastyIgniter package discovery both resolve `abandon.birthday`.
- Kept package and add-on permissions separate. Functional admin tests cover
  index/create/edit/archive/filter/restore behavior and verify that one catalog
  permission does not grant access to the other catalog.
- Default-package changes now run through one service transaction with
  deterministic row locking and the database unique guard. Rollback, no-default
  archive/restore behavior, a forced unique conflict, and two independent
  process/transaction writers are covered; concurrent writes leave one winner
  and return a readable `is_default` error to the loser.
- CAD prices are limited to the unsigned integer storage ceiling of
  `42949672.95`. Exact maximum, maximum plus one, and very large values are
  covered without exposing SQL or internal exception details.
- PHP 8.3 lint, Pint, 39 focused tests with 173 assertions, extension migration
  down/up, config/route/view caches, route discovery, Composer validation, and
  a clean Cloud Run Docker build passed. One existing PHPUnit deprecation is
  still reported by the project test harness.
- No payment, slot-hold, Booking, registration, Reservation, Order, vendor/core,
  production, secret, or real-data change is included. Render staging remains
  the fallback and Canada staging is unchanged.

Next step: request renewed review of Draft PR #53. Merge and Canada staging
migration remain blocked on approval; do not deploy from this local validation.

## 2026-07-17 - Canada staging Birthday catalog deployment and navigation blocker

Environment: Canada staging. Status: Runtime validation passed except for an
untranslated admin navigation label; a separate fix PR is required.

- Deployed PR #53 merge SHA `ac20afa4853694d5fe4572492e55baf12a694035`
  as Cloud Run revision `le-chateau-canada-staging-00017-t64`; the revision is
  Ready and serves 100% of Canada staging traffic. The previous stable revision
  remains available for rollback.
- Ran the existing-schema preflight and confirmed no pending root migrations.
  `igniter:up` then applied only the additive
  `abandon.birthday::2026_07_15_000000_create_birthday_catalog_tables`
  migration. Postflight checks confirmed the two catalog tables, required
  columns, available indexes, unique default guard, migration record, and
  unchanged Reservation/Order/Payment/Customer table structures.
- Super-admin browser validation passed for package and add-on create/edit,
  CAD minor-unit boundaries, invalid-price handling, default switching,
  no-default archive safety, archived filtering, restore, add-on disable, and
  absence of quantity fields. Automated tests remain the permission-isolation
  evidence; no real staff role was modified.
- Public/admin smoke checks, Livewire, retained test media, existing Birthday
  reservation date/slot behavior, browser console, and Cloud Run 5xx/error
  filtering passed. Six package/add-on QA records were removed and all four
  temporary validation jobs were deleted.
- The Restaurant navigation currently renders the raw Birthday translation
  keys. Root cause is that the extension passes translation keys directly
  instead of resolving them with `lang(...)`; no container hotfix was applied.

Next step: review and merge the focused Birthday navigation-label fix, redeploy
Canada staging, verify the two labels, and then create the documentation-only
final validation PR. Render/DigitalOcean fallback and production remain
unchanged.

## 2026-07-17 - Final Canada staging Birthday catalog acceptance

Environment: Canada staging. Status: Resolved; final documentation review
pending.

- PR #53 merge SHA `ac20afa4853694d5fe4572492e55baf12a694035`
  introduced the app-owned Birthday package/add-on catalog. Its initial image
  was built by Cloud Build `aec22814-3f81-46f7-a74c-12aaa7edff7d` and deployed
  as Ready revision `le-chateau-canada-staging-00017-t64`.
- PR #54 merge SHA `53960a9e705b271823e375056c2bfce93dcc95d1` was
  built as the full-SHA Canada image with Cloud Build
  `50de4c46-c51e-4cd4-86c0-723ce7d712f7`. Revision
  `le-chateau-canada-staging-00018-neb` passed its tagged `/healthz/` check
  before receiving 100% traffic. The runtime configuration fingerprint was
  unchanged, and revisions `00017-t64` and `00016-2tj` remain Ready for
  rollback.
- Restaurant navigation now shows exactly one `Birthday Packages` and one
  `Birthday Add-ons` entry. Both links open the expected list routes; raw
  translation keys, blank titles, duplicates, 404s, and 500s were not present.
- Read-only package/add-on regression confirmed empty lists, create forms,
  CAD currency, required catalog fields, Archived filters, no quantity field,
  and no destructive Delete action. The earlier CRUD, CAD minor-unit/maximum,
  invalid-price, deterministic default, archive/restore, enabled-state, and
  permission-isolation evidence remains valid.
- No migration ran in this deployment. A one-time read-only check using the
  application runtime account confirmed `birthday_packages`,
  `birthday_addons`, and the catalog migration record remain present. The job
  was deleted after the check and the existing business schema was unchanged.
- `/healthz/`, public pages, admin login/pages, Livewire, retained test media,
  browser consoles, and current-revision logs passed. The Birthday form still
  exposes only the two fixed slots, the Toronto-local plus-2 through plus-60
  date window, and the telephone field; it does not expose catalog prices,
  payment, hold, or Booking behavior.
- The prior three QA packages, three QA add-ons, and four catalog-validation
  Jobs remain removed. This validation created no package, add-on,
  Reservation, Booking, Order, Payment, or Customer record and left no
  temporary Job.
- Render and DigitalOcean remain unchanged fallbacks. Production, real data,
  real payment, outbound notification, secrets, and service-account keys were
  not touched.

Known independent issue: full fresh-install migration ordering still allows
the root Birthday reservation migration to run before the Reservation
extension creates its dependency. It is not a blocker for the already-migrated
Canada staging database and must be handled in a separate PR.

Next step: review and merge the final documentation PR. Only after that gate
may work begin on the separately scoped Birthday Booking domain and immutable
price snapshot.

## 2026-07-17 - Birthday Booking catalog-price snapshot domain

Environment: local Docker/MySQL validation only. Status: Pending Draft PR
review; Canada staging and production are unchanged.

- Added the independent `BirthdayBooking` domain with only `catalog_priced`
  and terminal `cancelled` states. A catalog-priced Booking records a customer
  selection and immutable catalog subtotal; it is not a Reservation, Order,
  Payment, slot hold, or confirmed booking and does not affect availability.
- Added reversible, additive `birthday_bookings` and
  `birthday_booking_addons` tables. The migration does not modify existing
  Reservation, Order, Payment, Customer, Location, package, or add-on tables;
  same-location/date/slot catalog-priced Bookings intentionally remain
  non-unique until the separately scoped hold phase.
- The transactional service reuses the Toronto plus-2 through plus-60 date
  window and fixed `12-16` / `16-20` slot definitions, computes UTC start/end
  times server-side, requires one enabled/unarchived CAD default package, and
  locks selected catalog rows in stable order. Guest count is informational
  only and does not affect price or capacity.
- Booking records retain Customer and Location references plus immutable
  contact, package, included-item, add-on, and integer minor-unit price
  snapshots. `catalog_subtotal_minor` is package plus selected add-ons only;
  it is not a tax-inclusive or final payable amount. Catalog edits and archive
  changes do not alter historical snapshots, and add-ons have no quantity.
- Model events reject persisted snapshot edits, physical Booking deletion,
  and individual add-on snapshot edits/deletes. Cancellation changes only
  status and cancellation time. Direct query-builder/bulk updates bypass model
  events and therefore remain outside the supported application boundary;
  future code must use `BirthdayBookingService` rather than mutate snapshot
  tables directly.
- Added read-only `Birthday > Bookings` list/detail pages protected by the
  independent `Admin.BirthdayBookings` permission. They expose historical
  snapshot values and formatted CAD subtotals without Create, Edit, Delete,
  Reservation, confirmation, or payment actions.
- Local MySQL migration up/down/up, focused domain/admin/migration tests,
  existing Birthday rules/catalog/admin/concurrency regression tests, PHP 8.3
  lint, Pint, Composer validation, config/route/view caches, route discovery,
  and clean Cloud Run/Render Docker builds passed. The test harness retains its
  known PHPUnit XML deprecation warning.
- This phase does not integrate the public reservation form, create test or
  real Bookings, deploy or migrate Canada staging, add holds/payments/webhooks,
  change registration, calculate tax/discounts, send notifications, or touch
  production/secrets. Render/DigitalOcean remain unchanged fallbacks.

Known independent issue: the existing full fresh-install migration ordering
can still run the root Birthday reservation migration before its Reservation
extension dependency. Validation used a dependency-ordered isolated schema;
this PR does not broaden scope to that issue.

Next step: review the Draft Birthday Booking snapshot PR. Do not merge,
deploy, or run the Canada staging migration until that review gate passes;
the 15-minute slot-hold phase starts only after merge and staging acceptance.

## 2026-07-17 - Canada staging Birthday Booking snapshot hydration blockers

Environment: Canada staging and local Docker/MySQL validation. Status: Open
(Q-007 and Q-008); runtime rolled back pending fix review.

- Deployed PR #56 merge SHA `8b6ba92c9f27b27e1479f91121379dedfc2e230c`
  as image tag `8b6ba92c9f27b27e1479f91121379dedfc2e230c` and Ready revision
  `le-chateau-canada-staging-00021-fom`. The additive Birthday Booking
  migration completed once through `php artisan igniter:up --force
  --no-interaction`; both new tables, expected indexes, foreign keys, and the
  single migration record were present without changing existing business
  tables.
- Tagged and live smoke checks passed for `/healthz/`, public pages, admin
  login, Livewire, and retained test media. Current-revision logs contained no
  startup, Cloud SQL, FUSE, cache, fatal, exception, or HTTP 5xx error.
- Isolated QA created synthetic catalog/customer/Booking records only long
  enough to validate status, public identifier, CAD snapshot values, integer
  minor-unit totals, pricing version, and same-slot non-occupancy behavior.
  Database reload then exposed Q-007: UTC start/end values were reinterpreted
  as Toronto-local datetimes by the default Eloquent cast, shifting the actual
  instant by four hours during daylight-saving time.
- Validation stopped before slot hold work. All QA Booking, snapshot,
  Customer, package, and add-on rows were removed; Reservation, Order,
  Payment, and payment-log counts remained at their baselines. Disposable QA
  and migration Jobs were deleted.
- Traffic was returned 100% to accepted revision
  `le-chateau-canada-staging-00018-neb`; revision `00021-fom` remains at 0%
  for diagnosis. The additive schema remains in Canada staging and was not
  destructively rolled back.
- The fix uses an extension-owned UTC cast for Booking instant fields and adds
  a database round-trip regression under `America/Toronto`. Direct
  MySQL/Eloquent round-trip validation passed locally.
- Focused MySQL regression also exposed Q-008: snapshot rows were inserted in
  catalog order, but the relationship query had no `ORDER BY`, so MySQL could
  hydrate them in source add-on ID index order. The same focused fix orders
  the relationship by immutable `sort_order_snapshot` and snapshot row ID.
  The dependency-ordered MySQL 8.4 schema passed all 13 focused Booking
  service tests with 112 assertions and no error or failure.
  Q-007 and Q-008 remain Open until the fix PR is merged, deployed, and the
  full Canada staging QA is repeated.
- Render and DigitalOcean remain unchanged fallbacks. Production, real data,
  real payment, notifications, secrets, and service-account keys were not
  touched.

Next step: review and merge the independent snapshot hydration fix, redeploy
only Canada staging, rerun the snapshot QA and admin/browser checks, then
create the final PR #56 deployment record. Do not start the 15-minute
slot-hold phase before Q-007 and Q-008 are resolved.

## 2026-07-17 - Canada staging Birthday Booking snapshot acceptance

Environment: Canada staging only. Status: Resolved; documentation PR pending
review.

- PR #56 merge SHA `8b6ba92c9f27b27e1479f91121379dedfc2e230c`
  supplied the additive Booking schema already migrated in the prior phase.
  PR #57 merge SHA and deployed git SHA
  `e2ca19d4407064bb9d34d4fe8fe947cd1624c5c2` supplied the UTC hydration and
  deterministic add-on relationship fixes. No migration ran in this phase.
- Cloud Build `2392136c-e2ce-44d7-bced-7b33450958cb` produced the full-SHA
  Canada image. Ready revision `le-chateau-canada-staging-00024-dof` passed
  tagged `/healthz/`, matched the accepted runtime configuration fingerprint,
  and now serves 100% of traffic. `00018-neb` and `00021-fom` remain available
  rollback revisions.
- Application-account QA reloaded summer and standard-time Bookings from
  Cloud SQL. Start/end, priced, and cancelled instants hydrated as UTC and
  converted back to the expected Toronto `12:00-16:00` slot. Q-007 is resolved.
- A `Late` add-on was created before a lower-sort `First` add-on. The reloaded
  relationship and read-only admin detail displayed `First`, then `Late`, with
  immutable sort snapshots and snapshot-row tie-break behavior. Q-008 is
  resolved.
- The accepted historical Booking retained its original contact, package,
  included-item, add-on, and integer CAD snapshots after source catalog and
  Customer changes. Package/add-on/catalog subtotals remained 100/75/175
  cents at pricing version 1. Model update/delete guards, terminal cancellation,
  invalid-state validation, and transaction rollback checks passed.
- Two catalog-priced Bookings for the same location/date/slot coexisted with
  distinct public IDs. Reservation and Order counts stayed at zero, Payment
  and Payment Log counts stayed at their preflight baselines, and no slot-hold
  table or row was created. The Booking phase therefore remains deliberately
  non-occupying and non-payable.
- Authenticated browser acceptance covered core admin pages plus Birthday
  Packages, Add-ons, and Bookings. The list/detail showed historical snapshots
  and CAD values without Create, Edit, Delete, Save, Confirm, Reservation,
  payment, or recalculation actions. Error-level console entries were zero;
  permission isolation remains covered by the focused automated tests.
- `/healthz/`, public pages, admin login, Livewire, and retained test media
  returned successfully with no localhost URL. Current-revision logs contained
  zero error-severity entries, HTTP 5xx, fatal/UTC/SQL/Cloud Storage/cache
  errors, or unhandled exceptions.
- All synthetic Booking snapshots, Bookings, Customer, package, and add-ons
  were removed in dependency order. All temporary preflight/validation/cleanup
  Jobs were deleted, and business-object baselines were unchanged. Render,
  DigitalOcean, production, secrets, real data, payment, and outbound mail
  were not changed.

Known independent issue: full fresh-install migration ordering remains outside
this validation and does not block the already-initialized Canada staging
database.

Next step: review and merge this documentation-only acceptance record. Do not
start the separately scoped 15-minute slot-hold phase before that gate.

## 2026-07-18 - 15-minute Birthday slot holds implementation

Environment: local isolated Docker/MySQL 8.4 only. Status: Pending review.

- Added an extension-owned `birthday_slot_holds` schema with a unique public
  identifier, one reusable row per location/date/slot, Booking ownership, UTC
  acquisition/expiry/release timestamps, status, and release reason. The
  migration is additive and safely reversible without changing existing
  Birthday Booking, Reservation, Order, or payment records.
- Hold duration is fixed at exactly 900 seconds. A hold is expired when
  `expires_at <= now`; availability and acquisition apply this rule lazily, so
  correctness does not depend on a cleanup scheduler. The optional
  `birthday:expire-slot-holds` command only performs housekeeping.
- Acquisition is transactional and database-enforced. Row locks, unique keys,
  and finite duplicate/deadlock retries prevent two Bookings from owning the
  same slot. Re-acquiring an active hold for the same Booking is idempotent and
  does not renew its public ID or expiry. Released or expired rows are reused
  atomically with a new public ID.
- Booking creation remains non-occupying. Only an eligible catalog-priced
  Booking can acquire a hold, and cancelled Bookings cannot acquire one.
  Cancelling a Booking releases its active hold in the same transaction, so a
  release failure also rolls back the cancellation.
- Release is owner-only, idempotent, and limited to defined reasons. Direct
  model update and physical deletion are blocked. Birthday Booking admin list
  and detail views expose hold status, public ID, and timestamps read-only;
  no create, renew, release, payment, or customer checkout action was added.
- MySQL 8.4.10 verification passed with 68 Birthday tests and 451 assertions,
  including true multi-process contention, exact-expiry-boundary reuse,
  same-Booking idempotency, different-slot independence, cancellation rollback,
  ownership rejection, and safe domain errors without SQLSTATE, index names,
  or personal data. Migration up/down/up also passed in the isolated schema.
- Composer validation, PHP syntax checks, Pint, Laravel config/route/view cache
  builds, and clean no-cache Cloud Run and Render Docker builds passed. Only
  pre-existing npm deprecation warnings and the existing PHPUnit XML
  deprecation were observed.
- No staging or production migration/deployment was performed. Render and
  DigitalOcean remain unchanged fallbacks. No secret, real data, payment,
  Reservation/Order logic, public Birthday checkout, vendor, or TastyIgniter
  core change is included.

Known independent issue: full fresh-install migration ordering remains outside
this PR. After review and merge, deploy the additive migration to Canada
staging in a separate controlled phase, then run hold concurrency and admin
read-only acceptance against Cloud SQL before starting customer checkout work.

## 2026-07-18 - Canada staging Birthday slot-hold display acceptance

Environment: Canada staging only. Status: Resolved; documentation PR pending
review.

- PR #59 merge SHA `7e6a1e6d6bf40c863aa17434287e718c65fd6d16`
  supplied the already-migrated 15-minute hold lifecycle. PR #60 merge SHA and
  deployed SHA `f1d5dc9c8a576e81b8f72f618080e1efb09db6b9` fixes only null
  timestamp display and its tests; hold services, migration, indexes, and
  concurrency behavior did not change.
- Cloud Build `c41097bb-06b6-45a3-a4ff-a10b5405ff73` produced the full-SHA
  Canada image with digest
  `sha256:4e56e80eaa8c9dcd704ea5ea67255e320817d2c457c064d5893801b289c5ec6c`.
  Ready revision `le-chateau-canada-staging-slot60-f1d5dc9c` passed tagged
  `/healthz/` at 0% traffic and now serves 100%. Revisions
  `le-chateau-canada-staging-slot59-7e6a1e6d` and
  `le-chateau-canada-staging-00024-dof` remain Ready rollback points.
- No migration ran. Read-only application-account checks confirmed the hold
  migration record, 14 columns, five required indexes, two restrictive foreign
  keys, and an initially empty hold table.
- Authenticated Birthday Booking list/detail acceptance covered active,
  released, effective-expired before cleanup, persisted expired after one
  cleanup command, and no-hold states. Empty timestamp fields rendered as an
  empty value, never isolated `UTC` or ` UTC`; every non-empty timestamp kept
  `Y-m-d H:i:s UTC`. List/detail status remained consistent and no acquire,
  renew, extend, release, edit, save, delete, confirmation, Reservation, or
  payment action was exposed.
- Dashboard, Reservations, Orders, Birthday Packages, Birthday Add-ons, and
  Birthday Bookings loaded in the retained authenticated session. Public
  pages, admin login, Livewire, and retained test media passed; no localhost
  resource or browser-visible runtime failure appeared. Permission isolation
  remains unchanged and covered by the focused admin tests; no real staff role
  was modified.
- PR #59 already proved 900-second exact expiry, no-renewal idempotency,
  same-slot/same-Booking/different-slot concurrency, reclaim, owner-only
  release, and transactional cancellation. This phase rechecked three
  900-second staging rows and the effective-expiry/cleanup display paths.
- Synthetic QA used one clearly named package, one `example.invalid` Customer,
  four Bookings, and three holds. All were removed by exact IDs; no add-on
  snapshot existed. Reservations and Orders remained 0, Payments 6, and
  Payment Logs 0. All temporary PR #60 Cloud Run Jobs were deleted.
- Current-revision error-severity and HTTP 5xx counts were zero. INFO-only
  GCS FUSE/config-cache startup lines were reviewed and were not errors. No
  fatal, SQLSTATE, Cloud SQL, FUSE permission, Laravel cache, route, class, or
  translation failure was found.
- The local host has no PHP runtime, so the complete feature suite was not
  rerun in this deployment phase. PR #60's focused tests and review remained
  unchanged, and the live staging acceptance above passed.

Render and DigitalOcean remain unchanged fallbacks; production, secrets, real
data, payment, registration, customer checkout, outbound mail, and scheduler
configuration were not changed. Full fresh-install migration ordering remains
a separate known issue. Next gate: review and merge the documentation-only
validation PR; do not begin payment or registration work in this phase.

## 2026-07-18 - Delivery feature flag and server-side gate

Environment: local isolated Docker/MySQL 8.4. Status: Pending PR review; not
deployed or configured on Canada staging.

- Added the project-level `DELIVERY_ENABLED` flag, exposed at runtime through
  `config('delivery.enabled')`. It defaults to false, parses common boolean
  forms explicitly, and fails closed for invalid values.
- Delivery is available only when both the project flag and the current
  Location's existing `delivery.is_enabled` setting are true. Disabling the
  project flag does not delete or overwrite Location, Delivery Area, fee,
  minimum-order, or historical Order configuration.
- Active fulfillment methods omit Delivery while preserving Collection/Pickup.
  A stale Delivery session falls back to Collection without changing cart
  items, quantities, prices, Birthday state, or Reservation state. If neither
  method is available, passive web middleware clears the invalid order type and
  temporary Delivery state without blocking homepage, Birthday, Reservation,
  login, or content routes.
- Storefront order-type changes, cart fulfillment validation, and checkout
  finalization use strict server-side checks. These food-ordering actions return
  an explicit non-sensitive domain error when no method is available, and a
  disabled Collection/Pickup selection is cleared and rejected rather than
  silently used. Existing TastyIgniter checkout validation remains authoritative
  for location, address, area, hours, minimum order, totals, and delivery fee.
- Generic Orders API Delivery creates and updates fail closed because that API
  cannot safely reconstruct the complete storefront area/fee context. Pickup
  creates and updates remain available, and historical Delivery orders remain
  readable and unchanged.
- No schema or migration was added. No staging/production environment variable
  was changed, no Order was submitted, and no real address, customer, payment,
  geocoding call, secret, vendor, or TastyIgniter core change is included.
- Local acceptance passed 48 Delivery tests/124 assertions on MySQL 8.4.10,
  including real homepage, Birthday, Reservation-account, login, and content
  routes plus strict cart/checkout/API boundaries. The previously passing 78
  Birthday regression cases remain unchanged. Config/route/view cache,
  Composer strict validation, Pint, and clean no-cache Cloud Run/Render builds
  also passed. Existing PHPUnit XML and npm dependency deprecation warnings are
  non-blocking.

Canada staging must remain `DELIVERY_ENABLED=false` when this change is later
deployed for closed-state acceptance. Delivery Areas remain unconfigured and
the D2 storefront/UI and D3 business-parameter phases have not started. Render
and DigitalOcean remain unchanged fallbacks. The fresh-install migration
ordering issue remains separate.

## 2026-07-18 - Canada staging Delivery D1 closed-state acceptance

Environment: Canada staging only. Status: Resolved; documentation PR pending
review.

- PR #62 merge/deployed SHA
  `6a1ccc1d95e25050abe13e36377a38db7c80e438` was built by Cloud Build
  `48710aec-3904-46a5-8842-0e8d1aa5a719`. The full-SHA image digest is
  `sha256:724e82849b6fd8d5befd27d213823e78a271349e53ac32758b9cadd4fe772095`.
  Ready revision `le-chateau-canada-staging-d1-6a1ccc1d` passed tagged
  `/healthz/` at 0% traffic and now serves 100%. Runtime template fingerprint
  `0f5d7552c062fac4` matched the retained service configuration.
- Canada staging explicitly uses `DELIVERY_ENABLED=false`; the generated
  Laravel config cache also resolved `config('delivery.enabled')` to false.
  The Location's existing Delivery and Collection settings both remain true,
  while Delivery Areas remain 0. The project gate therefore removes Delivery
  and leaves only Collection/Pickup active without deleting future Delivery
  configuration.
- No migration or schema command ran. Orders, Reservations, Birthday
  Bookings, Birthday holds, Customers, Payments, and Payment Logs remained at
  their preflight baselines after cleanup.
- A stale Delivery session normalized to Collection and cleared Delivery-only
  timeslot, area, and address-position state while preserving cart,
  Birthday, and Reservation state. The no-fulfillment strict cart/checkout
  path failed closed without blocking ordinary routes.
- Delivery order-type spoofing, checkout, and Orders API create/update paths
  returned safe 422 responses before a Delivery write. Pickup API create,
  update, read, and attempted conversion to Delivery behaved as expected;
  the synthetic Pickup Order, status history, API token, and API user were
  removed by exact identifiers. No historical Delivery Order exists in this
  staging database, so live historical-read acceptance was not applicable;
  the PR #62 automated regression remains the compatibility evidence.
- Browser Pickup acceptance showed only `Cueillette`. A menu item could be
  added, increased from one to two, decreased to one, removed, and added again
  before opening checkout. Subtotal and total stayed equal with no Delivery
  fee or address requirement, and no Order was submitted. The QA cart was
  empty after cleanup.
- Public routes, admin pages, Livewire, frontend/admin assets, and retained
  test media returned successfully. Browser console errors, current-revision
  error-severity logs, HTTP 5xx, unexpected fulfillment 422s, and runtime
  fatal/Cloud SQL/FUSE/cache errors were zero. Two expected API 422 responses
  were observed. All temporary D1 Jobs were deleted.

Ready rollback revisions remain
`le-chateau-canada-staging-slot60-f1d5dc9c`,
`le-chateau-canada-staging-slot59-7e6a1e6d`, and
`le-chateau-canada-staging-00024-dof`. Render, DigitalOcean, production,
secrets, real data, payment, outbound mail, and domain configuration were not
changed. D2 storefront Delivery UI and D3 Delivery Areas/business parameters
have not started; the fresh-install migration-ordering issue remains separate.

## 2026-07-18 - Conditional Delivery storefront UI

Environment: local isolated Docker/PHP 8.3/MySQL 8.4. Status: Pending PR
review; not deployed or enabled on Canada staging.

- Storefront Delivery presentation now uses the same `DeliveryAvailabilityGate`
  as D1. Delivery controls render only when the global flag and current
  Location Delivery setting are both enabled; Location Collection remains an
  independent Pickup control.
- The homepage remains Pickup-only when Delivery is closed and retains the
  Birthday venue CTA. When both Delivery conditions are enabled, the existing
  address-search flow is restored alongside Pickup and Birthday. The address
  input has a programmatic label and the current-location control has an
  accessible name. No real address or geocoding request was used in QA.
- Orange menu, cart, checkout, and mobile fulfillment components continue to
  consume the D1-filtered active order types. A new session defaults to Pickup
  when both methods are available, an existing valid Delivery selection is
  preserved, and Delivery becomes the default only when Collection is disabled.
  Neither-method state remains fail-closed without a fake food-ordering CTA.
- The menu information panel now filters Delivery and Collection schedule tabs
  through the same gate. Disabled tabs are absent from the DOM and tab order,
  while general opening hours remain available.
- Project overrides correct the Orange Alpine timeslot tuple and prevent its
  map initializer from running without a valid map target/coordinates. This
  keeps Pickup-to-Delivery-to-Pickup modal switching free of browser console
  errors without modifying vendor or TastyIgniter core.
- Required `en_CA` and `fr_CA` Delivery/Pickup, address-search, availability,
  unavailable-state, and Birthday CTA copy is project-owned; tests reject raw
  keys and verify the exact critical translations.
- Browser acceptance covered desktop and a 390x844 mobile viewport. Closed
  state had no address input, Delivery tab, hidden focusable Delivery control,
  or horizontal overflow. Enabled state showed both fulfillment radios with
  Pickup selected, exposed Delivery address guidance after switching, returned
  cleanly to Pickup, and recorded zero console errors after runtime assets were
  published as they are in both deployment images.
- The isolated PHP 8.3/MySQL 8.4 verification passed 63 Delivery tests with
  190 assertions and 78 Birthday tests with 520 assertions. PHP syntax, Pint
  across the changed PHP files, strict Composer validation, and config, route,
  and view cache generation all passed. Fresh no-cache builds of
  `Dockerfile.cloudrun` and `Dockerfile.render` also passed; only existing npm
  dependency deprecation notices were emitted.
- D1 server gates, checkout totals/fees, area validation, Orders API
  fail-closed behavior, Birthday, Reservation, cart contents, and historical
  data were not weakened or changed. No Order, migration, schema, Delivery
  Area, fee, minimum, hours, production, Render, DigitalOcean, secret, or real
  customer data change is included.

Canada staging remains on the prior D1 revision with
`DELIVERY_ENABLED=false`; Delivery Areas remain 0. After review and merge, D2
must first be deployed with the flag still false for Pickup-only acceptance.
D3 Delivery Areas and business parameters remain a separate phase, as does the
fresh-install migration-ordering issue.

## 2026-07-18 - Q-009 Canada staging D2 Pickup checkout blocker

Environment: Canada staging and local isolated Docker. Status: Blocked on
staging; fix PR pending review. Impact: D2 acceptance only; production,
Render, DigitalOcean, D3 configuration, and stored business data are unchanged.

- PR #64 merge SHA `fab804d276f60a05548039f7d39b45a2585ff912`
  built successfully in Cloud Build
  `ad91ad49-dbac-4fd9-8eb1-40c24e0c9970`. The Artifact Registry image digest
  is `sha256:cab7dcaef031e071e8dad086075e81d5bd8e5299dac0ed6bfe81e793a7cfeefc`.
  Ready revision `le-chateau-canada-staging-d2-fab804d2` passed tagged
  `/healthz/`, page, asset, startup-log, config-cache, and read-only database
  preflight at 0% traffic. Runtime fingerprint `5bc68581b170b321` matched the
  retained service configuration.
- Cached `config('delivery.enabled')` resolved to false. Location Delivery and
  Collection remained stored as enabled, effective Delivery remained false,
  and Delivery Areas remained 0. No migration or schema command ran.
- After a temporary 100% cutover, the desktop homepage and menu were
  Pickup-only: Pickup and Birthday CTAs remained available, Delivery search,
  schedule tab, hidden focusable controls, raw translation keys, horizontal
  overflow, and browser console errors were absent. Browser cart add,
  increase, decrease, remove, re-add, persistence, and fee-free totals passed.
- Q-009 was found before checkout submission: the Pickup checkout still
  rendered the upstream `delivery_comment` textarea labelled for a driver.
  This violates the D2 requirement that Delivery instructions not render for
  Pickup. No final Order, Customer, Payment, Payment Log, address, or Delivery
  Area was created.
- The browser cart was emptied, the disposable preflight Job was deleted, and
  traffic was returned to `le-chateau-canada-staging-d1-6a1ccc1d` at 100%.
  The failed D2 revision remains at 0% as an auditable rollback candidate;
  `/healthz/` returned `200 ok` after rollback.
- The proposed project-level Orange checkout partial omits only
  `delivery_comment` when the current order is not Delivery and preserves the
  field for real Delivery orders. It does not change checkout persistence,
  totals, payment, Orders API, vendor, or TastyIgniter core.
- Isolated PHP 8.3/MySQL 8.4 verification passed all 51 Delivery tests with
  182 assertions. The focused storefront UI suite passed 13 tests with 69
  assertions after the final generic-comment preservation assertion. Pint and
  Blade view caching passed. Fresh local
  `Dockerfile.cloudrun` and `Dockerfile.render` builds also passed.

Next step: review and merge the Q-009 fix PR, rebuild from the resulting full
SHA, and repeat D2 Pickup-only staging acceptance before any D3 work.

## 2026-07-19 - Delivery D2 Pickup-only Canada staging acceptance

Environment: Canada staging. Status: Resolved for D2; documentation review
pending. Impact: Pickup storefront presentation only. Production, Render,
DigitalOcean, Delivery business parameters, and stored business data are
unchanged.

- PR #64 (`fab804d276f60a05548039f7d39b45a2585ff912`) and the Q-009 fix in
  PR #65 (`31821289df9ae4a162cabd0cac7a3ac6fb04cd0c`) are deployed from the
  full merge SHA. Cloud Build `7ae74bf0-1943-4f15-a87d-d5fe43dac2af`
  produced digest
  `sha256:72371b610a2dff66d29dcee09a2095c72c2f6bb0d932d33744db3444c3689102`.
- Ready revision `le-chateau-canada-staging-d2fix-31821289` serves 100% of
  traffic. The failed D2 revision `le-chateau-canada-staging-d2-fab804d2`
  remains tagged at 0%; D1 `le-chateau-canada-staging-d1-6a1ccc1d` remains a
  rollback revision. Redacted D1/new-revision runtime fingerprints both equal
  `74bfef31792cdfcf`.
- `DELIVERY_ENABLED=false` is explicit and cached false. Stored Location
  Delivery and Collection settings remain enabled, effective Delivery is
  false, Delivery Areas remain 0, and the migration count remains 165. No
  migration or schema command ran.
- Desktop and 390x844 browser checks passed the Pickup-only homepage and menu:
  Pickup and Birthday CTAs remain, Delivery search/address/tabs/hidden
  focusable controls are absent, Collection is selected after refresh, and
  there is no horizontal overflow, raw translation key, or console error.
- The earlier PR #64 live pass already covered cart add/increase/decrease/
  remove/re-add/persistence and fee-free totals. During this redeploy check the
  store was still before its configured opening time, so a new cart item was
  correctly rejected by the existing schedule guard. No Order was submitted.
- Q-009 is resolved in the deployed full-SHA image: the project checkout
  override omits only `delivery_comment` for Pickup while preserving the
  ordinary `comment` field and the Delivery-only field path. The final focused
  PR #65 suite passed 13 tests with 69 assertions; all 51 Delivery tests passed
  with 182 assertions.
- A production-image disposable Job confirmed stale Delivery session cleanup,
  Pickup fallback, cart/Birthday/Reservation session preservation, and 422
  rejection without internal-detail leakage for storefront and Orders API
  Delivery spoofing. The Job was deleted after the checks.
- Orders/Pickup Orders/Delivery Orders remained 3/3/0; Customers,
  Reservations, Birthday Bookings, holds, and Delivery Areas remained 0;
  Payments/Payment Logs remained 6/0. Retained test media returned 200,
  `image/png`, 109065 bytes, and the accepted seven-day cache header.
- Admin Orders, Reservations, Birthday Bookings, Packages, Add-ons, and Media
  Manager loaded without console errors. Current-revision error-severity,
  HTTP 5xx, unexpected 422, fatal, SQL, FUSE, cache, and permission counts were
  zero. `/healthz/` returned `200 ok`.

Known non-blocking items: Q-005 remains open because the `en_CA` switch still
falls back to `fr_CA`. Q-010 is Deferred: the upstream Orange scheduled Pickup
time select is `wire:ignore` and did not synchronize a changed same-day value
back to Livewire during pre-opening QA. It did not change D2 behavior, future
orders remain disabled, and it must be handled separately before scheduled
Pickup is relied on. The staging `Cash On Delivery` payment-method label is not
a Delivery fulfillment control and remains outside D2/payment scope.

Next step: review and merge the documentation-only D2 validation PR. Do not
enable Delivery or begin D3 until its Delivery Area and business parameters
are separately approved.

## 2026-07-19 - Delivery D3A business parameter audit

Environment: Canada staging and local source audit. Status: Pending business
decisions. Impact: planning only; no runtime or database write.

- Baseline `4.x` is `ea5c6b5f263ba93f4bd28b9551435d85c66b7ff7`.
  Ready revision `le-chateau-canada-staging-d2fix-31821289` remains at 100%
  with explicit and cached `DELIVERY_ENABLED=false`.
- Read-only admin inspection confirmed stored Location Delivery and Collection
  are enabled, Delivery Areas are 0, effective storefront fulfillment is
  Pickup-only, and no Delivery launch fee exists. Retained Delivery values are
  CAD 0.00 minimum, 15-minute interval, 25-minute lead time, future orders off,
  and daily 12:00-21:00 hours. Pickup remains CAD 0.00 minimum and daily
  12:00-22:00.
- Tax mode is disabled with rate 0 and Delivery-charge tax off. Distance unit
  is kilometres. Geocoder is the configured Google/Nominatim Chain; no secret
  value is recorded.
- Native area support, overlap priority, address revalidation, totals order,
  fee/minimum/free-threshold behavior, schedules, rollback, D3B/D3C sequence,
  and the complete pending user decision table are recorded in
  `DELIVERY_D3_BUSINESS_PARAMETER_PLAN.md`.
- Preferred technical launch shape is one conservative polygon with one base
  fee rule, no distance surcharge, Pickup default, future orders off, and no
  tax-setting change. This is a recommendation only; every business value
  remains pending confirmation.
- Q-011 (Open, blocks D3C): before Delivery enablement, a controlled synthetic
  provider-failure test must prove that geocoder logs/public errors do not
  expose complete addresses, query URLs, provider credentials, geometry, SQL,
  or internal IDs. Public Nominatim identification, attribution, and quota
  suitability must also be accepted. Any required fix must be project-owned,
  not a vendor/core patch.

No Delivery Area, fee, minimum, schedule, tax, environment variable, Order,
Customer, address, schema, production, payment, mail, Render, or DigitalOcean
change was made. Q-005, Q-010, and fresh-install migration ordering remain
independent. Next step is user confirmation of the D3 decision table; Canada
staging must remain `DELIVERY_ENABLED=false` until D3B and tagged D3C
acceptance are complete.

## 2026-08-19 - Delivery D3B closed-gate staging configuration

Environment: Canada staging. Status: Resolved for the approved D3B
configuration scope; D3C remains blocked. Impact: Location coordinates,
Delivery Area, fee conditions, Delivery minimum, and Delivery schedule only.

- The user confirmed that staging contains no real customer, order, or payment
  data and authorized the scoped database writes and a disposable Cloud Run
  Job. Production, Render, DigitalOcean, schema, migrations, secrets, payment,
  mail, and public Delivery enablement were outside scope.
- The supplied KMZ/KML geometry was validated as one suitable polygon: one
  Document, no Folder, two Placemarks, one Point, one Polygon, and one ring.
  The ring has 37 coordinate entries including closure, 36 unique vertices,
  no consecutive duplicates, no self-intersection, and an approximate area of
  14.74 square kilometres.
- The current default Location coordinates were saved from the supplied map
  point and independently read back. The full street address is intentionally
  not repeated in this record.
- One default native polygon area, `D3 Montreal Delivery Area`, was retained.
  Admin readback confirmed Shape (polygon), 36 saved unique vertices, the
  expected boundary box, and no distance-based charge rows.
- Delivery conditions were saved in priority order: CAD 0.00 at or above a
  CAD 80.00 subtotal, then CAD 5.00 below CAD 80.00. Delivery minimum was
  saved as CAD 20.00.
- Delivery hours were changed to Monday-Friday 12:00-21:00 with Saturday and
  Sunday closed. Pickup hours and other Pickup settings were unchanged.
- The disposable same-image Job used the active staging service environment
  and Cloud SQL socket. Its transaction and after-save self-check confirmed
  one area, 36 vertices, the expected fee rules, an inside Location point, an
  outside synthetic point, and an inclusive boundary point. Earlier
  incompatible attempts created no area row and were corrected before the
  verified run.
- Admin readback independently confirmed the area, fee rules, minimum,
  schedule, coordinates, and boundary data. The public storefront still shows
  Pickup and Birthday paths only because `DELIVERY_ENABLED=false` remains in
  effect.
- The disposable Cloud Run Job was deleted after verification and an API GET
  returned 404. No Order, Customer, Reservation, Birthday, Payment, or Payment
  Log was created or modified by this task.

At completion of this D3B configuration step, Q-011 remained Open and blocked
D3C Delivery enablement. The later final clean-rerun section records its current
resolved status; remaining business decisions that affect the D3C acceptance
matrix are still independent.

## 2026-08-20 - Delivery D3 Q-011 controlled failure reproduced

Environment: Canada staging and local isolated tests. Status: Open; project
redaction fix pending review and staging redeployment. Impact: Delivery
geocoder logging and Livewire error privacy only.

- The active revision remained `le-chateau-canada-staging-d2fix-31821289` at
  100% traffic with `DELIVERY_ENABLED=false`; the storefront remained
  Pickup-only and the retained D3B area, fee, minimum, and hours were not
  changed.
- A disposable same-image Job used a synthetic address marker and synthetic
  failing endpoints. The application deliberately exercised the same empty
  geocoder diagnostic log path used by Orange.
- Application/Cloud Run logs contained the encoded synthetic address and the
  provider request URL. No provider credential, authorization header,
  geometry, SQLSTATE, or internal Location/area ID was observed in the tested
  log window. This is sufficient to keep Q-011 Open.
- Source audit found that Orange logs raw provider diagnostics for an empty
  geocode result. Address autocomplete and suggestion lookup also convert raw
  provider exception messages into Livewire validation errors. Staging debug
  is false, but that setting does not sanitize explicit logs or validation
  messages.
- The project-owned wrapper now covers direct forward and reverse facade
  exceptions before a collection is returned, as well as the autocomplete and
  Google place-lookup provider calls. It emits only a generic event and safe
  operation category, returns the existing generic invalid-address validation,
  preserves business validation, and does not catch programming `Error`
  instances. It does not modify vendor/core or suppress all application
  logging.
- Eight isolated redaction/regression tests passed with 48 assertions. They
  prove that synthetic address, URL, and credential markers are absent from
  validation payloads, only generic log events are emitted, direct forward and
  reverse exceptions fail closed, business coordinate validation is preserved,
  a programming error is not converted into address validation, and successful
  reverse geocoding preserves the provider-supplied formatted address instead
  of rebuilding it from address components.
- Both disposable Q-011 Cloud Run Jobs were permanently deleted after the
  test. Cloud Run list readback confirmed that both exact names are absent.
  No synthetic database row, temporary revision, cache, or configuration was
  retained. Synthetic Cloud Logging audit entries remain subject to the
  platform retention policy; they contain no real customer data.
- The native Nominatim provider inherits the incoming browser User-Agent or
  Referer instead of supplying a stable application identity. There is no
  shared cross-instance one-request-per-second limiter, and Cloud Run fan-out
  could send concurrent fallback traffic. Public Nominatim also prohibits
  autocomplete. Therefore, public Nominatim fallback is not approved for
  production Delivery traffic; this remains a separate production operations
  gate and does not expand the redaction fix.

The broader Delivery suite was also attempted without a database or application
key; database-dependent tests could not run in that isolated environment. No
database was created or migrated for this remediation.

No Delivery, Pickup, tax, polygon, fee, minimum, schedule, database schema,
Order, Customer, Reservation, Birthday, payment, mail, production, Render,
DigitalOcean, secret, or real-data change was made. Next step: review the
updated PR #69; do not merge or deploy until review passes. After review and
merge, deploy it to an isolated Canada staging revision with Delivery still
closed, rerun Q-011, and only then decide whether Q-011 can be resolved.

## 2026-08-20 - Delivery D3 Q-011 final clean rerun resolved

Environment: Canada staging. Status: Resolved for the tested failure path.
Impact: Delivery geocoder privacy gate only; Delivery remains globally closed.

- Corrected the Delivery working-hours mapping in one scoped transaction. The
  before-state was Monday closed, Tuesday-Saturday 12:00-21:00, and Sunday
  closed. The verified after-state is Monday-Friday 12:00-21:00 with Saturday
  and Sunday closed. Pickup and every other D3B business setting were
  unchanged.
- Built commit `3fc841d12e65155c445c9c747ee7f97ec3ea0f49` and tested image digest
  `sha256:388a60cd43539746cc1e5725066a4fd7fd9bee0c538401ab8646da68608df2d1`
  in isolated revision `le-chateau-canada-staging-q011-3fc841d` at 0% traffic.
  The accepted revision `le-chateau-canada-staging-d2fix-31821289` retained
  100% traffic and `DELIVERY_ENABLED=false` remained unchanged.
- The authoritative clean execution
  `delivery-d3-q011-clean3-20260820-xc79z` passed Cloud SQL mount, application
  bootstrap, and database preflight without emitting database diagnostics. It
  covered empty results, forward and reverse provider failures, autocomplete,
  place lookup, business validation, successful reverse geocoding, and the
  fail-closed Delivery API gate.
- Exact-execution log scanning and public/Livewire acceptance found no full
  synthetic address, provider URL, credential, raw provider exception,
  geometry, SQL/internal ID, database diagnostic, or PHP path exposure.
  Provider failure remained fail-closed, the cart/session stayed usable, and
  no Delivery Order was written.
- Desktop and mobile storefront checks passed for Pickup-only behavior,
  Birthday and Reservation routes, authenticated read-only Admin access,
  health, and absence of browser console errors. The active revision remained
  healthy and the isolated and active task windows contained no error-severity,
  HTTP 5xx, fatal PHP, SQL connection, or storage permission event.
- Final D3B readback confirmed one default polygon area with 36 vertices,
  CAD 5.00 base fee, free Delivery at CAD 80.00, CAD 20.00 minimum, distance
  surcharge off, and the corrected weekday schedule. Orders, Customers,
  Reservations, Birthday records, slot holds, Payments, and Payment Logs were
  unchanged before and after the rerun.
- Four disposable Jobs used for the schedule correction and clean acceptance
  attempts were permanently deleted after explicit approval; exact resource
  readback confirmed all four are absent. Cloud Shell test files and local
  temporary read-only clones were removed. Historical audit logs remain only
  under the platform retention policy; no temporary config or synthetic
  database row remains.
- Earlier non-authoritative attempts failed safely because of harness-only
  boolean parsing and an unavailable development test dependency. A still
  earlier aborted Job lacked its Cloud SQL socket. None used real customer
  data, and all are excluded from the authoritative clean acceptance window.
- Q-011 is therefore `Resolved` only for the current Canada staging tested
  failure path. Public Nominatim fallback is not approved for production
  Delivery traffic and remains a separate production operations decision.

Next step: review and merge the Q-011 validation PR. Keep
`DELIVERY_ENABLED=false`; freeze the project handoff baseline before any
separately approved D3C enablement work.

## 2026-08-20 - Claude handoff baseline freeze

Environment: repository, Canada staging read-only audit, and documentation.
Status: docs-only handoff PR pending review. Impact: project process and
handoff records only.

- Clean `4.x` was synchronized to PR #70 merge SHA
  `f731f775e5d3f069a959641323b544007ca21552`; no open Delivery implementation
  PR required continuation at freeze time.
- Fresh Cloud Run readback confirmed
  `le-chateau-canada-staging-d2fix-31821289` at 100% traffic,
  `le-chateau-canada-staging-q011-3fc841d` tagged at 0%, HTTP `200 ok`, and
  `DELIVERY_ENABLED=false`. The Q-011 revision is latest Ready but is not the
  main-traffic revision.
- Read-only infrastructure checks confirmed the configured runtime identity,
  zero user-managed service-account keys, runnable Cloud SQL reference,
  readable Storage and Artifact references, five of five resolvable Secret
  references without reading values, and a successful regional Q-011 build.
- `CLAUDE_HANDOFF.md` freezes the completed D1/D2/D3B and Q-011 state, Birthday
  and payment boundaries, known issues, fallbacks, production gates, exact D3C
  next phase, acceptance criteria, and stop conditions.
- `AGENT_WORKFLOW.md` introduces risk-based autonomous review: Level 0-3,
  implementation Review A, adversarial Review B, proportional verification,
  Ready PR rules, merge gates, destructive-operation approval, secret handling,
  and automatic continuation after an approved merge.
- Level 0 docs and routine Level 1/2 work no longer require a second agent by
  default. Payment, authentication/authorization, privacy architecture,
  destructive migration, money/availability races, production incidents,
  security boundaries, major refactors, and user-requested reviews still do.
- No standing docs-only auto-merge permission is assumed. The handoff PR must
  stop at the merge gate, and its eventual merge SHA becomes the
  `CLAUDE HANDOFF BASELINE`.

No Cloud Run setting, revision, traffic, database row/schema, Delivery Area,
fee, minimum, hours, Pickup, Birthday, Reservation, payment, production,
Render, DigitalOcean, secret, or real data was changed. Do not start D3C before
the handoff PR is merged.

## 2026-08-20 - Delivery D3 business decisions and leftover Job cleanup

Environment: repository documentation plus a Canada staging read-only audit and
an approved Cloud Run Jobs cleanup. Status: all D3 business decisions confirmed;
Delivery remains globally closed. Impact: business parameters, project records,
and disposable job resources only.

- Verified the frozen handoff baseline before any change. Clean `4.x` matched
  `6c9331c1526778e474be2a980831f1e5505955b4`, GitHub reported zero open pull
  requests, and the worktree was clean.
- Independently read back Canada staging. Main traffic remained 100% on
  `le-chateau-canada-staging-d2fix-31821289` with image digest
  `sha256:72371b610a2dff66d29dcee09a2095c72c2f6bb0d932d33744db3444c3689102`,
  `le-chateau-canada-staging-q011-3fc841d` and
  `le-chateau-canada-staging-d2-fab804d2` remained tagged at 0%, health returned
  `200 ok`, Cloud SQL read back `RUNNABLE` with automated backups enabled, the
  runtime identity still had zero user-managed keys, the media bucket volume was
  mounted at the documented path, and the revision bound the five documented
  Secret Manager references. No secret value was read.
- Extended the readback beyond the freeze record: all 26 revisions of the
  service were checked for the Delivery gate. Only four define
  `DELIVERY_ENABLED` —
  `le-chateau-canada-staging-q011-3fc841d`,
  `le-chateau-canada-staging-d2fix-31821289`,
  `le-chateau-canada-staging-d2-fab804d2`, and
  `le-chateau-canada-staging-d1-6a1ccc1d` — and all four read `false`. The
  other 22 predate the flag and do not define it. No revision in this service
  has ever enabled Delivery.
- Resolved every remaining `Pending confirmation` row in
  `DELIVERY_D3_BUSINESS_PARAMETER_PLAN.md`. Twenty-four business decisions were
  confirmed by the user on 2026-08-20 and are recorded in that table.
- Two confirmed decisions carry an accepted operational risk. Admin Delivery fee
  changes are allowed without an approval workflow even though the platform
  stores no actor or timestamp, and holiday closure relies on an operator
  toggling Delivery off in admin for the day. Both were explained before
  confirmation and accepted for first launch.
- One confirmed direction still needs a number. The tax rate and whether the
  Delivery fee is taxable in Quebec require accountant confirmation. Staging
  tax remains disabled, so it does not block D3C. The Google geocoding daily
  usage cap was set at 500 requests per day, with a budget alert, on
  2026-08-20.
- Audited 17 previously undocumented Cloud Run Jobs left from the July
  initialization phase. Three could write to the database
  (`birthday-migration-f40531e4`, `tastyigniter-empty-migrate-178366`,
  `tastyigniter-admin-create-178372`), three rewrote container configuration
  (`tastyigniter-init-canada-1783653711`,
  `tastyigniter-init-canada-1783654052`, `tastyigniter-init-canada-staging`),
  and eleven were read-only probes. `tastyigniter-admin-create-178372` created
  or reset a super-admin account and explains the previously unexplained
  `tastyigniter-admin-password` secret.
- Confirmed that none of the 17 stored a literal credential. Every password,
  secret, token, and key environment variable resolved through Secret Manager;
  the literal-value count was zero.
- Exported all 17 restorable definitions to the operator's Cloud Shell home
  directory, then deleted all 17 after explicit per-target confirmation. Exact
  readback confirmed zero remaining Jobs in the region and 17 of 17 absent.
  Post-deletion readback confirmed unchanged traffic, unchanged
  `DELIVERY_ENABLED=false`, and health `200 ok`.

No Cloud Run service, revision, traffic, image, environment variable, Cloud SQL
row or schema, Delivery Area, fee, minimum, schedule, Pickup, Birthday,
Reservation, payment, secret, production, Render, or DigitalOcean state was
changed. Delivery remains closed and D3C has not started.

## 2026-08-20 - Runtime configuration fingerprint FP-1

Environment: Canada staging, read-only measurement. Status: recorded.

- The runtime fingerprint recorded at the handoff freeze,
  `b82e3aa41eaba24a1bc54784f9f889209a83b178cda1baeec85f069337e41b1b`, is void.
  Its normalized field list and algorithm were never recorded and cannot be
  reconstructed, so the value can be neither confirmed nor disproved. It has
  never been verified. It is retired rather than repaired: writing an
  algorithm now would not turn the old digest into evidence of anything. No
  document may cite it as runtime evidence or compare a new measurement
  against it.
- Defined fingerprint FP-1 in `CLOUD_RUN_CANADA_STAGING_RUNTIME.md` with a
  fixed twenty-field list, an explicit algorithm, a reference implementation,
  and stated coverage limits. FP-1 is a new baseline whose history starts
  2026-08-20 and is not comparable to the void value.
- FP-1 records a per-field digest table alongside the total, so a future
  mismatch names the field that changed instead of only signalling that
  something did. The total is the SHA-256 of the published table, so the table
  alone re-verifies the total without access to the service.
- Field 16 records the Cloud SQL connection name as a digest rather than an
  instance count. A count cannot distinguish one attached instance from a
  different attached instance, so a database swap would have left a
  count-based fingerprint unchanged. Verified: substituting a different
  instance name, and separately a different project, each changed the field
  digest, and the field was confirmed to carry the connection name rather than
  an empty string.
- Recorded the confidentiality limit rather than implying protection that does
  not exist. Per-field digests localize change and are not a confidentiality
  control; a digest over a low-entropy value can be confirmed by anyone able
  to guess a candidate. A keyed hash was rejected because computing a
  fingerprint would then require reading a secret value.
- Baseline measured against `le-chateau-canada-staging-d2fix-31821289` at 100%
  traffic: FP-1
  `2127efd6d63de53e6d9fbc5388f9db3fee72d0575eec25a09b9f99e9ad8565d3`. Two
  independent runs produced byte-identical output.
- Field plaintext was assembled in memory only. No plaintext was printed,
  logged, written to disk, or committed; only the per-field table and the
  total are recorded. No secret value was read.

No Cloud Run service, revision, traffic, image, environment variable, Cloud SQL
row or schema, Delivery Area, fee, minimum, schedule, Pickup, Birthday,
Reservation, payment, secret, production, Render, or DigitalOcean state was
changed. Delivery remains closed and D3C has not started.

## 2026-08-20 - FP-1 implementation location and D3C pre/post gate

Environment: repository, plus a read-only Canada staging re-measurement.
Status: recorded.

- FP-1 is now implemented once, in `tools/fp1.py`. The copy embedded in
  `CLOUD_RUN_CANADA_STAGING_RUNTIME.md` is removed. The script is authoritative
  where it and the document's field table disagree.
- Re-measured Canada staging before the move. FP-1 for
  `le-chateau-canada-staging-d2fix-31821289` at 100% traffic is unchanged from
  the value recorded in PR #75, so staging has not drifted.
- D3C now carries an FP-1 pre/post gate. Record FP-1 for the main-traffic
  revision before the first D3C action and again after the last. An identical
  pair is the accepted evidence that the 100%-traffic path was untouched. A
  differing pair stops work, and the per-field digest table names the field
  that moved.
- A changed `revision` or `traffic_percent` in that pair means traffic moved,
  which is outside D3C scope and requires the separate cutover gate. FP-1
  refuses to run while traffic is split, so a split part way through D3C
  surfaces as an error rather than a silent pass.

No Cloud Run service, revision, traffic, image, environment variable, Cloud SQL
row or schema, Delivery Area, fee, minimum, schedule, Pickup, Birthday,
Reservation, payment, secret, production, Render, or DigitalOcean state was
changed. Delivery remains closed and D3C has not started.

## 2026-08-22 - Owner-side setting change: ASAP-only for Delivery and Pickup; Delivery minimum read back

Environment: Canada staging shared database, through the admin, changed by the
user. Status: recorded. No agent performed or requested the change.

- The user changed the ASAP/later restriction to ASAP-only for both order
  types on 2026-08-22 and confirmed it was intentional, neither an accident nor
  a by-product of the Friday investigation. Stored values as read back by the
  user: Delivery `time_restriction = 1`, Collection `time_restriction = 1`,
  Collection `min_order_amount = 0.00`, lead time 25, interval 15, and
  `future_orders.is_enabled = 0` for both.
- Prior value for Delivery: None, both ASAP and a later same-day slot allowed,
  as recorded in the D3A audit and confirmed as decision "ASAP: On" on
  2026-08-20. Prior value for Collection was not separately recorded in the
  audits. The decision table carries the original value, the new value, and
  the change date.
- Storefront read-back by the agent at 20:1x the same day on the 0%-traffic
  copies: the order-type dialog offers only "Dès que possible" for both order
  types, which is what ASAP-only renders. The change was already in effect at
  15:1x, when the same dialog offered no later choice.
- The user read back the stored Delivery minimum as `min_order_amount = 20.00`,
  agreeing with the decision. The storefront's "Min. Order Amount: $80.00" is
  therefore computed, not stored: source traces it to the "CA$5.00 below
  CA$80.00" fee rule, whose threshold the vendor treats as the area's minimum
  order total. Recorded in `D3C_PROGRESS.md` with the checkout-gate
  consequence and the two remedy shapes; no setting was changed for it.
- Q-010 is masked by this change, not fixed; recorded in `CLAUDE_HANDOFF.md`
  section 10.

No Cloud Run service, revision, traffic, image, environment variable, schema,
Delivery Area, fee rule, schedule, payment, secret, production, Render, or
DigitalOcean state was changed by any agent. The two stored restriction values
were changed by the user in the admin as described.

## 2026-08-23 - Three empty mail secrets created in Secret Manager

Environment: Google Cloud project `le-chateau-canada-staging`, Secret Manager.
Status: recorded. Level 2, performed on the user's explicit instruction after
PR #104 merged.

- Created, empty, with automatic replication like the existing secrets:
  `tastyigniter-mail-host`, `tastyigniter-mail-username`,
  `tastyigniter-mail-password`. Read back: each exists with zero versions.
  Before the action none of the three existed (listed and checked by name;
  the creation loop refuses to touch an existing name).
- No value was entered, seen, or passed through a command line, log, or
  conversation. The user adds the values in the console as new versions.
- No IAM change: the runtime service account already holds
  `roles/secretmanager.secretAccessor` at project level, and the existing
  secrets carry no per-secret bindings, so the new secrets need none.
- No Cloud Run revision, traffic, environment variable, image, database,
  schedule, or business value was changed. `MAIL_MAILER=log` stays on every
  revision; binding these secrets to a revision is a later, separate step,
  and that revision must also carry `MAIL_TEST_REDIRECT_TO`.
