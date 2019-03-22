<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var meshzp\rbacadmin\models\Setting $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="setting-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php
    if ($model->type == 'boolean') {
        echo $form->field($model, 'value')->checkbox(['label' => Yii::t('perm', 'on-off')]);
    } else {
        // Список значений для префикса телефонных номерв в админке
        if ($model->key == 'admin_phone_prefix') {
            echo $form->field($model, 'value')->dropDownList([
                'callto:' => 'callto:',
                'sip:'    => 'sip:',
            ]);
        } else {
            echo $form->field($model, 'value')->textarea(['rows' => 6]);
        }
    }
    ?>


    <div class="form-group">
        <?=
        Html::submitButton(
            $model->isNewRecord ? Yii::t('perm', 'Create') :
                Yii::t('perm', 'Update'),
            ['class' => $model->isNewRecord ?
                'btn btn-success' : 'btn btn-primary',
            ]
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
