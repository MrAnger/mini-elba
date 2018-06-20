<?php

namespace common\models\queries;

use common\models\Invoice;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\Invoice]].
 *
 * @see \common\models\Invoice
 */
class InvoiceQuery extends ActiveQuery {
	/**
	 * @return $this
	 */
	public function byCurrentUser() {
		return $this->andWhere([Invoice::tableName() . '.user_id' => \Yii::$app->user->id]);
	}

	/**
	 * @return $this
	 */
	public function paid() {
		return $this->andWhere([Invoice::tableName() . '.is_paid' => 1]);
	}

	/**
	 * @return $this
	 */
	public function notPaid() {
		return $this->andWhere([Invoice::tableName() . '.is_paid' => 0]);
	}
}