<?php

namespace common\helpers;

use common\models\Payment;
use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;


class PaymentHelper extends Model {
	/**
	 * @param ActiveQuery $query
	 *
	 * @return ActiveQuery
	 */
	public static function applyAccessByUser($query) {
		$query->andWhere(['=', Payment::tableName() . '.user_id', Yii::$app->user->id]);

		return $query;
	}

	/**
	 * @param Payment $model
	 *
	 * @return boolean
	 */
	public static function isAccessAllowed($model) {
		return $model->user_id == Yii::$app->user->id;
	}

	/**
	 * @param Payment $model
	 *
	 * @return float
	 */
	public static function getLinkedSum($model) {
		$sum = 0;

		foreach ($model->invoiceLinks as $link) {
			$sum += $link->sum;
		}

		return $sum;
	}

	/**
	 * @param Payment $model
	 *
	 * @return boolean
	 */
	public static function isAvailableDelete($model) {
		return ($model->getInvoiceLinks()->count() == 0);
	}
}
