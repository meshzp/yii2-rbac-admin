<?php

use meshzp\rbacadmin\models\AdminUser;
use kartik\widgets\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**
 * @var $userModel AdminUser
 * @var $form \yii\widgets\ActiveForm
 */

if (!isset($profiler)) {
    $profiler = false;
}
?>
<?php $form = ActiveForm::begin() ?>
<?= $form->field($userModel, 'username')->textInput(['placeholder' => Yii::t('perm', 'username'), 'disabled' => $profiler]) ?>
<?php if (!$userModel->password_hash): ?>
    <div class="form-group field-controluser-pass">
        <label class="control-label" for="controluser-pass">Пароль</label>
        <input type="text" id="controluser-pass" class="form-control" name="password" placeholder="Пароль">
    </div>
<?php endif; ?>
<?= $form->field($userModel, 'name')->textInput(['placeholder' => Yii::t('perm', 'Name'), 'disabled' => $profiler]) ?>
<?= $form->field($userModel, 'patronymic')->textInput(['placeholder' => Yii::t('perm', 'Secname'), 'disabled' => $profiler]) ?>
<?= $form->field($userModel, 'surname')->textInput(['placeholder' => Yii::t('perm', 'Surname'), 'disabled' => $profiler]) ?>
<?= $form->field($userModel, 'birth_date')->widget(DatePicker::className(), [
    'options'       => [],
    'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd 00:00:00'],
]); ?>
<?= $form->field($userModel, 'sex')->dropDownList(AdminUser::getSexList()); ?>
<?= $form->field($userModel, 'status')->dropDownList(AdminUser::getUserStatusList(), ['disabled' => $profiler]) ?>
<?= $form->field($userModel, 'company_position')->textInput(['placeholder' => Yii::t('perm', 'Company Position'), 'disabled' => $profiler]) ?>
<?= $form->field($userModel, 'in_group')->dropDownList(AdminUser::getGroupList(), ['disabled' => $profiler]) ?>
<?= $form->field($userModel, 'description')->textarea(['placeholder' => Yii::t('perm', 'Description')]) ?>
<?= $form->field($userModel, 'sip')->textInput(['placeholder' => Yii::t('perm', 'Sip'), 'disabled' => $profiler]) ?>
<?= $form->field($userModel, 'email')->textInput(['placeholder' => Yii::t('perm', 'Email'), 'disabled' => $profiler]) ?>
<?= $form->field($userModel, 'start_date')->widget(DatePicker::className(), [
    'options'       => ['disabled' => $profiler],
    'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd 00:00:00'],
]); ?>
<?= Html::submitButton(Yii::t('perm', 'perm-save'), ['class' => 'btn btn-primary btn-sm']); ?>
<?php ActiveForm::end(); ?>
