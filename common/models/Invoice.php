<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property Contractor $contractor
 * @property User $user
 * @property InvoiceItem[] $items
 */
class Invoice extends InvoiceBase {
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
			[['comment'], 'trim'],
			[['comment'], 'default'],
		]);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getContractor() {
		return $this->hasOne(Contractor::className(), ['id' => 'contractor_id']);
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
	public function getItems() {
		return $this->hasMany(InvoiceItem::className(), ['invoice_id' => 'id']);
	}
}
