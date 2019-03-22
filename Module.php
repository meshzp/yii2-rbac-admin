<?php

namespace meshzp\rbacadmin;

use meshzp\rbacadmin\models\AdminUser;
use meshzp\rbacadmin\models\AdminUsersLoginLog;
use meshzp\rbacadmin\models\ControlUserSessions;
use Yii;
use yii\base\BootstrapInterface;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Application;
use yii\web\Controller;
use yii\web\User;

/**
 * PERMISSION manager for RBAC.
 *
 * @property string $userTable
 * @property string $authItemTable
 * @property string $authItemRelationsTable
 * @property array $predefinedPermissions
 * @property array $allowToLogged
 * @property array $allowToAll
 * @property array $allowToGuest
 * @property int   $cache_duration
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    public $userTable              = '{{%perm_users}}';
    public $userLogTable           = '{{%perm_users_login_log}}';
    public $userRequestLogTable    = '{{%perm_request_log}}';
    public $userSessionTable       = '{{%perm_user_sessions}}';
    public $userSettingsTable      = '{{%perm_users_settings}}';
    public $authItemTable          = '{{%perm_auth_item}}';
    public $authItemRelationsTable = '{{%perm_auth_item_relations}}';
    public $settingsTable          = '{{%perm_settings}}';

    public $predefinedPermissions;

    const CACHE_TAG = 'RBACADMIN';

    // Разрешенные роуты по умолчанию всем
    public $allowToAll = [];

    // Разрешенные роуты по умолчанию только залогиненым
    public $allowToLogged = [];

    // Разрешенные роуты по умолчанию только гостям
    public $allowToGuest = [];

    // Cache Duration
    public $cache_duration = 3600;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::setAlias('@rbacadmin', '@vendor/meshzp/yii2-rbac-admin');
        if (!isset(Yii::$app->i18n->translations['perm'])) {
            Yii::$app->i18n->translations['perm'] = [
                'class'          => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en',
                'basePath'       => $this->getBasePath() . '/messages',
            ];
        }

        $this->controllerMap['create'] = $this->getNamespace() . '\commands\InitController';

        $userClass = ArrayHelper::getValue(Yii::$app->components, 'user.identityClass');
        if ($this->defaultRoute === 'default' && $userClass && is_subclass_of($userClass, 'yii\db\BaseActiveRecord')) {
            $this->defaultRoute = 'control';
        }
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        //Event listener for saving log into base when user acts on the site
        $app->on(Controller::EVENT_BEFORE_ACTION, [$this->getNamespace() . '\models\AdminRequestLog', 'LogRequest']);

        //Event listener for saving session_id into base when user logins
        $app->user->on(User::EVENT_AFTER_LOGIN, [$this->getNamespace() . '\models\AdminUser', 'afterLogin']);

        //Event listener for removing session_id from base when user logouts
        $app->user->on(User::EVENT_BEFORE_LOGOUT, [$this->getNamespace() . '\models\AdminUser', 'beforeLogout']);

        //Logout user if no session found in base
        if (Yii::$app->has('user', true) && !Yii::$app->user->isGuest && is_null(ControlUserSessions::findOne(['user_id' => Yii::$app->user->getId(), 'session_id' => Yii::$app->session->getId()]))) {
            Yii::$app->user->logout();
        }

        //Logout user if user is blocked in config
        if (Yii::$app->has('user', true) && !Yii::$app->user->isGuest) {
            $identityClass= ("\\" . get_class(Yii::$app->user->identity));
            /** @var $identityClass ActiveRecord */
            /** @var $user AdminUser */
            $user = $identityClass::findOne(['id' => Yii::$app->user->getId()]);
            if (!$user || !$user->canLogin()) {
                Yii::$app->user->logout();
            }
        }

        // Требовать смены пароля при первом входе
        if (Yii::$app instanceof Application && Yii::$app->settings->get('login.user_must_change_pass_on_first_login')) {
            if (Yii::$app->has('user') && !Yii::$app->user->isGuest) {
                // Если у пользователя нет даты смена пароля - значит он его не менял :)
                $identityClass= ("\\" . get_class(Yii::$app->user->identity));
                /**
                 * @var $identityClass ActiveRecord
                 */
                $user = $identityClass::findOne(['id' => Yii::$app->user->id]);
                if (!isset($user->change_pass_date)) {
                    Yii::$app->getSession()->setFlash('error', Yii::t('perm', 'perm-first-time-pass-change-message'));
                    if (!stripos(Yii::$app->request->url, '/rbacadmin/security/force-pass-change')) {
                        Yii::$app->response->redirect('/rbacadmin/security/force-pass-change');
                    }
                } elseif (time() - strtotime($user->change_pass_date) > Yii::$app->settings->get('login.password_expiration_time') * 24 * 60 * 60) {
                    Yii::$app->getSession()->setFlash('error', Yii::t('perm', 'perm-password-expire-message', ['day_count' => Yii::$app->settings->get('login.password_expiration_time')]) . ' ' . Html::a(Yii::t('perm', 'perm-change-pass'), '/rbacadmin/security/index'));
                }
            }
        }

        // Установить контроль рабочих сессий по IP-адресам
        if (Yii::$app->settings->get('login.limit_authorize_for_single_ip')) {
            if (Yii::$app->has('user') && !Yii::$app->user->isGuest) {
                /** @var AdminUsersLoginLog $loginLog */
                $loginLog = AdminUsersLoginLog::find()
                    ->where(['user_id' => Yii::$app->user->id])
                    ->orderBy('date_attempted DESC')
                    ->one();

                if ($loginLog && Yii::$app->request->getUserIP() != $loginLog->ip) {
                    Yii::$app->user->logout();
                    Yii::$app->response->redirect('/');
                }
            }
        }
    }

    /**
     * Gets namespace of current module
     *
     * @return string
     * @throws \ReflectionException
     */
    private function getNamespace()
    {
        $reflection = new \ReflectionClass($this);

        return $reflection->getNamespaceName();
    }
}
