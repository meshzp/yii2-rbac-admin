<?php

use yii\bootstrap\ActiveForm;
use meshzp\rbacadmin\components\PermHtml as Html;
use yii\helpers\Url;

/**
 * @var $changePasswordForm \meshzp\rbacadmin\models\TwoFactorAuthForm
 */
?>
<div class="perm-widget perm-widget_cabinet row">
    <div class="col-xs-12">
        <h3><?=Yii::t('perm', 'perm-change-password-info');?></h3>
        <?php $form = ActiveForm::begin(['id' => 'change-password-form', 'method' => 'post', 'action' => Url::to(['/rbacadmin/security/index']), 'enableAjaxValidation' => false]); ?>
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