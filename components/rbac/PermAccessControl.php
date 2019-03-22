<?php

namespace meshzp\rbacadmin\components\rbac;

use Yii;
use yii\base\ActionFilter;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\di\Instance;
use yii\web\ForbiddenHttpException;
use yii\web\User;

/**
 * Adaptation of Access filter class should be used in controller's behaviours
 * @package meshzp\rbacadmin\components\rbac
 */
class PermAccessControl extends ActionFilter
{
    /**
     * @var User User for check access.
     */
    private $_user = 'user';
    /**
     * @var array List of action that not need to check access.
     */
    public $allowActions = [];

    /**
     * Gets the user
     *
     * @return User
     * @throws InvalidConfigException if something goes wrong
     */
    public function getUser()
    {
        if (!$this->_user instanceof User) {
            $this->_user = Instance::ensure($this->_user, User::className());
        }
        return $this->_user;
    }

    /**
     * Sets the user
     *
     * @param User|string $user
     */
    public function setUser($user)
    {
        $this->_user = $user;
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $actionId = $action->getUniqueId();
        $user     = $this->getUser();
        if (Yii::$app->authManager->checkAccess($user->id, '/' . $actionId)) {
            return true;
        }
        $this->denyAccess($user, $actionId);

        return false;
    }

    /**
     * Denies the access of the user.
     * The default implementation will redirect the user to the login page if he is a guest;
     * if the user is already logged, a 403 HTTP exception will be thrown.
     *
     * @param  User|false $user the current user
     * @param  array|string $actionId the current user
     *
     * @return mixed
     * @throws ForbiddenHttpException if the user is already logged in.
     */
    protected function denyAccess($user, $actionId)
    {
        if ($user !== false && $user->getIsGuest()) {
            $user->loginRequired();
        } else {
            if ($actionId == $user->loginUrl[0]) {
                return Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl());
            }
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    /**
     * @inheritdoc
     */
    protected function isActive($action)
    {
        $uniqueId = $action->getUniqueId();
        if ($uniqueId === Yii::$app->getErrorHandler()->errorAction) {
            return false;
        }

        $user = $this->getUser();
        if ($user->getIsGuest() && is_array($user->loginUrl) && isset($user->loginUrl[0]) && $uniqueId === trim($user->loginUrl[0], '/')) {
            return false;
        }

        if ($this->owner instanceof Module) {
            // convert action uniqueId into an ID relative to the module
            $mid = $this->owner->getUniqueId();
            $id  = $uniqueId;
            if ($mid !== '' && strpos($id, $mid . '/') === 0) {
                $id = substr($id, strlen($mid) + 1);
            }
        } else {
            $id = $action->id;
        }

        foreach ($this->allowActions as $route) {
            if (substr($route, -1) === '*') {
                $route = rtrim($route, "*");
                if ($route === '' || strpos($id, $route) === 0) {
                    return false;
                }
            } else {
                if ($id === $route) {
                    return false;
                }
            }
        }
        return true;
    }
}
