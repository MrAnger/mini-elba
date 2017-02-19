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
			[['total_paid'], 'validateTotalPaid'],
		]);
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
				$this->addError('total_paid', "Это значение не может быть меньше 0.");
			} elseif ($this->total_paid > $availableTotalPaid) {
				$this->addError('total_paid', "Максимальное возможное значение для текущей позиции: " . Yii::$app->formatter->asCurrency($availableTotalPaid));
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
