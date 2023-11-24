---
sidebar_position: 2
---
# 安装指南

## 步骤一. 快速安装

> 当前版本还不稳定，随时会产生较大变更，目前当前安装方式较为繁琐。供用于部署测试使用，当然你也可以按照本安装方式来部署到正式环境中供用户使用。

> 如您使用 Windows 系统，建议通过 [WSL 2](https://learn.microsoft.com/en-us/windows/wsl/install) 进行体验。

您需要安装好 `git`、`docker` 及 `docker-compose`，确保这些命令能够正常执行。

1. 通过 `git` 将代码拉取到本地，切换至 `v2` 分支。
2. 进入 `deployments` 目录中执行 `sudo docker-compose -f docker-compose.yaml -f docker-compose.dependence.yaml -f seaweedfs-docker-compose.yaml up -d` 命令，会启动项目。
3. 浏览器打开 `localhost:8011` 进行体验。

具体执行命令：

```bash
git clone https://github.com/shi-yang/jnoj.git
cd jnoj
git fetch origin v2
git checkout v2
cd deployments
sudo docker-compose -f docker-compose.yaml -f docker-compose.dependence.yaml -f seaweedfs-docker-compose.yaml up -d
```

做完以上步骤，本地即可进行试用。

如果你需要部署到正式环境，需要借助 `nginx` 配置域名、https证书。

## 步骤二. 部署到生产环境

1. 请准备好一个域名，并已经将域名解析至对应的服务器。

2. 在服务器上按照步骤一操作完后，先尝试看看 ip:8011 （公有云可能会有安全策略没有开放此端口）是否能够打开，如果不能，需要排查哪里出现了问题。

3. 在终端中切换至 jnoj/deployments 目录，并执行 `bash init.sh`，这是一个初始化脚本，
会要求你输入你的域名，并做一些初始化工作。

4. 在你的服务器中安装一个叫 nginx 的软件，可通过 `apt install nginx` 或 `yum install nginx` 命令进行安装，

这里提供一份 nginx 配置，修改以下的域名，和证书路径，将其保存在 nginx 配置目录（通常在 `/etc/nginx/conf.d/yourdomain.conf`）

然后执行 `sudo nginx -s reload`，重新加载配置。

```
# 配置 80 端口永久重定向至 443 https
server {
	listen 80;
	listen [::]:80;
    # 注意修改你的域名
	server_name yourdomain.com www.yourdomain.com;
	return 301 https://www.yourdomain.com:$request_uri;
}

server {
	listen 443 ssl;
	listen [::]:443 ssl;
    # 注意修改你的域名
	server_name yourdomain.com www.yourdomain.com;

    # 注意修改你的域名证书所在路径
	ssl_certificate cert/yourdomain.com.pem;
	ssl_certificate_key cert/yourdomain.com.key;

	ssl_session_timeout 5m;
	ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE:ECDH:AES:HIGH:!NULL:!aNULL:!MD5:!ADH:!RC4;
	ssl_protocols TLSv1.1 TLSv1.2;
	ssl_prefer_server_ciphers on;

	client_max_body_size 512m;
	location / {
        proxy_pass http://127.0.0.1:8011/;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host:$server_port;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
	}
}
```
