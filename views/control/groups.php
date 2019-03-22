<?php

use meshzp\rbacadmin\components\PermHtml as Html;
use meshzp\rbacadmin\models\AdminUser;
use meshzp\rbacadmin\PermChangeAsset;
use yii\grid\GridView;
use yii\widgets\Pjax;

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \meshzp\rbacadmin\models\AdminUserSearch
 * @var $userModel AdminUser
 */

$this->registerJs('var action_for = "group"', \yii\web\View::POS_HEAD);
PermChangeAsset::register($this);

try{
    $gridView = GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'options'      => ['style' => 'font-size:90%'],
        'rowOptions'   => function ($data) {
            $class = '';
            switch ($data->status) {
                case AdminUser::STATUS_ACTIVE:
                    $class = 'success';
                    break;
                case AdminUser::STATUS_BANNED:
                    $class = 'warning';
                    break;
                case AdminUser::STATUS_FIRED:
                    $class = 'danger';
                    break;
            }

            return ['class' => $class];
        },
        'columns'      => [
            'name',
            [
                'attribute' => 'group_head_id',
                'value'     => function ($data) {
                    if ($data->group_head_id) {
                        return AdminUser::getUserFIOById($data->group_head_id);
                    }

                    return '';
                },
                'filter'    => Html::activeDropDownList($searchModel, 'group_head_id', AdminUser::getAllUsersList(true), ['class' => 'form-control', 'prompt' => Yii::t('perm', 'perm-select-with--')]),
            ],
            [
                'attribute' => 'in_group',
                'value'     => function ($data) {
                    if ($data->in_group) {
                        return AdminUser::getNameById($data->in_group);
                    }

                    return '';
                },
                'filter'    => Html::activeDropDownList($searchModel, 'in_group', AdminUser::getGroupList(), ['class' => 'form-control', 'prompt' => Yii::t('perm', 'perm-select-with--')]),
            ],
            [
                'attribute' => 'can_get_child_info',
                'value'     => function ($data) {
                    return AdminUser::getCanGetChildInfo($data->can_get_child_info);
                },
                'filter'    => Html::activeDropDownList($searchModel, 'can_get_child_info', AdminUser::getCanGetChildInfoList(), ['class' => 'form-control', 'prompt' => Yii::t('perm', 'perm-select-with--')]),
            ],
            [
                'label'  => Yii::t('perm', 'perm-users-count'),
                'format' => 'raw',
                'value'  => function ($data) {
                    /** @var $data AdminUser */
                    return $data->getCountOfUsersInGroup();
                },
            ],
            'description',
            [
                'attribute' => 'status',
                'value'     => function ($data) {
                    return AdminUser::getGroupStatusById($data->status);
                },
                'filter'    => Html::activeDropDownList($searchModel, 'status', AdminUser::getGroupStatusList(), ['class' => 'form-control', 'prompt' => Yii::t('perm', 'perm-select-with--')]),
            ],
            [
                'label'          => Yii::t('perm', 'perm-action-word'),
                'format'         => 'raw',
                'contentOptions' => ['style' => 'width: 170px;'],
                'value'          => function ($data) {
                    $button_activate    = Html::a('', ['#'], ['class' => 'button-empty activate glyphicon glyphicon glyphicon-ok-circle', 'data-pjax' => 0, 'data-id' => $data->id, 'title' => Yii::t('perm', 'perm-control-group-status-works')]);
                    $button_ban         = Html::a('', ['#'], ['class' => 'button-empty ban glyphicon glyphicon-remove-circle', 'data-pjax' => 0, 'data-id' => $data->id, 'title' => Yii::t('perm', 'perm-control-group-status-banned')]);
                    $button_fire        = Html::a('', ['#'], ['class' => 'button-empty fire glyphicon glyphicon-ban-circle', 'data-pjax' => 0, 'data-id' => $data->id, 'title' => Yii::t('perm', 'perm-control-group-status-deleted')]);
                    $button_edit        = Html::a('', ['control/group-edit', 'id' => $data->id], ['class' => 'button-empty glyphicon glyphicon-eye-open', 'data-pjax' => '0', 'title' => Yii::t('perm', 'perm-control-group-edit')]);
                    $button_permissions = Html::a('', ['control/ind-permissions', 'id' => $data->id], ['class' => 'button-empty glyphicon glyphicon-cog', 'data-pjax' => '0', 'title' => Yii::t('perm', 'perm-control-group-permissions')]);
                    $button_empty       = '<span class="button-empty"></span>';
                    switch ($data->status) {
                        case AdminUser::STATUS_ACTIVE:
                            $button_activate = $button_empty;
                            break;
                        case AdminUser::STATUS_BANNED:
                            $button_ban = $button_empty;
                            break;
                        case AdminUser::STATUS_FIRED:
                            $button_fire = $button_empty;
                            break;
                    }

                    return $button_activate . $button_ban . $button_fire . $button_edit . $button_permissions;
                },
            ],
        ],
    ]);
} catch (\Exception $e){
    $gridView = '<div class="alert alert-danger">' . Yii::t('perm', 'perm-gridview-error'). '</div>';
}

$this->title = Yii::t('perm', 'perm-group-list');
?>

<?= $this->render('groups_nav_tabs', ['active' => 0]) ?>
<h1><?= $this->title; ?></h1>
<?= Html::a(Yii::t('perm', 'perm-create-new-group'), ['control/group-edit'], ['class' => 'btn btn-warning', 'style' => 'float:right;']) ?>
<div class="perm-widget perm-widget_cabinet gray">
    <div class="wrp">
        <?php Pjax::begin(['timeout' => false]) ?>
        <?= $gridView; ?>
        <?php Pjax::end() ?>
    </div>
</div>
