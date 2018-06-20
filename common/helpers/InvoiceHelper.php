<?php

namespace common\helpers;

use common\models\Invoice;
use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;


class InvoiceHelper extends Model {
	/**
	 * @param ActiveQuery $query
	 *
	 * @return ActiveQuery
	 */
	public static function applyAccessByUser($query) {
		$query->andWhere(['=', Invoice::tableName() . '.user_id', Yii::$app->user->id]);

		return $query;
	}

	/**
	 * @param Invoice $model
	 *
	 * @return boolean
	 */
	public static function isAccessAllowed($model) {
		return $model->user_id == Yii::$app->user->id;
	}

	/**
	 * @param Invoice $model
	 *
	 * @return boolean
	 */
	public static function isAvailableDelete($model) {
		return empty($model->payments);
	}
}
