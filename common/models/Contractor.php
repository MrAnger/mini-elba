<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 */
class Contractor extends ContractorBase {
	/**
	 * @inheritdoc
	 */
	public function behaviors() {
		return [
			'timestamps' => [
				'class' => TimestampBehavior::className(),
				'value' => new Expression('NOW()'),
			],
		];
	}
}
