# BUSINESS_CONFIGURATION_PLAN

本文件是第二阶段的业务配置和魁北克英法双语本地化规划。它只说明后台配置顺序、文案准备方向和后续风险，不开发新功能，不修改 TastyIgniter core，不修改订单、支付、预约冲突、认证或安全逻辑。

## 业务目标

第一版网站要解决这些问题：

- 顾客可以用法语或英语浏览网站。
- 法语为默认语言，英语为可切换语言。
- 顾客可以线上查看菜单。
- 顾客可以提交订餐 / 自取订单。
- 顾客可以预约生日派对场地。
- 店员可以在后台管理菜单、订单和预约。

第一版的重点是先把 TastyIgniter 已有能力配置清楚：菜单、商品、自取、预约、基础邮件和双语文案。不要一开始就开发复杂功能。

## 魁北克本地化原则

- 法语内容必须完整，不做英文-only 网站。
- 英语作为可切换语言，服务英语顾客。
- 面向顾客的主要页面、菜单、按钮、表单、邮件和预约说明都需要英法双语。
- 代码、变量名、文件名、配置 key、commit message 仍使用英文。
- 面向顾客的文案需要法语和英语两份。
- 法语建议使用加拿大法语 / 魁北克语境表达，不要直接使用生硬机器翻译。
- 不要在文档中给出法律保证；这里只记录产品层面的合规倾向，例如“上线前需要人工审核法语文案、隐私政策、条款和税费配置”。

## TastyIgniter 当前多语言能力检查

本次只做代码和运行环境层面的检查，不登录后台，不读取管理员密码。

已确认：

- 后台 Languages 入口存在，位置为 `Settings > Languages`，内部 URL 为 `/admin/languages`。
- TastyIgniter core 有 `languages` 和 `language_translations` 相关模型、控制器和迁移。
- `lang/` 目录存在，当前项目根目录已有 `lang/en` 和 `lang/vendor/.gitignore`。
- `lang/vendor/` 是适合放扩展、主题、core 翻译覆盖文件的位置，常见形式为 `lang/vendor/<package>/<locale>/<file>.php`。
- `igniter:language-install <locale>` 命令存在，用于从 TastyIgniter marketplace 拉取语言包；命令帮助示例为 `fr_FR`，说明系统倾向使用完整 locale code。
- `config/app.php` 当前默认 `locale` 和 `fallback_locale` 仍为 `en`，但 TastyIgniter 运行时会从后台默认语言和 `supported_languages` 生成 localization 配置。
- 前台 localization middleware 支持从 URL 第一段、浏览器语言和 session 读取语言。
- General settings 中有 `detect_language` 选项，可开启浏览器语言检测。
- Page 模型有 `language_id`，说明静态页面可以按语言区分。
- MailLayout 模型有 `language_id`，说明邮件布局支持按语言区分。
- MailTemplate 使用语言 key 和邮件视图生成内容，邮件模板可在后台配置，但是否能按顾客语言自动选择不同邮件版本需要后续实际验证。
- Orange 主题当前来自 `vendor/tastyigniter/ti-theme-orange`，不是项目根目录的 `themes/` 自定义主题。
- Orange 主题大量前台文案使用 `@lang(...)` 或 `lang(...)`，适合通过语言文件翻译。
- Orange 主题中仍存在少量组件配置说明和异常提示的英文硬编码；这些不应直接改 vendor，后续如影响顾客界面，应通过自定义主题或扩展处理。
- 未发现现成的前台语言切换按钮。第一版需要先检查后台页面是否能显示语言切换；如果没有，需要后续主题改造增加 `Français | English` 切换入口。

当前判断：

- 系统文案、按钮、错误提示：优先通过 Languages 后台和 `lang/vendor/` 翻译。
- 静态页面：优先通过后台 Pages 按语言创建。
- 菜单商品、分类、商品选项：需要在后台验证是否有语言切换字段。如果没有内置多语言字段，第一版可以用双语名称临时上线，例如 `Crème glacée / Ice Cream`，但这不是最终理想方案。
- 预约页面和结账页面：多数系统文案可通过语言文件翻译；页面结构和语言切换按钮可能需要后续主题改造。

