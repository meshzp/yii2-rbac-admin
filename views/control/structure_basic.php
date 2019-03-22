<?php

use meshzp\rbacadmin\components\PermHtml as Html;
use meshzp\rbacadmin\models\AdminUser;
use yii\grid\GridView;
use yii\widgets\Pjax;

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $data AdminUser
 */

try{
    $gridView = GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => false,
        'showOnEmpty'  => false,
        'showHeader'   => false,
        'tableOptions' => ['class' => 'table table-borderless'],
        'summary'      => '',
        'columns'      => [
            [
                'attribute'      => 'name',
                'format'         => 'raw',
                'value'          => function ($data) {
                    /** @var $data AdminUser */
                    return Html::a($data->name . '<span class="badge">' . $data->getUsersInGroup() . '</span>', ['control/group-edit', 'id' => $data->id], ['data-pjax' => 0, 'class' => 'list-group-item']);
                },
                'contentOptions' => ['width' => '200px;'],
            ],
            [
                'label'  => Yii::t('perm', 'perm-control-user-in-groups'),
                'format' => 'raw',
                'value'  => function ($data) {
                    /** @var $data AdminUser */
                    return $data->formatUserInGroupAsHtml();
                },
            ],
        ],
    ]);
} catch (\Exception $e){
    $gridView = '<div class="alert alert-danger">' . Yii::t('perm', 'perm-gridview-error'). '</div>';
}

$this->title = Yii::t('perm', 'perm-structure');
?>

<?= $this->render('users_nav_tabs', ['active' => 1]) ?>
<h1><?= $this->title; ?></h1>
<div class="perm-widget col-md-6 perm-widget_cabinet gray">
    <div class="wrp row">
        <?php Pjax::begin(['timeout' => false]) ?>
        <?= $gridView; ?>
        <?php Pjax::end() ?>
    </div>
</div>
