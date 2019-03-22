<?php

use meshzp\rbacadmin\models\AdminUser;

/**
 * @var $model AdminUser
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \meshzp\rbacadmin\models\ControlPermissionsSearch
 */

if ($model->group_flag == AdminUser::GROUP_FLAG_IT_IS_USER) {
    $this->title = Yii::t('perm', 'perm-edit-user-permissions', ['data' => $model->username]);
    echo $this->render('users_nav_tabs', ['active' => 5, 'user_id' => $model->id]);
}
if ($model->group_flag == AdminUser::GROUP_FLAG_IT_IS_GROUP) {
    $this->title = Yii::t('perm', 'perm-edit-group-permissions', ['data' => $model->name]);
    echo $this->render('groups_nav_tabs', ['active' => 2, 'group_id' => $model->id]);
}

?>
<h1><?= $this->title ?></h1>
<?= $this->render('ind_permissions', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel, 'user_id' => $model->id]) ?>
