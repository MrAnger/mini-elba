<?php

namespace common\models;

use Yii;

/**
 * @property string $inn
 */
class Profile extends \dektrium\user\models\Profile {
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return array_merge(parent::rules(), [
			[['inn'], 'string', 'max' => 64],
			[['inn'], 'trim'],
			[['inn'], 'default'],
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return array_merge(parent::attributeLabels(), [
			'inn' => Yii::t('app', 'Inn'),
		]);
	}
} 