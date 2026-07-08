# 部署准备 Checklist

## 目标

本文件用于部署前检查，重点保护真实菜单数据、上传图片、生产环境配置和备份流程。

部署准备阶段的核心原则是：生产数据库、`storage`、uploads / media、`.env` 和真实业务数据不能被本地开发配置或部署脚本覆盖。

## 当前部署前状态

- 本地菜单真实数据录入已 Deferred。
- 本地只保留少量测试数据。
- 真实菜单、分类、价格和菜品图片将在准生产或生产环境后台录入。
- 当前仍需验证结账流程、预约流程、邮件、支付、备份和生产性能。

## 生产环境必须保护的数据

- 数据库。
- `storage`。
- uploads / media。
- 菜品图片。
- `.env`。
- Carté Key。
- 邮件服务配置。
- 支付密钥。
- 管理员账号。
- 真实顾客信息。
- 订单数据。
- 预约数据。

## 部署环境建议

推荐生产环境使用：

- Linux server。
- Nginx。
- PHP-FPM。
- OPcache。
- MariaDB / MySQL。
- `APP_DEBUG=false`。
- Laravel config cache / route cache / view cache。
- HTTPS。
- 持久化 `storage` / uploads。
- 定期数据库和文件备份。

说明：本地 Docker 后台响应慢主要受 Windows bind mount、Laravel / TastyIgniter 大量小文件读取和开发模式影响。正式 Linux + PHP-FPM + OPcache + Laravel 缓存环境应单独验证，不应直接用本地速度判断生产性能。

## 禁止事项

- 不得在生产环境运行会清空数据库的初始化脚本。
- 不得用本地 `.env` 覆盖生产 `.env`。
- 不得提交 `.env`。
- 不得提交 Carté Key、支付密钥、邮件密码。
- 不得把本地测试数据库覆盖到生产。
- 不得在未备份前重建容器 volume。
- 不得删除上传目录。
- 不得把真实顾客数据下载到不安全位置。

## 首次部署前 checklist

| 检查项 | 为什么重要 | 当前状态 | 验收方式 | 备注 |
| --- | --- | --- | --- | --- |
| 服务器环境 | 生产环境需要稳定、可维护 | Pending | 确认服务器系统、CPU、内存、磁盘和访问权限 | 推荐 Linux |
| PHP 版本 | TastyIgniter / Laravel 依赖 PHP 版本 | Pending | 执行 `php -v` 并确认版本满足项目要求 | 需与项目依赖匹配 |
| Composer install | 安装 PHP 依赖 | Pending | 执行生产模式 `composer install` 成功 | 不提交 `vendor/` |
| Node build / frontend assets | 前台资源需要构建 | Pending | 执行前端构建并确认 CSS / JS 可加载 | 不在生产用开发 watcher |
| `.env` 生产配置 | 生产密钥和连接配置不能用本地值 | Pending | 人工确认 `.env` 只存在于服务器安全位置 | 不进 GitHub |
| `APP_DEBUG=false` | 避免泄露错误详情并提升性能 | Pending | 确认 `.env` 中 `APP_DEBUG=false` | 上线必须关闭 debug |
| `APP_URL` | 影响链接、邮件和回调 URL | Pending | 确认 `APP_URL` 是正式域名 HTTPS URL | 不使用本地地址 |
| 数据库连接 | 网站数据依赖数据库 | Pending | 应用能连接生产数据库 | 不能覆盖已有生产数据 |
| `storage` writable | Laravel 需要写缓存、日志和上传文件 | Pending | 确认 Web 用户可写必要目录 | 权限不能过宽 |
| uploads / media writable | 菜品图片和媒体上传依赖持久目录 | Pending | 后台上传测试图片成功 | 上传目录必须持久化 |
| queue / scheduler | 部分通知或任务可能依赖后台任务 | Pending | 检查项目是否需要 queue / scheduler | 如需要，配置 supervisor / cron |
| mail 配置 | 订单、预约和管理员通知依赖邮件 | Pending | 使用测试邮件验证发送和收件 | 不提交邮件密码 |
| payment 配置 | 上线收款需要安全支付配置 | Pending | 使用沙盒或测试模式验证 | 不提交支付密钥 |
| HTTPS | 顾客资料、登录和支付必须加密 | Pending | 浏览器显示 HTTPS 正常 | 推荐自动续期证书 |
| admin login | 管理员需要可访问后台 | Pending | 后台登录页可访问，管理员可登录 | 不在文档记录密码 |
| frontend home | 顾客入口必须可访问 | Pending | 首页返回 200 且视觉正常 | 检查语言切换 |
| menu page | 顾客点单依赖菜单页 | Pending | 菜单页返回 200，Pickup 正常 | 真实菜单录入后复查 |
| reservation page | 生日派对预约入口依赖预约页 | Pending | 预约页返回 200 | 后续验证真实预约流程 |
| cart / checkout | 点单流程依赖购物车和结账 | Pending | 购物车和结账入口可访问 | 不用真实支付测试 |
| backup plan | 真实菜单和订单必须可恢复 | Pending | 数据库和上传文件备份可执行 | 上线前必须完成 |

