<?php

namespace meshzp\rbacadmin\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Класс поиска по истории запросов пользователя
 */
class AdminRequestLogSearch extends AdminRequestLog
{
    public $user_id;
    public $username;
    public $date_created;
    public $request;
    public $get_params;
    public $post_params;
    public $date_from;
    public $date_to;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'username', 'date_created', 'request', 'get_params', 'post_params'], 'safe'],
            [['date_from', 'date_to'], 'date', 'format' => 'php:Y-m-d'],
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = AdminRequestLog::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => ['date_created' => SORT_DESC],
                'attributes'   => ['date_created'],
            ],
        ]);

        $this->load($params);

        $this->parseDateRangeFromString('date_created', 'date_from', 'date_to');

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['user_id' => $this->user_id])
            ->andFilterWhere(['like', 'request', $this->request])
            ->andFilterWhere(['like', 'get_params', $this->get_params])
            ->andFilterWhere(['like', 'post_params', $this->post_params])
            ->andFilterWhere(['>=', 'date_created', $this->date_from ? $this->date_from . ' 00:00:00' : null])
            ->andFilterWhere(['<=', 'date_created', $this->date_to ? $this->date_to . ' 23:59:59' : null]);

        return $dataProvider;
    }

    /**
     * For DateRangePicker
     *
     * @param $attr
     * @param $start_attr
     * @param $end_attr
     * @param string $separator
     *
     * @return bool
     */
    private function parseDateRangeFromString($attr, $start_attr, $end_attr, $separator = '-')
    {
        if (!empty($this->{$attr}) && strpos($this->{$attr}, ' - ') !== false) {
            list($this->{$start_attr}, $this->{$end_attr}) = explode(" {$separator} ", $this->{$attr});

            return true;
        }

        return false;
    }
}
