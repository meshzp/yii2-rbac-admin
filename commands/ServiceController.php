<?php

namespace meshzp\rbacadmin\commands;

use yii\console\Controller;

/**
 * Special Service Controller
 */
class ServiceController extends Controller
{
    /**
     * @return bool
     * @throws \ReflectionException
     */
    public static function actionUpdatePhpdoc()
    {
        $reflectorWeb  = new \ReflectionClass('yii\web\Application');
        $sWebApp       = file_get_contents($reflectorWeb->getFileName());
        $pattern       = '/.*\@property User \$user.*/';
        $replacement   = <<<'EOR'
 * @property \meshzp\rbacadmin\models\AdminUser $user
 * @property \meshzp\rbacadmin\components\Settings $settings
EOR;
        $sWebApp       = preg_replace($pattern, $replacement, $sWebApp);
        $iUpdateResult = file_put_contents($reflectorWeb->getFileName(), $sWebApp);
        echo (($iUpdateResult === false) ? 'Error replacing yii\web\Application' : 'yii\web\Application replaced successfully') . PHP_EOL;

        $reflectorBase  = new \ReflectionClass('yii\base\Application');
        $sBaseApp       = file_get_contents($reflectorBase->getFileName());
        $pattern       = '/.*\@property \\\\yii\\\\rbac\\\\ManagerInterface \$authManager.*/';
        $replacement   = <<<'EOR'
 * @property \meshzp\rbacadmin\components\rbac\PermManager $authManager
EOR;
        $sBaseApp       = preg_replace($pattern, $replacement, $sBaseApp);
        $iUpdateResult = file_put_contents($reflectorBase->getFileName(), $sBaseApp);
        echo (($iUpdateResult === false) ? 'Error replacing yii\base\Application' : 'yii\base\Application replaced successfully') . PHP_EOL;

        return true;
    }
}
