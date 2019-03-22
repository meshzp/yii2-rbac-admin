<?php

use meshzp\rbacadmin\components\PermHtml as Html;
use meshzp\rbacadmin\models\AdminUser;
use yii\widgets\ListView;
use yii\widgets\Pjax;

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $userModel AdminUser
 */

try{
    $listView = ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions'  => [
            'class' => 'user-item',
        ],
        'itemView'     => '_user_item',
        'viewParams'   => ['activeItem' => $userModel->id],
        'layout'       => "{items}{pager}",
        'emptyText'    => false,
    ]);
} catch (\Exception $e){
    $listView = '<div class="alert alert-danger">' . Yii::t('perm', 'perm-listview-error'). '</div>';
}

$this->title = Yii::t('perm', 'perm-user-list');
?>

<?= $this->render('users_nav_tabs', ['active' => 3]) ?>
<h1><?= $this->title; ?></h1>
<?= Html::a(Yii::t('perm', 'perm-create-new-user'), ['control/user-edit'], ['class' => 'btn btn-warning', 'style' => 'float:right;']) ?>
<div class="row">

    <?php Pjax::begin(['id' => 'user-comments', 'timeout' => false]) ?>
    <div class="col-xs-2 leftfield" style="border-right: solid 1px black;">

        <?= $listView; ?>

    </div>
    <div class="col-xs-10">
        <?php if ($userModel) : ?>
            <?= $this->render('_user_form', ['userModel' => $userModel]) ?>
        <?php endif; ?>
    </div>
    <?php Pjax::end() ?>
</div>
