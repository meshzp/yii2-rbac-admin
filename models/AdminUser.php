<?php

namespace meshzp\rbacadmin\models;

use meshzp\rbacadmin\components\PermHtml as Html;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $auth_key
 * @property integer $status
 * @property string $mobile
 * @property string $email
 * @property integer $auth_type
 * @property string $change_pass_date
 * @property integer $group_flag
 * @property string $name
 * @property string $surname
 * @property string $patronymic
 * @property integer $sex
 * @property string $birth_date
 * @property string $start_date
 * @property string $company_position
 * @property integer $in_group
 * @property string $description
 * @property string $sip
 * @property string $date_created
 * @property string $date_updated
 * @property integer $can_get_child_info
 * @property integer $group_head_id
 *
 * @property AdminUsersSettings $usersSettings
 */
class AdminUser extends ActiveRecord implements IdentityInterface
{
    const STATUS_BANNED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_FIRED  = 2;

    const AUTHTYPE_SIMPLE   = 1;
    const AUTHTYPE_APP      = 2;
    const AUTHTYPE_SMS      = 3;
    const AUTHTYPE_RECOVERY = 4;
    const AUTHTYPE_FALLBACK = 5;

    public $isAdmin = true;
    public $usersInGroup;
    public $usersInGroupHtml;

    const SEX_FEMALE = 0;
    const SEX_MALE   = 1;

    const NOT_IN_GROUP = 0;

    const GROUP_HAVE_NO_HEAD = 0;

    const GROUP_FLAG_IT_IS_USER  = 0;
    const GROUP_FLAG_IT_IS_GROUP = 1;

    const CAN_GET_CHILD_INFO_NO  = 0;
    const CAN_GET_CHILD_INFO_YES = 1;

    public $birthdate_min_date = "-80 years";
    public $birthdate_max_date = "-18 years";

    public $startdate_min_date = "-5 years";
    public $startdate_max_date = "+5 years";

