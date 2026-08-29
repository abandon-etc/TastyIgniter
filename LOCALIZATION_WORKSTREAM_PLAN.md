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

#### 阻塞项:Carte Key 绑定在 Cloud Run 上必然失败(2026-08-29 定位)

官方语言包依赖 Carte Key,而后台 "Attach Carté Key" 在本部署形态下**必定** 500。
根因不是网络、也不是主机名不匹配:`UpdateManager::applyCarte()` 第一件事就是
`setCarte()`,其第一行 `SystemHelper::replaceInEnv()` 要改写
`/var/www/html/.env`,而该文件被 `.dockerignore` 明确排除(Cloud Run 用环境变量),
于是抛 `FileNotFoundException`,**在任何网络请求发出之前**。

##### 必须分开的两件事,不要合称"解锁"

**(a) 能不能【看到】官方目录里有没有 `fr_CA`。**
远端目录走 `HubManager::listLanguages()`,而 `Languages` 控制器 `use ManagesUpdates`,
其第 43、81 行 `throw_unless(hasValidCarte(), ...)` 把门。`hasValidCarte()` 需要
carte_key **和** carte_info 同时非空,两者都由 `setCarte()` 的 `setPref()` 写入
共享设置库。所以 `.env` 修好后 `setCarte()` 能走完,**(a) 成立**。

