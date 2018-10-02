JNOJ 更新
==========

当前 JNOJ 还在不断开发中，仍然存在不少已知的未知的Bug，你可以在 [CHANGELOG.md](../CHANGELOG.md) 文件中查看目前的版本更新情况。

在你部署 JNOJ 后，若需要更新到最新版，可以在 jnoj 目录下执行以下两条命令：

```bash
git pull              # 获取GitHub上最新代码
./yii migrate         # 数据库迁移工具，用于更新数据库的变化情况
```

在执行 `git pull` 时，可能会因修改了本地文件而发生冲突，你可用搜索引擎搜索`git pull 冲突` 的解决办法。
