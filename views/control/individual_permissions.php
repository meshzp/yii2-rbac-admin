<?php

use meshzp\rbacadmin\models\AdminUser;
use meshzp\rbacadmin\models\ControlPermissions;
use meshzp\rbacadmin\models\ControlPermissionsSearch;
use meshzp\rbacadmin\PermChangeAsset;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \meshzp\rbacadmin\models\ControlPermissionsSearch
 * @var $userModel AdminUser
 * @var $user_id integer
 */

$this->title = Yii::t('perm', 'perm-permission-list');
$this->registerJs("var user_id = {$user_id};", View::POS_HEAD);
PermChangeAsset::register($this);

try{
    $gridView = GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'columns'      => [
            [
                'attribute' => 'type',
                'format'    => 'raw',
                'value'     => function ($data) {
                    return ControlPermissions::getTextStatusList()[$data['type']];
                },
                'filter'    => Html::activeDropDownList($searchModel, 'type', ControlPermissions::getTextStatusList(), ['class' => 'form-control', 'prompt' => Yii::t('perm', 'perm-select-with--')]),
            ],
            [
                'attribute'      => 'name',
                'contentOptions' => ['class' => 'permission-name'],
            ],
            'description',
            [
                'attribute' => 'enabled',
                'format'    => 'raw',
                'value'     => function ($data) {
                    return '<input type="checkbox" name="my-checkbox" class="hidden"' . ($data['enabled'] ? 'checked' : '') . '>';
                },
                'filter'    => Html::activeDropDownList($searchModel, 'enabled', ControlPermissions::getEnabledList(), ['class' => 'form-control', 'prompt' => Yii::t('perm', 'perm-select-with--')]),
            ],
            [
                'attribute' => 'followed_by',
                'format'    => 'raw',
                'value'     => function ($data) {
                    return ControlPermissionsSearch::getFollowedList()[$data['followed_by']];
                },
                'filter'    => Html::activeDropDownList($searchModel, 'followed_by', ControlPermissionsSearch::getFollowedList(), ['class' => 'form-control', 'prompt' => Yii::t('perm', 'perm-select-with--')]),
            ],
        ],
    ]);
} catch (\Exception $e){
    $gridView = '<div class="alert alert-danger">' . Yii::t('perm', 'perm-gridview-error'). '</div>';
}

?>

<h1><?= $this->title; ?></h1>
<?= Html::a(Yii::t('perm', 'perm-control-permission-alt-view'), ['control/ind-permissions', 'id' => $user_id], ['class' => 'btn btn-warning']) ?>
<div class="perm-widget perm-widget_cabinet gray">
    <div class="wrp">
        <?php Pjax::begin() ?>
        <?= $gridView; ?>
        <?php Pjax::end() ?>
    </div>
</div>
