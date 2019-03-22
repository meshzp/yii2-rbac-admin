<?php

use meshzp\rbacadmin\models\AdminUser;

/**
 * @var $userModel AdminUser
 */
$this->title = $userModel ? Yii::t('perm', 'perm-user-profile', ['data' => $userModel->username]) : Yii::t('perm', 'perm-user-no-profile');

?>
<h1><?= $this->title ?></h1>
<?php if ($userModel): ?>
<?= $this->render('_user_form', ['userModel' => $userModel, 'profiler' => true]) ?>
<?php endif; ?>
