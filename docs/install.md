# 依赖
 * web均需要`PHP5`以上版本，并开启`mysql`和`curl`支持。v3如果需要`[tex]`标签支持，需要`tetex tetex-extra （或texlive） ghostscript imagemagick`，如果打算使用此功能请在conn.php中注释掉`latex_content($data)`函数处理标签。
 * backend：`libmysqlclient libpthread`
 * judger: `libpthread`
 * vjudge: `glib2.0 libre2 libcurl libjpeg libhtmlcxx libcrypto`
 * 本地判题需要有相应环境，例如`gcc openjdk fpc python`等
 * 编译backend与vjudge请安装相应库的dev包

# 安装

## 下载
 * 在 web 目录（如：/var/www/html 具体路径根据自己的服务器而定）下 `git clone https://github.com/shi-yang/jnuoj.git`

## Web
 * 进入 web 目录
  * 安装composer：`curl -sS https://getcomposer.org/installer | php`
  * 安装依赖组件: `php composer.phar install`
  * 参照`config.sample.php`配置`config.php`，注意端口、验证字符串需要与dispatcher配置相同
  * 导入`data.sql`到数据库
 * 将域名指向 web 目录即可

## Backend/Dispatcher(Required)
 * 编译：
  * cd backend/dispatcher
  * ./configure && make
 * 参照config.sample.ini配置config.ini

## Backend/Judger(Optional)
 * 本地判题需要此组件
 * 编译
  * cd backend/judger
  * ./configure && make
 * 建立工作用户
  * 建立一个低权限的账户，UID填入config.ini中（替换1002）
  * 参照config.sample.ini配置config.ini，注意端口、验证字符串需要与dispatcher配置相同。

## Backend/Vjudge(Optional)
 * 远程判题需要此组件
 * 编译
  * cd backend/vjudge
  * ./configure && make
 * 参照config.sample.ini配置config.ini，注意端口和验证字符串需要与dispatcher相同。
  * 通过config.ini中相应字段的有无可以开启/关闭相应的VJ，例如若其中有[PKU]字段则开启PKU的VJ，没有则关闭。
  * 每一VJ下可配置一个以上的帐号，以加快判题速度。

# 运行
 * 在相应目录下执行`nohup ./src/dispatcher`、`nohup ./src/judger`、`nohup ./src/vjudge`启动dispatcher、judger和vjudge
 * 评测数据放在judger下的`testdata`目录，输入文件为.in，输出文件为.out，一一对应。special judge放在`spj`目录，请将所有者设置为root、权限设置为600以防泄漏
