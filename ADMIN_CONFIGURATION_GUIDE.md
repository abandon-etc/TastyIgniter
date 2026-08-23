# ADMIN_CONFIGURATION_GUIDE

本文件是第三阶段的后台配置执行指南，面向不会编程的店主使用。目标是帮助你在 TastyIgniter 后台手动配置魁北克冰淇淋店第一版网站：线上菜单、自取订单、生日派对场地预约，以及法语默认、英语可切换的前台体验。

## 使用前说明

- 这份文档指导你在 TastyIgniter 后台手动配置。
- 不需要写代码。
- 不需要修改 `core`。
- 不需要修改 `vendor/`。
- 不要把管理员密码、数据库密码、支付密钥、token 或真实顾客数据写进本文档、GitHub、截图文件名或聊天记录。
- 如果后台界面和本文档描述不一致，请不要猜；先记录为“待确认问题”。
- 如果某个按钮名称本文档写为“需要后台实际确认”，说明当前阶段没有登录后台验证，不应该编造按钮名称。
- 第一版优先通过后台配置完成。只有后台确实不能满足时，才进入主题改造或扩展开发。

## 登录后台

后台登录地址：

```text
http://127.0.0.1:8000/admin/login
```

操作方式：

1. 打开上面的地址。
2. 你自己输入管理员邮箱和密码。
3. Codex 不应读取、记录、截图或保存你的管理员邮箱和密码。
4. 登录后先不要急着改内容，建议按本文档顺序逐项配置。

## 配置顺序总览

推荐按这个顺序配置：

1. 语言设置。
2. 店铺基础信息。
3. 营业时间。
4. 自取设置。
5. 菜单分类。
6. 商品选项。
7. 商品。
8. 生日派对预约区域和容量。
9. 邮件通知。
10. 支付方式。
11. 前台检查。
12. 英法双语检查。

原因：语言和店铺基础信息会影响后面的页面、邮件和前台显示；菜单和预约配置完成后，再检查前台流程会更清楚。

## 英法双语语言配置

目标：

- 法语为默认语言。
- 英语为可切换语言。
- 前台顾客界面必须支持英法双语。
- 后台管理员界面第一版可以先保留英文。

后台操作步骤：

1. 登录后台。
2. 进入 `Settings`。
3. 找到 `Languages`。根据代码检查，内部地址应为 `/admin/languages`。
4. 查看当前已有语言。第一阶段安装后通常至少已有 English。
5. 添加或启用法语：
   - 优先检查是否可以使用 `fr_CA`。
   - 如果后台或语言包不支持 `fr_CA`，再检查是否支持 `fr_FR`、`fr` 或其他法语 locale。
   - 具体按钮名称需要后台实际确认，可能是 `New`、`Create`、`Install`、`Import translations` 或类似按钮。
6. 添加或启用英语：
   - 优先检查是否可以使用 `en_CA`。
   - 如果不支持 `en_CA`，先使用系统已有 English。
7. 设置法语为默认语言：
   - 在 Languages 列表中寻找默认语言设置按钮或星标。
   - 如果后台按钮名称不同，记录实际按钮名称。
8. 确认法语和英语都是启用状态。
9. 进入 `Settings > General`，检查是否有 `Detect Browser Language` 或类似选项。
10. 第一版不要只依赖浏览器语言检测；仍然需要前台可见的语言切换方式。

如何检查 `fr_CA` / `en_CA` 是否支持：

- 在添加语言时尝试输入或选择 `fr_CA`。
- 如果系统提示不可用，尝试 `fr_FR` 或后台实际提供的法语选项。
- TastyIgniter 的语言安装命令示例使用完整 locale code，例如 `fr_FR`，所以第一选择是完整 locale。
- 如果第一版只能使用 `fr_FR` 或 `fr`，可以先接受，但顾客可见法语文案必须人工改成加拿大法语 / 魁北克语境。

如何检查前台是否能切换语言：

