<?php

use meshzp\rbacadmin\components\PermHtml as Html;

/**
 * @var $this yii\web\View
 * @var $model meshzp\rbacadmin\models\TwoFactorAuthForm
 */

$this->title = Yii::t('perm', 'perm-manage-authentication');
?>
<div class="grid-container">
    <div class="row">
        <div class="col-xs-12">
            <h1><?= Html::encode($this->title) ?></h1>

            <div class="perm-widget perm-widget_cabinet row">
                <div class="col-md-7" style="padding-top: 8px;">
                    <p><?= Html::tag('span', Yii::t('perm', 'perm-enabled') . '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>', ['class' => 'text-success']) ?> <?= Yii::t('perm', 'perm-authentication-enabled'); ?></p>
                    <div>
                        <?= Html::a(Yii::t('perm', 'perm-reconfigure'), ['/rbacadmin/security/2fa-intro'], ['class' => 'btn btn-default btn-sm']) ?>
                        <?= Html::a(Yii::t('perm', 'perm-disable'), ['/rbacadmin/security/2fa-disable'], ['class' => 'btn btn-default btn-sm']) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-3 col-xs-offset-1">
        </div>
    </div>
</div>