## 真实菜单录入前 checklist

| 检查项 | 为什么重要 | 当前状态 | 验收方式 | 备注 |
| --- | --- | --- | --- | --- |
| 确认生产数据库不会被重建 | 避免真实菜单、订单和预约丢失 | Pending | 检查部署脚本和数据库初始化流程 | 禁止清空生产库 |
| 确认上传目录持久化 | 避免菜品图片丢失 | Pending | 重启服务后上传文件仍存在 | 覆盖 `storage` 前必须备份 |
| 确认菜品图片上传正常 | 真实菜单需要图片上传能力 | Pending | 后台上传一张测试图片并显示正常 | 测试图不含敏感信息 |
| 确认后台性能可接受 | 大量录菜需要可操作后台 | Pending | 在准生产后台试录少量测试商品 | 如仍慢，做生产性能诊断 |
| 确认管理员账号安全 | 防止后台被未授权访问 | Pending | 管理员账号、密码和 2FA 策略由店主确认 | 不写入文档 |
| 确认备份可以执行 | 录入前后都需要可恢复 | Pending | 成功生成数据库和上传目录备份 | 备份不进 GitHub |
| 录入前数据库备份 | 录入前建立恢复点 | Pending | 备份文件已生成并可下载到安全位置 | 不放公开目录 |
| 录入前上传目录备份 | 防止已有媒体丢失 | Pending | `storage` / uploads 已备份 | 包含 media 文件 |
| 录入后数据库备份 | 保存真实菜单录入结果 | Pending | 录入完成后立即备份数据库 | 标注日期 |
| 录入后上传目录备份 | 保存真实菜品图片 | Pending | 录入完成后立即备份上传目录 | 标注日期 |

## 备份策略

至少需要：

- 数据库备份。
- `storage` / uploads 备份。
- `.env` 安全备份，不进 GitHub。
- 备份频率建议：
  - 菜单大量录入前：立即备份。
  - 菜单大量录入后：立即备份。
  - 上线初期：每日备份。
  - 稳定后：至少每日数据库备份，定期文件备份。
- 恢复演练：
  - 至少在非生产环境验证一次数据库恢复。
  - 至少验证一次上传图片恢复。
- 备份文件不提交 GitHub。

## 准生产验证

真实上线前，建议使用 staging / pre-production 环境验证：

- 后台性能。
- 图片上传。
- 菜单录入。
- 预约流程。
- 结账流程。
- 邮件发送。
- 支付沙盒。
- 语言切换。
- 法语默认。
- 移动端显示。

## 上线前仍需确认的问题

- Q-001：Carté Key / `fr_CA` Marketplace 翻译导入仍 Open。
- Q-005：完整站点翻译仍 Open。
- 真实菜单数据 Deferred。
- 支付未配置。
- 邮件服务未配置。
- 生日派对预约具体规则未最终配置。
- 税费 / 小费 / 支付设置仍需确认。
- 生产性能仍需验证。

## 上线后注意事项

- 不要随意重建数据库。
- 不要覆盖 uploads。
- 菜单录入后立即备份。
- 定期备份。
- 监控订单和预约。
- 检查邮件是否送达。
- 检查支付是否正常。
- 记录任何后台性能问题。
