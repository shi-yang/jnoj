---
sidebar_position: 3
---
# 更新指南

## 1. 更新指南

当代码/版本有更新后，进入 jnoj 所在目录，执行 

```bash
git pull
cd deployments
docker-compose -f docker-compose.yaml down
docker-compose -f docker-compose.yaml build
docker-compose -f docker-compose.yaml up -d
```

这几个操作命令的含义是：

1. 会通过 git 来获取最新代码
2. 进入 deployments 目录
3. 重新构建 docker-compose.yaml 文件中定义的镜像
4. 启动 docker-compose.yaml 文件中定义的镜像

**注意：** 如果 git 上游指向的是 https://github.com/shi-yang/jnoj，执行 `git pull` 时可能会产生冲突。
建议 fork 一份到自己名下，用自己的仓库来部署，通过 pull request 的形式来更新。
