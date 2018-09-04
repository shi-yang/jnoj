LAMP 环境搭建
--------------

> 这里只介绍 Ubuntu 系统下的环境搭建。

#### Apache 安装（你也可以选择 Ｎginx）
1. 获取最新资源包
    ```bash
    sudo apt-get update 
    ```
2. 安装Apache
    ```bash
    sudo apt install apache2 -y
    ```
3. 检查是否开启Apache，一般安装完会默认开启。
    ```bash
    sudo systemctl status apache2
    ```

#### 数据库安装

这里安装 MySQL：
```bash
sudo apt install mysql-server mysql-client
```
在安装过程中，它会要求你设置 mysql 服务器 root 帐户的密码。 

#### PHP 脚本语言的安装

这里选择安装 PHP 7.2 版本

```bash
sudo apt install php7.2-mysql php7.2-curl php7.2-json php7.2-cgi php7.2 libapache2-mod-php7.2 php7.2-mbstring
```

安装完后，需要开启Apache Rewrite功能
--------------------------------
1. 启用 rewrite
    ```bash
    sudo a2enmod rewrite
    ```
2. 修改apache2.conf配置文件
    
    将其中的AllowOverride None 全部替换为 AllowOverride All
    ```bash
    sudo vim /etc/apache2/apache2.conf
    ```
3. 重启 Apache
    ```bash
    sudo service apache2 restart
    ```