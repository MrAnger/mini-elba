<?php

namespace frontend\models;

use common\models\Contractor;
use common\models\Invoice;
use common\models\Payment;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class PaymentGraphForm extends Model {
	/**
	 * @var integer[]
	 */
	public $contractorIds;

	public $dateRange;

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['contractorIds'], 'safe'],
			[['dateRange'], 'string'],

			[['contractorIds', 'dateRange'], 'trim'],
			[['dateRange', 'contractorIds'], 'default'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'contractorIds' => 'Контрагенты',
			'dateRange'     => 'Дата',
		];
	}

	/**
	 * @param array $params
	 * @param array $overriddenParams
	 *
	 * @return array
	 */
	public function calculate($params, $overriddenParams = []) {
		$this->load($params);

		$this->setAttributes($overriddenParams, false);

		$this->validate();
		$this->clearErrors();

		$output = [];

		list($dateFrom, $dateTo) = $this->getDateRange();
		$this->dateRange = "$dateFrom - $dateTo";

		$this->fixContractorIds();

		$query = Payment::find()
			->byCurrentUser()
			->includedIntoStat()
			->joinWith('contractor')
			->andWhere(['between', 'date', $dateFrom, $dateTo]);

		$query->andFilterWhere(['in', 'contractor_id', $this->contractorIds]);

		// Подготавливаем каркас данных
		$date = date_create_from_format('Y-m-d', $dateFrom);
		do {
			$output[$this->formatDate($date)] = [];

			$date->add(date_interval_create_from_date_string("+1 months"));
		} while ($date->format('Ym') <= date_create_from_format('Y-m-d', $dateTo)->format('Ym'));

		// Заполняем массив данными
		foreach ($query->batch() as $batch) {
			/** @var Payment $payment */
			foreach ($batch as $payment) {
				foreach ($output as $dataIndex => &$data) {
					if (!array_key_exists($payment->contractor_id, $data)) {
						$data[$payment->contractor_id] = [
							'name'  => $payment->contractor->name,
							'value' => 0,
						];
					}

					if ($this->formatDate($payment->date) == $dataIndex) {
						$data[$payment->contractor_id]['value'] += $payment->income;
					}

					unset($data);
				}
			}
		}

		return $output;
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 *
	 * @throws
	 */
	public function formatData($data) {
		$formatter = Yii::$app->formatter;

		$graphData = [];
		$graphValues = [];

		$isFirstColumnSetted = false;
		foreach ($data as $dateName => $item) {
			if (!$isFirstColumnSetted) {
				$tmp = array_merge(['Дата'], ArrayHelper::getColumn($item, 'name'));

				$graphData[] = $tmp;

				$isFirstColumnSetted = true;
			}

			$tmp = [$dateName];

			$sum = 0;
			foreach ($item as $contractorData) {
				$tmp[] = ArrayHelper::getValue($contractorData, 'value');
				$sum += ArrayHelper::getValue($contractorData, 'value');
			}

			if ($sum > 0) {
				$graphValues[] = $sum;
			}

			$graphData[] = $tmp;
		}

		// Hack :)
		if (empty($graphValues)) {
			$graphValues[] = 0;
		}

		return [
			'graphData' => $graphData,

			'min'   => min(array_values($graphValues)),
			'max'   => max(array_values($graphValues)),
			'avg'   => array_sum($graphValues) / count($graphValues),
			'total' => array_sum($graphValues),

			'minFormatted'   => $formatter->asCurrency(min(array_values($graphValues))),
			'maxFormatted'   => $formatter->asCurrency(max(array_values($graphValues))),
			'avgFormatted'   => $formatter->asCurrency(array_sum($graphValues) / count($graphValues)),
			'totalFormatted' => $formatter->asCurrency(array_sum($graphValues)),
		];
	}

	/**
	 * @return array
	 */
	public function getContractorDropdownList() {
		static $cache;

		if ($cache === null) {
			$query = Contractor::find()
				->byCurrentUser()
				->orderBy(['name' => SORT_ASC]);

			$cache = ArrayHelper::map($query->all(), 'id', 'name');
		}

		return $cache;
	}

	/**
	 * @return array
	 */
	private function getDateRange() {
		if ($this->dateRange !== null) {
			return [
				explode(' - ', $this->dateRange)[0],
				explode(' - ', $this->dateRange)[1],
			];
		}

		$dateFrom = date_create();
		$dateFrom->setDate($dateFrom->format('Y'), 1, 1);

		$dateTo = date_create();
		$dateTo->setDate($dateTo->format('Y'), $dateTo->format('m'), 1);
		$dateTo->add(date_interval_create_from_date_string("+1 months"));
		$dateTo->add(date_interval_create_from_date_string("-1 days"));

		return [
			$dateFrom->format('Y-m-d'),
			$dateTo->format('Y-m-d'),
		];
	}

	private function fixContractorIds() {
		if ($this->contractorIds === null) {
			$this->contractorIds = array_keys($this->getContractorDropdownList());
		}
	}

	/**
	 * @param string|\DateTime $date
	 *
	 * @return string
	 */
	private function formatDate($date) {
		if (is_string($date))
			$date = date_create($date);

		$monthNameMap = [
			'01' => 'Январь',
			'02' => 'Февраль',
			'03' => 'Март',
			'04' => 'Апрель',
			'05' => 'Май',
			'06' => 'Июнь',
			'07' => 'Июль',
			'08' => 'Август',
			'09' => 'Сентябрь',
			'10' => 'Октрябрь',
			'11' => 'Ноябрь',
			'12' => 'Декабрь',
		];

		return ArrayHelper::getValue($monthNameMap, $date->format('m')) . $date->format(' Y');
	}

	public function formName() {
		return '';
	}
}