1. 打开前台首页 `http://127.0.0.1:8000`。
2. 查找是否有语言切换入口，例如 `Français | English`。
3. 分别打开菜单页、预约页、结账页，查看语言是否跟随切换。
4. 如果 Orange 主题没有语言切换按钮，记录为后续主题改造任务。

记录方式：

| 问题位置 | 现象 | 建议处理 |
| --- | --- | --- |
| 前台导航 | 没有 `Français | English` 切换按钮 | 后续主题改造 |
| 菜单商品 | 无法分别填写法语和英语名称 | 先用双语名称，后续评估主题或扩展 |
| 邮件模板 | 无法按顾客语言自动发送 | 记录为后续扩展风险 |

## 店铺基础信息配置

你需要准备这些信息：

- 店铺名称。
- 地址。
- 城市。
- 省份。
- 邮编。
- 电话。
- 联系邮箱。
- 时区。
- 默认货币。
- 默认语言。

建议配置：

- 时区：使用店铺实际所在地对应的加拿大东部时区；后台具体选项名称需要实际确认。
- 默认货币：加拿大元，后台可能显示为 `CAD`。
- 默认语言：法语。

哪些可以使用真实值：

- 店铺名称。
- 店铺公开地址。
- 店铺公开电话。
- 店铺公开邮箱。
- 营业时间。
- 公开品牌图片。

哪些不要写进 GitHub：

- 管理员密码。
- 数据库密码。
- 邮件服务密码。
- 支付密钥。
- 私人手机号。
- 未公开的个人邮箱。
- 真实顾客资料。
- 真实支付资料。

## 营业时间配置

目标：让顾客看到准确营业时间，并让自取和预约规则符合店铺实际运营。

需要你先决定：

- 每周哪些天营业。
- 每天开门时间。
- 每天关门时间。
- 节假日是否营业。
- 生日派对预约是否与普通营业时间一致。
- 是否有只接受预约、不接受普通自取的时段。

后台操作步骤：

1. 登录后台。
2. 进入店铺位置或营业时间相关页面。根据第一阶段记录，可能在 `Locations` 或 `/admin/locations`。
3. 找到工作时间、营业时间、schedule 或 hours 相关区域。
4. 按星期逐项填写开门和关门时间。
5. 如果有节假日设置，先记录入口；上线前再完整配置。
6. 保存后打开前台，检查营业时间是否显示正确。
7. 用法语和英语都检查一次营业时间显示。

注意：

- 如果生日派对只在特定时段开放，不要仅依赖普通营业时间；还需要在 Reservation 设置中单独检查可预约时段。
- 如果后台没有节假日规则入口，记录为待确认问题。

## 自取 / 配送配置

第一版建议优先只启用自取，配送后置。

原因：

- 冰淇淋对温度敏感。
- 配送会增加包装、时间、融化和品质风险。
- 第一版先验证菜单、订单和店员处理流程更稳妥。

后台配置检查项：

- 是否启用 Pickup。
- 是否关闭或暂不启用 Delivery。
- 自取提前准备时间。
- 普通冰淇淋是否允许当天自取。
- 冰淇淋蛋糕是否需要提前 24 或 48 小时。
- 生日派对是否必须提前预约。

后台操作步骤：

1. 登录后台。
2. 进入店铺位置、自取/配送或订单类型设置页面。具体入口需要后台实际确认。
3. 找到 Pickup / Collection 相关设置。
4. 启用 Pickup / Collection。
5. 找到 Delivery 相关设置。
6. 第一版关闭 Delivery，或保持未启用状态。
7. 设置普通订单准备时间。
8. 对冰淇淋蛋糕和生日派对，用商品说明或预约说明明确提前准备要求。
9. 保存后打开前台，确认顾客不会误以为第一版支持配送。

顾客说明建议：

- French: `La première version est offerte pour cueillette seulement. La livraison pourrait être ajoutée plus tard.`
- English: `Pickup only for the first version. Delivery may be added later.`

### 配送运费规则的排序（重要）

