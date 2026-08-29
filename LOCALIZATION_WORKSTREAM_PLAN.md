# 板块二:语言与本地化工作流规划

日期:2026-08-24
状态:规划文档(2026-08-23 固定顺序的 D 步)。**只规划,不动工**;
每一项实施都按项目工作流另行立项、审批。
依据:`CLAUDE_HANDOFF.md` §11 记录的 2026-08-23 咨询结论——
**默认语言纯法语、法语必须完整、保留英文切换入口**;Q-002 升级为必做。

## 1. 2026-08-24 核实的现状(在 0% 副本 d3c-min-9a4c1bc8 上,当前主题)

1. **页头已有语言切换入口**:首页与菜单页页头渲染 `Français | English`
   链接,指向 `/language/fr_CA` 与 `/language/en_CA`。
   `FRONTEND_REMEDIATION_PLAN.md` 中"未发现可见切换组件、/en_CA 路由 404"
   的 Q-002 分析写于旧版主题,已过时。
2. **但英文切换当前无效**:访问 `/language/en_CA` 后页面仍整体法语
   (`<html lang>` 仍 `fr_CA`,标题仍 Accueil,文案法语)。与在册 Q-005
   ("`en_CA` 回落 `fr_CA`")一致。确切机制(session 未写入,还是 en_CA
   语言记录导致回落)在实施时定位——这是 W2 的第一件事。
3. **切换链接高度不达标**:两个语言链接在 2026-08-22 的无障碍读数中
   属于高度不足 24px 的六个链接之列(主题问题)。
4. **Q-004 的前提已变化**:当年"第一版 Pickup only,首页配送搜索框误导"
   的判断,随 D3C 配送启用推进而不再成立;首页配送组件现属有意为之。
   Q-004 按"已被业务决策取代"归档,不再列为待修。
5. **翻译量基线**:Q-001 记录 `fr_CA` 翻译 0/2992;门店关键路径已有
   `lang/vendor/` 局部覆盖(如 `igniter-cart/fr_CA`),但英文串仍大量
   存在(如 "We are open"、"Min. Order Amount"、结账拒绝语)。
