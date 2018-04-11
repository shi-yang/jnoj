Webuploader
============
WebUploader是一个简单的以Html5为主，Flash为辅的现代文件上传组件。在现代的浏览器里面能充分发挥html5的优势，同时又不摒弃主流IE浏览器，延用原来的Flash运行时。两套运行时，同样的调用方式，可供用户任意选用。WebUploader采用大文件分片并发上传，极大的提高了文件上传效率。

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist iisns/yii2-webuploader "*"
```

or add

```
"iisns/yii2-webuploader": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :


在你想要出现“选择文件”的地方，放下如下代码：

#多图上传

```
<?= \app\widgets\webuploader\MultiImage::widget() ?>
```

更多信息
--------

[http://fex.baidu.com/webuploader/](http://fex.baidu.com/webuploader/) 