启用配送后，后台“配送区域”里的运费规则是一个可以拖动排序的列表。请记住
三件事：

1. **行序就是生效顺序。** 系统从第一行开始找，找到第一条符合这笔订单金额的
   规则就只用那一条，后面的规则不再看。
2. **“满 80 元免运费”必须保持在第一行。** 如果它被拖到“所有订单收 5 元”
   下面，满 80 元的订单也会被收 5 元运费。系统不会报错，也不会提示，只是
   收费悄悄变了，通常要到顾客投诉才会发现。
3. **改动之后请自己核对一次。** 打开前台菜单页，点“More info”，在“Delivery
   Areas”里确认第一行显示的是“Free above $80.00”。

如果不确定，请不要拖动这些行；先记录为“待确认问题”。

补充一条，和“最低消费”有关：网站显示并执行的配送最低消费，来自“配送设置”
里的最低消费金额（目前是 20 元），而不是运费规则里的金额。将来如果要给某个
较远的配送区域单独设更高的起送门槛（比如满 40 元才配送），请在该区域的规则
里选“不提供配送”（delivery is not available）并填 40，而不要用“低于 40 元
收 X 元运费”这种写法——后者只会被当作运费规则，不会被当作起送门槛。

## 菜单分类配置

目标：建立清晰、双语、适合冰淇淋店的菜单分类。

后台创建分类步骤：

1. 登录后台。
2. 进入菜单分类页面。根据第一阶段记录，入口可能是 `/admin/categories`。
3. 点击新建分类。具体按钮名称需要后台实际确认。
4. 填写分类名称。
5. 填写分类描述，如果后台提供该字段。
6. 设置排序或优先级，如果后台提供该字段。
7. 设置状态为启用。
8. 保存。
9. 回到前台菜单页检查显示。

英法双语分类建议：

| French / English | 适合放什么 |
| --- | --- |
| `Crème glacée / Ice Cream` | 单球、双球、杯装、甜筒、软冰淇淋。 |
| `Coupes glacées / Sundaes` | 圣代、酱料组合、浇头组合。 |
| `Laits frappés / Milkshakes` | 奶昔和冰沙类饮品。 |
| `Gâteaux à la crème glacée / Ice Cream Cakes` | 标准蛋糕、定制生日蛋糕、节日蛋糕。 |
| `Forfaits de fête d’anniversaire / Birthday Party Packages` | 生日派对套餐说明；第一版更适合作为预约说明，不建议直接普通下单。 |
| `Boissons / Drinks` | 瓶装水、汽水、咖啡、热巧克力。 |
| `Extras / Add-ons` | 蜡烛、餐具、额外浇头、额外酱料。 |
| `Spéciaux saisonniers / Seasonal Specials` | 限时口味、节日款、夏季限定。 |

双语填写规则：

- 如果后台支持分语言字段：法语字段填法语，英语字段填英语。
- 如果后台不支持分语言字段：第一版可以临时使用双语名称，例如 `Crème glacée / Ice Cream`。
- 不要只写英文分类。
- 上线前请人工审核法语表达。

## 商品选项配置

目标：把顾客下单时需要选择的信息标准化，减少店员电话确认次数。

后台创建商品选项步骤：

1. 登录后台。
2. 进入商品选项页面。根据第一阶段记录，入口可能是 `/admin/menu_options`。
3. 点击新建选项。具体按钮名称需要后台实际确认。
4. 填写选项名称。
5. 选择显示类型，例如单选、多选、下拉或数量。具体类型以后台实际提供为准。
6. 添加选项值。
7. 设置是否必选。
8. 保存。
9. 在商品编辑页面把选项关联到对应商品。

建议商品选项：

