<?php

namespace meshzp\rbacadmin\controllers;

use yii\web\Controller;

/**
 * Default RBACADMIN Module Controller
 *
 * @author Artem Khodos <artemkhodos@gmail.com>
 * @since 1.0
 */
class DefaultController extends Controller
{

    /**
     * Default RBACADMIN Module Action
     */
    public function actionIndex()
    {
        return var_dump($this->getUniqueId());
    }
}
