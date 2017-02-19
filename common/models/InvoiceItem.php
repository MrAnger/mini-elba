<?php

namespace common\models;

use Yii;

/**
 * @property Invoice $invoice
 */
class InvoiceItem extends InvoiceItemBase {
	public function rules() {
		return array_merge(parent::rules(), [
			[['name', 'unit'], 'trim'],
			[['name', 'unit'], 'default'],
		]);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getInvoice() {
		return $this->hasOne(Invoice::className(), ['id' => 'invoice_id']);
	}
}
