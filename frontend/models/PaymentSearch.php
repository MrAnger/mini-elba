<?php

namespace frontend\models;

use common\helpers\PaymentHelper;
use common\models\Payment;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class PaymentSearch extends Payment {
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
		$query = Payment::find();

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		PaymentHelper::applyAccessByUser($query);

		$this->load($params);

		$this->setAttributes($overriddenParams, false);

		// Валидириуем, что бы пустые значения стали NULL да и просто что бы было :)
		$this->validate();
		// Очищаем массив ошибок - нам не надо выводить на сайте :)
		$this->clearErrors();

		$query->andFilterWhere([
			'contractor_id' => $this->contractor_id,
			'date'          => $this->date,
			'income'        => $this->income,
		]);

		$query->andFilterWhere(['like', 'description', $this->description]);

		return $dataProvider;
	}

	public function formName() {
		return '';
	}
}
