---
sidebar_position: 3
---
# 安装指南

如您使用 Windows 系统，建议通过 [WSL 2](https://learn.microsoft.com/en-us/windows/wsl/install) 进行体验。

您需要安装好 `git`、`yarn`、`docker` 及 `docker compose`，确保这些命令能够正常执行。

1. 通过 `git` 将代码拉取到本地，切换至 `v2` 分支。
2. 进入 `deployments` 目录中执行 `docker compose -f docker-compose.yaml -f docker-compose.admin.yaml up -d` 命令，会启动项目。
3. 进入 `deployments` 目录中执行 `docker compose -f seaweedfs-docker-compose.yaml up -d` 命令，会启动储存系统。
4. 浏览器打开 `localhost:9500`，将 `scripts/db` 的 SQL 文件放在 jnoj 数据库中执行。
5. 进入 `web` 目录，执行 `yarn install && yarn dev -p 9918` 命令，将在 9918 端口启动前端。
6. 浏览器打开 `localhost:9918` 进行体验。

具体执行命令：

```bash
git clone https://github.com/shi-yang/jnoj.git
cd jnoj
git fetch origin v2
git checkout v2
cd deployments
docker compose -f docker-compose.yaml -f docker-compose.admin.yaml up -d
docker compose -f seaweedfs-docker-compose.yaml up -d
cd ../web
yarn install
yarn dev -p 9918
```
