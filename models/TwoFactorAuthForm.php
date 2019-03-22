<?php

namespace meshzp\rbacadmin\models;

use Google\Authenticator\GoogleAuthenticator;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;

/**
 * Confirmation code request form
 */
class TwoFactorAuthForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;
    public $auth_code;
    public $mobile;
    public $email;
    public $recovery_code;

    public $new_password;
    public $confirm_password;

    /**
     * @var \meshzp\rbacadmin\models\AdminUser|null
     */
    private $_user;

    const SESS_KEY_NAME_SECRET_CODE = '_otp_secret_code';
    const SESS_KEY_NAME_USERNAME    = '_otp_username';

    const SCENARIO_ENABLE            = 'enable';
    const SCENARIO_DISABLE           = 'disable';
    const SCENARIO_LOGIN             = 'login';
    const SCENARIO_LOGIN_2FA         = 'login_2fa';
    const SCENARIO_ACTION_CONFIRM    = 'action_confirm';
    const SCENARIO_CHANGE_PASS       = 'change_pass';
    const SCENARIO_FIRST_CHANGE_PASS = 'first_change_pass';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password', 'auth_code', 'mobile', 'recovery_code', 'confirm_password', 'new_password'], 'required'],

            ['password', 'validatePassword'],

            ['rememberMe', 'boolean'],

            ['auth_code', 'filter', 'filter' => 'trim'],
            ['auth_code', 'string', 'length' => 6],
            ['auth_code', 'integer', 'min' => 0, 'max' => 999999],
            ['auth_code', 'validateAuthCode'],

            ['mobile', 'filter', 'filter' => 'trim'],
            ['mobile', 'required'],
            ['mobile', 'unique', 'targetClass' => AdminUser::className(), 'message' => 'This mobile has already been taken.'],
            ['mobile', 'match', 'pattern' => '/^\+\d{11,14}$/', 'message' => 'Your mobile can only contain numeric characters with plus character ahead.'],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'unique', 'targetClass' => AdminUser::className(), 'message' => 'This email has already been taken.'],
            ['email', 'email'],

            ['recovery_code', 'filter', 'filter' => 'trim'],
            ['recovery_code', 'string', 'length' => 9],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_ENABLE            => ['auth_code'],
            self::SCENARIO_DISABLE           => [],
            self::SCENARIO_LOGIN             => ['username', 'password', 'rememberMe'],
            self::SCENARIO_LOGIN_2FA         => ['auth_code'],
            self::SCENARIO_CHANGE_PASS       => ['password', 'new_password', 'confirm_password', 'username'],
            self::SCENARIO_FIRST_CHANGE_PASS => ['password', 'new_password', 'confirm_password', 'username', 'mobile', 'email'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'auth_code'        => Yii::t('perm', 'perm-authentication-code'),
            'mobile'           => Yii::t('perm', 'perm-mobile'),
            'mobile_cc'        => Yii::t('perm', 'perm-mobile-confirm-code'),
            'username'         => Yii::t('perm', 'perm-username'),
            'password'         => Yii::t('perm', 'perm-password'),
            'rememberMe'       => Yii::t('perm', 'perm-remember-me'),
            'recovery_code'    => Yii::t('perm', 'perm-recovery-code'),
            'new_password'     => Yii::t('perm', 'perm-new-password'),
            'confirm_password' => Yii::t('perm', 'perm-confirm-password'),
        ];
    }

    public function generateRandomSecretCode()
    {
        $ga = new GoogleAuthenticator();
        Yii::$app->session->set(self::SESS_KEY_NAME_SECRET_CODE, $ga->generateSecret());
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $this->getUser();
            Yii::$app->session->set(self::SESS_KEY_NAME_USERNAME, $this->username);
            if ($this->_user->auth_type == AdminUser::AUTHTYPE_SIMPLE) {
                $this->logLoginAttempt(AdminUser::AUTHTYPE_SIMPLE, 1);
                Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);

                return true;
            } else {
                Yii::$app->response->redirect(['/rbacadmin/auth/two-factor']);

                return false;
            }
        } else {
            $this->logLoginAttempt(AdminUser::AUTHTYPE_SIMPLE);

            return false;
        }
    }

    /**
     * @return \meshzp\rbacadmin\models\AdminUsersSettings|null the saved model or null if saving fails
     */
    public function enableTwoFactorAuth()
    {
        if ($this->validate()) {
            $this->username = Yii::$app->user->identity->username;
            $this->getUser();
            $userSettings = AdminUsersSettings::findOne(Yii::$app->user->getId());
            if ($userSettings === null) {
                $userSettings          = new AdminUsersSettings();
                $userSettings->user_id = Yii::$app->user->getId();
            }
            $userSettings->security_recovery_codes_alert = 1;
            $userSettings->security_secret_code          = Yii::$app->session->get(self::SESS_KEY_NAME_SECRET_CODE);
            if ($userSettings->save()) {
                Yii::$app->user->identity->auth_type = AdminUser::AUTHTYPE_APP;
                $result                              = Yii::$app->user->identity->save();

                if ($result) {
                    if (!empty(Yii::$app->user->identity->email)) {
                        $this->sendEnableEmail();
                    }
                }

                return $userSettings;
            }
        }

        return null;
    }

    /**
     * Disable two-factor authentication
     *
     * @return bool
     */
    public function disableTwoFactorAuth()
    {
        $result = false;
        if ($this->validate()) {
            $this->username = Yii::$app->user->identity->username;
            $this->getUser();
            $this->_user->scenario  = AdminUser::SCENARIO_CHANGE_AUTH_TYPE;
            $this->_user->auth_type = AdminUser::AUTHTYPE_SIMPLE;
            $result                 = $this->_user->save();

            if ($result) {
                $this->sendDisableEmail();
            }
        }

        return $result;
    }

    /**
     * Process two-factor authentication
     *
     * @return bool
     */
    public function authenticate()
    {
        $this->getUser();
        $auth_type = $this->_user->auth_type;

        if ($this->validate()) {
            $this->logLoginAttempt($auth_type, 1);
            Yii::$app->session->remove(self::SESS_KEY_NAME_USERNAME);

            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            $this->logLoginAttempt($auth_type);

            return false;
        }
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     */
    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Validates the auth code.
     * This method serves as the inline validation for auth code.
     *
     * @param string $attribute the attribute currently being validated
     */
    public function validateAuthCode($attribute)
    {
        $this->getUser();
        if ($this->scenario == self::SCENARIO_LOGIN_2FA) {
            switch ($this->_user->auth_type) {
                case AdminUser::AUTHTYPE_APP:
                    $ga     = new GoogleAuthenticator();
                    $result = $ga->checkCode($this->_user->usersSettings->security_secret_code, $this->{$attribute});
                    break;
                default:
                    $result = false;
            }
        } elseif ($this->scenario == self::SCENARIO_ENABLE) {
            $ga     = new GoogleAuthenticator();
            $result = $ga->checkCode(Yii::$app->session->get(self::SESS_KEY_NAME_SECRET_CODE), $this->{$attribute});
        } else {
            $result = false;
        }

        if (!$result) {
            $this->addError($attribute, 'Authentication code is incorrect.');
        }
    }

    /**
     * Sends an email, for notifying user about enable two-factor authentication.
     *
     * @return boolean whether the email was send
     */
    private function sendEnableEmail()
    {
        $bResult = \Yii::$app->mailer->compose(['html' => '@rbacadmin/mail/two_factor_authentication/enabled-html', 'text' => '@rbacadmin/mail/two_factor_authentication/enabled-text'], ['user' => $this->_user])
            ->setFrom([\Yii::$app->params['noreplyEmail'] => \Yii::$app->name . ' robot'])
            ->setTo($this->_user->email)
            ->setSubject(\Yii::$app->name . ' - Two-factor authentication enabled')
            ->send();

        return $bResult;
    }

    /**
     * Sends an email, for notifying user about disable two-factor authentication.
     *
     * @return boolean whether the email was send
     */
    private function sendDisableEmail()
    {
        $bResult = \Yii::$app->mailer->compose(['html' => '@rbacadmin/mail/two_factor_authentication/disabled-html', 'text' => '@rbacadmin/mail/two_factor_authentication/disabled-text'], ['user' => $this->_user])
            ->setFrom([\Yii::$app->params['noreplyEmail'] => \Yii::$app->name . ' robot'])
            ->setTo($this->_user->email)
            ->setSubject(\Yii::$app->name . ' - Two-factor authentication disabled')
            ->send();

        return $bResult;
    }

    /**
     * Logs a login attempt if username exists
     *
     * @param int $login_type
     * @param int $is_successful
     *
     * @return bool
     */
    private function logLoginAttempt($login_type, $is_successful = 0)
    {
        if ($this->_user !== null) {
            $log                = new AdminUsersLoginLog();
            $log->user_id       = $this->_user->getId();
            $log->ip            = Yii::$app->request->userIP;
            $log->login_type    = $login_type;
            $log->is_successful = $is_successful;

            return $log->save();
        }

        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return \meshzp\rbacadmin\models\AdminUser|null
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $username = Yii::$app->session->get(self::SESS_KEY_NAME_USERNAME);
            if (empty($this->username) && !empty($username)) {
                $this->username = $username;
            }
            $this->_user = AdminUser::findByUsername($this->username);
        }

        return $this->_user;
    }

    /**
     * Changes registered user password.
     *
     * @return bool
     */
    public function changePassword()
    {
        $this->getUser();
        if ($this->validate()) {
            try {
                $this->_user->setPassword($this->new_password);
                $this->_user->generateAuthKey();
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());

                return false;
            }
            if ($this->_user->save(false)) {
                Yii::$app->session->setFlash('success', Yii::t('perm', 'security.pass-change-message'));
                if (!empty(Yii::$app->user->identity->email)) {
                    try {
                        return $this->sendChangePasswordNotification();
                    } catch (\Exception $e){
                        Yii::$app->session->setFlash('error', $e->getMessage());
                    }
                } else {
                    return true;
                }
            } else {
                Yii::$app->session->setFlash('danger', Yii::t('perm', 'security.pass-change-error-message'));
            }
        }

        return false;
    }

    /**
     * Changes registered user password.
     *
     * @return bool
     */
    public function firstChangePassword()
    {
        $this->getUser();
        if ($this->validate()) {
            try {
                $this->_user->setPassword($this->new_password);
                $this->_user->generateAuthKey();
                $this->_user->email            = $this->email;
                $this->_user->mobile           = $this->mobile;
                $this->_user->change_pass_date = date('Y-m-d H:i:s');
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());

                return false;
            }

            if ($this->_user->save(false)) {
                Yii::$app->session->setFlash('success', Yii::t('perm', 'security.pass-change-message'));
                if (!empty(Yii::$app->user->identity->email)) {
                    try {
                        $this->sendChangePasswordNotification();
                    } catch (\Exception $e){
                        Yii::$app->session->setFlash('error', $e->getMessage());
                    }
                }

                Yii::$app->response->redirect('/rbacadmin/security/index');
            } else {
                Yii::$app->session->setFlash('danger', Yii::t('perm', 'security.pass-change-error-message'));
            }
        }

        return false;
    }

    /**
     * Sends notification about password change.
     *
     * @return boolean whether the email was sent
     * @throws InvalidConfigException
     */
    public function sendChangePasswordNotification()
    {
        $time = Yii::$app->formatter->asDatetime(time(), 'php: Y-m-d H:i:s');
        if ($this->_user) {
            return \Yii::$app->mailer->compose(['html' => '@rbacadmin/mail/passwordChangedNotification-html', 'text' => '@rbacadmin/mail/passwordChangedNotification-text'], ['user' => $this->_user, 'time' => $time])
                ->setFrom([\Yii::$app->params['noreplyEmail'] => \Yii::$app->name . ' robot'])
                ->setTo($this->_user->email)
                ->setSubject(\Yii::$app->name . ' - ' . Yii::t('perm', 'perm-pass-changed'))
                ->send();
        }

        return false;
    }
}
