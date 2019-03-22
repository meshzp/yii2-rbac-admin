<?php

namespace meshzp\rbacadmin\models;

use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidParamException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "settings".
 *
 * @property integer $id
 * @property string $type
 * @property string $section
 * @property string $key
 * @property string $value
 * @property boolean $active
 * @property string $created
 * @property string $modified
 *
 * @author Aris Karageorgos <aris@phe.me>
 */
class Setting extends ActiveRecord implements SettingInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Yii::$app->getModule('rbacadmin')->settingsTable;
    }

    /**
     * Make dropdown or array of validators used in settings
     *
     * @param bool $forDropDown if false - return array or validators, true - key=>value for dropDown
     *
     * @return array
     */
    public function getTypes($forDropDown = true)
    {
        $values = [
            'string'  => ['value', 'string'],
            'integer' => ['value', 'integer'],
            'boolean' => ['value', 'boolean', 'trueValue' => "1", 'falseValue' => "0", 'strict' => true],
            'float'   => ['value', 'number'],
            'email'   => ['value', 'email'],
            'ip'      => ['value', 'ip'],
            'url'     => ['value', 'url'],
            'object'  => ['value', function ($attribute) {
                try {
                    $object = Json::decode($this->{$attribute});
                } catch (InvalidParamException $e) {
                    $this->addError($attribute, Yii::t('perm', '"{attribute}" must be a valid JSON object', [
                        'attribute' => $attribute,
                    ]));

                    return;
                }
                if (!is_array($object)) {
                    $this->addError($attribute, Yii::t('perm', '"{attribute}" must be a valid JSON object', [
                        'attribute' => $attribute,
                    ]));
                }
            }],
        ];

        if (!$forDropDown) {
            return $values;
        }

        $return = [];
        foreach ($values as $key => $value) {
            $return[$key] = Yii::t('perm', $key);
        }

        return $return;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['value'], 'string'],
            [['section', 'key'], 'string', 'max' => 255],
            [
                ['key'],
                'unique',
                'targetAttribute' => ['section', 'key'],
                'message'         =>
                    Yii::t('perm', '{attribute} "{value}" already exists for this section.'),
            ],
            ['type', 'in', 'range' => array_keys($this->getTypes(false))],
            [['type', 'created', 'modified'], 'safe'],
            [['active'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $validators = $this->getTypes(false);
        if (!array_key_exists($this->type, $validators)) {
            $this->addError('type', Yii::t('perm', 'Please select correct type'));

            return false;
        }

        $model = DynamicModel::validateData([
            'value' => $this->value,
        ], [
            $validators[$this->type],
        ]);

        if ($model->hasErrors()) {
            $this->addError('value', $model->getFirstError('value'));

            return false;
        }

        if ($this->hasErrors()) {
            return false;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        Yii::$app->settings->clearCache();
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();
        Yii::$app->settings->clearCache();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'       => Yii::t('perm', 'ID'),
            'type'     => Yii::t('perm', 'Type'),
            'section'  => Yii::t('perm', 'Section'),
            'key'      => Yii::t('perm', 'Key'),
            'value'    => Yii::t('perm', 'Value'),
            'active'   => Yii::t('perm', 'Active'),
            'created'  => Yii::t('perm', 'Created'),
            'modified' => Yii::t('perm', 'Modified'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'      => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'modified',
                ],
                'value'      => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSettings()
    {
        $settings = static::find()->where(['active' => true])->asArray()->all();

        return array_merge_recursive(
            ArrayHelper::map($settings, 'key', 'value', 'section'),
            ArrayHelper::map($settings, 'key', 'type', 'section')
        );
    }

    /**
     * @inheritdoc
     */
    public function setSetting($section, $key, $value, $type = null)
    {
        $model = static::findOne(['section' => $section, 'key' => $key]);

        if ($model === null) {
            $model         = new static();
            $model->active = 1;
        }
        $model->section = $section;
        $model->key     = $key;
        $model->value   = strval($value);

        if ($type !== null) {
            $model->type = $type;
        } else {
            $model->type = gettype($value);
        }

        return $model->save();
    }

    /**
     * @inheritdoc
     */
    public function activateSetting($section, $key)
    {
        $model = static::findOne(['section' => $section, 'key' => $key]);

        if ($model && $model->active == 0) {
            $model->active = 1;

            return $model->save();
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function deactivateSetting($section, $key)
    {
        $model = static::findOne(['section' => $section, 'key' => $key]);

        if ($model && $model->active == 1) {
            $model->active = 0;

            return $model->save();
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function deleteSetting($section, $key)
    {
        $model = static::findOne(['section' => $section, 'key' => $key]);

        if ($model) {
            return $model->delete();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllSettings()
    {
        return static::deleteAll();
    }
}
