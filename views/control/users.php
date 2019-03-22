<?php

use meshzp\rbacadmin\components\PermHtml as Html;
use meshzp\rbacadmin\models\AdminUser;
use meshzp\rbacadmin\PermChangeAsset;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \meshzp\rbacadmin\models\AdminUserSearch
 * @var $userModel AdminUser
 * @var $passModel AdminUser
 */

$this->registerJs('var action_for = "user"', \yii\web\View::POS_HEAD);
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
            'username',
            [
                'format' => 'raw',
                'value'  => function ($data) {
                    return AdminUser::getUserFIOById($data->id);
                },
            ],
            [
                'attribute' => 'in_group',
                'value'     => function ($data) {
                    if ($data->in_group) {
                        $group = AdminUser::getNameById($data->in_group);

                        return $group . ' (' . AdminUser::getStatusNameById($data->in_group) . ')';
                    }

                    return '';
                },
                'filter'    => Html::activeDropDownList($searchModel, 'in_group', AdminUser::getGroupList(), ['class' => 'form-control', 'prompt' => Yii::t('perm', 'perm-select-with--')]),
            ],
            [
                'attribute' => 'company_position',
                'filter'    => Html::activeDropDownList($searchModel, 'company_position', AdminUser::getCompanyPositionsList(), ['class' => 'form-control', 'prompt' => Yii::t('perm', 'perm-select-with--')]),
            ],
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
                    $button_activate    = Html::a('', ['#'], ['class' => 'button-empty activate glyphicon glyphicon glyphicon-ok-circle', 'data-pjax' => 0, 'data-id' => $data->id, 'title' => Yii::t('perm', 'perm-control-user-status-works')]);
                    $button_ban         = Html::a('', ['#'], ['class' => 'button-empty ban glyphicon glyphicon-remove-circle', 'data-pjax' => 0, 'data-id' => $data->id, 'title' => Yii::t('perm', 'perm-control-user-status-banned')]);
                    $button_fire        = Html::a('', ['#'], ['class' => 'button-empty fire glyphicon glyphicon-ban-circle', 'data-pjax' => 0, 'data-id' => $data->id, 'title' => Yii::t('perm', 'perm-control-user-status-fired')]);
                    $button_change_pass = Yii::$app->user->can('/rbacadmin/control/user-change-pass') ? Html::a('', ['#'], ['class' => 'button-empty glyphicon glyphicon-credit-card', 'data-pjax' => '0', 'data-toggle' => 'modal', 'data-target' => '#pass_change', 'title' => Yii::t('perm', 'perm-control-user-pass-change')]) : '';
                    $button_edit        = Html::a('', ['control/user-edit', 'id' => $data->id], ['class' => 'button-empty glyphicon glyphicon-eye-open', 'data-pjax' => '0', 'title' => Yii::t('perm', 'perm-control-user-edit')]);
                    $button_permissions = Html::a('', ['control/ind-permissions', 'id' => $data->id], ['class' => 'button-empty glyphicon glyphicon-cog', 'data-pjax' => '0', 'title' => Yii::t('perm', 'perm-control-user-permissions')]);
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

                    return $button_activate . $button_ban . $button_fire . $button_change_pass . $button_edit . $button_permissions;
                },
            ],
        ],
    ]);
} catch (\Exception $e){
    $gridView = '<div class="alert alert-danger">' . Yii::t('perm', 'perm-gridview-error'). '</div>';
}

$this->title = Yii::t('perm', 'perm-user-list');
?>

<?= $this->render('users_nav_tabs', ['active' => 0]) ?>
<h1><?= $this->title; ?></h1>
<?= Html::a(Yii::t('perm', 'perm-create-new-user'), ['control/user-edit'], ['class' => 'btn btn-warning', 'style' => 'float:right;']) ?>
<div class="perm-widget perm-widget_cabinet gray">
    <div class="wrp">
        <?php Pjax::begin(['timeout' => false]) ?>
        <?= $gridView; ?>
        <?php Pjax::end() ?>
    </div>
</div>

<div id="pass_change" class="modal fade" role="dialog">
    <?php $form = ActiveForm::begin(['action' => Url::toRoute(['control/user-change-pass']), 'method' => 'post']); ?>
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"
                    style="white-space: nowrap;"><?= Yii::t('perm', 'perm-control-enter-new-pass'); ?> <b
                            class="pwd-username"></b></h4>
            </div>
            <div class="modal-body">
                <?= $form->field($passModel, 'id')->textInput()->hiddenInput()->label(false) ?>
                <div class="form-group field-controluser-pass">
                    <label class="control-label"
                           for="controluser-pass"><?= Yii::t('perm', 'perm-new-password') ?></label>
                    <input type="password" id="controluser-pass" class="form-control" name="password"
                           placeholder="<?= Yii::t('perm', 'perm-password') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <?= Html::submitButton(Yii::t('perm', 'perm-control-set-new-pass'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>

    </div>
    <?php ActiveForm::end(); ?>
</div>
