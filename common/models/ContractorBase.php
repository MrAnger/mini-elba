<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%contractor}}".
 *
 * @property string $id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 */
class ContractorBase extends \yii\db\ActiveRecord {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{%contractor}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['name'], 'required'],
			[['name'], 'string', 'max' => 250],
			[['name'], 'unique'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id'         => Yii::t('app', 'ID'),
			'name'       => Yii::t('app', 'Name'),
			'created_at' => Yii::t('app', 'Created At'),
			'updated_at' => Yii::t('app', 'Updated At'),
		];
	}
}
