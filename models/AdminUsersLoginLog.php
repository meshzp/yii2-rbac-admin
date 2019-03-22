<?php

namespace meshzp\rbacadmin\models;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "admin_users_login_log".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $ip
 * @property string $date_attempted
 * @property integer $login_type
 * @property integer $is_successful
 *
 * @property AdminUser $user
 */
class AdminUsersLoginLog extends ActiveRecord
{
    const STATUS_UNSUCCESSFUL = 0;
    const STATUS_SUCCESSFUL   = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Yii::$app->getModule('rbacadmin')->userLogTable;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class'      => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'date_attempted',
                ],
                'value'      => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'ip', 'login_type', 'is_successful'], 'required'],
            [['user_id', 'login_type', 'is_successful'], 'integer'],
            [['date_attempted'], 'safe'],
            [['ip'], 'string', 'max' => 16],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => AdminUser::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'user_id'        => 'User ID',
            'ip'             => Yii::t('perm', 'perm-login-ip'),
            'date_attempted' => Yii::t('perm', 'perm-login-date-attempted'),
            'login_type'     => Yii::t('perm', 'perm-login-type'),
            'is_successful'  => Yii::t('perm', 'perm-login-is-successful'),
        ];
    }

    /**
     * Finds latest five log attempts by user
     *
     * @param int $user_id
     *
     * @return \yii\db\ActiveQuery
     */
    public static function findByUserId($user_id)
    {
        return static::find()
            ->where([
                'user_id' => $user_id,
            ])
            ->orderBy('date_attempted DESC')
            ->limit(5);
    }

    /**
     * Finds the last one attempt to log in
     * Uses in cabinet users menu
     *
     * @param int $user_id
     *
     * @return AdminUsersLoginLog|array|ActiveRecord|null
     */
    public static function findLastAttemptByUserId($user_id)
    {
        return static::find()
            ->where([
                'user_id'       => $user_id,
                'is_successful' => self::STATUS_SUCCESSFUL,
            ])
            ->orderBy('date_attempted DESC')
            ->one();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(AdminUser::className(), ['id' => 'user_id']);
    }

    /**
     * @return string
     */
    public function getLoginTypeLabel()
    {
        return AdminUser::getLoginTypesList()[$this->login_type];
    }

    /**
     * @return string
     */
    public function getLoginSuccessfulLabel()
    {
        return isset(self::getLoginSuccessfulList()[$this->is_successful]) ? self::getLoginSuccessfulList()[$this->is_successful] : '';
    }

    /**
     * @return array
     */
    public static function getLoginSuccessfulList()
    {
        return [
            self::STATUS_UNSUCCESSFUL => Yii::t('perm', 'perm-unsuccessful'),
            self::STATUS_SUCCESSFUL   => Yii::t('perm', 'perm-successful'),
        ];
    }
}
