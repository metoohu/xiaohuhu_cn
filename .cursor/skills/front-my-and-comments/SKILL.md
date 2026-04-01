---
name: front-my-and-comments
description: 前台登录用户个人中心 front.my.*、评论提交 front.comments.store、中间件 front.active 与表情包配置。在用户要改「我的资料、表情包、评论、前台禁用用户」相关逻辑时使用。
---

# 前台会员区与评论

## 路由（`routes/web.php`）

- 评论：`Route::post('comments', ...)->middleware('front.active')->name('front.comments.store')`。
- 会员中心：`Route::middleware(['auth','front.active'])->prefix('my')->name('front.my.')`  
  - 资料：`profile` / `profile.update`  
  - 表情包：`stickers`、`stickers.json`、`stickers.store`、`stickers.destroy`

## 中间件 `front.active`

- 类：`App\Http\Middleware\EnsureFrontUserNotDisabled`（别名 `front.active`）。
- 被禁用的前台用户：应无法使用评论与 `my` 下功能；新增「需登录且仅活跃会员」接口时优先考虑叠加 `auth` + `front.active`。

## 控制器

- 评论：`App\Http\Controllers\Front\CommentController`（`store` 等）。
- 资料/表情包：`UserProfileController`、`UserStickerController`。

## 配置

- `config/front.php` 中含 SEO、列表分页、表情包相关项（如数量上限等，以文件为准）。

## 实现注意

- 评论内容若含 `[:sticker:id]` 等占位，需与 `CommentContentFormatter` 或项目现有解析逻辑一致，勿重复造解析规则。
- 前台认证使用默认 `web` guard（`User` 模型），与后台 `admin` guard 区分。

## 自检

- [ ] 未登录评论/访问 `my` 行为符合预期（重定向登录或 403）。
- [ ] 禁用用户访问评论与 `my` 被拒绝。
- [ ] 路由名均为 `front.*`，勿与 `admin.*` 混用。
