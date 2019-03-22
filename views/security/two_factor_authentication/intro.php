<?php

use meshzp\rbacadmin\components\PermHtml as Html;
use yii\bootstrap\ActiveForm;
use meshzp\rbacadmin\models\AdminUser;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $logModel \meshzp\rbacadmin\models\AdminUsersLoginLog
 */

$this->title = Yii::t('perm', 'perm-two-factor-authentication');
?>
<div class="grid-container">
    <div class="row">
        <div class="col-xs-12">
            <h1><?= Html::encode($this->title) ?></h1>

            <div class="perm-widget perm-widget_cabinet">
                <div class="col-xs-16">
                    <?php if (Yii::$app->user->identity->auth_type != AdminUser::AUTHTYPE_SIMPLE) : ?>
                    <div class="alert alert-warning" role="alert">
                        <p class="lead"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> <?=Yii::t('perm', 'perm-warning')?></p>
                        <p><?=Yii::t('perm', 'perm-warning-desc')?></p>
                    </div>
                    <?php endif; ?>
                    <h3><?=Yii::t('perm', 'perm-intro-title'); ?></h3>
                    <p><?=Yii::t('perm', 'perm-intro-desc', ['app-name' => Yii::$app->name]); ?></p>
                    <h3><?=Yii::t('perm', 'perm-intro-how-it-works-title'); ?></h3>
                    <?=Yii::t('perm', 'perm-intro-how-it-works-desc', ['app-name' => Yii::$app->name]); ?>
                    <h3><?=Yii::t('perm', 'perm-intro-setup'); ?></h3>
                    <div class="row">
                        <div class="col-md-7">
                            <p><?=Yii::t('perm', 'perm-intro-setup-desc-app'); ?></p>
                            <?php $form = ActiveForm::begin(['id' => 'totp-app-form', 'action' => Url::to(['/rbacadmin/security/2fa-app']), 'enableAjaxValidation' => false]); ?>
                            <?= Html::submitButton(Yii::t('perm', 'perm-intro-setup-app-button'), ['class' => 'btn btn-success', 'name' => 'signup-button']) ?>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-3 col-xs-offset-1">
        </div>
    </div>
</div>
