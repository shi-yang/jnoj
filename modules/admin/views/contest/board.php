<?php

use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $model app\models\Contest */

$this->title = $model->title;
$this->registerJsFile('@web/js/scrollboard.js', ['depends' => 'yii\web\JqueryAsset']);
$this->registerCssFile('@web/css/scrollboard.css');

$start_time = $model->start_time;
$lock_time = $model->lock_board_time;
$problem_count = $model->getProblemCount();
$url = Url::toRoute(['contest/board', 'id' => $model->id, 'json' => true]);
$this->registerJs("
    function getSubmitList() {
        var data = new Array();
        $.ajax({
            type: \"GET\",
            content: \"application/x-www-form-urlencoded\",
            url: \"{$url}\",
            dataType: \"json\",
            data: {},
            async: false,
            success: function(result) {
                for (var key in result.data) {
                    var sub = result.data[key];
                    data.push(new Submit(sub.submitId, sub.username, sub.alphabetId, sub.subTime, sub.resultId));
                }
            },
            error: function() {
                alert(\"获取Submit数据失败\");
            }
        });
        return data;
    }
    function getTeamList() {
        var data = new Array();
        $.ajax({
            type: \"GET\",
            content: \"application/x-www-form-urlencoded\",
            url: \"{$url}\",
            dataType: \"json\",
            async: false,
            data: {},
            success: function(result) {
                for (var key in result.data) {
                    var team = result.data[key];
                    data[team.username] = new Team(team.username, team.nickname, null, 1);
                }
            },
            error: function() {
                alert(\"获取Team数据失败\");
            }
        });
        return data;
    }
", \yii\web\View::POS_END);

$this->registerJs("
var board = new Board({$problem_count}, new Array(4, 4, 4), StringToDate(\"{$start_time}\"), StringToDate(\"{$lock_time}\"));
board.showInitBoard();
$('html').keydown(function(e) {
    if (e.keyCode == 13) {
        board.keydown();
    }
});
");
