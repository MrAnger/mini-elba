<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%invoice}}".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $contractor_id
 * @property string $name
 * @property float $summary
 * @property float $total_paid
 * @property boolean $is_paid
 * @property string $comment
 * @property string $created_at
 * @property string $updated_at
 *
 */
class InvoiceBase extends \yii\db\ActiveRecord {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{%invoice}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['user_id', 'contractor_id', 'name', 'summary'], 'required'],
			[['user_id', 'contractor_id'], 'integer'],
			[['summary', 'total_paid'], 'number'],
			[['is_paid'], 'boolean'],
			[['comment'], 'string'],
			[['created_at', 'updated_at'], 'safe'],
			[['name'], 'string', 'max' => 250],
			[['user_id', 'name'], 'unique', 'targetAttribute' => ['user_id', 'name'], 'message' => 'Счет с данным названием уже существует.'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id'            => Yii::t('app', 'ID'),
			'user_id'       => Yii::t('app', 'User'),
			'contractor_id' => Yii::t('app', 'Contractor'),
			'name'          => Yii::t('app', 'Name'),
			'summary'       => Yii::t('app', 'Summary'),
			'total_paid'    => Yii::t('app', 'Total Paid'),
			'is_paid'       => Yii::t('app', 'Is Paid'),
			'comment'       => Yii::t('app', 'Comment'),
			'created_at'    => Yii::t('app', 'Created At'),
			'updated_at'    => Yii::t('app', 'Updated At'),
		];
	}
}
