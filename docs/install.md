环境需求
------------

在 Linux 环境下安装。判题机是在 Linux 环境下写的，Windows 下无法运行判题机。

搭建 LNMP (或 LAMP) 环境：PHP 7.x、MySQL、Nginx / Apache2

本教程分为 **一键安装脚本**、**手动安装过程**。这两个过程选择其中一个即可。

一键安装脚本
-----------

> 适合在纯净的系统或未运行 Web 有关服务的系统中。

**注意！！！此方法目前仅在 Ubuntu 18.04、Ubuntu 16.04、Centos 7.2+ 中测试通过。其它 Linux 系统还改进安装脚本。**

执行以下命令，进行安装：
```
wget https://raw.githubusercontent.com/shi-yang/jnoj/master/docs/install.sh
sudo bash install.sh
```

该脚本将 OJ 安装在 `/home/judge/jnoj` 目录下。


手动安装过程
------------

搭建 LNMP (或 LAMP) 环境，可以参考：[LNMP 环境搭建](environment.md)。

1. 下载　`jnoj`。
    运行命令：
    ~~~
    git clone https://github.com/shi-yang/jnoj.git
    ~~~

2. 配置 Web 端
    1. 配置数据库信息
    
        在 `jnoj/config/db.php` 文件中配置数据库信息，请根据数据库实际情况修改相应的 `username` 和 `password`。在服务器上可以使用 `vim` 或 `nano` 命令进行编辑。例如：
        
        ```php
        return [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=jnoj',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
        ];
        ``` 
        **注意：** Web 程序不会为你创建数据库，需要你自己手动创建该数据库（创建方法：运行 `mysql -u root -p` 登录MySQL，然后 `create database jnoj;`，执行 `quit;` 可退出MySQL，注意此处命令有分号）。

    2. 执行安装命令
    
        进入 jnoj 目录，在命令行运行 `./yii install` 来安装。安装过程会自动导入所需的 SQL 数据，并且需要你根据提示输入 OJ 管理员的账号密码。
    
    3. 修改 `/etc/nginx/sites-enabled/default` 文件，需要修改的配置：
        ```
        server {
                listen 80 default_server;
                listen [::]:80 default_server;

                # 修改 root 后的路径为 jnoj/web 目录所对应的路径。看你具体把 jnoj 目录放到哪里。
                root /home/judge/jnoj/web;

                index index.php;

                server_name _;

                location / {
                        try_files $uri $uri/ /index.php?$args;
                }

                location ~ \.php$ {
                        include snippets/fastcgi-php.conf;
                        fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
                }
        }
        ```
        修改后使用 `sudo nginx -s reload` 重现加载配置
    做好以上步骤后便可以使用 Web 端：
    
    ~~~
    http://ip地址
    本地主机则访问  http://127.0.0.1
    ~~~
    
    此时还不能进行判题，需配置判题机才能判题。
    
3. 配置判题机
    1. 安装编译的依赖，运行命令：`sudo apt install libmysqlclient-dev libmysql++-dev`
    2. 创建一个用于判题的用户，运行命令：`sudo useradd -m -u 1536 judge`
    3. 将控制台切换到 `judge` 目录（即运行 `cd judge`命令），然后运行 `make` 命令
    4. 运行 `sudo ./dispatcher` 命令

4. 配置配置多边形系统
    
    1. 将控制台切换到 `polygon` 目录（即运行 `cd polygon`命令），然后运行 `make` 命令
    2. 运行 `sudo ./polygon` 命令

### 安装过程执行命令如下：

注意，在下面这些命令中，有 `vim` 开头的是需要编辑文件的，若不会使用 `vim`，则将 `vim` 改成 `nano`。执行 `nano  文件名` 命令后会进入 nano 编辑器并打开文件，修改好用组合键 `Ctrl X` 退出。

##### 创建数据库
~~~
$ mysql -u root -p
mysql> create database jnoj;
mysql> quit;
~~~

##### 安装过程

在以下命令中，`#` 字符及之后的字符为注释，不用输入
~~~
$ git clone https://github.com/shi-yang/jnoj.git
$ cd jnoj
$ vim config/db.php
$ ./yii install
$ sudo useradd -m -u 1536 judge
$ cd judge
$ sudo apt install libmysqlclient-dev libmysql++-dev
$ make
$ sudo ./dispatcher
$ cd ../polygon
$ make
$ sudo ./polygon
~~~
