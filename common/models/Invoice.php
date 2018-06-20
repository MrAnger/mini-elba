<?php

namespace common\models;

use common\models\queries\InvoiceQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;


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
 * @property Contractor $contractor
 * @property User $user
 * @property InvoiceItem[] $items
 * @property Payment[] $payments
 */
class Invoice extends \yii\db\ActiveRecord {
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

			[['comment'], 'trim'],
			[['comment'], 'default'],
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

	/**
	 * @inheritdoc
	 *
	 * @return InvoiceQuery the active query used by this AR class.
	 */
	public static function find() {
		return new InvoiceQuery(get_called_class());
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getContractor() {
		return $this->hasOne(Contractor::className(), ['id' => 'contractor_id']);
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
	public function getItems() {
		return $this->hasMany(InvoiceItem::className(), ['invoice_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPayments() {
		return $this->hasMany(Payment::className(), ['id' => 'payment_id'])
			->viaTable(PaymentLinkToInvoice::tableName(), ['invoice_id' => 'id']);
	}
}
