<?php

namespace common\components;

use common\models\PaymentData;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * @author MrAnger
 */
class PaymentParser1c extends Component {
	const SECTION_DOCUMENT_START = 'СекцияДокумент';
	const SECTION_DOCUMENT_END = 'КонецДокумента';

	public $map = [
		'Дата'               => 'date',
		'Сумма'              => 'income',
		'Номер'              => 'document_number',
		'Код'                => 'operation_code',
		'НазначениеПлатежа'  => 'description',
		'ПлательщикСчет'     => 'payer_schet',
		'ПлательщикИНН'      => 'payer_inn',
		'Плательщик'         => 'payer_name',
		'Плательщик1'        => 'payer_name1',
		'ПлательщикБИК'      => 'payer_bank_bik',
		'ПлательщикКорсчет'  => 'payer_bank_kor_schet',
		'ПлательщикБанк'     => 'payer_bank_name',
		'ПлательщикБанк1'    => 'payer_bank_name1',
		'ПлательщикРасчСчет' => 'payer_bank_rasch_schet',
		'ПлательщикКПП'      => 'payer_bank_kpp',
		'ПолучательСчет'     => 'recipient_schet',
		'Получатель'         => 'recipient_name',
		'Получатель1'        => 'recipient_name1',
		'ПолучательИНН'      => 'recipient_inn',
		'ПолучательРасчСчет' => 'recipient_bank_rasch_schet',
		'ПолучательБанк'     => 'recipient_bank_name',
		'ПолучательБанк1'    => 'recipient_bank_name1',
		'ПолучательБИК'      => 'recipient_bank_bik',
		'ПолучательКорсчет'  => 'recipient_bank_kor_schet',
		'ПолучательКПП'      => 'recipient_bank_kpp',
	];

	/**
	 * @param string $filePath
	 *
	 * @return PaymentData[]
	 */
	public function parse($filePath) {
		$output = [];

		$documentData = [];
		$isDocumentProcessing = false;
		foreach (file($filePath) as $line) {
			$line = mb_convert_encoding(rtrim($line), "utf-8", "windows-1251");

			if (strpos($line, '=') === false) {
				$key = $line;
				$value = '';
			} else {
				list($key, $value) = explode('=', $line, 2);
			}

			if ($key == self::SECTION_DOCUMENT_START && $value == 'Платежное поручение') {
				$documentData = [];
				$isDocumentProcessing = true;
			} elseif ($isDocumentProcessing && $key == self::SECTION_DOCUMENT_END) {
				$object = new PaymentData();
				$object->load($documentData, '');
				$object->validate();

				$output[] = $object;

				$isDocumentProcessing = false;
			} elseif ($isDocumentProcessing) {
				$convertedKey = ArrayHelper::getValue($this->map, $key);

				if ($convertedKey !== null)
					$documentData[$convertedKey] = $value;
			}
		}


		return $output;
	}
}
