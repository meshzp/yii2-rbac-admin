<?php

use yii\helpers\Html;
use meshzp\rbacadmin\models\AdminUser;

/* @var $groupModel AdminUser */

use yii\bootstrap\ActiveForm;

?>
<?php $form = ActiveForm::begin() ?>
<?= $form->field($groupModel, 'name')->textInput(['placeholder' => Yii::t('perm', 'Name')]) ?>
<?= $form->field($groupModel, 'status')->dropDownList(AdminUser::getGroupStatusList()) ?>
<?= $form->field($groupModel, 'group_head_id')->dropDownList(AdminUser::getAllUsersList(true)) ?>
<?= $form->field($groupModel, 'in_group')->dropDownList(AdminUser::getGroupList()) ?>
<?= $form->field($groupModel, 'description')->textInput(['placeholder' => Yii::t('perm', 'Description')]) ?>
<?= $form->field($groupModel, 'can_get_child_info')->dropDownList(AdminUser::getCanGetChildInfoList()) ?>
<?= Html::submitButton(Yii::t('perm', 'perm-save'), ['class' => 'btn btn-primary btn-sm']); ?>
<?php ActiveForm::end(); ?>