6. **邮件基线**:2026-08-23 邮件演练读到的三封邮件为英文或混合
   ("You just received a Cueillette order"、"Your Order Update")。
   顾客邮件法语化是生产门禁项(`CLAUDE_HANDOFF.md` §10"Still open on
   mail")。
7. **货币显示基线**:金额显示为 `$13.99` 式。`config/currency.php` 的
   `formatter` 为 null,金额由 `number_format()` 按币种记录里的分隔符
   格式化;locale 驱动的 `PHPIntl` formatter 存在但未启用
   (`CLAUDE_HANDOFF.md` §10 三因辨析)。

## 2. 工作项

### W2(先行)——Q-002:让既有切换真正可用

#### 源码核实结果(2026-08-29,只读,未改动任何东西)

外部行为的诊断成立,但两处机制与推测不同,记在这里以免照错误的模型去改。

**路由不是正则约束,是写死的白名单。** `/language/{locale}` 定义在**项目自己的**
`routes/web.php:16`,不在 vendor:

    abort_unless(in_array($locale, ['fr_CA', 'en_CA'], true), 404);

所以 `/language/en` 返回 404 是因为 `en` 不在这个数组里,**不是**因为匹配不上
`xx_YY` 形式。反过来,`en_CA` 已经在白名单里,**把语言记录的 code 改成
`en_CA` 不需要动路由**。

**静默回落的确切链条。** 路由放行后:

1. `setSessionLocale('en_CA')` —— `Session::put()`,**完全不校验**,会话里就此存下
   `en_CA`;
2. `setLocale('en_CA')` —— `isValid()` 判断 `in_array($locale, supportedLocales())`,
   失败则 **`return false` 并且不抛异常、不写日志**;
3. 之后每个请求的 `getLocale()` 只在 `isValid()` 通过时才采用会话值,否则回落
   `config('localization.locale')`,也就是默认的 `fr_CA`。

**校验依据不是 `Language::code`,而是一个存储参数。**
`System/ServiceProvider.php` 分两路取值:默认语言取
`Language::getDefault()->code`,而**支持列表取 `params('supported_languages')`**。

**由此得出一条对改法有决定性影响的约束:**
`supported_languages` 只由 `Language::applySupportedLanguages()` 刷新,而它的调用
点只有两个——`LanguageObserver::saved()`(模型保存)和后台 Languages 控制器。
**因此直接 `UPDATE ti_languages SET code='en_CA'` 不会刷新这个参数**:参数里仍
是 `en`,`isValid('en_CA')` 依旧失败,切换依旧静默回落,改动看起来毫无效果。
**这一改必须走后台界面(或经模型保存),不能用裸 SQL。**

#### 首页文案:是可翻译键,不是写死的法文

`lang/fr_CA/delivery.php` 里有 `home_title_pickup` 与 `home_title_delivery`,标题
走的是语言键。所以不存在"法语大标题 + 英文导航"的半成品风险,主题文案可翻译化
**不构成本修复的前置条件**。

#### 但"en_CA 那批文件全是死文件"需要修正

`lang/en` **存在而且是当前生效的目录**(记录 code 就是 `en`),含 6 个文件:
`auth`、`birthday_booking`、`delivery`、`pagination`、`passwords`、`validation`。
`lang/en_CA` 只有 `delivery.php` 一个,且与 `lang/en/delivery.php` **键完全相同**
(各 11 个键)。

所以把 code 改成 `en_CA` 是一次**替换,不是激活**。净效果:

- **真正被激活的**是 `lang/vendor/` 下四个 `en_CA` 覆盖目录
  (igniter-cart / igniter-local / igniter-orange / igniter-reservation),它们没有
  `en` 对应目录,今天确实是死的;
- `lang/en_CA/delivery.php` 只是接替同内容的 `lang/en/delivery.php`,无得失;
- **`lang/en` 的另外五个文件不会丢**:`config/app.php` 的
  `fallback_locale` 是 `'en'`,Laravel 按键回落,缺失的键仍从 `lang/en/` 取到。

**需要在验收里补一条**:那四个 vendor 覆盖目录会随本修复第一次真正上线,其内容
从未被顾客看到过。它们不是回归风险(英文现在根本进不去),但属于未经验证即
生效的内容,应纳入 W2 的验收范围。



顺序放最前,因为切换不通,任何"英文侧"的验收都无法进行。

1. 定位 `/language/en_CA` 不生效的机制:切换路由是否写 session
   (`igniter.translation.locale`);`en_CA` 语言记录的状态(启用?
   为空?回落链);`Localization` 中 URL 段/浏览器语言/session 的
   优先级。Q-005 与 Q-002 在此汇合:预计根因同源。
2. 修复方向(以项目侧/子主题为界,不改 vendor):让 en_CA 生效并使
   `<html lang>` 跟随;确认切换后返回当前页;移动端入口可见;
   链接触达尺寸补足 24px(主题覆盖)。
3. 验收(执行标准):三类页面(首页、菜单、预约)上点击切换,页面
   语言、`<html lang>`、返回位置全部正确;移动端 390px 可见可点。

### W1——Q-001:法语完整的导入途径

咨询结论要求"法语必须完整",不再是"先覆盖关键文案即可"的旧口径,
但仍分批执行:

1. 途径决策:有 Carté Key → 后台/命令行导入 Marketplace `fr_CA`
   语言包(若无 `fr_CA` 包,评估 `fr_FR` 为底、魁北克语境人工改),
   再叠加项目 `lang/vendor/` 定制;无 Key → 全量走本地
   `lang/vendor/` 覆盖。Key 只经后台或本机 `.env`,不进聊天与仓库
   (`FRONTEND_REMEDIATION_PLAN.md` 的安全规则照旧)。
2. 批次:第一批=顾客关键路径(首页、菜单、购物篮、结账、预约、
   订单状态);第二批=账户/登录注册/错误提示;第三批=扫尾,以
   缺翻译审计(遍历渲染页面找英文残留)收口。
3. 法语文案一律由懂魁北克法语的人审核后合入;机器翻译不直接上线。
4. 英文侧:保留英文切换意味着 en_CA 也要可用——vendor 英文源即内容,
   但 en_CA 语言记录必须能正确解析(与 W2 同修)。
5. 验收工具:`LOCALIZATION_CHECKLIST.md` 逐面清单,配合逐页人工读。

### W3——邮件模板

1. 顾客邮件(订单确认、状态更新)默认法语、法语完整;店员提醒邮件
   语言由店主定(可英文或双语)。
2. 实施前先调查:模板本地化机制(每模板单语体;`MailLayout` 有
   `language_id`,按顾客语言选择发送是否被支持,`LOCALIZATION_CHECKLIST.md`
   标记为待验证);顾客 locale 从何处取(账户语言?下单时的界面语言?)。
3. 与保留英文切换的关系:若顾客以英文界面下单,邮件语言跟随何者——
   规则请店主确认(建议:跟随下单时界面语言;默认法语)。
4. 验收:重演 2026-08-23 的邮件演练路径(重定向收件箱、0% 副本、
   `MAIL_TEST_REDIRECT_TO`),读回三类邮件的语言与排版。

### W4——货币格式(合并考虑)

1. 决策点:法语界面金额是否按加式法语惯例显示 `13,99 $`(英文界面
   `$13.99`)——即按 locale 变化;或全站单一格式。涉及顾客感知与
   小票一致性,请店主定。
2. 技术路径(实施时验证):启用/接入 locale 感知的 formatter
   (`PHPIntl` 已在但未启用),或调整币种记录分隔符(后者是全站单一
   格式,做不到随语言变)。
3. 与税的交叉:`QUEBEC_TAX_PLAN.md` 的 TPS/TVQ 行名、税额同样要随
   语言正确显示;两个工作流在结账页汇合,验收合并做。
4. 金额比较逻辑与显示无关(D3C 已按数值验证),本项纯显示层。

## 3. 顺序与批次建议

W2 → W1(第一批)→ W3 → W1(第二三批)→ W4,每项独立 PR 链:
Level 1 代码 + 0% 副本验证(Level 2),不改 vendor/core,主题动作
一律走子主题/覆盖。切换器(W2)与关键路径法语(W1 第一批)构成
法语门禁的最小闭合集;邮件(W3)在生产门禁清单上独立存在。

## 4. 本轮不做

不写代码、不改主题、不导入语言包、不改语言/币种/邮件设置、不提交
Carté Key。实施每项动工前按工作流另行审批。
