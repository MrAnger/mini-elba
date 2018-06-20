<?php

namespace common\models\queries;

use common\models\Contractor;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\Contractor]].
 *
 * @see \common\models\Contractor
 */
class ContractorQuery extends ActiveQuery {
	/**
	 * @return $this
	 */
	public function byCurrentUser() {
		return $this->andWhere([Contractor::tableName() . '.user_id' => \Yii::$app->user->id]);
	}
}