**(b) 能不能【真的装上】。答案:不能,且与 `.env` 无关。**
语言包**不走 composer**(那是扩展和主题的路),`LanguageManager::installLanguagePack()`
直接落盘:

    $filePath = $this->langPath.'/vendor/'.$langDirectory.'/'.$locale.'/'.$meta['file'];
    File::makeDirectory(dirname($filePath), 0777, true, true);
    File::put($filePath, "<?php

return ".var_export($strings, true).";
");

`langPath` 是 `App::langPath()`,即 `/var/www/html/lang`。两重障碍,各自独立致命:
其一,应用根目录属 root,php-fpm 以 www-data 运行,建目录与写文件都会
Permission denied;其二,**即便放开权限也没有意义** —— 容器文件系统是每实例的,
运行期写出的文件在实例被替换时消失,对并发的其他实例也不可见。

**结论:装包在不可变镜像部署下不成立,修好 `.env` 不改变这一点。** 用户的判断
成立,但理由比"它要写 composer.json"更准确——语言包这条路根本不碰 composer。

##### 因此方法二的真实价值,请照此表述

修好 `.env` 换来的是**"能看到官方有没有 `fr_CA` 包"**,不是"能装"。这仍然有价值——
它正是 W1 当前的阻塞项——但**路没有通**,后来人不要据此以为可以直接装。

真要拿到包,走**构建期**:在有可写文件系统的地方(本机或构建步骤)完成绑定与
下载,把生成的 `lang/vendor/<包名>/<locale>/<文件>.php` **提交进仓库**、随镜像部署。
仓库里现有的四个 `lang/vendor/*/en_CA/` 与 `fr_CA/` 覆盖目录正是这个形状,
说明项目实际上一直在这么做。依赖项:本机 Docker 于 2026-08-28 记为暂不可用,
这条路要先解决它。

##### 方法二的完整改法(未实施)

在 `docker/cloudrun/start.sh` 里,以 root 身份:

1. 文件不存在才创建,**不要截断已存在的文件**:
   `[ -f /var/www/html/.env ] || : > /var/www/html/.env`
2. **只把这一个文件**授予 www-data,并收紧权限:
   `chown www-data:www-data /var/www/html/.env` 与 `chmod 600` 之。
3. **不要 chown 应用根目录。** 只放开这一个文件是够的:覆盖写一个已存在的文件
   只需要该**文件**的写权限,目录写权限只在创建、删除、改名时才需要。
   `replaceInEnv()` 做的正是覆盖写,所以窄授权足够,而根目录保持只读,
   `composer.json`、`auth.json`、`lang/` 仍然写不进去——这是好事,见上文 (b)。

Dockerfile.cloudrun 现只 chown 了 `storage`、`bootstrap/cache`、`public`,应用根目录
仍属 root,这与用户看到的 `file_put_contents(/var/www/html/composer.json):
Permission denied` 一致,也是上面第 3 条成立的前提。

`.env` 内容保持为空即可:`replaceInEnv()` 的 `preg_replace` 找不到匹配行,原样写回
空内容、不抛异常,执行继续走到 `setPref()`,真正的持久化发生在共享设置库。
**不要在该文件里预置 `IGNITER_CARTE_KEY=` 行**——那样 Key 会被写进一个每实例
独立且随实例消失的文件,徒增一处明文副本。

##### 方法一为什么不够

给修订设 `IGNITER_CARTE_KEY` 环境变量能填上
`config('igniter-system.carteKey')`(`config/system.php:17`),`prepareHeaders()`
确实读它。但 `hasValidCarte()` 还要 `carte_info`,而它只由 `setCarte()` 写入。
所以浏览路径仍被 `throw_unless` 挡住,**(a) 也过不了**。

##### Key 轮换:等走通之后,不是现在

堆栈把 Key 前 15 个字符记进了 Cloud Logging,每点一次按钮多一条。40 位泄 15 位
实际风险低,但轮换近乎零成本。**在绑定真正走通之后**,去 tastyigniter.com 重新
生成一把并弃用当前这把。**现在不要换**——尚未走通就换,只会多出一把同样被日志
沾过的 Key。同时:在走通之前不要再重试那个按钮,每次重试都只是多写一条。

##### 性质与排期

改 `start.sh` 属代码改动,随部署上线,可在 0% 副本上完整验证,与"法语工作流与
部署耦合"的既定安排一致,不急。

##### 取包不需要可写的完整实例 —— 本机 Docker 不再是 W1 前置(2026-08-29 核实)

`HubManager::requestRemoteData()` 就是一次带认证的 JSON POST:端点
`https://api.tastyigniter.com/v2`(`config/system.php:178`),头部
`Authorization: Bearer <carte key>`、`X-Igniter-Host: gethostname()`、
`X-Igniter-User-Ip`、`X-Igniter-Platform: php:<版本>;version:<版本>;url:<APP_URL>`,
体内附加 `client=tastyigniter` 与
`server=base64(serialize(['php'=>…,'url'=>…,'version'=>…,'host'=>…]))`。

**这些字段全部是可在实例之外复现的字符串。** 因此取包可以退化成一个独立脚本:
`languages` 取目录 → `language/apply` 取条目与 hash → `language/download` 取
`data.strings`,再按 `installLanguagePack()` 同样的 `var_export` 形状落盘。
**不需要一个可写文件系统上的 TastyIgniter 实例,本机 Docker 可用与否与此无关;
2026-08-28 那条阻塞项就 W1 而言撤销。**

**边界要说清:** 以上只从客户端源码确认了请求形状,服务端是否另有校验(许可、
站点 URL、频率)无法从这里断言。

**2026-08-29 实测,这条边界真的被撞上了。** 一次不带认证的 `languages` 调用返回
**HTTP 403 / Cloudflare Error 1010 `browser_signature_banned`** —— "站点所有者已
根据你的浏览器签名封禁访问",响应明写 "Do not retry"。**请求没有到达 API 自己的
鉴权层**,所以这既不是 401 也不是"没有 fr_CA",**语言目录问题仍未回答**。

含义有两层:

1. `api.tastyigniter.com` 前面有 WAF,按客户端签名拦截通用脚本 UA。**"取包退化成
   一个独立脚本"这个结论要加限定**:脚本除了 carte key,还需要一个 WAF 接受的
   客户端签名。源码看不到这一层,这正是上面那句"服务端可能另有校验"的兑现。
2. **未更换 UA 重试。** 用另一个 UA 绕过一条针对 UA 的封禁属于规避访问控制,
   且响应明确要求不要重试。是否以官方客户端签名(Guzzle)重试,属于与供应商
   关系的判断,**须由用户决定,不由代理自行决定**。

**成本最低的替代:直接在 tastyigniter.com 登录后查看语言目录页面。** 若网站本身
列出可用语言包,"有没有 `fr_CA`" 当场就有答案,零次 API 调用、零次密钥使用、
零次日志沾染。建议先走这条。

**由此浮出一条与最初怀疑同源、但这次真正成立的事实:**
`SystemHelper::resolveUrl()` 就是 `config('app.url')`,即 **APP_URL**;它同时出现在
`X-Igniter-Platform` 头和 `server` 载荷里。**hub 看到的站点 URL 就是该修订的
APP_URL。** 这对那次 500 无关(异常发生在任何请求之前),但对**绑定本身**是活的:
从副本绑定,发出去的是副本自己的 URL,而 tastyigniter.com 上登记的是正式站 URL。

##### 覆盖风险:导入前必须先落实,否则会静默吃掉手写魁北克法语

`installLanguagePack()` 写的路径是 `lang/vendor/<包>/<locale>/<文件>.php`,
仓库现有的手写覆盖**正在这些路径上**。逐文件清点(2026-08-29):

| 路径 | 键数 |
| --- | --- |
| `lang/vendor/igniter-cart/fr_CA/default.php` | 24 |
| `lang/vendor/igniter-local/fr_CA/default.php` | 16 |
| `lang/vendor/igniter-orange/fr_CA/default.php` | 21 |
| `lang/vendor/igniter-reservation/fr_CA/default.php` | 30 |
| 四个对应的 `en_CA/default.php` | 3 / 4 / 3 / 2 |

**处在碰撞路径上的共 103 个键、8 个文件**(fr_CA 91,en_CA 12)。其余 177 个键位于
`lang/fr_CA/`、`lang/en_CA/`、`lang/en/`,属项目自有命名空间,`installLanguagePack()`
**不会**触碰——这条区分要保留,免得把风险面夸大成全部 280 个。

**手写译文必须成为构建输入,而不是一份文档清单。** 仅有"手写键清单"不够:第一次
合并之后,产出文件里官方键与手写键混在一起,下次官方包更新时无法再分辨哪些是
自有的,合并会退化成一次性考古。所以结构改成:

1. **手写译文留在自己的源文件里**,例如 `lang/_overrides/<包>/<locale>.php`。
   这是唯一的手工编辑入口,也是"自有资产"的权威所在;
2. **提交进仓库的 `lang/vendor/...` 是产物**,由"官方包 + 覆盖源"生成。产物仍要
   提交,因为运行期不可写、加载器也不分层(一个路径一个文件),合并只能在生成期
   用 `array_replace_recursive(官方, 覆盖)` 完成;
3. **生成步骤可重复、可在 CI 里跑。** 官方包更新时重跑即可,覆盖源不受影响,
   自有译文不会被吃掉——这一点从纪律变成机制;
4. **生成脚本输出 diff 供 PR 审阅。** 产物是提交文件,所以 `git diff` 天然就是
   比对面;脚本另外打印"本次哪些键来自官方、哪些被覆盖源盖住",让审阅者不必
   靠肉眼比对两棵树。

首次迁移的一次性工作:把现有 8 个文件、103 个手写键(fr_CA 91 / en_CA 12)拆到
覆盖源里,并确认重新生成的产物与当前提交内容一致——**那次 diff 应当为空**,
这就是迁移正确性的验收。

**在这套生成机制落地之前,不导入任何包。**

##### 绑定是共享写入,按共享写入报批

`setCarte()` 的 `setPref()` 把 `carte_key` 与 `carte_info` 写进**共享设置库**,所以
无论从哪个副本绑定,**正式站也会随之进入"已绑定"状态**,其后台会出现市场相关
界面。对顾客不可见,风险低,但它不是"只在 0% 副本上做的事"。按老规矩:

- 单独审批,不并入其他批次;
- 写前记原值 —— 两个 pref **预期为空,但要实读确认,不得假设**;
- 写后读回两个 pref;
- 回退方法:清空这两个 pref(`clearCarte()` 走的也是 `replaceInEnv()`,同样需要
  `.env` 存在,所以回退能力依赖本次 `start.sh` 改动已上线);
- FP-1 在主流量修订上前后各一次。

##### 不碰生产的验证路径(建议按此排)

把 `start.sh` 的改动构建成一个 **0% 流量副本**,在那里完成绑定,即可回答 W1 当前
唯一的阻塞问题:官方到底有没有 `fr_CA` 包,还是只有 `fr_FR`。全程不碰主流量镜像,
不切流量,也不必等 C 方案那次部署。

**一个必须先决定的变量:** 该副本的 APP_URL 是它自己的 tagged URL,而登记在
tastyigniter.com 的是正式站 URL,两者不一致(见上文)。两种处理:

- 在该副本上把 `APP_URL` 设为**已登记的正式站 URL**,让 hub 看到登记站点。
  副作用是该副本生成的链接会指向正式站——对"只用来绑定"的用途可以接受,
  但要记住这台副本此后不宜再用于其他链接相关的验证;
- 或者保持原样,接受 hub 可能因 URL 不匹配而拒绝或错误归属的风险。

**独立脚本那条路根本没有这个问题**,因为 `url` 字段由脚本显式给定。这也是它现在
比"从副本绑定"更优的理由。

##### Cloudflare 1010 的正当后续:联系 TastyIgniter 支持

不换 UA 重试。正当路径是**向 TastyIgniter 支持询问该来源为何被 Cloudflare 1010
拦截** —— 很可能是云端出口 IP 或通用 UA 触发的误判,而本站持有有效 Carte。
这是首选后续,排在任何技术性绕行之前。

##### igniter-translate 评估(2026-08-29,只读,未安装)

包名 `tastyigniter/ti-ext-translate`,仓库 `github.com/tastyigniter/ti-ext-translate`。

**一、v4 兼容性:真实,不只是市场页面的声明。** Packagist 元数据:最新
**v4.0.13,发布于 2026-08-22**(一周前,活跃维护),`require` 为
`{"tastyigniter/core": "^v4.0"}`,类型 `tastyigniter-package`。本站 core 为
**v4.3.1**,满足该约束。共 15 个版本。

**二、迁移与共享库影响:只增一张表,不动任何既有表。**
全仓库只有一个迁移 `2020_06_04_000300_create_attributes_table.php`,内容是
`Schema::create('igniter_translate_attributes')`(带前缀即
`ti_igniter_translate_attributes`),字段 `id` / `locale`(索引) /
`translatable_id`(索引) / `translatable_type`(索引) / `attribute`(mediumText)。
`down()` 只 `dropIfExists` 这张表。

**它不 ALTER 任何既有表,也不改任何既有行。** 因此审批规格是"在共享库新增一张
独立表",而不是"改动既有 schema":仍需审批,但爆炸半径是一张新表,且可干净回退。

**三、存储模型与继承 —— 用户现在录的法语不会白录。**
非默认语言的译文以"每(locale, 模型)一条 JSON"存进上述表。**默认语言走模型自己的
列**,两处源码各自坐实:

- 写:`performSetTranslatableAttribute()` 中
  `if (activeLocale === defaultLocale && !is_array($value)) return $value;`
  —— 默认语言的值原样落回普通列,不进译文表;
- 读:`getAttributeTranslatedValue()` 中
  `elseif ($locale == $translatableDefaultLocale || $translatableUseFallback)`
  取 `$this->model->getAttributes()`,且 `$translatableUseFallback` 默认为 `true`。

`translatableDefaultLocale` 取自 `$localization->getDefaultLocale()`,即
`Language::getDefault()->code`,**当前是 fr_CA**。

**结论:现在录进 `ti_menus` 等列里的法语,会成为默认语言内容,并且同时是任何
缺译语言的回退值。装扩展不会让它作废。**

**一条随之而来的不变量,请当作约束记住:** 以上成立的前提是**法语始终是默认
语言记录**。若日后把默认语言改成英语,所有既有列的含义会整体翻转。"法语是默认
语言"从此是承重设定,不是偏好。

覆盖的模型(`EventRegistry::bootTranslatableModels()`):`Menu`
(`menu_name`, `menu_description`)、`Category`、`Ingredient`、`MenuOption`、
`MenuOptionValue`、`Mealtime`、`Location`、`MailTemplate`、`Page`、
`StaticPageMenu`、`StaticPageMenuItem`。菜品名与描述确在其中。

**四、locale 选择器:源码与市场页面的说法不一致。**
`Extension.php` 只注册了**后台表单控件**(`TRLText`/`TRLTextarea`/`TRLRichEditor`/
`TRLMarkdownEditor`/`TRLRepeater`)与模型引导。**没有任何路由、没有前台组件、
没有前台 locale 选择器。** 市场页面所称的"选择器"实为后台表单里的逐字段语言页签。

由此两条:

- **不与现有 `Français|English` 切换器冲突** —— 它不注册竞争路由或组件;
- **不解决 W2。** `en` 与 `en_CA` 的代码不匹配在本项目自己的 `routes/web.php`
  白名单与语言记录里,该扩展不碰。

**但它依赖 W2 被修好:** `initTranslatableLocale()` 用的是
`$localization->getLocale()` —— 正是那个校验失败时静默回落的函数。**W2 未修之前,
顾客切到英文会静默回落 fr_CA,该扩展也就跟着供给 fr_CA 内容。** 所以 W2 是这个
扩展对英文顾客产生价值的前置条件,不是被它取代。

##### 内容翻译与界面字符串是两件事,不要合并

该扩展解决的是**内容**(菜品、分类、页面)。**W1 的 2992 条界面字符串不在其内。**

界面字符串从哪来仍**未知**:市场没有语言分类,而代码里确有
`HubManager::listLanguages()`。那个端点背后是否还有活着的服务,**从这里无法判定**
—— 唯一一次探测在到达 API 之前就被 Cloudflare 1010 挡住。在支持答复或一次成功
调用之前,这一项保持"未知",不要按任一假设推进。

##### 给店主的行动建议(不是阻塞项)

**继续录菜单,只写法语。** 理由已由上文第三点坐实:法语会成为默认语言内容并兼作
回退值,任何情况下都是必需的那一份,先录不会白费。装扩展要在共享库跑迁移、需要
审批,且扩展尚未验证——但这些都**不构成停下来的理由**,店主不必等待。

##### W1 状态(保持)

官方有没有 `fr_CA` 包,在绑定走通前**无法确认**。不要按任一假设先行开工:若最终
确认只有 `fr_FR`,魁北克用词仍须人工过一遍,但这个判断现在取不到。

#### 改动约束(改法未定,约束先定)

**一、语言记录在共享数据库里,写入立刻作用于正式站。** 无法先在 0% 副本上验证
——这是"所有副本共用一个库"那条结构性限制的又一次命中,与不能给单个副本改营业
时间是同一个成因。因此按共享设置写入的规矩办:

- 单独审批,不并入其他批次;
- 写前记原值(`code` 当前为 `en`),写后读回;
- 回退方法明确:把 `code` 改回 `en`,**同样必须走后台**,因为回退也要靠模型保存
  去刷新 `supported_languages`;
- FP-1 在主流量修订上前后各取一次。

需要与上面的核实结果合起来读:**写入通道被限定为后台界面**,所以这次的"写前记
原值/写后读回"两端都应当在数据库层面取证(读 `ti_languages.code` 与
`supported_languages` 两个值),而写入动作本身在后台完成。只读回后台屏幕不够
——`supported_languages` 是否真的跟着刷新,正是这次成败的关键。

**二、验收按本文件 W2 的标准执行:** 三类页面(首页、菜单、预约)点切换后语言、
`<html lang>`、返回位置三项全部正确;移动端 390px 下切换器可见可点;链接触达
高度补足 24px。再加上文所述的第四条:四个 vendor `en_CA` 覆盖目录的内容首次
生效,需实际过目。



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
