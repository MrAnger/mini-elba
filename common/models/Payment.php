<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property Contractor $contractor
 * @property User $user
 * @property PaymentLinkToInvoice[] $invoiceLinks
 * @property Invoice[] $invoices
 * @property boolean $isLinkedComplete
 */
class Payment extends PaymentBase {
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
			[['income'], 'required'],
			[['description'], 'trim'],
			[['description'], 'default'],
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
	public function getInvoiceLinks() {
		return $this->hasMany(PaymentLinkToInvoice::className(), ['payment_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getInvoices() {
		return $this->hasMany(Invoice::className(), ['id' => 'invoice_id'])
			->viaTable(PaymentLinkToInvoice::tableName(), ['payment_id' => 'id']);
	}

	/**
	 * @return bool
	 */
	public function getIsLinkedComplete() {
		$linkedSum = 0;
		foreach ($this->invoiceLinks as $link) {
			$linkedSum += $link->sum;
		}

		return ($linkedSum >= $this->income);
	}

	/**
	 * @return float
	 */
	public function getAvailableLinkSum() {
		if ($this->isNewRecord)
			return 0;

		$availableSum = $this->income;

		foreach ($this->invoiceLinks as $link) {
			$availableSum -= $link->sum;
		}

		if ($availableSum < 0) {
			$availableSum = 0;
		}

		return $availableSum;
	}

	/**
	 * @param integer $invoiceId
	 *
	 * @return PaymentLinkToInvoice
	 */
	public function getInvoiceLink($invoiceId) {
		return PaymentLinkToInvoice::findOne([
			'payment_id' => $this->id,
			'invoice_id' => $invoiceId,
		]);
	}
}
