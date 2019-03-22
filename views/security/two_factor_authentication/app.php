<?php

/* @var $this yii\web\View */
/* @var $model \meshzp\rbacadmin\models\TwoFactorAuthForm */

use meshzp\rbacadmin\components\PermHtml as Html;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use meshzp\rbacadmin\models\TwoFactorAuthForm;

$this->title = Yii::t('perm', 'perm-two-factor-authentication');
$code =Yii::$app->session->get(TwoFactorAuthForm::SESS_KEY_NAME_SECRET_CODE);
$droni = 1;
?>
<div class="grid-container">
    <div class="row">
        <div class="col-xs-12">
            <h1><?= Html::encode($this->title) ?></h1>

            <div class="perm-widget perm-widget_cabinet row">
                <div class="col-xs-16">
                    <h2><?=Yii::t('perm', 'perm-app-title-1', ['app-name' => Yii::$app->name]); ?></h2>
                    <p><?=Yii::t('perm', 'perm-app-title-1-desc'); ?></p>

                    <h2><?=Yii::t('perm', 'perm-app-how-it-works-title'); ?></h2>
                    <div class="clearfix">
                        <strong><?=Yii::t('perm', 'perm-app-configure-app'); ?></strong>
                        <img src="//chart.googleapis.com/chart?cht=qr&chs=220x220&chld=Q|0&chl=<?= urlencode("otpauth://totp/".Yii::$app->user->identity->username."?issuer=".urlencode(Yii::$app->name)."&secret=".Yii::$app->session->get(TwoFactorAuthForm::SESS_KEY_NAME_SECRET_CODE)) ?>" alt="QR code" style="float: right;" />
                        <p><?=Yii::t('perm', 'perm-app-how-it-works-desc', ['app-name' => Yii::$app->name]); ?></p>
                        <p><?=Yii::t('perm', 'perm-app-qr-code-text'); ?>, <?= Html::a(Yii::t('perm', 'perm-app-qr-code-link'), '#secret-code-modal', ['data-toggle' => 'modal', 'data-target' => '#secret-code-modal']) ?>.</p>
                        <?php
                        Modal::begin([
                            'id' => 'secret-code-modal',
                            'header' => '<h4>'.Yii::t('perm', 'perm-app-your-secret').'</h4>',
                        ]);
                        echo Yii::$app->session->get(TwoFactorAuthForm::SESS_KEY_NAME_SECRET_CODE);
                        Modal::end();
                        ?>
                    </div>
                    <p>
                        <strong><?=Yii::t('perm', 'perm-app-secret-code-hint')?></strong>
                        <?php $form = ActiveForm::begin(['id' => 'totp-app-form', 'enableAjaxValidation' => false]); ?>
                            <?= $form->field($model, 'auth_code', ['inputOptions' => ['placeholder' => '123456', 'style' => 'width: auto;',]]) ?>

                            <?= Html::submitButton(Yii::t('perm', 'perm-app-enable'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                        <?php ActiveForm::end(); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-xs-3 col-xs-offset-1">
        </div>
    </div>
</div>
