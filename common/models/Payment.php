<?php

namespace common\models;

use common\models\queries\PaymentQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%payment}}".
 *
 * @property string $id
 * @property integer $user_id
 * @property integer $contractor_id
 * @property string $date
 * @property integer $document_number
 * @property float $income
 * @property float $outcome
 * @property string $description
 * @property integer $is_include_into_stat
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Contractor $contractor
 * @property User $user
 * @property PaymentLinkToInvoice[] $invoiceLinks
 * @property Invoice[] $invoices
 * @property boolean $isLinkedComplete
 * @property string $name
 */
class Payment extends ActiveRecord {
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
		return '{{%payment}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['user_id', 'contractor_id', 'date'], 'required'],
			[['user_id', 'contractor_id', 'document_number', 'is_include_into_stat'], 'integer'],
			[['date', 'created_at', 'updated_at'], 'safe'],
			[['income', 'outcome'], 'number'],
			[['description'], 'string'],
			[['user_id', 'contractor_id', 'date', 'document_number', 'income', 'outcome'], 'unique', 'targetAttribute' => ['user_id', 'contractor_id', 'date', 'document_number', 'income', 'outcome'], 'message' => 'Поступление с выбранными параметрами уже существует.'],

			[['income'], 'required'],
			[['description', 'document_number', 'is_include_into_stat'], 'trim'],
			[['description', 'document_number'], 'default'],
			[['is_include_into_stat'], 'default', 'value' => 1],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id'                   => Yii::t('app', 'ID'),
			'user_id'              => Yii::t('app', 'User'),
			'contractor_id'        => Yii::t('app', 'Contractor'),
			'date'                 => Yii::t('app', 'Date'),
			'document_number'      => Yii::t('app', 'Document Number'),
			'income'               => Yii::t('app', 'Income'),
			'outcome'              => Yii::t('app', 'Outcome'),
			'is_include_into_stat' => Yii::t('app', 'Is Include Into Stat'),
			'description'          => Yii::t('app', 'Description'),
			'created_at'           => Yii::t('app', 'Created At'),
			'updated_at'           => Yii::t('app', 'Updated At'),
		];
	}

	/**
	 * @inheritdoc
	 *
	 * @return PaymentQuery the active query used by this AR class.
	 */
	public static function find() {
		return new PaymentQuery(get_called_class());
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
	public function getInvoiceLinks() {
		return $this->hasMany(PaymentLinkToInvoice::className(), ['payment_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getInvoices() {
		return $this->hasMany(Invoice::className(), ['id' => 'invoice_id'])
			->viaTable(PaymentLinkToInvoice::tableName(), ['payment_id' => 'id']);
	}

	/**
	 * @return bool
	 */
	public function getIsLinkedComplete() {
		$linkedSum = 0;
		foreach ($this->invoiceLinks as $link) {
			$linkedSum += $link->sum;
		}

		return ($linkedSum >= $this->income);
	}

	/**
	 * @param integer $invoiceId
	 *
	 * @return PaymentLinkToInvoice
	 */
	public function getInvoiceLink($invoiceId) {
		return PaymentLinkToInvoice::findOne([
			'payment_id' => $this->id,
			'invoice_id' => $invoiceId,
		]);
	}

	/**
	 * @return string
	 */
	public function getName() {
		$formatter = Yii::$app->formatter;

		return "Поступление на " . $formatter->asCurrency($this->income) . " " . $formatter->asDate($this->date) . " от " . $this->contractor->name;
	}
}
