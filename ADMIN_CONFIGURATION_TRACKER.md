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
