<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%payment_link_to_invoice}}".
 *
 * @property integer $payment_id
 * @property integer $invoice_id
 * @property integer $sum
 *
 * @property Payment $payment
 * @property Invoice $invoice
 */
class PaymentLinkToInvoice extends ActiveRecord {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{%payment_link_to_invoice}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['payment_id', 'invoice_id', 'sum'], 'required'],
			[['payment_id', 'invoice_id'], 'integer'],
			[['sum'], 'number'],

			[['sum'], 'trim'],
			[['sum'], 'default'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'payment_id' => Yii::t('app', 'Payment'),
			'invoice_id' => Yii::t('app', 'Invoice'),
			'sum'        => Yii::t('app', 'Sum'),
		];
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
