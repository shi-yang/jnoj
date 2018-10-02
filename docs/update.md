JNOJ 更新
==========

当前 JNOJ 还在不断开发中，仍然存在不少已知的未知的Bug，你可以在 [CHANGELOG.md](../CHANGELOG.md) 文件中查看目前的版本更新情况。

在你部署 JNOJ 后，若需要更新到最新版，需要在 jnoj 目录下执行以下命令：

```bash
git pull                  # 获取GitHub上最新代码
./yii migrate             # 数据库迁移工具，用于更新数据库的变化情况
cd judge                  # 进入 judge 目录
sudo pkill -9 dispatcher  # 结束判题机进程
make                      # 重新编译 judge
sudo ./dispatcher         # 运行判题机进程
cd ../polygon             # 进入 polygon 目录
sudo pkill -9 polygon     # 结束Polygon进程
make                      # 重新编译 Polygon
sudo ./polygon            # 运行Polygon进程

```

在执行 `git pull` 时，可能会因修改了本地文件而发生冲突，你可用搜索引擎搜索`git pull 冲突` 的解决办法。
