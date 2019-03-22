<?php
namespace meshzp\rbacadmin\models;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "uploaded_documents".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $username
 * @property string $request
 * @property string $get_params
 * @property string $post_params
 * @property string $date_created
 *
 * @property AdminUser $user
 */
class AdminRequestLog extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Yii::$app->getModule('rbacadmin')->userRequestLogTable;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'date_created',
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'username' => Yii::t('perm', 'Username'),
            'date_created' => Yii::t('perm', 'Date Created'),
            'request' => Yii::t('perm', 'perm-request'),
            'get_params' => Yii::t('perm', 'perm-get-params'),
            'post_params' => Yii::t('perm', 'perm-post-params'),
        ];
    }

    public static function LogRequest()
    {
        if (!Yii::$app->user->isGuest && strpos(Yii::$app->request->url, '/debug/') === false) {
            $log = new AdminRequestLog();
            $log->user_id = Yii::$app->user->identity->getId();
            $log->username = Yii::$app->user->identity->username;
            $log->request = Yii::$app->request->method . ' ' . Yii::$app->request->url;
            $log->get_params = json_encode(Yii::$app->request->get(), JSON_PRETTY_PRINT);
            $log->post_params = json_encode(Yii::$app->request->post(), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
            $log->save();
        }
    }
}
