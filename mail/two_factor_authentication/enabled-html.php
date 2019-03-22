<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user meshzp\rbacadmin\models\AdminUser */

?>
<div class="two_factor_authentication-enabled">
    <?= Yii::t('perm', 'perm-mail-two-factor-auth-enabled-html', [
        'username' => Html::encode($user->username),
        'app_name' => Yii::$app->name,
    ]) ?>
</div>
