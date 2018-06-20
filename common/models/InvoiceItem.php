<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

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
 * @property Invoice $invoice
 */
class InvoiceItem extends ActiveRecord {
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

			[['name', 'unit'], 'trim'],
			[['name', 'unit'], 'default'],
			[['total_paid'], 'validateTotalPaid'],
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

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getInvoice() {
		return $this->hasOne(Invoice::className(), ['id' => 'invoice_id']);
	}

	public function validateTotalPaid() {
		if ($this->isNewRecord)
			return true;

		if ($this->total_paid !== null) {
			$availableTotalPaid = $this->getAvailableTotalPaid();

			if ($this->total_paid < 0) {
				$this->addError('total_paid', "'" . $this->getAttributeLabel('total_paid') . "' не может быть меньше 0.");
			} elseif ($this->total_paid > $availableTotalPaid) {
				$this->addError('total_paid', "Максимальное возможное значение '" . $this->getAttributeLabel('total_paid') . "' для текущей позиции: " . Yii::$app->formatter->asCurrency($availableTotalPaid));
			}
		}
	}

	/**
	 * @return float
	 */
	public function getAvailableTotalPaid() {
		if ($this->isNewRecord)
			return 0;

		$availableSum = $this->invoice->total_paid;

		foreach ($this->invoice->items as $item) {
			if ($item->id == $this->id)
				continue;

			$availableSum -= $item->total_paid;
		}

		if ($availableSum > $this->summary) {
			$availableSum = $this->summary;
		}

		return $availableSum;
	}
}
