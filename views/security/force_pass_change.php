<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $logModel \meshzp\rbacadmin\models\AdminUsersLoginLog
 * @var $changePasswordForm \meshzp\rbacadmin\models\TwoFactorAuthForm
 */

$this->title = Yii::t('perm', 'perm-force-password-change');
?>

<div class="grid-container">
    <div class="row">
        <div class="col-xs-11 col-xs-offset-1">
            <h3><?= Html::encode($this->title) ?></h3>

            <?php $form = ActiveForm::begin(['id' => 'change-password-form', 'method' => 'post', 'action' => Url::to(['/rbacadmin/security/force-pass-change']), 'enableAjaxValidation' => false]); ?>
            <?= $form->field($changePasswordForm, 'mobile')->textInput(['placeholder' => Yii::t('perm', 'perm-mobile')])->label(false) ?>
            <?= $form->field($changePasswordForm, 'email')->textInput(['placeholder' => Yii::t('perm', 'email')])->label(false) ?>
            <?= $form->field($changePasswordForm, 'password')->passwordInput(['placeholder' => Yii::t('perm', 'perm-password')])->label(false) ?>
            <?= $form->field($changePasswordForm, 'new_password')->passwordInput(['placeholder' => Yii::t('perm', 'perm-new-password')])->label(false) ?>
            <?= $form->field($changePasswordForm, 'confirm_password')->passwordInput(['placeholder' => Yii::t('perm', 'perm-confirm-password')])->label(false) ?>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-5"><?= Html::submitButton(Yii::t('perm', 'perm-change-password'), ['class' => 'btn btn-primary', 'name' => 'change-password-button']) ?></div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
