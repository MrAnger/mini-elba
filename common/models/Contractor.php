<?php

namespace common\models;

use common\models\queries\ContractorQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%contractor}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property string $inn
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 * @property Payment[] $payments
 * @property Invoice[] $invoices
 */
class Contractor extends ActiveRecord {
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
			[['inn'], 'string', 'max' => 64],
			[['user_id', 'name'], 'unique', 'targetAttribute' => ['user_id', 'name'], 'message' => 'Контрагент с таким названием уже существует.'],
			[['user_id', 'inn'], 'unique', 'targetAttribute' => ['user_id', 'inn'], 'message' => 'Контрагент с таким ИНН уже существует.'],

			[['inn'], 'trim'],
			[['inn'], 'default'],
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
			'inn'        => Yii::t('app', 'Inn'),
			'created_at' => Yii::t('app', 'Created At'),
			'updated_at' => Yii::t('app', 'Updated At'),
		];
	}

	/**
	 * @inheritdoc
	 *
	 * @return ContractorQuery the active query used by this AR class.
	 */
	public static function find() {
		return new ContractorQuery(get_called_class());
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser() {
		return $this->hasOne(User::className(), ['id' => 'user_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPayments() {
		return $this->hasMany(Payment::className(), [
			'contractor_id' => 'id',
			'user_id'       => 'user_id',
		]);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getInvoices() {
		return $this->hasMany(Invoice::className(), [
			'contractor_id' => 'id',
			'user_id'       => 'user_id',
		]);
	}
}
