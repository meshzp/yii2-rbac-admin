<?php

use meshzp\rbacadmin\models\AdminUser;
use meshzp\rbacadmin\PermChangeAsset;
use kartik\daterange\DateRangePicker;
use yii\grid\GridView;
use yii\widgets\Pjax;

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \meshzp\rbacadmin\models\AdminRequestLogSearch
 * @var $data AdminUser
 * @var $model \meshzp\rbacadmin\models\AdminRequestLog
 */

try{
    $gridView = GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'showOnEmpty'  => true,
        'showHeader'   => true,
        'summary'      => '',
        'columns'      => [
            [
                'filter'         => DateRangePicker::widget([
                    'model'            => $searchModel,
                    'attribute'        => 'date_created',
                    'convertFormat'    => true,
                    'pluginOptions'    => [
                        'locale' => ['format' => 'Y-m-d'],
                    ],
                    'autoUpdateOnInit' => false,
                ]),
                'attribute'      => 'date_created',
                'format'         => 'raw',
                'contentOptions' => ['style' => 'width:260px;'],
                'value'          => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->date_created);
                },],
            [
                'attribute' => 'user_id',
                'filter'    => AdminUser::getAllUsersList(true),
                'value'     => function ($data) {
                    return $data->username . ($data->username == AdminUser::getLoginById($data->user_id) ? '' : ' (' . Yii::t('perm', 'perm-login-was-changed') . ')');
                },
                'label'     => Yii::t('perm', 'User'),
            ],
            [
                'attribute' => 'request',
                'format'    => 'raw',
                'value'     => function ($data) {
                    $viewData = explode('?', $data->request);
                    $url      = explode(' ', $viewData[0]);
                    if (isset($url[1]) && isset($url[0]) && !empty($url[0])) {
                        $method = $url[0];
                        $link   = $url[1];

                        return '<b>(' . $method . ')</b>' . ' ' . $link;
                    }

                    return $viewData[0];
                },
            ],
            'get_params',
            'post_params',
        ],
    ]);
} catch (\Exception $e){
    $gridView = '<div class="alert alert-danger">' . Yii::t('perm', 'perm-gridview-error'). '</div>';
}

$this->title = Yii::t('perm', 'perm-request-logs');
PermChangeAsset::register($this);
?>

<h1><?= $this->title; ?></h1>
<div class="perm-widget perm-widget_cabinet gray">
    <div class="wrp">
        <?php Pjax::begin(['timeout' => false]) ?>
        <?= $gridView; ?>
        <?php Pjax::end() ?>
    </div>
</div>
