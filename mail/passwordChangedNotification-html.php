<?php

use yii\helpers\Html;

/** @var $this yii\web\View */
/** @var $user meshzp\rbacadmin\models\AdminUser */

?>
<div class="password-reset">
    <?= \Yii::t('perm', 'perm-mail-password-change-notify-html', [
        'username' => Html::encode($user->username),
        'time'     => $time,
    ]) ?>
</div>
