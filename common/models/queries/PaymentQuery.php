<?php

namespace common\models\queries;

use common\models\Payment;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\Payment]].
 *
 * @see \common\models\Payment
 */
class PaymentQuery extends ActiveQuery {
	/**
	 * @return $this
	 */
	public function byCurrentUser() {
		return $this->andWhere([Payment::tableName() . '.user_id' => \Yii::$app->user->id]);
	}

	/**
	 * @return $this
	 */
	public function includedIntoStat() {
		return $this->andWhere([Payment::tableName() . '.is_include_into_stat' => 1]);
	}
}