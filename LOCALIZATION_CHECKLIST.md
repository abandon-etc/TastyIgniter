# LOCALIZATION_CHECKLIST

本文件是魁北克冰淇淋店网站第一版的英法双语检查清单。目标是确保前台顾客界面默认法语完整，同时英语可切换。

| Item | English content needed | French content needed | Can be configured in admin | Needs theme work | Needs extension later | Notes |
| --- | --- | --- | --- | --- | --- | --- |
| 首页文案 | Yes | Yes | Yes | Unknown | No | 法语必须完整；如果首页来自主题固定布局，可能需要主题改造。 |
| 菜单页标题和说明 | Yes | Yes | Unknown | Unknown | No | 系统文案多半可通过语言文件翻译；页面自定义说明需后台验证。 |
| 商品分类 | Yes | Yes | Unknown | No | Unknown | 需要验证分类是否支持按语言保存；否则先用双语名称。 |
| 商品名称 | Yes | Yes | Unknown | No | Unknown | 需要验证商品字段是否有语言切换；否则先用双语名称。 |
| 商品描述 | Yes | Yes | Unknown | No | Unknown | 描述比名称更适合双语并排作为临时方案。 |
| 商品选项 | Yes | Yes | Unknown | No | Unknown | 例如 `Format / Size`、`Saveur / Flavor`。 |
| 购物车文案 | Yes | Yes | Yes | No | No | 多数来自语言 key，优先用 Languages 或 `lang/vendor/`。 |
| 结账页面文案 | Yes | Yes | Yes | Unknown | No | 表单字段和错误提示通常来自语言文件；布局调整可能需要主题。 |
| 表单字段标签 | Yes | Yes | Yes | Unknown | No | 包括姓名、电话、邮箱、备注、地址等。 |
| 表单 placeholder | Yes | Yes | Yes | Unknown | No | 需要逐页检查是否全部来自语言文件。 |
| 错误提示 | Yes | Yes | Yes | No | No | Laravel validation 和 TastyIgniter 扩展语言文件需要法语覆盖。 |
| 预约页面标题 | Yes | Yes | Yes | Unknown | No | Reservation 文案来自扩展语言文件，页面引导说明可能需要主题或后台页面。 |
| 预约表单字段 | Yes | Yes | Yes | Unknown | No | 日期、时间、人数、姓名、电话、备注等。 |
| 生日派对 notes 提示 | Yes | Yes | Unknown | Yes | No | 如果默认表单备注文案不可后台改，需要主题改造。 |
| 预约成功提示 | Yes | Yes | Yes | No | No | 通过 Reservation 语言文件或后台提示配置确认。 |
| 预约失败提示 | Yes | Yes | Yes | No | No | 包括时间不可用、容量不足、必填字段错误。 |
| 订单确认邮件 | Yes | Yes | Yes | Unknown | Unknown | 邮件模板可配置；自动按顾客语言发送需后续验证。 |
| 新订单提醒邮件 | Yes | Yes | Yes | No | Unknown | 给店员的邮件可先英文或双语；顾客邮件必须双语。 |
| 预约确认邮件 | Yes | Yes | Yes | Unknown | Unknown | 需要确认模板和语言选择机制。 |
| 新预约提醒邮件 | Yes | Yes | Yes | No | Unknown | 店员邮件可先英文或双语。 |
| 预约取消通知 | Yes | Yes | Yes | Unknown | Unknown | 需要测试取消流程是否触发邮件。 |
| 订单状态 | Yes | Yes | Yes | No | No | 例如 pending、accepted、completed、canceled。 |
| 预约状态 | Yes | Yes | Yes | No | No | 例如 pending、confirmed、canceled、completed。 |
| 支付说明 | Yes | Yes | Yes | Unknown | No | 第一版建议到店支付 / 电话确认，不写真实支付密钥。 |
| 自取说明 | Yes | Yes | Yes | Unknown | No | 普通商品当天自取，蛋糕提前 24 或 48 小时。 |
| 配送说明 | Yes | Yes | Yes | Unknown | Unknown | 第一版建议暂不启用配送；如果启用，说明必须双语。 |
| 营业时间 | Yes | Yes | Yes | No | No | 后台配置时间，显示格式需检查法语 locale。 |
| 隐私政策 | Yes | Yes | Yes | No | No | 上线前需要人工审核，不在本文件给法律保证。 |
| 服务条款 | Yes | Yes | Yes | No | No | 上线前需要人工审核。 |
| 过敏原提示 | Yes | Yes | Yes | Unknown | No | 不做医疗承诺；只提示顾客备注并由店员确认。 |
| 联系页面 | Yes | Yes | Yes | Unknown | No | 地址、电话、营业时间、表单字段都要双语。 |
| SEO title | Yes | Yes | Yes | Unknown | No | 每个顾客页面都需要法语和英语标题。 |
| SEO description | Yes | Yes | Yes | Unknown | No | 避免只写英文 meta description。 |
| 图片 alt text | Yes | Yes | Unknown | Unknown | No | 如果图片由后台管理，优先后台配置；否则主题改造。 |
| 语言切换按钮 | Yes | Yes | Unknown | Yes | No | 建议显示 `Français | English`。当前未确认 Orange 主题已有组件。 |
| 移动端显示 | Yes | Yes | No | Yes | No | 检查长法语文本是否换行正常。 |
| 导航菜单 | Yes | Yes | Unknown | Unknown | No | 如果菜单项来自主题 meta，需要后续主题方式处理。 |
| 页脚文案 | Yes | Yes | Unknown | Unknown | No | 地址、社交链接、政策链接需要双语。 |
| 邮件页脚 | Yes | Yes | Yes | Unknown | No | MailLayout 有 `language_id`，但发送语言选择需验证。 |
| 购物按钮 | Yes | Yes | Yes | No | No | 例如 add to order、checkout、continue shopping。 |
| 预约按钮 | Yes | Yes | Yes | Unknown | No | 例如 reserve、confirm booking、cancel。 |
| 空状态提示 | Yes | Yes | Yes | No | No | 例如购物车为空、无可用时段。 |
| 加载和确认提示 | Yes | Yes | Yes | Unknown | No | 保存中、提交中、确认弹窗等。 |
| 税费显示文案 | Yes | Yes | Yes | No | No | 税率和税名上线前由店主或会计确认。 |
| 优惠码文案 | Yes | Yes | Yes | No | No | 如果第一版不用优惠码，可后置。 |
| 顾客账户页面 | Yes | Yes | Yes | Unknown | No | 登录、注册、地址簿、订单历史等。 |
| 生日派对说明页 | Yes | Yes | Yes | Unknown | No | 建议用后台页面先做；复杂布局再主题改造。 |
| Cookie 或隐私提示 | Yes | Yes | Unknown | Unknown | Unknown | 如果以后接入分析或广告工具，需要补充。 |

## 使用方式

1. 先在后台配置法语和英语。
2. 逐页打开前台，按本表检查。
3. 对每一项标记实际结果。
4. 如果后台能改，就先后台改。
5. 如果后台不能改，但只是显示样式或按钮位置问题，记录为主题改造。
6. 如果涉及新字段、自动发送不同语言邮件、订金或复杂预约规则，记录为后续扩展开发。
7. 上线前请人工审核所有法语文案，尤其是首页、菜单、结账、预约、邮件、隐私政策和服务条款。
