<?php

namespace meshzp\rbacadmin\controllers;

use meshzp\rbacadmin\components\rbac\PermAccessControl as AccessControl;
use meshzp\rbacadmin\models\AdminUser;
use meshzp\rbacadmin\models\AdminUsersLoginLog;
use meshzp\rbacadmin\models\TwoFactorAuthForm;
use Yii;
use yii\web\Controller;

/**
 * Security Control Controller (performs actions and showing sections of Security Page)
 */
class SecurityController extends Controller
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
     * Action to show main Security Page
     *
     * @return string Security Page
     */
    public function actionIndex()
    {
        $logModel                     = AdminUsersLoginLog::findByUserId(Yii::$app->user->identity->getId());
        $changePasswordForm           = new TwoFactorAuthForm(['scenario' => TwoFactorAuthForm::SCENARIO_CHANGE_PASS]);
        $changePasswordForm->username = Yii::$app->user->identity->username;
        if (Yii::$app->request->isPost) {
            if ($changePasswordForm->load(Yii::$app->request->post())) {
                $changePasswordForm->changePassword();
            }
        }

        return $this->render('index', [
            'logModel'           => $logModel,
            'changePasswordForm' => $changePasswordForm,
        ]);
    }

    /**
     * Action to show Security Password-Change Page
     *
     * @return string Password-Change Page
     */
    public function actionForcePassChange()
    {
        // Если пользователь уже менял пароль - то данная форма ему не доступна.
        $user = AdminUser::findOne(['id' => Yii::$app->user->id]);
        if (isset($user->change_pass_date)) {
            Yii::$app->response->redirect('/rbacadmin/security/index');
        }

        $changePasswordForm           = new TwoFactorAuthForm(['scenario' => TwoFactorAuthForm::SCENARIO_FIRST_CHANGE_PASS]);
        $changePasswordForm->username = Yii::$app->user->identity->username;
        if (Yii::$app->request->isPost) {
            if ($changePasswordForm->load(Yii::$app->request->post())) {
                $changePasswordForm->firstChangePassword();
            }
        }

        return $this->render('force_pass_change', [
            'changePasswordForm' => $changePasswordForm,
        ]);
    }

    /**
     * Action to show current user Login History Page
     *
     * @return string Login History Page
     */
    public function actionLoginHistory()
    {
        $logModel = AdminUsersLoginLog::findByUserId(Yii::$app->user->identity->getId());

        return $this->render('login_history', [
            'logModel' => $logModel,
        ]);
    }

    /**
     * Action to show page of beginning two-factor authentication procedure
     *
     * @return string Two-Factor Authentication Intro Page
     */
    public function action2faIntro()
    {
        return $this->render('two_factor_authentication/intro');
    }

    /**
     * Action to show page of performing two-factor authentication procedure
     *
     * @return string Two-Factor Authentication App Page
     */
    public function action2faApp()
    {
        $enable2faModel = new TwoFactorAuthForm(['scenario' => TwoFactorAuthForm::SCENARIO_ENABLE]);

        if ($enable2faModel->load(Yii::$app->request->post())) {
            if ($enable2faModel->enableTwoFactorAuth()) {
                Yii::$app->session->setFlash('success', Yii::t('perm', 'perm-two-factor-auth-enabled'));

                return Yii::$app->response->redirect(['/rbacadmin/security/2fa-configure']);
            }
        } else {
            $enable2faModel->generateRandomSecretCode();
        }

        return $this->render('two_factor_authentication/app', [
            'model' => $enable2faModel,
        ]);
    }

    /**
     * Action to show page of two-factor authentication settings
     *
     * @return string Two-Factor Authentication Configure Page
     */
    public function action2faConfigure()
    {
        return $this->render('two_factor_authentication/configure');
    }

    /**
     * Action to disable two-factor authentication
     *
     * @return string Security Page
     */
    public function action2faDisable()
    {
        $disable2faModel = new TwoFactorAuthForm(['scenario' => TwoFactorAuthForm::SCENARIO_DISABLE]);
        $disable2faModel->disableTwoFactorAuth();

        return $this->redirect(['/rbacadmin/security/index']);
    }
}