## 语言策略

- Default language: French。
- Secondary language: English。
- 第一选择 locale codes：`fr_CA` 和 `en_CA`，因为店铺位于加拿大魁北克，且 TastyIgniter 的语言安装命令示例使用完整 locale code，例如 `fr_FR`。
- 备选 locale codes：如果 TastyIgniter marketplace 或后台不支持 `fr_CA` / `en_CA`，可以先使用系统实际支持的 `fr_FR` / `en` 或 `fr` / `en`，再把顾客可见法语文案人工调整为魁北克语境。
- URL 或 session-based language switching：TastyIgniter 的 localization middleware 支持 URL 第一段读取 locale，也支持 session locale。第一版建议优先使用系统现有机制；如果前台没有语言切换组件，后续通过主题改造实现 `Français | English` 按钮。
- 浏览器语言检测：可以作为辅助，但不要作为唯一机制。当前代码中浏览器检测只取前两位语言码；如果后台使用 `fr_CA` / `en_CA`，需要实际测试浏览器检测是否能匹配。
- 后台管理员界面可以先保留英文，前台顾客界面必须优先双语。
- 语言切换按钮建议显示为 `Français | English`。
- 法语 fallback 不应依赖英语。上线前要人工检查法语页面是否完整。

## 需要双语化的顾客界面清单

第一版必须准备法语和英语内容：

- 首页。
- 菜单页。
- 商品分类。
- 商品名称。
- 商品描述。
- 商品选项。
- 购物车。
- 结账页面。
- 自取说明。
- 配送说明，如果第一版启用配送。
- 生日派对预约页面。
- 预约表单字段。
- 预约说明。
- 预约成功 / 失败提示。
- 订单确认邮件。
- 新订单提醒邮件。
- 预约确认邮件。
- 取消预约邮件。
- 隐私政策。
- 服务条款。
- 联系页面。
- 营业时间说明。
- 过敏原提示。
- 图片 `alt` text。
- SEO title 和 meta description。

## 后台配置优先级

### 第一优先级：必须先配置

- 店铺基础信息。
- 法语默认语言。
- 英语第二语言。
- 营业时间。
- 菜单分类。
- 商品。
- 商品选项。
- 自取设置。
- 预约时段。
- 预约容量。
- 基础邮件通知。
- 前台语言切换方案。
- 基础过敏原提示。

### 第二优先级：上线前配置

- 支付方式。
- 税费。
- 优惠码。
- 正式邮件服务。
- 隐私政策和条款页面。
- 真实品牌图片。
- 所有顾客可见文案的人工校对。
- 法语文案审核。
- 移动端显示检查。
- 订单和预约邮件的真实收发测试。

### 第三优先级：以后再考虑

- 自定义生日预约字段。
- 订金流程。
- Google Calendar 同步。
- 短信通知。
- 自定义扩展。
- 深度主题改造。
- 自动翻译工作流。
- 复杂配送逻辑。

## 菜单分类建议

| English | French | 适合放什么商品 |
| --- | --- | --- |
| Ice Cream | Crème glacée | 单球、双球、杯装、甜筒、软冰淇淋、家庭装。 |
| Sundaes | Coupes glacées | Sundae、圣代杯、酱料和浇头组合。 |
| Milkshakes | Laits frappés | 奶昔、冰沙类饮品、特色混合饮品。 |
| Ice Cream Cakes | Gâteaux à la crème glacée | 标准冰淇淋蛋糕、定制蛋糕、节日蛋糕。 |
| Birthday Party Packages | Forfaits de fête d’anniversaire | 场地预约套餐、儿童生日套餐、含蛋糕或饮品的组合。 |
| Drinks | Boissons | 瓶装水、汽水、咖啡、热巧克力等。 |
| Add-ons | Extras | 额外浇头、蜡烛、餐具、蛋糕牌、额外酱料。 |
| Seasonal Specials | Spéciaux saisonniers | 夏季限定、节日限定、限时口味。 |

建议第一版不要设置太多分类。分类越少，顾客越容易看懂，店员也更容易维护。

