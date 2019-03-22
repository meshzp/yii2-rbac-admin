<?php

use meshzp\rbacadmin\models\AdminUser;

/**
 * @var $userModel AdminUser
 */
$this->title = $userModel->username ? Yii::t('perm', 'perm-edit-user', ['data' => $userModel->username]) : Yii::t('perm', 'perm-create-new-user');

?>
<?= $this->render('users_nav_tabs', ['active' => 4, 'user_id' => $userModel->id]) ?>
<h1><?= $this->title ?></h1>
<?= $this->render('_user_form', ['userModel' => $userModel]) ?>
