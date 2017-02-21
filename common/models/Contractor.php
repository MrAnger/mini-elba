<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property User $user
 * @property Payment[] $payments
 * @property Invoice[] $invoices
 */
class Contractor extends ContractorBase {
	/**
	 * @inheritdoc
	 */
	public function behaviors() {
		return [
			'timestamps' => [
				'class' => TimestampBehavior::className(),
				'value' => new Expression('NOW()'),
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return array_merge(parent::rules(), [
			[['inn'], 'trim'],
			[['inn'], 'default'],
		]);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser() {
		return $this->hasOne(User::className(), ['id' => 'user_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPayments() {
		return $this->hasMany(Payment::className(), [
			'contractor_id' => 'id',
			'user_id'       => 'user_id',
		]);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getInvoices() {
		return $this->hasMany(Invoice::className(), [
			'contractor_id' => 'id',
			'user_id'       => 'user_id',
		]);
	}
}
