<?php

use meshzp\rbacadmin\components\PermHtml as Html;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use meshzp\rbacadmin\models\AdminUser;

/**
 * @var $this \yii\web\View
 * @var $logModel meshzp\rbacadmin\models\AdminUsersLoginLog
 * @var $changePasswordForm meshzp\rbacadmin\models\TwoFactorAuthForm
 */

$logDataProvider = new ActiveDataProvider([
    'query' => $logModel,
    'pagination' => false,
    'sort' => false,
]);

try{
    $gridView = GridView::widget([
        'summary' => false,
        'tableOptions' => ['class' => 'table table-striped'],
        'dataProvider' => $logDataProvider,
        'columns' => [
            'ip:text',
            'date_attempted:datetime',
            [
                'attribute' => 'login_type',
                'value' => 'loginTypeLabel',
            ],
            [
                'attribute' => 'is_successful',
                'value' => 'loginSuccessfulLabel',
            ],
        ],
    ]);
} catch (\Exception $e){
    $gridView = '<div class="alert alert-danger">' . Yii::t('perm', 'perm-gridview-error'). '</div>';
}

$this->title = Yii::t('perm', 'perm-security');
?>
<div class="grid-container">
    <div class="row">
        <div class="col-xs-12">
            <h1><?= Html::encode($this->title) ?></h1>

            <div class="perm-widget perm-widget_cabinet">
                <div class="col-xs-16">
                    <h3><?=Yii::t('perm', 'perm-security-authentication'); ?></h3>
                    <p><?=Yii::t('perm', 'perm-security-authentication-desc', ['app-name' => Yii::$app->name]); ?></p>
                    <?php if (Yii::$app->user->identity->auth_type == AdminUser::AUTHTYPE_SIMPLE) : ?>
                        <p><?=Yii::t('perm', 'perm-status'); ?> <?= Html::tag('span', Yii::t('perm', 'perm-off').' <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>', ['class' => 'text-danger']) ?></p>
                        <p>
                            <?= Html::a(Yii::t('perm', 'perm-setup-authentication'), ['/rbacadmin/security/2fa-intro'], ['class' => 'btn btn-default btn-sm'])?>
                        </p>
                    <?php else : ?>
                        <p>
                            <?=Yii::t('perm', 'perm-status'); ?> <?= Html::tag('span', Yii::t('perm', 'perm-enabled').' <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>', ['class' => 'text-success']) ?>
                            <?= Html::a(Yii::t('perm', 'perm-edit'), ['/rbacadmin/security/2fa-configure'], ['class' => 'btn btn-default btn-sm'])?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="perm-widget perm-widget_cabinet">
                <div class="col-xs-16">
                    <h3><?=Yii::t('perm', 'perm-last-logins'); ?></h3>

                    <p><?=Yii::t('perm', 'perm-last-logins-desc'); ?></p>
                    <p><?= $gridView; ?></p>
                    <p>
                        <?= Html::a(Yii::t('perm', 'perm-full-login-button'), ['/rbacadmin/security/login-history'], ['class' => 'btn btn-default btn-sm'])?>
                    </p>
                </div>

                <div class="change_password_form clearfix col-xs-16">
                    <div class="wrp">
                        <?= $this->render('change_password', ['changePasswordForm' => $changePasswordForm]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
