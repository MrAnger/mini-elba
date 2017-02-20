<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%payment_link_to_invoice}}".
 *
 * @property integer $payment_id
 * @property integer $invoice_id
 * @property integer $sum
 */
class PaymentLinkToInvoiceBase extends \yii\db\ActiveRecord {
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
}
