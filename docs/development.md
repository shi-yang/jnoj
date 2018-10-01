二次开发说明
-----------

> 要进行二次开发，最好有一定的 Web 开发基础，

### 目录结构

      assets/             资源文件的定义
      commands/           控制台命令
      components/         Web 应用程序组件
      config/             Web 应用程序配置信息
      controllers/        控制器(Controller)文件
      docs/               文档目录
      judge/              判题机所在目录
      judge/data          判题数据目录
      mail/               发邮件时的视图模板
      messages/           多语言翻译
      migrations/         数据库迁移时的各种代码
      models/             模型(Model)文件
      modules/admin       Web 后台应用
      modules/polygon     多边形出题系统
      runtime/            Web 程序运行时生成的缓存
      tests/              各种测试
      vendor/             第三方依赖
      views/              视图(View)文件
      web/                Web 入口目录
      widgets/            各种插件
      socket.php          用于启动 Socket，提供消息通知功能

### WEB 端

WEB 端是采用　PHP　语言 yii2 框架来写的，MVC 模式。
 - M 指业务模型，在目录结构中的 models 文件夹下。
 - V 指用户界面，在目录结构中的 views 文件夹下。
 - C 指控制器，在目录结构中的 controllers 文件夹下。

如果需要修改用户界面，只需在 views 文件夹下找到相关文件来修改。

比如，当你想修改此链接的页面：`http://127.0.0.1/jnoj/web/wiki/contest`，那么视图文件可以在 `jnoj/views/wiki/contest.php`，
控制器在 `jnoj/controllers/WikiController.php` 的 `actionContest()`。

后台应用程序及 polygon 应用程序在 modules 目录下，该目录下也是一个 MVC结构。
