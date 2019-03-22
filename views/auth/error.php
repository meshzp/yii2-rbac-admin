<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use meshzp\rbacadmin\components\PermHtml as Html;

$this->title = $name;
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>

    <p>
        <?= Yii::t('perm', 'perm-server-error') ?>
    </p>
    <p>
        <?= Yii::t('perm', 'perm-contact-server-error') ?>
    </p>

</div>
