<?php

use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $user meshzp\rbacadmin\models\AdminUser
 */

echo Yii::t('perm', 'perm-mail-password-change-notify-text', [
    'username' => Html::encode($user->username),
    'time'     => $time,
]);
