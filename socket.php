<?php
use Workerman\Worker;
use PHPSocketIO\SocketIO;

include __DIR__ . '/vendor/autoload.php';

// 全局数组保存uid在线数据
$uidConnectionMap = array();
// 记录最后一次广播的在线用户数
$last_online_count = 0;
// 记录最后一次广播的在线页面数
$last_online_page_count = 0;

// PHPSocketIO服务
$sender_io = new SocketIO(2120);
// 客户端发起连接事件时，设置连接socket的各种事件回调
$sender_io->on('connection', function($socket){
    // 当客户端发来登录事件时触发
    $socket->on('login', function ($uid)use($socket){
        global $uidConnectionMap, $last_online_count, $last_online_page_count;
        // 已经登录过了
        if(isset($socket->uid)){
            return;
        }
        // 更新对应uid的在线数据
        $uid = (string)$uid;
        if (!isset($uidConnectionMap[$uid])) {
            $uidConnectionMap[$uid] = 0;
        }
        // 这个uid有++$uidConnectionMap[$uid]个socket连接
        ++$uidConnectionMap[$uid];
        // 将这个连接加入到uid分组，方便针对uid推送数据
        $socket->join($uid);
        $socket->uid = $uid;
    });

    // 当客户端断开连接是触发（一般是关闭网页或者跳转刷新导致）
    $socket->on('disconnect', function () use($socket) {
        if(!isset($socket->uid)) {
            return;
        }
        global $uidConnectionMap, $sender_io;
        // 将uid的在线socket数减一
        if(--$uidConnectionMap[$socket->uid] <= 0) {
            unset($uidConnectionMap[$socket->uid]);
        }
    });
});

// 当$sender_io启动后监听一个http端口，通过这个端口可以给任意uid或者所有uid推送数据
$sender_io->on('workerStart', function(){
    // 监听一个http端口
    $inner_http_worker = new Worker('Text://0.0.0.0:2121');
    // 当http客户端发来数据时触发
    $inner_http_worker->onMessage = function ($http_connection, $buffer) {
        global $uidConnectionMap;
        global $sender_io;
        $data = json_decode($buffer, true);
        if (isset($data['uid'])) {
            $sender_io->to($data['uid'])->emit('msg', $data['content']);
        } else {
            $sender_io->emit('msg', $data['content']);
        }
        return $http_connection->send('ok');
        //$_POST = $_POST ? $_POST : $_GET;
        // 推送数据的url格式 type=publish&to=uid&content=xxxx
        /*switch(@$_POST['type']){
            case 'publish':
                global $sender_io;
                $to = @$_POST['to'];
                $_POST['content'] = htmlspecialchars(@$_POST['content']);
                // 有指定uid则向uid所在socket组发送数据
                if($to){
                    $sender_io->to($to)->emit('new_msg', $_POST['content']);
                    // 否则向所有uid推送数据
                }else{
                    $sender_io->emit('new_msg', @$_POST['content']);
                }
                // http接口返回，如果用户离线socket返回fail
                if($to && !isset($uidConnectionMap[$to])){
                    return $http_connection->send('offline');
                }else{
                    return $http_connection->send('ok');
                }
        }
        return $http_connection->send('fail');*/
    };
    // 执行监听
    $inner_http_worker->listen();
});

Worker::runAll();
