<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Solution;

/* @var $this yii\web\View */
/* @var $model app\modules\polygon\models\Problem */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['model'] = $model;

?>
<div class="row">
    <div class="col-md-4">
        <p>
            该页面用于生成、编辑程序的测试数据。
        </p>
        <hr>
        <p>
            测试的输入文件需自行制作，然后在下边表格上传。为文本文件，文件名称随意。</p>
        <p>
            测试的输出文件在上传输入文件后，点击此处<a href="#" class="btn btn-success">刷新</a>按钮，
            会根据提供的“<?= Html::a(Yii::t('app', 'Solution'), ['/polygon/problem/solution', 'id' => $model->id]) ?>”自行生成。
        </p>
    </div>
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">
                <table class="table">
                    <caption>标准输入文件</caption>
                    <tr>
                        <th>文件名</th>
                        <th>大小</th>
                        <th>修改时间</th>
                        <th>操作</th>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table">
                    <caption>标准输出文件</caption>
                    <tr>
                        <th>文件名</th>
                        <th>大小</th>
                        <th>修改时间</th>
                        <th>操作</th>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
