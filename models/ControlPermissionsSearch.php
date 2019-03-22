<?php

namespace meshzp\rbacadmin\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * Class ControlPermissionsSearch - the search model of ControlPermissions Model
 * @package meshzp\rbacadmin\models
 */
class ControlPermissionsSearch extends ControlPermissions
{

    const PERMISSION_FOLLOWED_BY_DEFAULTS   = 0;
    const PERMISSION_FOLLOWED_BY_GROUP      = 1;
    const PERMISSION_FOLLOWED_BY_INDIVIDUAL = 2;

    public $followed_by;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'description', 'type', 'followed_by', 'controller'], 'safe'],
            [['name', 'description', 'controller'], 'string'],
            [['type', 'followed_by'], 'integer'],
            ['followed_by', 'in', 'range' => [self::PERMISSION_FOLLOWED_BY_DEFAULTS, self::PERMISSION_FOLLOWED_BY_GROUP, self::PERMISSION_FOLLOWED_BY_INDIVIDUAL]],
        ];
    }

    /**
     *  Get list of permission follow politics
     *
     * @return array
     */
    public static function getFollowedList()
    {
        return
            [
                self::PERMISSION_FOLLOWED_BY_DEFAULTS   => Yii::t('perm', 'perm-permission-default-politics'),
                self::PERMISSION_FOLLOWED_BY_GROUP      => Yii::t('perm', 'perm-permission-group-politics'),
                self::PERMISSION_FOLLOWED_BY_INDIVIDUAL => Yii::t('perm', 'perm-permission-individual-politics'),
            ];
    }

    /**
     * Search available permission by params and user_id
     *
     * @param array $params
     * @param int $user_id
     *
     * @return ActiveDataProvider
     */
    public function search($params, $user_id = 0)
    {
        $query = (new Query())
            ->select([
                'cai.controller',
                'cai.name',
                'cai.type',
                'cai.description',
                'cairu.enabled as switch_enable',
                'cairg.enabled as parent_enabled',
                'if(cairu.enabled is null, cairg.enabled, cairu.enabled) as enabled',
            ])
            ->from(Yii::$app->getModule('rbacadmin')->authItemTable . ' as cai')
            ->leftJoin(Yii::$app->getModule('rbacadmin')->userTable . ' AS au', 'au.id = ' . $user_id)
            ->leftJoin(Yii::$app->getModule('rbacadmin')->authItemRelationsTable . ' AS cairu', 'cairu.name = cai.name and cairu.admin_id = au.id')
            ->leftJoin(Yii::$app->getModule('rbacadmin')->authItemRelationsTable . ' AS cairg', 'cairg.name = cai.name and cairg.admin_id = au.in_group AND au.group_flag = ' . AdminUser::GROUP_FLAG_IT_IS_USER)
            ->groupBy('cai.name');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => [
                    'controller' => SORT_ASC,
                    'type'       => SORT_ASC,
                ],
                'attributes'   => [
                    'controller',
                    'type',
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['cai.type' => $this->type]);
        $query->andFilterWhere([
            'or',
            ['like', 'cai.name', $this->name],
            ['like', 'controller', $this->name],
        ]);
        $query->andFilterWhere(['like', 'cai.description', trim($this->description)]);
        if ($user_id && !is_null($this->followed_by) && $this->followed_by != '') {
            $query->andHaving(['followed_by' => $this->followed_by]);
        }

        return $dataProvider;
    }
}