| French / English | 适合商品 | 说明 |
| --- | --- | --- |
| `Format / Size` | 普通冰淇淋、奶昔、圣代 | 小、中、大或杯装大小。 |
| `Saveur / Flavor` | 普通冰淇淋、奶昔、蛋糕 | 口味选择。 |
| `Garnitures / Toppings` | 普通冰淇淋、圣代 | 彩糖、巧克力酱、坚果等。 |
| `Sauce / Sauce` | 圣代、奶昔 | 巧克力、焦糖、草莓等。 |
| `Type de cornet / Cone Type` | 甜筒冰淇淋 | 普通甜筒、华夫甜筒等。 |
| `Message sur le gâteau / Cake Message` | 冰淇淋蛋糕 | 蛋糕文字。 |
| `Bougies / Candles` | 蛋糕、生日套餐 | 是否需要蜡烛。 |
| `Ustensiles / Utensils` | 蛋糕、生日套餐 | 是否需要盘子、叉子、餐巾。 |
| `Note sur les allergies / Allergy Note` | 所有商品 | 收集过敏提醒；不做医疗或法律承诺。 |

适用建议：

- 普通冰淇淋：`Format / Size`、`Saveur / Flavor`、`Garnitures / Toppings`、`Type de cornet / Cone Type`、`Note sur les allergies / Allergy Note`。
- 冰淇淋蛋糕：`Saveur / Flavor`、`Message sur le gâteau / Cake Message`、`Bougies / Candles`、`Ustensiles / Utensils`、`Note sur les allergies / Allergy Note`。
- 生日套餐：第一版优先通过 Reservation 的 comment / notes 收集信息，不建议做成复杂商品选项。

## 商品配置

目标：创建第一版可展示、可自取的基础商品。

后台创建商品步骤：

1. 登录后台。
2. 进入商品页面。根据第一阶段记录，入口可能是 `/admin/menus`。
3. 点击新建商品。具体按钮名称需要后台实际确认。
4. 选择商品分类。
5. 填写商品名称。
6. 填写商品描述。
7. 填写价格。本文档只用 `$X.XX` 占位，后台请用真实价格。
8. 选择是否启用。
9. 关联商品选项。
10. 如果后台支持库存或可售时间，按店铺实际情况配置。
11. 保存。
12. 打开前台菜单页检查显示。

示例商品：

| French / English | 分类 | 价格占位 | 自取 | 配送 | 备注 |
| --- | --- | --- | --- | --- | --- |
| `Coupe une boule / Single Scoop Cup` | `Crème glacée / Ice Cream` | `$X.XX` | Yes | No | 普通冰淇淋，当天自取。 |
| `Cornet deux boules / Double Scoop Cone` | `Crème glacée / Ice Cream` | `$X.XX` | Yes | No | 选择口味和甜筒类型。 |
| `Coupe glacée classique / Classic Sundae` | `Coupes glacées / Sundaes` | `$X.XX` | Yes | No | 可选酱料和浇头。 |
| `Lait frappé à la vanille / Vanilla Milkshake` | `Laits frappés / Milkshakes` | `$X.XX` | Yes | No | 第一版建议自取。 |
| `Petit gâteau à la crème glacée / Small Ice Cream Cake` | `Gâteaux à la crème glacée / Ice Cream Cakes` | `$X.XX` | Yes | Maybe later | 需要提前 24 或 48 小时。 |
| `Gâteau d’anniversaire personnalisé / Custom Birthday Cake` | `Gâteaux à la crème glacée / Ice Cream Cakes` | `$X.XX` | Yes | Maybe later | 需要提前准备和人工确认。 |
| `Forfait anniversaire de base / Birthday Party Basic Package` | `Forfaits de fête d’anniversaire / Birthday Party Packages` | `$X.XX` | No | No | 第一版建议通过 Reservation 预约。 |
| `Forfait anniversaire avec gâteau / Birthday Party Cake Package` | `Forfaits de fête d’anniversaire / Birthday Party Packages` | `$X.XX` | No | No | 第一版建议通过 Reservation 预约后由店员确认。 |

重要建议：

- 生日套餐第一版更适合通过 Reservation 预约，不建议作为普通商品直接下单。
- 商品价格、库存、口味是否可售，需要你根据真实店铺情况决定。
- 不要把真实顾客订单或真实支付资料写进文档。

