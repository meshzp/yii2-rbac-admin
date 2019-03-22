<?php

namespace meshzp\rbacadmin\models;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "perm_auth_item".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property string $controller
 * @property integer $created_at
 * @property integer $updated_at
 */
class ControlPermissions extends ActiveRecord
{
    const TYPE_CONTROLLER = 1;
    const TYPE_ACTION     = 2;
    const TYPE_PERMISSION = 3;

    const ENABLED_YES = 1;
    const ENABLED_NO  = 0;

    const PHPDOC_PERMISSION_IDENTIFIER        = '@permission';
    const PHPDOC_HIDDEN_CONTROLLER_IDENTIFIER = '@hide_controller';

    /**
     * Get list of permission types
     *
     * @return array
     */
    public static function getTextStatusList()
    {
        return [
            self::TYPE_CONTROLLER => Yii::t('perm', 'Controller'),
            self::TYPE_ACTION     => Yii::t('perm', 'Action'),
            self::TYPE_PERMISSION => Yii::t('perm', 'Permission'),
        ];
    }

    /**
     * Get list of named enabled/disabled status
     *
     * @return array
     */
    public static function getEnabledList()
    {
        return [
            self::ENABLED_YES => Yii::t('perm', 'perm-word-yes'),
            self::ENABLED_NO  => Yii::t('perm', 'perm-word-no'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%perm_auth_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['name', 'rule_name', 'description', 'data', 'controller'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            ['type', 'in', 'range' => [self::TYPE_CONTROLLER, self::TYPE_ACTION, self::TYPE_PERMISSION]],
        ];
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
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                ],
                'value'      => new Expression('NOW()'),
            ],
            [
                'class'      => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'updated_at',
                ],
                'value'      => new Expression('NOW()'),
            ],
            [
                'class'      => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value'      => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'        => Yii::t('perm', 'Name'),
            'type'        => Yii::t('perm', 'Type'),
            'description' => Yii::t('perm', 'Description'),
            'rule_name'   => Yii::t('perm', 'Rule Name'),
            'data'        => Yii::t('perm', 'Data'),
            'created_at'  => Yii::t('perm', 'Date Created'),
            'updated_at'  => Yii::t('perm', 'Date Updated'),
            'enabled'     => Yii::t('perm', 'Enabled-Disabled'),
        ];
    }

    /**
     * Get array of all permission names
     *
     * @return array
     */
    public static function getItemNamesArray()
    {
        $result = self::find()->asArray()->all();

        return $result ? ArrayHelper::getColumn($result, 'name') : [];
    }

    /**
     * Remove all permission from DB by array of their names
     *
     * @param $arrayOfNames
     *
     * @return int
     */
    public static function removeItems($arrayOfNames)
    {
        if (!is_array($arrayOfNames)) {
            return 0;
        }
        if (!empty($arrayOfNames)) {
            $stringOfNames = implode("','", $arrayOfNames);
            $tableName     = new Expression(Yii::$app->getModule('rbacadmin')->userTable);
            $query         = Yii::$app->db->createCommand("DELETE FROM {$tableName} WHERE `name` in ('{$stringOfNames}')");
            try {
                $query->execute();
            } catch (\Exception $e) {
                return 0;
            }
        }

        return 1;

    }

    /**
     * Add permissions to DB from array
     *
     * @param $items
     *
     * @return int
     */
    public static function addItems($items)
    {
        if (is_array($items)) {
            if (!empty($items)) {
                foreach ($items as $name => $prop) {
                    $model              = new static();
                    $model->name        = $name;
                    $model->type        = $prop['type'];
                    $model->description = $prop['description'];
                    $model->controller  = $prop['controller'];
                    try {
                        if (!$model->save()) {
                            return 0;
                        }
                    } catch (\Exception $e){

                    }
                }
            }

            return 1;
        }

        return 0;
    }

    /**
     * Grant all permissions to selected user
     *
     * @param array $permissions
     * @param integer $user_id
     *
     * @return bool
     */
    public static function grantAllPermissionsToUser($permissions, $user_id)
    {
        if (!empty($permissions)) {
            $permissions = array_keys($permissions);
            foreach ($permissions as $permission) {
                if (static::addNewUserPermission($permission, $user_id, static::ENABLED_YES)) {
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Add new permission record to the db by 'permission-name', 'id of group/user', and proper 'status'
     *
     * @param $permission
     * @param $user_id
     * @param $enabled
     *
     * @return bool
     */
    public static function addNewUserPermission($permission, $user_id, $enabled)
    {
        $query   = (new Query())
            ->select('name')
            ->from(Yii::$app->getModule('rbacadmin')->authItemRelationsTable)
            ->where(['name' => $permission, 'admin_id' => $user_id])
            ->exists();
        $command = Yii::$app->db->createCommand();
        if ($query == 0) {
            $command->insert(Yii::$app->getModule('rbacadmin')->authItemRelationsTable, ['name' => $permission, 'admin_id' => $user_id, 'enabled' => $enabled]);
        } else {
            $command->update(Yii::$app->getModule('rbacadmin')->authItemRelationsTable, ['enabled' => $enabled], ['name' => $permission, 'admin_id' => $user_id]);
        }
        try {
            $command->execute();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Remove existed permission record from the db by 'permission-name', 'id of group/user'
     *
     * @param $permission
     * @param $user_id
     *
     * @return bool
     */
    public static function addDeleteUserPermission($permission, $user_id)
    {
        $query   = (new Query())
            ->select('name')
            ->from(Yii::$app->getModule('rbacadmin')->authItemRelationsTable)
            ->where(['name' => $permission, 'admin_id' => $user_id])
            ->exists();
        $command = Yii::$app->db->createCommand();
        if ($query) {
            try {
                $command->delete(Yii::$app->getModule('rbacadmin')->authItemRelationsTable, ['name' => $permission, 'admin_id' => $user_id]);
                $command->execute();
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }
}
