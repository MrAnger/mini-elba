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
 * @property string $name
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
			[['description', 'document_number', 'is_include_into_stat'], 'trim'],
			[['description', 'document_number'], 'default'],
			[['is_include_into_stat'], 'default', 'value' => 1],
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

	/**
	 * @return string
	 */
	public function getName() {
		$formatter = Yii::$app->formatter;

		return "Поступление на " . $formatter->asCurrency($this->income) . " " . $formatter->asDate($this->date) . " от " . $this->contractor->name;
	}
}
