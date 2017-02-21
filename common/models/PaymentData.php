<?php

namespace common\models;

use Yii;
use yii\base\Model;

class PaymentData extends Model {
	public $date;
	public $income;
	public $document_number;
	public $operation_code;
	public $description;
	public $payer_schet;
	public $payer_inn;
	public $payer_name;
	public $payer_name1;
	public $payer_bank_bik;
	public $payer_bank_kor_schet;
	public $payer_bank_name;
	public $payer_bank_name1;
	public $payer_bank_rasch_schet;
	public $payer_bank_kpp;
	public $recipient_schet;
	public $recipient_name;
	public $recipient_name1;
	public $recipient_inn;
	public $recipient_bank_rasch_schet;
	public $recipient_bank_name;
	public $recipient_bank_name1;
	public $recipient_bank_bik;
	public $recipient_bank_kor_schet;
	public $recipient_bank_kpp;

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['date'], 'date'],
			[['income'], 'number'],
			[[
				'document_number', 'operation_code',
				'payer_schet', 'payer_inn', 'payer_bank_bik', 'payer_bank_kor_schet', 'payer_bank_rasch_schet', 'payer_bank_kpp',
				'recipient_schet', 'recipient_inn', 'recipient_bank_rasch_schet', 'recipient_bank_bik', 'recipient_bank_kor_schet', 'recipient_bank_kpp',
			], 'integer'],
			[[
				'description',
				'payer_name', 'payer_name', 'payer_bank_name', 'payer_bank_name1',
				'recipient_name', 'recipient_name1', 'recipient_bank_name', 'recipient_bank_name1',
			], 'string'],

			[[
				'document_number', 'operation_code', 'description',
				'payer_name', 'payer_name', 'payer_bank_name', 'payer_bank_name1',
				'recipient_name', 'recipient_name1', 'recipient_bank_name', 'recipient_bank_name1', 'recipient_bank_kpp',
			], 'trim'],

			[[
				'document_number', 'operation_code', 'description',
				'payer_name', 'payer_name', 'payer_bank_name', 'payer_bank_name1', 'recipient_bank_kpp',
				'recipient_name', 'recipient_name1', 'recipient_bank_name', 'recipient_bank_name1',
			], 'default'],
		];
	}
}
