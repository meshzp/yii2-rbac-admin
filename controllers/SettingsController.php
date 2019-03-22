<?php

namespace meshzp\rbacadmin\controllers;

use meshzp\rbacadmin\components\rbac\PermAccessControl as AccessControl;
use meshzp\rbacadmin\models\Setting;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Main Settings Section Controller (as a part of RBACADMIN module)
 */
class SettingsController extends Controller
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
     * Action to show main Settings Page
     *
     * @return mixed Settings Page
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Action to update existing setting
     *
     * @param integer $id option_id
     *
     * @return mixed Update Page
     */
    public function actionUpdate($id)
    {
        /**
         * @var $model Setting
         */
        try {
            $model = $this->findModel($id);
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['/rbacadmin/settings/index']);
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->render(
            'update',
            [
                'model' => $model,
            ]
        );
    }

    /**
     * Finds a Setting model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return Setting the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Setting::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
