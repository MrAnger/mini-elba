<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%invoice_item}}".
 *
 * @property string $id
 * @property string $invoice_id
 * @property string $name
 * @property integer $quantity
 * @property string $unit
 * @property double $price
 * @property double $summary
 * @property double $total_paid
 * @property boolean $is_paid
 *
 */
class InvoiceItemBase extends \yii\db\ActiveRecord {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{%invoice_item}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['invoice_id', 'name', 'summary'], 'required'],
			[['invoice_id'], 'integer'],
			[['price', 'summary', 'total_paid', 'quantity'], 'number'],
			[['is_paid'], 'boolean'],
			[['name'], 'string', 'max' => 250],
			[['unit'], 'string', 'max' => 10],
			[['invoice_id', 'name'], 'unique', 'targetAttribute' => ['invoice_id', 'name'], 'message' => 'Позиция с данным названием уже существует.'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id'         => Yii::t('app', 'ID'),
			'invoice_id' => Yii::t('app', 'Invoice ID'),
			'name'       => Yii::t('app', 'Name'),
			'quantity'   => Yii::t('app', 'Quantity'),
			'unit'       => Yii::t('app', 'Unit'),
			'price'      => Yii::t('app', 'Price'),
			'summary'    => Yii::t('app', 'Summary'),
			'total_paid' => Yii::t('app', 'Total Paid'),
			'is_paid'    => Yii::t('app', 'Is Paid'),
		];
	}
}
