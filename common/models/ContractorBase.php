<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%contractor}}".
 *
 * @property string $id
 * @property string $user_id
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
			[['name', 'user_id'], 'required'],
			[['name'], 'string', 'max' => 250],
			[['user_id', 'name'], 'unique', 'targetAttribute' => ['user_id', 'name'], 'message' => 'Контрагент с таким названием уже существует.'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id'         => Yii::t('app', 'ID'),
			'user_id'    => Yii::t('app', 'User'),
			'name'       => Yii::t('app', 'Name'),
			'created_at' => Yii::t('app', 'Created At'),
			'updated_at' => Yii::t('app', 'Updated At'),
		];
	}
}