## 生日派对预约配置

目标：用 TastyIgniter Reservation 现有能力配置生日派对场地预约，不开发自定义字段，不开发自定义预约算法。

映射关系：

| TastyIgniter 项 | 生日派对含义 |
| --- | --- |
| Dining Areas | 生日派对区域。 |
| Tables | 可预约场地或房间。 |
| Capacity | 最大人数。 |
| Reservation Time Interval | 可选择的预约时间间隔。 |
| Stay Time | 派对时长。 |
| Minimum Advance Time | 至少提前多久预约。 |
| Maximum Advance Time | 最多可提前多久预约。 |
| Reservation Status | 待确认、已确认、取消、完成等状态。 |

后台操作步骤：

1. 登录后台。
2. 进入 Reservation 相关页面。根据第一阶段记录，预约入口可能是 `/admin/reservations`。
3. 查找 Dining Areas 或 Dining Sections / Tables 相关页面。具体入口需要后台实际确认。
4. 创建生日派对区域，例如 `Salle de fête d’anniversaire / Birthday Party Room`。
5. 创建一个或多个可预约空间，例如 `Salle 1 / Room 1`。
6. 设置每个空间的 Capacity。
7. 进入 Reservation 设置页面，配置 Reservation Time Interval。
8. 配置 Stay Time，作为派对时长。
9. 配置 Minimum Advance Time，例如生日派对至少提前预约。具体小时数由你决定。
10. 配置 Maximum Advance Time，例如允许预约未来多少天。具体天数由你决定。
11. 检查 Reservation Status，确认是否有待确认、已确认、取消等状态。
12. 保存后打开前台预约页检查流程。

第一版先用 comment / notes 字段收集：

- 孩子年龄。
- 生日主题。
- 是否需要冰淇淋蛋糕。
- 过敏原说明。
- 特殊要求。

英法双语备注提示文案：

- French: `Veuillez nous indiquer l’âge de l’enfant, le thème de la fête, le nombre d’invités, si vous souhaitez un gâteau à la crème glacée, ainsi que toute note liée aux allergies ou aux demandes spéciales.`
- English: `Please tell us the birthday child’s age, party theme, number of guests, whether you need an ice cream cake, and any allergy notes or special requests.`

注意：

- 不要把真实儿童姓名、真实生日日期或真实顾客资料写进 GitHub 文档。
- 预约冲突检测逻辑不在本阶段修改。
- 如果后台 notes 字段文案不能改，记录为后续主题改造任务。

## 邮件通知配置

第一版需要检查或配置的邮件：

- 顾客订单确认。
- 店员新订单提醒。
- 顾客预约确认。
- 店员新预约提醒。
- 预约取消通知。

后台操作步骤：

1. 登录后台。
2. 进入邮件设置或邮件模板页面。根据第一阶段记录，可能是 `/admin/mail_templates`、`/admin/mail_layouts` 或 `Settings > Mail`。
3. 开发阶段先使用 log mail，不配置真实邮件服务密码。
4. 查找订单确认邮件模板。
5. 查找新订单提醒邮件模板。
6. 查找预约确认邮件模板。
7. 查找新预约提醒邮件模板。
8. 查找预约取消通知模板。
9. 为顾客邮件准备法语和英语版本。
10. 保存后，后续在测试流程中确认邮件内容。

说明：

- 开发阶段可以先用 log mail。
- 上线前再配置真实邮件服务。
- 顾客邮件必须准备法语和英语版本。
- 如果系统不能按顾客语言自动发送不同邮件，记录为后续扩展风险。
- 不要在文档、截图、GitHub 或聊天里写真实 SMTP 密码、API key 或 token。

## 支付方式配置

第一版开发阶段建议先不要接入真实支付。

上线前再决定：

- 到店支付。
- 电话确认。
- Stripe。
- PayPal。
- Square。

后台操作建议：

