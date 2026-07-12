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
