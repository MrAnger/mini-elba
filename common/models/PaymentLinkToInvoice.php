<?php

namespace common\models;

use Yii;

/**
 * @property Payment $payment
 * @property Invoice $invoice
 */
class PaymentLinkToInvoice extends PaymentLinkToInvoiceBase {
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return array_merge(parent::rules(), [
			[['sum'], 'trim'],
			[['sum'], 'default'],
		]);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPayment() {
		return $this->hasOne(Payment::className(), ['id' => 'payment_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getInvoice() {
		return $this->hasOne(Invoice::className(), ['id' => 'invoice_id']);
	}
}
