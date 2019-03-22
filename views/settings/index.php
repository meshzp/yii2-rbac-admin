<?php

use yii\helpers\Html;

/**
 * @var $this yii\web\View
 */

$this->title                   = Yii::t('perm', 'perm-basic-settings');
$this->params['breadcrumbs'][] = $this->title;

$settings = Yii::$app->settings;
?>

<div class="site-index">
    <h1><?= $this->title ?></h1>

    <div class="bs-block">
        <h2><?= Yii::t('perm', 'perm-basic-settings-pass-secure') ?></h2>
        <p><?= Yii::t('perm', 'perm-basic-settings-pass-text') ?></p>
        <ul class="list-group">
            <li class="list-group-item">
                <?= Yii::t('perm', 'perm-basic-settings-ip') ?>
                <strong><?= $settings->get('login.limit_authorize_for_single_ip') ? Yii::t('perm', 'perm-word-yes') : Yii::t('perm', 'perm-word-no'); ?></strong>
                <?= Html::a(Yii::t('perm', 'Change'), ['/rbacadmin/settings/update', 'id' => 3]) ?>
            </li>
            <li class="list-group-item">
                <?= Yii::t('perm', 'perm-basic-settings-need-change-pass') ?>
                <strong><?= $settings->get('login.user_must_change_pass_on_first_login') ? Yii::t('perm', 'perm-word-yes') : Yii::t('perm', 'perm-word-no'); ?></strong>
                <?= Html::a(Yii::t('perm', 'Change'), ['/rbacadmin/settings/update', 'id' => 4]) ?>
            </li>
            <li class="list-group-item">
                <?= Yii::t('perm', 'perm-basic-settings-period-pass-expire') ?>
                <strong><?= $settings->get('login.password_expiration_time') ?></strong>
                <?= Html::a(Yii::t('perm', 'Change'), ['/rbacadmin/settings/update', 'id' => 5]) ?>
            </li>
        </ul>
    </div>
</div>
