<?php

namespace common\helpers;

use common\models\Contractor;
use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;


class ContractorHelper extends Model {
	/**
	 * @param ActiveQuery $query
	 *
	 * @return ActiveQuery
	 */
	public static function applyAccessByUser($query) {
		$query->andWhere(['=', Contractor::tableName() . '.user_id', Yii::$app->user->id]);

		return $query;
	}

	/**
	 * @param Contractor $model
	 *
	 * @return boolean
	 */
	public static function isAccessAllowed($model) {
		return $model->user_id == Yii::$app->user->id;
	}

	/**
	 * @param Contractor $model
	 *
	 * @return boolean
	 */
	public static function isAvailableDelete($model) {
		return ($model->getPayments()->count() == 0 && $model->getInvoices()->count() == 0);
	}
}
