<?php

namespace meshzp\rbacadmin\commands;

use meshzp\rbacadmin\components\ControlPermission;
use meshzp\rbacadmin\models\AdminUser;
use meshzp\rbacadmin\models\ControlPermissions;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;
use yii\web\Application;

/**
 * Permission Controller implements actions for Permissions Control Module.
 */
class InitController extends Controller
{
    /**
     * Creates admin user with config privileges grant
     *
     * @param string $username
     * @param string $password
     * @param string $alias Alias of application which permission should be granted (empty for only module permissions)
     *
     * @return bool
     * @throws \yii\base\Exception
     */
    public function actionAdmin($username, $password, $alias = '')
    {
        $baseAdmin                = new AdminUser();
        $baseAdmin->username      = $username;
        $baseAdmin->email         = "{$username}@rbac.admin";
        $baseAdmin->password_hash = Yii::$app->security->generatePasswordHash($password);
        $baseAdmin->auth_key      = Yii::$app->security->generateRandomString();
        $baseAdmin->scenario      = AdminUser::SCENARIO_CHANGE_PASS;
        $baseAdmin->description   = 'It is perm admin user with all rights enabled';
        $baseAdmin->status        = AdminUser::STATUS_ACTIVE;
        $module_to_process        = $this->module;
        if (!empty($alias) && Yii::getAlias($alias)) {
            $configs = [
                '/config/web.php',
                '/../common/config/main.php',
                '/../common/config/main-local.php',
                '/config/main.php',
                '/config/main-local.php',
            ];

            $configs = array_map(function ($conf) use ($alias) {
                return Yii::getAlias($alias) . $conf;
            }, $configs);

            $configs = array_filter($configs, function ($conf) {
                return file_exists($conf);
            });

            $result_config = [];

            foreach ($configs as $conf) {
                @$result_config = ArrayHelper::merge($result_config, require($conf));
            }

            if (!empty($result_config)) {
                $module_to_process = new Application($result_config);
            }
        }
        if ($baseAdmin->save()) {
            echo "Admin user '{$username}' has been successfully created!" . PHP_EOL;
            $controlPermission = new ControlPermission();
            $controlPermission->renewAuthItemBase($module_to_process->getModule('rbacadmin')->predefinedPermissions);
            echo "Grant all permissions/routes to '{$username}'...";
            if (
                !$controlPermission->grantClearModulePermissionsToUser($module_to_process, $baseAdmin->id, 0) ||
                !ControlPermissions::addNewUserPermission(
                    array_keys($module_to_process->getModule('rbacadmin')->predefinedPermissions)[0],
                    $baseAdmin->id,
                    ControlPermissions::ENABLED_YES
                )
            ) {
                echo PHP_EOL . 'Action corrupts! Something goes wrong when granting permissions to user!' . PHP_EOL;

                return ExitCode::UNSPECIFIED_ERROR;
            }
        } else {
            echo PHP_EOL . 'Action corrupts! Something goes wrong with creating user!' . PHP_EOL;

            return ExitCode::UNSPECIFIED_ERROR;
        }
        echo PHP_EOL . 'Action success!' . PHP_EOL;
        echo 'To start, please login in browser and follow next link: http(s)://your_site_backend/rbacadmin/control/users' . PHP_EOL;
        echo "Next, change the permissions to the '{$username}' user!" . PHP_EOL;

        return ExitCode::OK;
    }
}
