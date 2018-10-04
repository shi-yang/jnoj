JNOJ Change Log
===============

该文件显示了各版本间的改变。`Enh`表示添加新功能，`Chg`表示修改功能，`Bug`表示修复Bug。

0.4.0 under development
-----------------------

- Bug: 封榜后不再显示别人的提交
- Enh: 通过修改配置文件 `config/params.php` 的 `isShareCode` 参数来确定用户是否公开自己的代码

0.3.0 2018.10.3
----------------

- Enh: 导入 hustoj 题目的功能
- Chg: 将 Markdown 编辑器换成富文本编辑器（为兼容其它OJ的数据迁移）
- Enh: 测试数据上传文件的功能
- Chg: KaTeX公式风格习惯调整（单个$识别符号为行内公式，双个$识别符号为多行公式）
- Enh: 完善题数排行页面的功能
- Bug: QQ号改为长整型
- Bug: 修复个人赛排序方式
- Enh: 在问题列表页面，对已解决问题增加个提示标签
- Bug: 修复rating计算
- Bug: 调整缓存依赖
- Enh: 代码高亮
- Enh: 代码编辑器
- Chg: 删除多余的管理员权限
- Enh: 在Polygon中添加验题的功能