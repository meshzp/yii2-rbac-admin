<?php

namespace meshzp\rbacadmin\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_sessions".
 *
 * @property string $session_id
 * @property integer $user_id
 */
class ControlUserSessions extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Yii::$app->getModule('rbacadmin')->userSessionTable;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'session_id'], 'required'],
            [['user_id'], 'integer'],
            [['session_id'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'user_id'    => 'User ID',
            'session_id' => 'Session ID',
        ];
    }

    /**
     * @inheritdoc
     */
    public function saveSessionId()
    {
        if (!is_null(Yii::$app->user->getId()) && !is_null(Yii::$app->session->getId())) {
            $this->user_id = Yii::$app->user->getId();
            $this->session_id = Yii::$app->session->getId();
            $this->save();
        }
    }
}
