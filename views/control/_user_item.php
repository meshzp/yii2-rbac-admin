<?php

use meshzp\rbacadmin\models\AdminUser;
use yii\helpers\Html;

/**
 * @var $model AdminUser
 * @var $activeItem integer
 */

$in_group = AdminUser::findOne(['id' => $model->in_group]);
?>
<div class="control-user-item row <?php if ($activeItem == $model->id): ?>chosen-user<?php endif; ?>">
    <div class="col-xs-12">
        <div class="user-login"><?= Html::a($model->username, ['control/user-manager', 'id' => $model->id], ['class' => 'btn btn-success', 'style' => 'width: 100%; margin:10px 0;']); ?></div>
        <div class="user-name-surname">
            <i><b><?= Yii::t('perm', 'fio-word') ?> </b> <?= ': ' . $model->name . ' ' . $model->patronymic . ' ' . $model->surname; ?></i>
        </div>
        <div class="user-in-group"><?= $in_group ? Yii::t('perm', 'perm-in-group', ['data' => $in_group->name]) : Yii::t('perm', 'perm-not-in-group') ?></div>
        <div class="user-company-position"><?= Yii::t('perm', 'perm-company-position', ['data' => $model->company_position ? $model->company_position : Yii::t('perm', 'perm-not-defined')]) ?></div>
    </div>
</div>
