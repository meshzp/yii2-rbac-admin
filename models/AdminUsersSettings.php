<?php

namespace meshzp\rbacadmin\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "users_settings".
 *
 * @property integer $user_id
 * @property integer $security_recovery_codes_alert
 * @property string $security_secret_code
 * @property string $security_sms_fallback_number
 *
 * @property AdminUser $user
 */
class AdminUsersSettings extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Yii::$app->getModule('rbacadmin')->userSettingsTable;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'security_secret_code'], 'required'],
            [['user_id', 'security_recovery_codes_alert'], 'integer'],
            [['security_secret_code', 'security_sms_fallback_number'], 'string', 'max' => 16],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => AdminUser::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('perm', 'User ID'),
            'security_recovery_codes_alert' => Yii::t('perm', 'Security Recovery Codes Alert'),
            'security_secret_code' => Yii::t('perm', 'Security Secret Code'),
            'security_sms_fallback_number' => Yii::t('perm', 'Security SMS Fallback Number'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(AdminUser::className(), ['id' => 'user_id']);
    }
}
