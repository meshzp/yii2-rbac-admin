<?php

use meshzp\rbacadmin\models\AdminUser;

/**
 * @var $groupModel AdminUser
 */
$this->title = $groupModel->name ? Yii::t('perm', 'perm-edit-group', ['data' => $groupModel->name]) : Yii::t('perm', 'perm-create-new-group');

?>
<?= $this->render('groups_nav_tabs', ['active' => 1, 'group_id' => $groupModel->id]) ?>
<h1><?= $this->title ?></h1>
<?= $this->render('_group_form', ['groupModel' => $groupModel]) ?>
