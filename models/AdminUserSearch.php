<?php

namespace meshzp\rbacadmin\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CounterGlobalSearch represents the model behind the search form about `common\models\CounterGlobal`.
 *
 * @property integer $register_counter
 * @property string $amount_counter
 */
class AdminUserSearch extends AdminUser
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'name', 'surname', 'in_group', 'company_position', 'can_get_child_info', 'group_head_id', 'status'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param integer $group_flag
     *
     * @return ActiveDataProvider
     */
    public function search($params, $group_flag)
    {
        $query = AdminUser::find()->where(['group_flag' => $group_flag]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'in_group' => $this->in_group,
            'status'   => $this->status,
        ]);
        $query->andFilterWhere(['like', 'name', $this->name]);

        if ($group_flag) {
            $query->andFilterWhere([
                'group_head_id'      => $this->group_head_id,
                'can_get_child_info' => $this->can_get_child_info,
                'description'        => $this->description,
            ]);
        } else {
            $query->andFilterWhere([
                'surname'          => $this->surname,
                'company_position' => $this->company_position,
            ]);
            $query->andFilterWhere(['like', 'username', $this->username]);
        }

        return $dataProvider;
    }
}
