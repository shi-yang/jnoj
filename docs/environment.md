环境搭建
--------

您可以选择安装 LNMP 或者 LAMP。选择一个即可。建议安装 LNMP。

## LNMP 环境搭建（建议）

> LNMP 指 Linux、Nginx、MySQL、PHP

> 这里只介绍 Ubuntu 系统下的环境搭建。其它 Linux 系统不会可以上网搜一下。

#### Nginx 安装

1. 获取最新资源包
    ```bash
    sudo apt-get update 
    ```
2. 安装 Nginx
    ```bash
    sudo apt install nginx -y
    ```

#### 数据库安装

这里安装 MySQL。（也可以选择安装 MariaDB，某些操作系统上会是 MariaDB）

```bash
sudo apt install mysql-server mysql-client
```

安装 MySQL 数据库后。执行 `mysql_secure_installation` 命令，根据提示设置 `root` 用户的密码。

#### PHP 的安装

> 注意：PHP 要求 7.0 以上。执行下列命令后，在让你确认 `Do you want to continue? [Y/n]` 前会有提示：`The following NEW packages will be installed:`。若列举的包里面包含 `php7.` 开头则直接回车即可。若在一些较老的系统上执行可能会有 `php5.` 的提示，则需要另寻其它办法安装。


```bash
sudo apt install php-fpm php-mysql php-common php-gd php-zip php-mbstring php-xml
```
