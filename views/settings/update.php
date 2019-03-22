<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var meshzp\rbacadmin\models\Setting $model
 */

$this->title = Yii::t('perm', $model->key);

$this->params['breadcrumbs'][] = ['label' => Yii::t('perm', 'perm-basic-settings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('perm', 'Update');

?>
<div class="setting-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?=
    $this->render(
        '_form',
        [
            'model' => $model,
        ]
    ) ?>

</div>
