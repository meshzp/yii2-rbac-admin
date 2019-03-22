<?php

namespace meshzp\rbacadmin\components;

use meshzp\rbacadmin\components\rbac\PermAccessControl as AccessControl;
use meshzp\rbacadmin\models\ControlPermissions;
use Yii;
use yii\base\Module;
use yii\db\Expression;
use yii\helpers\Inflector;

/**
 * ControlPermissionController implements the Permissions Manager.
 */
class ControlPermission
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
            ],
        ];
    }

    /**
     * Refresh table of Auth Items.
     *
     * @param array $aPredefined
     *
     * @return mixed
     */
    public function refreshAuthItemBase($aPredefined)
    {
        $result = [];
        $this->getAuthItems(Yii::$app, $result);
        $localPermissions = $result = array_merge($result, $this->getPreDefinedItems($aPredefined));
        $baseItems        = ControlPermissions::getItemNamesArray();
        $localItems       = array_keys($localPermissions);
        $itemsToRemove    = array_diff($baseItems, $localItems);
        $itemsToAdd       = array_diff($localItems, $baseItems);
        foreach ($localPermissions as $k => $item) {
            if (!in_array($k, $itemsToAdd)) {
                unset($localPermissions[$k]);
            }
        };
        if (ControlPermissions::removeItems($itemsToRemove)) {
            ControlPermissions::addItems($localPermissions);
        }

        return $result;
    }

    /**
     * Renew Permissions in DB (delete removed controllers/actons and add new controllers/actions)
     * This method uses list of predefined permissions, that we must add in any way
     *
     * @param array $aPredefined
     *
     * @throws \yii\db\Exception
     */
    public function renewAuthItemBase($aPredefined)
    {
        $result = $this->refreshAuthItemBase($aPredefined);
        if ($result) {
            foreach ($result as $k => $v) {
                $permission = ControlPermissions::find()->where(['name' => $k, 'description' => $v['description'], 'controller' => $v['controller']])->exists();
                if (!$permission) {
                    $permission = ControlPermissions::find()->where(['name' => $k])->exists();
                    if ($permission) {
                        $tableName = new Expression(Yii::$app->getModule('rbacadmin')->authItemTable);
                        Yii::$app->db->createCommand("UPDATE {$tableName} SET description = '{$v['description']}', controller = '{$v['controller']}' WHERE name='" . $k . "'")->execute();
                    }
                }
            }
        }
    }

    /**
     * Get route(s) + permissions (from code)
     *
     * @param \yii\base\Module $module
     * @param array $result
     * @param integer $is_child
     */
    private function getAuthItems($module, &$result, $is_child = 0)
    {
        try {

            foreach ($module->getModules() as $id => $child) {
                if (($child = $module->getModule($id)) !== null) {
                    $this->getAuthItems($child, $result, 1);
                }
            }

            $namespace = trim($module->controllerNamespace, '\\') . '\\';
            $this->getControllerFiles($module, $namespace, '', $result, $is_child);
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
    }

    /**
     * Get permissions from config
     *
     * @param array $aPredefined
     *
     * @return array
     */
    private function getPreDefinedItems($aPredefined)
    {
        $result = [];
        if (isset($aPredefined) && !empty($aPredefined)) {
            foreach ($aPredefined as $k => $v) {
                $result[trim($k)] = [
                    'type'        => ControlPermissions::TYPE_PERMISSION,
                    'description' => $v,
                    'controller'  => 'predefinedPermissions',
                ];
            }
        }

        return $result;
    }

    /**
     * Get list controller under module
     *
     * @param \yii\base\Module $module
     * @param string $namespace
     * @param string $prefix
     * @param mixed $result
     * @param integer $is_child
     *
     * @return void
     */
    private function getControllerFiles($module, $namespace, $prefix, &$result, $is_child)
    {
        //Получаем path по napespace
        try {
            $path = Yii::getAlias('@' . str_replace('\\', '/', $namespace));
            if (!is_dir($path)) {
                return;
            }
            //Перебираем все файлы в папке
            foreach (scandir($path) as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($path . '/' . $file)) {
                    $this->getControllerFiles($module, $namespace . $file . '\\', $prefix . $file . '/', $result, $is_child);
                    //проверяем, есть ли оконцание у файла "....Controller.php", т.е. является ли контроллером
                } elseif (strcmp(substr($file, -14), 'Controller.php') === 0) {
                    //ID = результат преобразования названия файлов в названия контроллеров с camelCase > роут
                    $id = Inflector::camel2id(substr(basename($file), 0, -14));
                    //className = результат обратного преобразования роут > camelCase + 'Controller'
                    $className  = $namespace . Inflector::id2camel($id) . 'Controller';
                    $hiddenFlag = $this->checkIfControllerIsHidden($className);
                    if (!$hiddenFlag) {
                        $result[($is_child ? '/' . $module->id : '') . '/' . $id . '/*'] = [
                            'type'        => ControlPermissions::TYPE_CONTROLLER,
                            'description' => $this->getControllerPhpDoc($className),
                            'controller'  => ($is_child ? '/' . $module->id : '') . '/' . $id,
                        ];

                        $permissions = $this->getPermissionsFromController($className, $id, $module, $is_child);
                        if (!empty($permissions)) {
                            foreach ($permissions as $permission) {
                                $result += $permission;
                            }
                        }

                        if (strpos($className, '-') === false && class_exists($className) && is_subclass_of($className, 'yii\base\Controller')) {
                            //Получаем список всех экшнов и ложим в result
                            $this->getControllerActions($className, $prefix . $id, $module, $result);
                        }
                    }
                }
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
    }

    /**
     * Get list action of controller
     *
     * @param mixed $type
     * @param string $id
     * @param \yii\base\Module $module
     * @param string|array $result
     */
    private function getControllerActions($type, $id, $module, &$result)
    {
        try {
            /* @var $controller \yii\base\Controller */
            $controller = Yii::createObject($type, [$id, $module]);
            //Получаем массив экшнов конкретного контроллера и ложим в $result
            $this->getActionRoutes($controller, $result);
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
    }

    /**
     * Get route of action
     *
     * @param \yii\base\Controller $controller
     * @param array $result all controller action.
     */
    private function getActionRoutes($controller, &$result)
    {
        try {
            $prefix = '/' . $controller->uniqueId . '/';
            $class  = new \ReflectionClass($controller);
            foreach ($class->getMethods() as $method) {
                $name = $method->getName();
                if ($method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0 && $name !== 'actions') {
                    //Заполняется массив роутов
                    $result[$prefix . Inflector::camel2id(substr($name, 6))] = [
                        'type'        => ControlPermissions::TYPE_ACTION,
                        'description' => $this->getActionPhpDoc($method),
                        'controller'  => '/' . $controller->getUniqueId(),
                    ];
                }
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
    }

    /**
     * Get php-doc description for selected controller by controller name
     * (it gets only the second line of php-doc comment)
     *
     * @param string $controllerName
     *
     * @return string
     */
    private function getControllerPhpDoc($controllerName)
    {
        $reflection = new \ReflectionClass($controllerName);
        $phpDoc     = $reflection->getDocComment();
        $docLines   = preg_split('~\R~u', $phpDoc);

        return isset($docLines[1]) ? trim($docLines[1], "\t *") : '';
    }

    /**
     * Get php-doc description for selected action by action name
     * (it gets only the second line of php-doc comment)
     *
     * @param \ReflectionMethod $action
     *
     * @return string
     */
    private function getActionPhpDoc($action)
    {
        $docLines = preg_split('~\R~u', $action->getDocComment());

        return isset($docLines[1]) ? trim($docLines[1], "\t *") : '';
    }

    /**
     * Get all strings of php-doc started from PHPDOC_PERMISSION_IDENTIFIER (@permission '<permission_name>' <permission_info>)
     * for selected controller and retunrs this list
     *
     * @param string $controllerName name of controller
     * @param string $id of controller
     * @param Module $module used module
     * @param integer $is_child flag (true if is child of another)
     *
     * @return array
     */
    private function getPermissionsFromController($controllerName, $id, $module, $is_child)
    {
        $aPermissions = [];
        $r            = new \ReflectionClass($controllerName);
        $phpDoc       = $r->getDocComment();
        $docLines     = preg_split('~\R~u', $phpDoc);
        foreach ($docLines as $docLine) {
            $aDocLine = explode(ControlPermissions::PHPDOC_PERMISSION_IDENTIFIER, $docLine);
            if (isset($aDocLine[1])) {
                $permission = explode("'", trim($aDocLine[1]), 3);
                if (isset($permission[2])) {
                    $aPermissions[] = [
                        $permission[1] => [
                            'type'        => ControlPermissions::TYPE_PERMISSION,
                            'description' => $permission[2],
                            'controller'  => ($is_child ? '/' . $module->id : '') . '/' . $id,
                        ],
                    ];
                }
            }
        }

        return $aPermissions;
    }

    /**
     * Make a verification if current controller is hidden for processing by rbacadmin
     * Method check controller's php-doc line PHPDOC_HIDDEN_CONTROLLER_IDENTIFIER (@hide-controller)
     *
     * @param string $controllerName
     *
     * @return int
     */
    private function checkIfControllerIsHidden($controllerName)
    {
        $reflection = new \ReflectionClass($controllerName);
        $phpDoc     = $reflection->getDocComment();
        $docLines   = preg_split('~\R~u', $phpDoc);
        foreach ($docLines as $docLine) {
            $aDocLine = explode(ControlPermissions::PHPDOC_HIDDEN_CONTROLLER_IDENTIFIER, $docLine);
            if (isset($aDocLine[1])) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * Grant all permissions of module to selected user
     *
     * @param \yii\base\Module $module
     * @param integer $user_id
     * @param integer $isChild
     *
     * @return bool
     */
    public function grantClearModulePermissionsToUser($module, $user_id, $isChild = 1)
    {
        $result = [];
        $this->getAuthItems($module, $result, $isChild);
        if (empty($result) || !ControlPermissions::addItems($result) || !ControlPermissions::grantAllPermissionsToUser($result, $user_id)) {
            return false;
        }

        return true;
    }
}
