<?php

/* @var $this yii\web\View */
/* @var $logModel meshzp\rbacadmin\models\AdminUsersLoginLog */

use meshzp\rbacadmin\components\PermHtml as Html;
use integready\pagesize\PageSize;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\widgets\Pjax;

$logDataProvider = new ActiveDataProvider([
    'query' => $logModel,
]);

try{
    $gridView = GridView::widget([
        'tableOptions' => ['class' => 'table table-striped'],
        'dataProvider' => $logDataProvider,
        'filterSelector' => 'select[name="per-page"]',
        'columns' => [
            'ip:text',
            'date_attempted:datetime',
            [
                'attribute' => 'login_type',
                'value' => 'loginTypeLabel',
            ],
            [
                'attribute' => 'is_successful',
                'value' => 'loginSuccessfulLabel',
            ],
        ],
    ]);
} catch (\Exception $e){
    $gridView = '<div class="alert alert-danger">' . Yii::t('perm', 'perm-gridview-error'). '</div>';
}

try{
    $pageSize = PageSize::widget(['label' => Yii::t('perm', 'rows-on-page')]);
} catch (\Exception $e){
    $pageSize = '';
}

$this->title = Yii::t('perm', 'perm-login-history');
?>
<div class="grid-container">
    <div class="row">
        <div class="col-xs-12">
            <h1><?= Html::encode($this->title) ?></h1>

            <p><?=Yii::t('perm', 'perm-login-history-desc');?></p>

            <div class="perm-widget perm-widget_cabinet">
                <div class="col-xs-16">
                    <?php Pjax::begin(); ?>
                    <?= $gridView; ?>
                    <?= $pageSize; ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
