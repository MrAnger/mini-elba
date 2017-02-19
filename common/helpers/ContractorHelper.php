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
	 */
	public static function applyAccessByUser($query) {
		$query->andWhere(['=', Contractor::tableName() . '.user_id', Yii::$app->user->id]);
	}

	/**
	 * @param Contractor $model
	 *
	 * @return boolean
	 */
	public static function isAccessAllowed($model) {
		return $model->user_id == Yii::$app->user->id;
	}
}
