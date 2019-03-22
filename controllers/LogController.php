<?php

namespace meshzp\rbacadmin\controllers;

use meshzp\rbacadmin\components\rbac\PermAccessControl as AccessControl;
use meshzp\rbacadmin\models\AdminRequestLogSearch;
use Yii;
use yii\web\Controller;

/**
 * Log Controller of RBACADMIN module (performs log managing section of module)
 *
 */
class LogController extends Controller
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
     * Action to show Two-Factor Authorization Page
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel  = new AdminRequestLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }
}
