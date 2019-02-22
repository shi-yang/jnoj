<p align="center">
    <a href="https://www.jnoj.org" target="_blank">
        <img src="docs/favicon.ico" height="100px">
    </a>
    <h1 align="center">Jiangnan Online Judge</h1>
    <br>
</p>

江南在线评测系统（Jiangnan Online Judge），是一个在线的判题系统。
用户可以在线提交程序多种程序（如C、C++、Java）源代码，系统对源代码进行编译和执行，并通过预先设计的测试数据来检验程序源代码的正确性。

在线体验
---------
[JNOJ Demo](https://demo.jnoj.org)

功能特性
----------

- [x] 滚榜 - 在比赛结束后，提供滚动榜单的功能
- [x] VIP用户机制 - 可以将题目设为仅 VIP 用户可见
- [x] 线上线下赛场景 - 为线下举办的比赛增加限制：如限制参加比赛的账户，参加比赛的账户不能自己修改个人信息
- [x] 线下赛打印功能 - 为线下赛提供打印代码的功能
- [x] 题目打印功能 - 创建比赛后，可以将题目信息一键导出为 PDF，以供打印。
- [x] 公告弹窗提示 - 在比赛过程中，发布公告时可以给所有在线用户发布弹窗提示有新公告
- [x] 作业 - 可以用于教学练习
- [x] 记录出错数据 - 可以查看提交方案的出错数据
- [x] 新闻门户首页 - 首页显示新闻，各种公告等
- [x] 支持多语言 - 现支持 C、C++、Java、Python3
- [x] 支持(Katex)数学公式
- [x] 题目描述多组样例 - 在一道题目的样例描述中，可以提供多组样例
- [x] 对比赛发布题解
- [x] 问题标签 - 给问题添加标签，方便查询
- [x] 基于 MVC 框架开发 - 开发便于上手
- [x] 多边形出题系统 - 一个功能完善的出题系统。出题目，生成数据，验题等。
- [x] Web 在线管理题目数据 - 在线对题目测试数据进行增删改查
- [x] 排行榜 - 对用户解题数量等进行排行
- [x] 积分榜 - 对参加比赛的用户进行积分结算，并进行排名

开发计划
--------

以下列举了一些今后版本可能会添加的功能，如果你有新的功能需求，可以提 issues。

- [ ] Polygon 权限管理 - 题目创建人、验题人权限
- [ ] 删除某道题的提交记录 - 可用于在比赛后发现某道题不合理，删除该题的提交记录
- [ ] 代码查重

帮助文档
--------

1. [安装教程](docs/install.md)
2. [更新教程](docs/update.md)
3. [https://github.com/shi-yang/jnoj/wiki](https://github.com/shi-yang/jnoj/wiki)


开发者支持
---------
Issues: [https://github.com/shi-yang/jnoj/issues](https://github.com/shi-yang/jnoj/issues)

Wiki: [https://github.com/shi-yang/jnoj/wiki](https://github.com/shi-yang/jnoj/wiki)

QQ、微信：

![QQ & Wechat](docs/images/contact.png)

参与贡献
--------

[https://www.jnoj.org/contribute.html](https://www.jnoj.org/contribute.html)

目录结构
----------

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
