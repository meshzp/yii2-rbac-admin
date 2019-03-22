<?php

namespace meshzp\rbacadmin;

use yii\web\AssetBundle;

/**
 * Permission Change Asset
 *
 */
class PermChangeAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@vendor/meshzp/yii2-rbac-admin/assets';
    /**
     * @inheritdoc
     */
    public $css = [
        'main.css',
        'bootstrap-switch.css',
        'thumbler-switch.css'
    ];
    /**
     * @inheritdoc
     */
    public $js = [
        'bootstrap-switch.min.js',
        'control_actions.js',
        'thumbler_switch.js',
        'permission_switch.js'
//        'two_factor.js'
    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
