<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%payment}}".
 *
 * @property string $id
 * @property integer $user_id
 * @property integer $contractor_id
 * @property string $date
 * @property integer $document_number
 * @property integer $operation_code
 * @property float $income
 * @property float $outcome
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 *
 */
class PaymentBase extends \yii\db\ActiveRecord {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{%payment}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['user_id', 'contractor_id', 'date'], 'required'],
			[['user_id', 'contractor_id', 'document_number', 'operation_code'], 'integer'],
			[['date', 'created_at', 'updated_at'], 'safe'],
			[['income', 'outcome'], 'number'],
			[['description'], 'string'],
			[['user_id', 'contractor_id', 'date', 'document_number', 'operation_code', 'income', 'outcome'], 'unique', 'targetAttribute' => ['user_id', 'contractor_id', 'date', 'document_number', 'operation_code', 'income', 'outcome'], 'message' => 'Поступление с выбранными параметрами уже существует.'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id'              => Yii::t('app', 'ID'),
			'user_id'         => Yii::t('app', 'User'),
			'contractor_id'   => Yii::t('app', 'Contractor'),
			'date'            => Yii::t('app', 'Date'),
			'document_number' => Yii::t('app', 'Document Number'),
			'operation_code'  => Yii::t('app', 'Operation Code'),
			'income'          => Yii::t('app', 'Income'),
			'outcome'         => Yii::t('app', 'Outcome'),
			'description'     => Yii::t('app', 'Description'),
			'created_at'      => Yii::t('app', 'Created At'),
			'updated_at'      => Yii::t('app', 'Updated At'),
		];
	}
}
