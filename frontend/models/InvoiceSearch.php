<?php

namespace frontend\models;

use common\helpers\InvoiceHelper;
use common\models\Invoice;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class InvoiceSearch extends Invoice {
	/**
	 * @inheritdoc
	 */
	public function scenarios() {
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors() {
		return [];
	}

	/**
	 * @param array $params
	 * @param array $overriddenParams
	 *
	 * @return ActiveDataProvider
	 */
	public function search($params, $overriddenParams = []) {
		$query = Invoice::find()
			->joinWith('contractor')
			->joinWith('payments');

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		InvoiceHelper::applyAccessByUser($query);

		$this->load($params);

		$this->setAttributes($overriddenParams, false);

		// Валидириуем, что бы пустые значения стали NULL да и просто что бы было :)
		$this->validate();
		// Очищаем массив ошибок - нам не надо выводить на сайте :)
		$this->clearErrors();

		$query->andFilterWhere([
			'contractor_id' => $this->contractor_id,
			'is_paid'       => $this->is_paid,
		]);

		$query->andFilterWhere(['like', 'name', $this->name]);

		return $dataProvider;
	}

	public function formName() {
		return '';
	}
}