    const SCENARIO_STATUS_MODERATION = "_scenario_status_moderation";
    const SCENARIO_EDIT_GROUP        = "_scenario_edit_group";
    const SCENARIO_EDIT_USER         = "_scenario_edit_user";
    const SCENARIO_CHANGE_PASS       = "_scenario_change_pass";
    const SCENARIO_PROFILE_EDIT      = "_scenario_profile_edit";
    const SCENARIO_CHANGE_AUTH_TYPE  = "_user_change_auth_type";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Yii::$app->getModule('rbacadmin')->userTable;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_BANNED, self::STATUS_FIRED]],

            ['can_get_child_info', 'in', 'range' => [self::CAN_GET_CHILD_INFO_NO, self::CAN_GET_CHILD_INFO_YES]],

            ['group_flag', 'default', 'value' => self::GROUP_FLAG_IT_IS_USER, 'on' => self::SCENARIO_EDIT_USER],
            ['group_flag', 'default', 'value' => self::GROUP_FLAG_IT_IS_GROUP, 'on' => self::SCENARIO_EDIT_GROUP],
            ['group_flag', 'in', 'range' => [self::GROUP_FLAG_IT_IS_USER, self::GROUP_FLAG_IT_IS_GROUP]],

            ['sex', 'in', 'range' => [self::SEX_FEMALE, self::SEX_MALE]],

            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => AdminUser::className(), 'message' => Yii::t('perm', 'perm-user-nickname-error-unique')],
            ['username', 'string', 'min' => 3, 'max' => 16],
            ['username', 'match', 'pattern' => '/^[\.a-zA-Z0-9_-]+$/', 'message' => Yii::t('perm', 'perm-user-nickname-error-pattern')],

            ['name', 'filter', 'filter' => 'trim'],
            ['name', 'required'],
            ['name', 'string', 'min' => 3, 'max' => 16],

            ['surname', 'filter', 'filter' => 'trim'],
            ['surname', 'required'],
            ['surname', 'string', 'min' => 1, 'max' => 16],
            ['surname', 'match', 'pattern' => '/^[a-zA-Z\x{0400}-\x{04FF}-]+$/u'],

            ['patronymic', 'filter', 'filter' => 'trim'],
            ['patronymic', 'string', 'max' => 16],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 64],
            ['email', 'unique', 'targetClass' => AdminUser::className(), 'message' => Yii::t('perm', 'perm-user-email-error-unique')],

            ['birth_date', 'filter', 'filter' => 'trim'],
            ['birth_date', 'yii\validators\DateValidator', 'format' => 'php:Y-m-d H:i:s', 'min' => date("Y-m-d H:i:s", strtotime($this->birthdate_min_date)), 'max' => date("Y-m-d H:i:s", strtotime($this->birthdate_max_date))],

            ['start_date', 'filter', 'filter' => 'trim'],
            ['start_date', 'yii\validators\DateValidator', 'format' => 'php:Y-m-d H:i:s', 'min' => date("Y-m-d H:i:s", strtotime($this->startdate_min_date)), 'max' => date("Y-m-d H:i:s", strtotime($this->startdate_max_date))],

            ['company_position', 'filter', 'filter' => 'trim'],
            ['company_position', 'string', 'max' => 45],

            ['description', 'filter', 'filter' => 'trim'],
            ['description', 'string', 'max' => 45],

            ['sip', 'filter', 'filter' => 'trim'],
            ['sip', 'match', 'pattern' => '/^\d{11,14}$/', 'message' => Yii::t('perm', 'perm-user-mobile-error-pattern')],

            ['in_group', 'integer'],
            ['in_group', 'default', 'value' => self::NOT_IN_GROUP],

            ['group_head_id', 'integer'],
            ['group_head_id', 'default', 'value' => self::GROUP_HAVE_NO_HEAD],

            ['group_flag', 'integer'],

        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class'      => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'date_created',
                ],
                'value'      => new Expression('NOW()'),
            ],
            [
                'class'      => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'date_updated',
                ],
                'value'      => new Expression('NOW()'),
            ],
            [
                'class'      => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'date_updated',
                ],
                'value'      => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id'                   => Yii::t('perm', 'ID'),
            'Username'             => Yii::t('perm', 'Username'),
            'group_head_id'        => Yii::t('perm', 'Group Head Id'),
            'surname'              => Yii::t('perm', 'Surname'),
            'name'                 => Yii::t('perm', 'Name'),
            'patronymic'           => Yii::t('perm', 'Secname'),
            'sex'                  => Yii::t('perm', 'Sex'),
            'status'               => Yii::t('perm', 'Status'),
            'birth_date'           => Yii::t('perm', 'Birthdate'),
            'start_date'           => Yii::t('perm', 'Startdate'),
            'company_position'     => Yii::t('perm', 'Company Position'),
            'in_group'             => Yii::t('perm', 'In Group'),
            'description'          => Yii::t('perm', 'Description'),
            'sip'                  => Yii::t('perm', 'Sip'),
            'email'                => Yii::t('perm', 'Email'),
            'password_hash'        => Yii::t('perm', 'Password Hash'),
            'password_reset_token' => Yii::t('perm', 'Password Reset Token'),
            'auth_key'             => Yii::t('perm', 'Auth Key'),
            'auth_type'            => Yii::t('perm', 'Auth Type'),
            'date_created'         => Yii::t('perm', 'Date Created'),
            'date_updated'         => Yii::t('perm', 'Date Updated'),
            'can_get_child_info'   => Yii::t('perm', 'Can Get Child Info'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_STATUS_MODERATION] = ['status'];
        $scenarios[self::SCENARIO_EDIT_GROUP]        = ['group_flag', 'name', 'status', 'group_head_id', 'group_id', 'description', 'can_get_child_info'];
        $scenarios[self::SCENARIO_EDIT_USER]         = ['group_flag', 'username', 'name', 'patronymic', 'surname', 'birth_date', 'sex', 'status', 'company_position', 'in_group', 'description', 'sip', 'email', 'start_date'];
        $scenarios[self::SCENARIO_CHANGE_PASS]       = ['auth_key', 'password_hash'];
        $scenarios[self::SCENARIO_PROFILE_EDIT]      = ['sex', 'birth_date', 'description'];
        $scenarios[self::SCENARIO_CHANGE_AUTH_TYPE]  = ['auth_type'];

        return $scenarios;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     *
     * @return AdminUser|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by id
     *
     * @param integer $id
     *
     * @return AdminUser|null
     */
    public static function findIdentity($id)
    {
        /**
         * @var $model \meshzp\rbacadmin\models\AdminUser|null
         */
        $model = static::findOne([
            'id'     => $id,
            'status' => self::STATUS_ACTIVE,
        ]);

        return $model;
    }

    /**
     * Finds user by access token
     *
     * @param string $token
     * @param null $type
     *
     * @return AdminUser|null
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        /**
         * @var $model \meshzp\rbacadmin\models\AdminUser|null
         */
        $model = static::findOne([
            'access_token' => $token,
        ]);

        return $model;
    }

    /**
     * Model id getter
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Model auth_key getter
     * @return string
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * Model authentication key validator
     *
     * @param string $authKey
     *
     * @return bool
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     *
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates "remember me" authentication key
     * @throws \Exception because of unnormal length
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     *
     * @throws \Exception if unknown error happened
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @return string[]
     */
    public static function getLoginTypesList()
    {
        return [
            self::AUTHTYPE_SIMPLE   => 'Simple',
            self::AUTHTYPE_APP      => 'Application',
            self::AUTHTYPE_SMS      => 'SMS',
            self::AUTHTYPE_RECOVERY => 'Recovery code',
            self::AUTHTYPE_FALLBACK => 'Fallback SMS',
        ];
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     *
     * @return AdminUser|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status'               => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     *
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire    = Yii::$app->params['user.passwordResetTokenExpire'];

        return $timestamp + $expire >= time();
    }

    /**
     * Generates new password reset token
     * @throws \Exception because of unnormal length
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Get list of sex
     * @return array
     */
    public static function getSexList()
    {
        return [
            self::SEX_MALE   => Yii::t('perm', 'perm-sex-male'),
            self::SEX_FEMALE => Yii::t('perm', 'perm-sex-female'),
        ];
    }

    /**
     *
     * @param $id
     *
     * @return string
     */
    public static function getNameById($id)
    {
        $model = static::findOne($id);

        return $model->name;
    }

    /**
     * Get username of user by his ID
     *
     * @param $id
     *
     * @return string
     */
    public static function getLoginById($id)
    {
        $model = static::findOne($id);

        return $model->username;
    }

    /**
     * Get status name of user by his ID
     *
     * @param $id
     *
     *
     * @return mixed
     */
    public static function getStatusNameById($id)
    {
        $model = static::findOne($id);

        return self::getGroupStatusById($model->status);
    }

    /**
     * Get count of users in group
     * @return int|string
     */
    public function getCountOfUsersInGroup()
    {
        $model = static::find()->where(['in_group' => $this->id]);

        return $model->exists() ? $model->count() : 0;
    }

    /**
     * Get list of group statuses available
     * @return array
     */
    public static function getGroupStatusList()
    {
        return [
            self::STATUS_ACTIVE => Yii::t('perm', 'perm-control-group-status-works'),
            self::STATUS_BANNED => Yii::t('perm', 'perm-control-group-status-banned'),
            self::STATUS_FIRED  => Yii::t('perm', 'perm-control-group-status-deleted'),
        ];
    }

    /**
     * Get list of users statuses available
     * @return array
     */
    public static function getUserStatusList()
    {
        return [
            self::STATUS_ACTIVE => Yii::t('perm', 'perm-control-user-status-works'),
            self::STATUS_BANNED => Yii::t('perm', 'perm-control-user-status-banned'),
            self::STATUS_FIRED  => Yii::t('perm', 'perm-control-user-status-fired'),
        ];
    }

    /**
     * Get geoup status by its ID
     *
     * @param $id
     *
     * @return mixed
     */
    public static function getGroupStatusById($id)
    {
        return isset(static::getGroupStatusList()[$id]) ? static::getGroupStatusList()[$id] : '';
    }

    /**
     * Get user status by id
     *
     * @param $id
     *
     * @return mixed
     */
    public static function getUserStatusById($id)
    {
        return isset(static::getUserStatusList()[$id]) ? static::getUserStatusList()[$id] : '';
    }

    /**
     * Get list of all groups
     * @return array
     */
    public static function getGroupList()
    {
        $groups  = static::findAll(['group_flag' => self::GROUP_FLAG_IT_IS_GROUP]);
        $aGroups = [
            0 => Yii::t('perm', 'perm-control-not-in-group'),
        ];
        foreach ($groups as $group) {
            $aGroups[$group->id] = $group->name;
        }

        return $aGroups;
    }

    /**
     * Get list of all users
     *
     * @param bool $with_logins
     *
     * @return array
     */
    public static function getAllUsersList($with_logins = false)
    {
        $users  = static::findAll(['group_flag' => self::GROUP_FLAG_IT_IS_USER]);
        $aUsers = [
            0 => Yii::t('perm', 'perm-control-no-user'),
        ];
        foreach ($users as $user) {
            $aUsers[$user->id] = $with_logins ? $user->username . ' (' . $user->surname . ' ' . $user->name . ' ' . $user->patronymic . ')' : $user->surname . ' ' . $user->name . ' ' . $user->patronymic;
        }

        return $aUsers;
    }

    /**
     * Get full name
     *
     * @param $id
     *
     * @return mixed
     */
    public static function getUserFIOById($id)
    {
        return static::getAllUsersList(true)[$id];
    }

    /**
     * Get list of all variants of 'list all childs'
     * @return array
     */
    public static function getCanGetChildInfoList()
    {
        return [
            self::CAN_GET_CHILD_INFO_NO  => Yii::t('perm', 'perm-word-no'),
            self::CAN_GET_CHILD_INFO_YES => Yii::t('perm', 'perm-word-yes'),
        ];
    }

    /**
     * Get value of 'show child list' flag
     *
     * @param $flag
     *
     * @return mixed
     */
    public static function getCanGetChildInfo($flag)
    {
        return static::getCanGetChildInfoList()[$flag];
    }

    /**
     * Get list of all available company positions
     * @return array
     */
    public static function getCompanyPositionsList()
    {
        $query = new Query;
        $query->select('company_position')
            ->from(static::tableName())
            ->groupBy('company_position');
        $rows       = $query->all();
        $aPositions = [];
        foreach ($rows as $row) {
            $aPositions[$row['company_position']] = $row['company_position'];
        }

        return $aPositions;
    }

    /**
     * Get list of all users related to the current group and return count of them
     * @return int
     */
    public function getUsersInGroup()
    {
        if ($this->group_flag == self::GROUP_FLAG_IT_IS_GROUP) {
            $this->usersInGroup = static::find()
                ->select(['a.*'])
                ->leftJoin(static::tableName() . ' a', static::tableName() . '.id = a.in_group')
                ->where([static::tableName() . '.id' => $this->id, 'a.group_flag' => 0])
                ->asArray()
                ->all();
            if (!empty($this->usersInGroup)) {
                return count($this->usersInGroup);
            }
        }

        return 0;
    }

    /**
     * Get list of users related to the current group and return them as array or false (if there is no users)
     * @return array|bool
     */
    public function getUsersInGroupIdsArray()
    {
        $users = [];
        if ($this->getUsersInGroup() && isset($this->usersInGroup)) {
            foreach ($this->usersInGroup as $u) {
                $users[] = $u['id'];
            }

            return $users;
        }

        return false;
    }

    /**
     * Create and return array of html-button-elements related to users
     * @return string
     */
    public function formatUserInGroupAsHtml()
    {
        if (empty($this->usersInGroup)) {
            return '';
        }
        $this->usersInGroupHtml = '<ul class="list-group">';
        foreach ($this->usersInGroup as $user) {
            $isGroupLeader          = ($user['id'] == $this->group_head_id) ? 'list-group-item-info ' : '';
            $isNotActive            = ($user['status'] != 1) ? 'list-group-item-danger ' : '';
            $this->usersInGroupHtml .= Html::a($user['surname'] . ' ' . $user['patronymic'] . ' ' . $user['name'] . ' (' . $user['username'] . ') ', ['control/user-edit', 'id' => $user['id']], ['class' => 'list-group-item ' . $isGroupLeader . $isNotActive, 'data-pjax' => 0]);
        }
        $this->usersInGroupHtml .= '</ul>';

        return $this->usersInGroupHtml;
    }

    /**
     * Check if current user have privilegy to log-in (related on his status and status of his group)
     * @return bool
     */
    public function canLogin()
    {
        if ($this->group_flag == self::GROUP_FLAG_IT_IS_USER && $this->status == self::STATUS_ACTIVE) {
            if ($this->in_group) {
                $group = static::findOne(['id' => $this->in_group, 'group_flag' => self::GROUP_FLAG_IT_IS_GROUP]);
                if ($group->status == self::STATUS_ACTIVE) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds session id into base
     */
    public static function afterLogin()
    {
        $userSession = new ControlUserSessions();
        $userSession->saveSessionId();
    }

    /**
     * Removes session id from base
     * @throws \Exception|\Throwable in case delete failed.
     */
    public static function beforeLogout()
    {
        $userSession = ControlUserSessions::findOne(['user_id' => Yii::$app->user->getId(), 'session_id' => Yii::$app->session->getId()]);
        if (!is_null($userSession)) {
            $userSession->delete();
        }
    }

    /**
     * Relation with AdminUserSettings table
     * @return \yii\db\ActiveQuery
     */
    public function getUsersSettings()
    {
        return $this->hasOne(AdminUsersSettings::className(), ['user_id' => 'id']);
    }
}