## 商品配置建议

以下是示例结构，不使用真实价格。价格请在后台用真实菜单确认后再填写。

| English name | French name | Category | English description | French description | Pickup | Delivery | Prep needed | Recommended options |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Single Scoop Cup | Coupe une boule | Ice Cream / Crème glacée | One scoop of your favourite flavour in a cup. | Une boule de votre saveur préférée servie dans une coupe. | Yes | No for first version | Same day | Size, Flavor, Toppings, Allergy Note |
| Double Scoop Cone | Cornet deux boules | Ice Cream / Crème glacée | Two scoops served in a cone. | Deux boules servies dans un cornet. | Yes | No for first version | Same day | Flavor, Cone Type, Toppings, Allergy Note |
| Classic Sundae | Coupe glacée classique | Sundaes / Coupes glacées | Ice cream with sauce, whipped cream, and toppings. | Crème glacée avec sauce, crème fouettée et garnitures. | Yes | No for first version | Same day | Flavor, Sauce, Toppings, Allergy Note |
| Vanilla Milkshake | Lait frappé à la vanille | Milkshakes / Laits frappés | A classic vanilla milkshake. | Un lait frappé classique à la vanille. | Yes | No for first version | Same day | Size, Flavor, Allergy Note |
| Small Ice Cream Cake | Petit gâteau à la crème glacée | Ice Cream Cakes / Gâteaux à la crème glacée | A small cake for a family celebration. | Un petit gâteau pour une célébration en famille. | Yes | Maybe later | 24 or 48 hours | Cake Message, Candles, Utensils, Pickup Time Requirement, Allergy Note |
| Custom Birthday Cake | Gâteau d’anniversaire personnalisé | Ice Cream Cakes / Gâteaux à la crème glacée | A custom cake with message and flavour choices. | Un gâteau personnalisé avec message et choix de saveurs. | Yes | Maybe later | 48 hours | Flavor, Cake Message, Candles, Utensils, Allergy Note |
| Birthday Party Basic Package | Forfait anniversaire de base | Birthday Party Packages / Forfaits de fête d’anniversaire | Party space reservation with a simple ice cream service. | Réservation d’un espace de fête avec service de crème glacée simple. | Not a normal pickup item | No | Advance reservation | Party Notes, Guest Count, Cake Needed, Allergy Note |
| Birthday Party Cake Package | Forfait anniversaire avec gâteau | Birthday Party Packages / Forfaits de fête d’anniversaire | Party space reservation with an ice cream cake option. | Réservation d’un espace de fête avec option de gâteau à la crème glacée. | Not a normal pickup item | No | Advance reservation | Party Notes, Cake Message, Candles, Utensils, Allergy Note |

建议：

- 普通冰淇淋先支持自取。
- 冰淇淋蛋糕需要设置提前准备时间。
- 生日套餐不要当普通商品直接下单，第一版更适合通过 Reservation 预约，再由店员确认细节。

## 商品选项建议

| English | French | 适用范围 | 说明 |
| --- | --- | --- | --- |
| Size | Format | 普通冰淇淋、奶昔、圣代 | 例如 small / medium / large。 |
| Flavor | Saveur | 普通冰淇淋、蛋糕、奶昔、生日套餐 | 第一版先维护常用口味，避免太复杂。 |
| Toppings | Garnitures | 普通冰淇淋、圣代 | 例如 sprinkles、chocolate sauce、nuts。 |
| Sauce | Sauce | 圣代、奶昔 | 巧克力、焦糖、草莓等。 |
| Cone Type | Type de cornet | 甜筒冰淇淋 | 普通甜筒、华夫甜筒等。 |
| Cake Message | Message sur le gâteau | 冰淇淋蛋糕、生日套餐 | 顾客填写蛋糕上的短文字。 |
| Candles | Bougies | 冰淇淋蛋糕、生日套餐 | 是否需要蜡烛。 |
| Utensils | Ustensiles | 蛋糕、生日套餐 | 是否需要盘子、叉子、餐巾。 |
| Pickup Time Requirement | Délai de préparation | 蛋糕、生日套餐 | 对顾客说明需要提前 24 或 48 小时。 |
| Allergy Note | Note sur les allergies | 所有商品 | 收集过敏提醒，但不做医疗或法律承诺。 |

