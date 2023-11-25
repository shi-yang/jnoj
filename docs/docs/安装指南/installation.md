---
sidebar_position: 2
---
# 安装指南

## 一. 快速安装

> 当前版本还不稳定，随时会产生较大变更。供用于部署测试使用，当然你也可以按照本安装方式来部署到正式环境中供用户使用。

> 如您使用 Windows 系统，建议通过 [WSL 2](https://learn.microsoft.com/en-us/windows/wsl/install) 进行体验。

您需要安装好 `git`、`docker` 及 `docker-compose`，确保这些命令能够正常执行。

1. 通过 `git` 将代码拉取到本地，切换至 `v2` 分支。
2. 进入 `deployments` 目录中执行 `bash init.sh` 和 `sudo docker-compose -f docker-compose.yaml -f docker-compose.dependence.yaml -f docker-compose.seaweedfs.yaml up -d` 命令，会启动项目。
3. 浏览器打开 `localhost:8011` 进行体验。

具体执行命令：

```bash
git clone https://github.com/shi-yang/jnoj.git
cd jnoj
git fetch origin v2
git checkout v2
cd deployments
bash init.sh
sudo docker-compose -f docker-compose.yaml -f docker-compose.dependence.yaml -f seaweedfs-docker-compose.yaml up -d
```

> 命令解释：`bash init.sh` 会运行 deployments 目录下的 init.sh 文件，**这一步很重要**，该文件会做一些初始化操作，例如初始化密钥。
> 并且会要求你输入域名或者IP，如果你输入域名，请前配置好域名指向；如果你输入的是IP，请输入局域网IP（如果是本地安装）或者公网IP（例如云服务器安装）。  
> IP变动或者原本是IP访问，要变更为域名访问，需要执行一下 `bash init.sh` 文件重新输入一下，否则会前端会找不到后端地址。

做完以上步骤，本地即可进行试用。

如果你需要部署到生产环境，需要借助 `nginx` 配置域名、SSL 证书（用于让网站打开时出现 https，否则浏览器会提示不安全）。

## 二. 自定义网站信息

在安装完成后，你可能需要调整网站配置，比如名称、Logo等，你可参考 [定制网站配置](../二次开发/前端开发) 中进行调整。


## 三. 部署到生产环境

1. 请准备好一个域名，并已经将域名解析至对应的服务器，做好域名备案工作。

2. 在服务器上按照 [一. 快速安装] 操作完后，先尝试看看 ip:8011 （公有云可能会有安全策略没有开放此端口，需暂时开放此端口，测试后再关闭）是否能够打开，如果不能，需要排查哪里出现了问题。

3. 申请一份 SSL 证书，你可通过各大公有云平台获取免费或者付费证书，
你也可利用 [https://github.com/acmesh-official/acme.sh](https://github.com/acmesh-official/acme.sh) 该项目来获取和自动更新证书。

4. 在你的服务器中安装一个叫 nginx 的软件，可通过 `apt update && apt -y install nginx` 或 `yum update && yum -y install nginx` 命令进行安装，并且配置一下 nginx。

这里提供一份 nginx 配置，修改一下的域名，和证书路径，将其保存在 nginx 配置目录（保存为 `/etc/nginx/conf.d/yourdomain.conf`）

请注意修改该文件内的域名为实际域名；并将 SSL 证书放置在 `/etc/nginx/cert/` 目录下，对应修改一下修改你的域名证书所在路径。

然后执行 `sudo nginx -s reload`，会重新加载 nginx 配置。

nginx 文件内容如下：

```
# 配置 80 端口永久重定向至 443 https
server {
	listen 80;
	listen [::]:80;
	# 注意此处修改你的域名
	server_name yourdomain.com www.yourdomain.com;
	return 301 https://www.yourdomain.com:$request_uri;
}

server {
	listen 443 ssl;
	listen [::]:443 ssl;

	# 注意此处修改你的域名
	server_name yourdomain.com www.yourdomain.com;

	# 注意修改你的域名证书所在路径
	ssl_certificate cert/yourdomain.com.pem;
	ssl_certificate_key cert/yourdomain.com.key;

	ssl_session_cache shared:SSL:1m;
	ssl_session_timeout 5m;

	# 自定义设置使用的TLS协议的类型以及加密套件（以下为配置示例，请您自行评估是否需要配置）
	# TLS协议版本越高，HTTPS通信的安全性越高，但是相较于低版本TLS协议，高版本TLS协议对浏览器的兼容性较差。
	ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE:ECDH:AES:HIGH:!NULL:!aNULL:!MD5:!ADH:!RC4;
	ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;

	#表示优先使用服务端加密套件。默认开启
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
		proxy_cache_bypass $http_upgrade;
	}
}
```
