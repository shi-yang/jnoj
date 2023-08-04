---
sidebar_position: 3
---
# 安装 & 升级指南

## 1. 安装

> 当前版本还不稳定，随时会产生较大变更，目前当前安装方式较为繁琐。供用于部署测试使用，当然你也可以按照本安装方式来部署到正式环境中供用户使用（不建议）。

> 如您使用 Windows 系统，建议通过 [WSL 2](https://learn.microsoft.com/en-us/windows/wsl/install) 进行体验。

您需要安装好 `git`、`yarn`、`docker` 及 `docker compose`，确保这些命令能够正常执行。

1. 通过 `git` 将代码拉取到本地，切换至 `v2` 分支。
2. 进入 `deployments` 目录中执行 `docker compose -f docker-compose.yaml up -d` 命令，会启动项目。
3. 进入 `deployments` 目录中执行 `docker compose -f seaweedfs-docker-compose.yaml up -d` 命令，会启动储存系统。
5. 进入 `web` 目录，执行 `yarn install && yarn dev -p 9918` 命令，将在 9918 端口启动前端。
6. 浏览器打开 `localhost:9918` 进行体验。

具体执行命令：

```bash
git clone https://github.com/shi-yang/jnoj.git
cd jnoj
git fetch origin v2
git checkout v2
cd deployments
docker compose -f docker-compose.yaml up -d
docker compose -f seaweedfs-docker-compose.yaml up -d
cd ../web
yarn install
yarn dev -p 9918
```

做完以上步骤，本地即可进行试用。如果你需要部署到正式环境，需要借助 `nginx` 配置域名、https证书。

需要做如下操作，关于域名的配置，在 `jnoj/depolyments/nginx` 中提供了一个示例，你可参照示例进行配置：

1. 配置一个域名 api.domain.com 指向 localhost:8092。该操作用于配置应用程序后端接口所在地址。
2. 配置一个域名 api.admin.domain.com 指向 localhost:8093。该操作用于配置Admin管理后台应用程序后端接口所在地址。
3. 配置一个域名 usercontent.domain.com 指向 localhost:8333。该操作用于配置用户文件储存所在地址。目前使用的是 [https://github.com/seaweedfs/seaweedfs](https://github.com/seaweedfs/seaweedfs) 项目来做储存，储存内容报错题目测试点、用户头像、题目图片等各种文件。
4. 配置一个域名 domain.com 指向 localhost:3000。该操作用于配置用户访问的地址。
5. 修改 jnoj/deployments/.env 中的 USER_CONTENT_DOMAIN 为 usercontent.domain.com。
6. 修改 jnoj/web/src/setting.json 文件，按照该文件内容配置网站信息。
7. 修改 jnoj/web/.env.production 文件，将 api.domain.com、api.admin.domain 替换对应的地址。

配置完成后，可执行如下命令重启：

```bash
cd jnoj/web
pm2 start ecosystem.config.js
yarn install && yarn build && pm2 restart jnoj-web && docker restart jnoj_interface_1 && docker restart jnoj_sandbox_1 && docker restart jnoj_admin_1
```

## 2. 升级

进入 jnoj/web 所在目录，执行 

```bash
git pull && yarn install && yarn build && pm2 restart jnoj-web && docker restart jnoj_interface_1 && docker restart jnoj_sandbox_1 && docker restart jnoj_admin_1
```

这个操作命令的含义是：
1. 会通过 git 来获取最新代码
2. 通过 yarn 安装可更新的包并重新构建前端项目，通过 pm2 重启前端项目
3. 后面的三个 docker restart 则是重启相关的容器，从而达到更新的目的