适用建议：

- 普通冰淇淋：`Size`、`Flavor`、`Toppings`、`Cone Type`、`Allergy Note`。
- 冰淇淋蛋糕：`Flavor`、`Cake Message`、`Candles`、`Utensils`、`Pickup Time Requirement`、`Allergy Note`。
- 生日套餐：优先通过 Reservation comment / notes 收集信息，商品选项只作为后续扩展参考。

## 生日派对预约配置方案

第一版基于 TastyIgniter Reservation 现有能力，不开发自定义字段，不开发自定义预约算法。

映射建议：

| TastyIgniter Reservation 项 | 生日派对场地预约含义 |
| --- | --- |
| Dining Areas | 生日派对区域，例如 `Birthday Party Room` / `Salle de fête d’anniversaire`。 |
| Tables | 一个可预约场地、房间或区域内的可预约资源。 |
| Capacity | 最大人数，包括儿童和成人。 |
| Reservation Time Interval | 可预约开始时间间隔，例如每 30 或 60 分钟一个可选时间。 |
| Stay Time | 派对时长，例如 90 分钟或 120 分钟。 |
| Minimum Advance Time | 最少提前预约时间，例如至少提前 48 小时。 |
| Maximum Advance Time | 最多可提前预约的范围，例如未来 30 或 60 天。 |
| Reservation Status | 新预约、已确认、已取消、已完成等状态。 |

第一版建议：

- Dining Area 建立一个生日派对区域。
- Table 建立一个或多个可预约空间。
- Capacity 按真实场地最大人数设置，不要为了接单而写过高。
- Stay Time 代表派对时长。
- Minimum Advance Time 用来避免当天临时派对无法准备。
- Reservation Status 先使用系统已有状态，由店员人工确认。

第一版先不要开发自定义字段。可以先用 Reservation 的 comment / notes 字段收集：

- 生日孩子年龄。
- 派对主题。
- 预计人数。
- 是否需要冰淇淋蛋糕。
- 是否有过敏提醒。
- 是否需要餐具、蜡烛或额外服务。

英法双语字段提示文案示例：

- English: “Please tell us the birthday child’s age, party theme, number of guests, and whether you need an ice cream cake.”
- French: “Veuillez nous indiquer l’âge de l’enfant, le thème de la fête, le nombre d’invités et si vous souhaitez un gâteau à la crème glacée.”

过敏提醒示例：

- English: “Please mention any allergy concerns. Our team will review your note before confirming the order or reservation.”
- French: “Veuillez indiquer toute préoccupation liée aux allergies. Notre équipe examinera votre note avant de confirmer la commande ou la réservation.”

## 自取 / 配送建议

第一版建议先只做自取。原因：

- 冰淇淋对温度敏感，配送会增加包装、时间和品质风险。
- 自取流程更简单，适合先验证线上菜单和店员处理流程。
- 配送可以作为第二阶段或第三阶段上线前再评估。

取餐规则建议：

- 普通冰淇淋：当天可自取。
- 奶昔和圣代：建议只接受较短时间内自取。
- 冰淇淋蛋糕：需要提前 24 或 48 小时。
- 定制蛋糕：建议至少提前 48 小时。
- 生日套餐：必须提前预约，并由店员确认。

顾客说明示例：

- English: “Pickup only for the first version. Delivery may be added later.”
- French: “La première version est offerte pour cueillette seulement. La livraison pourrait être ajoutée plus tard.”

- English: “Ice cream cakes require advance preparation. Please order at least 24 or 48 hours ahead.”
- French: “Les gâteaux à la crème glacée nécessitent une préparation à l’avance. Veuillez commander au moins 24 ou 48 heures à l’avance.”

- English: “Birthday parties must be reserved in advance and confirmed by our team.”
- French: “Les fêtes d’anniversaire doivent être réservées à l’avance et confirmées par notre équipe.”

## 邮件通知建议

第一版需要的邮件通知：

