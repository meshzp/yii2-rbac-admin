<?php

use meshzp\rbacadmin\components\PermHtml as Html;
use yii\bootstrap\ActiveForm;
use meshzp\rbacadmin\models\AdminUser;

/**
 * @var $this \yii\web\View
 * @var $form \yii\bootstrap\ActiveForm
 * @var $model \meshzp\rbacadmin\models\TwoFactorAuthForm
 * @var $user \meshzp\rbacadmin\models\AdminUser
 */

$this->title = Yii::t('perm', 'perm-two-factor-authentication');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="grid-container">
    <div class="row">
        <div class="col-xs-12">
            <h1><?= Html::encode($this->title) ?></h1>

            <?php if ($user->auth_type == AdminUser::AUTHTYPE_APP) : ?>
            <p><?=Yii::t('perm', 'perm-two-factor-open')?></p>
            <?php else : ?>
            <p><?=Yii::t('perm', 'perm-two-factor-resent-desc')?>
                <?= Html::a(Yii::t('perm', 'perm-two-factor-resent'), '#resend_the_code', ['id' => 'resend_mobile']); ?>.
            </p>
            <?php endif; ?>

            <div class="perm-widget perm-widget_cabinet row">
                <div class="col-xs-5">
                    <?php $form = ActiveForm::begin(['id' => '2fa-form']); ?>

                        <?= $form->field($model, 'auth_code') ?>

                        <div class="form-group">
                            <?= Html::submitButton(Yii::t('perm', 'perm-verify'), ['class' => 'btn btn-primary', 'name' => 'verify-button']) ?>
                        </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
