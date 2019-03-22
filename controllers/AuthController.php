<?php

namespace meshzp\rbacadmin\controllers;

use meshzp\rbacadmin\components\rbac\PermAccessControl as AccessControl;
use meshzp\rbacadmin\models\TwoFactorAuthForm;
use Yii;
use yii\web\Controller;

/**
 * Authentification Controller of RBACADMIN module (performs authentification actions of module)
 *
 * @hide_controller
 */
class AuthController extends Controller
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
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Action to show Two-Factor Authorization Page
     *
     * @return mixed
     */
    public function actionTwoFactor()
    {
        $model = new TwoFactorAuthForm(['scenario' => TwoFactorAuthForm::SCENARIO_LOGIN_2FA]);
        if ($model->load(Yii::$app->request->post()) && $model->authenticate()) {
            return $this->goBack();
        } else {
            $user = $model->getUser();

            return $this->render('two_factor', [
                'model' => $model,
                'user'  => $user,
            ]);
        }
    }
}