- 订单确认给顾客。
- 新订单提醒给店员。
- 预约确认给顾客。
- 新预约提醒给店员。
- 预约取消通知。

可以优先通过 TastyIgniter 后台或邮件模板配置：

- 邮件布局。
- 邮件模板。
- 订单确认基础文案。
- 新订单提醒基础文案。
- 预约确认基础文案。
- 新预约提醒基础文案。
- 取消预约基础文案。

后续可能需要扩展或主题改造：

- 按顾客选择的语言自动发送不同语言邮件。
- 更复杂的生日派对提醒。
- 预约前自动提醒。
- 短信通知。

要求：

- 所有顾客邮件必须有英文和法文版本。
- 如果第一版系统无法按顾客语言自动发送不同语言邮件，应记录为后续主题或扩展开发风险。
- 不要在文档或仓库中保存真实 SMTP 密码、API key 或邮件服务 token。

## 支付配置建议

第一版建议先关闭在线支付，使用到店支付 / 电话确认，或只在上线前再接 Stripe / PayPal / Square。

原因：

- 第一版重点是验证菜单、自取和预约流程。
- 真实支付涉及密钥、退款、税费、账务和测试流程，不适合在规划阶段直接接入。
- 生日派对订金流程可能需要更清楚的业务规则后再设计。

注意：

- 不要配置真实支付密钥。
- 不要写任何真实账号信息。
- 上线前需要确认加拿大 / 魁北克税费配置，但本文件不编造税率。
- 上线前应由店主或会计确认税费、发票和支付方式设置。

## 后台操作清单

非程序员可以按这个顺序操作：

1. 打开 `http://127.0.0.1:8000/admin`。
2. 使用自己的管理员账号登录后台。
3. 进入 `Settings > Languages`。
4. 创建或安装法语语言，优先尝试 `fr_CA`；如果系统不支持，再用实际可用法语 locale。
5. 创建或确认英语语言，优先尝试 `en_CA`；如果系统不支持，再用实际可用英语 locale。
6. 设置法语为默认语言。
7. 确认英语为启用状态。
8. 进入 `Settings > General`，检查是否需要开启或关闭浏览器语言检测。
9. 进入 `Settings > Locations` 或 `Locations`，配置店铺地址、电话和营业信息。
10. 配置营业时间。
11. 进入菜单分类页面，创建英法双语分类。
12. 进入菜单商品页面，创建商品，并准备英法文商品名和描述。
13. 创建商品选项，例如 size、flavor、toppings、cake message。
14. 配置自取规则。
15. 暂时关闭或不启用复杂配送。
16. 进入 Reservation 相关页面，创建生日派对区域和可预约空间。
17. 配置预约容量、预约时间间隔、派对时长和提前预约时间。
18. 进入邮件模板或邮件设置页面，准备英法双语邮件内容。
19. 检查前台首页、菜单、购物车、结账、预约页面是否显示法语默认内容。
20. 检查是否有 `Français | English` 切换入口；如果没有，记录为主题改造任务。
21. 请会说法语的人审核所有顾客可见法语文案。

## 哪些事情不要现在做

- 不修改 core。
- 不修改 vendor。
- 不开发支付功能。
- 不开发自定义预约算法。
- 不开发新扩展。
- 不接入真实支付密钥。
- 不上传真实顾客数据。
- 不做复杂配送逻辑。
- 不用机器翻译直接作为最终上线文案。
- 不做英文-only 网站。
- 不在仓库中提交 `.env`。
- 不记录管理员密码、数据库真实密码、API key、token 或真实顾客信息。

## 下一阶段建议

完成业务配置和本地化规划后，建议顺序是：

1. 用户根据本文件在后台手动配置基础业务。
2. 配置法语和英语语言。
3. 验证前台菜单和预约流程。
4. 验证语言切换。
5. 再决定是否需要主题改造。
6. 最后才考虑扩展开发。

建议下一阶段不要马上写代码。先在后台把真实业务数据填进去，并记录哪些地方后台无法满足双语或生日派对预约需求。那些真实缺口才是后续主题改造或扩展开发的依据。
