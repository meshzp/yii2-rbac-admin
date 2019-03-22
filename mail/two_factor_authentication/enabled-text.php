<?php

/**
 * @var $this yii\web\View
 * @var $user meshzp\rbacadmin\models\AdminUser
 */

echo Yii::t('perm', 'perm-mail-two-factor-auth-enabled-text', [
    'username' => $user->username,
    'app_name' => Yii::$app->name,
]);
