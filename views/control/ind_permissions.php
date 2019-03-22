<?php

use meshzp\rbacadmin\models\AdminUser;
use meshzp\rbacadmin\models\ControlPermissions;
use meshzp\rbacadmin\PermChangeAsset;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \meshzp\rbacadmin\models\ControlPermissionsSearch
 * @var $userModel AdminUser
 * @var $user_id integer
 */

$this->title = Yii::t('perm', 'perm-permission-list');
$changeUrl   = Url::toRoute(['control/change']);
$deleteUrl   = Url::toRoute(['control/delete']);
$this->registerJs("var user_id = {$user_id}; var c_url = '{$changeUrl}', d_url = '{$deleteUrl}';", View::POS_HEAD);
PermChangeAsset::register($this);

try{
    $gridView = GridView::widget([
        'export'       => false,
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'rowOptions'   => function ($data) {
            switch ($data['type']) {
                case ControlPermissions::TYPE_CONTROLLER:
                    return ['class' => 'info-permission-row', 'style' => 'background-color: #d9edf7;'];
                case ControlPermissions::TYPE_PERMISSION:
                    return ['class' => 'warning-permission-row', 'style' => 'background-color: #fcf8e3;'];
                default:
                    return ['class' => 'default permission-row', 'style' => 'background: transparent;'];
            }
        },
        'columns'      => [
            [
                'format'            => 'raw',
                'attribute'         => 'controller',
                'group'             => true,
                'groupedRow'        => true,
                'groupOddCssClass'  => 'danger',
                'groupEvenCssClass' => 'danger',
                'value'             => function ($model, $key, $index, $widget) {
                    return $model['controller'];
                },
                'contentOptions'    => ['style' => 'font-size: 2em; font-weight: 700;'],
            ],
            [
                'hidden'    => true,
                'attribute' => 'type',
                'format'    => 'raw',
                'value'     => function ($data) {
                    return ControlPermissions::getTextStatusList()[$data['type']];
                },
                'filter'    => Html::activeDropDownList($searchModel, 'type', ControlPermissions::getTextStatusList(), ['class' => 'form-control', 'prompt' => Yii::t('perm', 'perm-select-with--')]),
            ],
            [
                'attribute'      => 'name',
                'contentOptions' => ['class' => 'permission-name', 'style' => 'font-weight: 700; '],
            ],
            'description',
            [
                'label'          => Yii::t('perm', 'Parent Enabled'),
                'attribute'      => 'parent_enabled',
                'format'         => 'raw',
                'value'          => function ($data) {
                    if (!is_null($data['parent_enabled'])) {
                        return $data['parent_enabled'] ? Yii::t('perm', 'perm-control-permission-allowed') : Yii::t('perm', 'perm-control-permission-disallowed');
                    } else {
                        return '';
                    }
                },
                'contentOptions' => ['style' => 'width: 10px; padding-left: 20px; padding-right: 20px;'],
            ],
            [
                'label'          => Yii::t('perm', 'perm-permission-allow'),
                'attribute'      => 'switch_enable',
                'format'         => 'raw',
                'value'          => function ($data) {
                    return '<div class="material-switch  inverted pull-right">
                            <input id="' . $data['name'] . '" name="' . $data['name'] . '" type="checkbox" ' . ((isset($data['switch_enable']) && $data['switch_enable'] == 1) ? 'checked' : '') . '/>
                            <label for="' . $data['name'] . '" class="label-success"></label>
                        </div>';
                },
                'contentOptions' => ['style' => 'width: 10px; padding-left: 20px; padding-right: 20px;'],
            ],
            [
                'label'          => Yii::t('perm', 'perm-permission-disallow'),
                'attribute'      => 'switch_enable',
                'format'         => 'raw',
                'value'          => function ($data) {
                    return '<div class="material-switch direct pull-right">
                            <input id="a-' . $data['name'] . '" name="' . $data['name'] . '" type="checkbox" ' . ((isset($data['switch_enable']) && $data['switch_enable'] == 0) ? 'checked' : '') . '/>
                            <label for="a-' . $data['name'] . '" class="label-danger"></label>
                        </div>';
                },
                'contentOptions' => ['style' => 'width: 10px; padding-left: 20px; padding-right: 20px;'],
            ],
        ],
    ]);
} catch (\Exception $e){
    $gridView = '<div class="alert alert-danger">' . Yii::t('perm', 'perm-gridview-error'). '</div>';
}

?>

<h1><?= $this->title; ?></h1>
<?php // Html::a(Yii::t('perm', 'perm-control-permission-alt-view'), ['control/individual-permissions', 'id' => $user_id], ['class' => 'btn btn-warning']) ?>
<div id="access-tooltip"></div>
<?php if (!Yii::$app->user->can('/rbacadmin/control/change') && !Yii::$app->user->can('/rbacadmin/control/delete')) : ?>
    <div class="alert alert-danger">
        <?= Yii::t('perm', 'perm-alert-no-changes-no-flushes'); ?>
    </div>
<?php else: ?>
    <?php if (!Yii::$app->user->can('/rbacadmin/control/change')) : ?>
        <div class="alert alert-warning">
            <?= Yii::t('perm', 'perm-alert-no-changes'); ?>
        </div>
    <?php endif; ?>
    <?php if (!Yii::$app->user->can('/rbacadmin/control/delete')) : ?>
        <div class="alert alert-warning">
            <?= Yii::t('perm', 'perm-alert-no-flushes'); ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php if (Yii::$app->user->can('/rbacadmin/control/change') && Yii::$app->user->can('/rbacadmin/control/delete')) : ?>
    <div class="alert alert-success">
        <?= Yii::t('perm', 'perm-alert-all-access'); ?>
    </div>
<?php endif; ?>
<div class="perm-widget perm-widget_cabinet gray">
    <div class="wrp">
        <?php Pjax::begin() ?>
        <?= $gridView; ?>
        <?php Pjax::end() ?>
    </div>
</div>
