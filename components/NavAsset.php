<?php

namespace meshzp\rbacadmin\components;

use yii\web\AssetBundle;

/**
 * Permission Change Asset
 *
 */
class NavAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@vendor/meshzp/yii2-rbac-admin/assets';
    /**
     * @inheritdoc
     */
    public $css = [
        'nav.css',
    ];

}