1. 登录后台。
2. 进入支付设置页面。根据第一阶段记录，入口可能是 `/admin/payments`。
3. 开发阶段不要填写真实支付密钥。
4. 如果需要让流程可测试，优先使用非真实支付方式，例如到店支付或电话确认。具体后台名称需要实际确认。
5. 上线前再决定是否启用 Stripe、PayPal 或 Square。
6. 上线前请店主或会计确认加拿大 / 魁北克税费配置。

提醒：

- 不要在文档中写真实支付密钥。
- 不要在 GitHub 写真实支付账号信息。
- 不要编造税率。
- 真实支付上线前必须单独测试。

## 前台检查清单

配置后打开这些页面检查：

- 首页。
- 菜单页。
- 商品详情。
- 购物车。
- 结账页。
- 预约页。
- 联系页。
- 语言切换。
- 移动端显示。

建议检查方式：

1. 先用法语默认语言打开每个页面。
2. 再切换到英语检查每个页面。
3. 检查按钮、表单、错误提示、空状态提示是否仍是英文-only。
4. 检查长法语文案在手机宽度下是否换行正常。
5. 截图保存到本机，不要把含顾客隐私或后台账号信息的截图提交到 GitHub。

## 英法双语检查清单

请结合 `LOCALIZATION_CHECKLIST.md` 使用。

实际操作顺序：

1. 打开 `LOCALIZATION_CHECKLIST.md`。
2. 从首页开始检查。
3. 检查菜单分类、商品名称、商品描述和商品选项。
4. 检查购物车和结账流程。
5. 检查预约页面和预约提示。
6. 检查邮件模板。
7. 检查订单状态和预约状态。
8. 检查自取说明、支付说明、营业时间、隐私政策、服务条款和过敏原提示。
9. 检查 SEO title、SEO description 和图片 `alt` text。
10. 检查 `Français | English` 语言切换入口。
11. 检查移动端显示。
12. 对每一项记录：后台能否配置、是否需要主题改造、是否以后需要扩展。

## 记录问题的方式

如果你在后台发现某个字段不能双语、某个按钮不能翻译、某个页面没有语言切换，请按下面表格记录。

| 问题位置 | 截图文件名 | 法语是否缺失 | 英语是否缺失 | 是否影响上线 | 建议处理方式 |
| --- | --- | --- | --- | --- | --- |
| 示例：首页导航 | `homepage-language-switch-missing.png` | Yes | No | Yes | 主题改造 |
| 示例：商品名称 | `menu-item-language-field-missing.png` | Unknown | Unknown | Maybe | 后台配置 / 暂时接受 |
| 示例：预约备注提示 | `reservation-notes-copy.png` | Yes | No | Yes | 主题改造 |

建议处理方式只能选：

- 后台配置。
- 主题改造。
- 扩展开发。
- 暂时接受。

判断标准：

- 后台能改文字或字段：优先后台配置。
- 后台不能改，但只是页面显示、按钮、文案或语言切换入口：优先主题改造。
- 需要新字段、复杂预约规则、订金流程、按顾客语言自动发不同邮件：以后再考虑扩展开发。
- 不影响第一版上线，且有清楚说明：可以暂时接受。

## 当前不要做的事情

- 不改 core。
- 不改 vendor。
- 不开发自定义扩展。
- 不接入真实支付密钥。
- 不上传真实顾客资料。
- 不做复杂配送。
- 不把机器翻译直接作为最终法语文案。
- 不做英文-only 网站。
- 不修改订单逻辑。
- 不修改支付逻辑。
- 不修改预约冲突检测逻辑。
- 不修改登录认证逻辑。
- 不修改安全逻辑。

## 下一阶段建议

完成本后台配置指南后，下一阶段建议：

1. 你按照本指南在后台配置基础内容。
2. 你记录遇到的问题。
3. Codex 根据问题判断是否需要主题改造。
4. 再创建主题改造 PR。
5. 最后才考虑扩展开发。

下一阶段仍然建议先少写代码。只有当后台配置无法满足法语默认、英语切换、生日派对预约说明或前台展示时，再进入主题改造。
