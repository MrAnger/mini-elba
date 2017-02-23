<?php

namespace common\models;

use common\helpers\ContractorHelper;
use common\helpers\InvoiceHelper;
use common\helpers\PaymentHelper;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class AccountData extends Model {
	public $accountInn;
	public $contractors = [];
	public $invoices = [];
	public $invoicesItems = [];
	public $payments = [];
	public $paymentsLinks = [];

	/** @var User */
	private $userModel;

	private $contractorMap = [];
	private $invoiceMap = [];
	private $paymentMap = [];

	/**
	 * @inheritdoc
	 */
	public function init() {
		parent::init();

		$this->userModel = Yii::$app->user->identity;
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['accountInn'], 'integer'],
			[['contractors', 'invoices', 'payments', 'paymentsLinks', 'invoicesItems'], 'safe'],

			[['accountInn', 'contractors', 'invoices', 'payments', 'paymentsLinks', 'invoicesItems'], 'trim'],
			[['accountInn'], 'default'],
			[['contractors', 'invoices', 'payments', 'paymentsLinks', 'invoicesItems'], 'default', 'value' => []],
		];
	}

	public function exportData() {
		$this->setExportAccountInn();
		$this->setExportContractors();
		$this->setExportInvoices();
		$this->setExportPayments();

		return Json::encode($this->attributes);
	}

	/**
	 * @param string $filePath
	 *
	 * @return array
	 */
	public function importData($filePath) {
		$output = [
			'state'  => true,
			'errors' => [],
		];

		$accountData = new static(Json::decode(file_get_contents($filePath)));

		if (!$accountData->validate()) {
			$output['state'] = false;
			$output['errors'][] = 'Данные импорта не прошли валидацию.';

			return $output;
		}

		$result = $accountData->setImportAccountInn();

		if (!$result['state'])
			return $result;

		$result = $accountData->setImportContractors();

		if (!$result['state'])
			return $result;

		$result = $accountData->setImportInvoices();

		if (!$result['state'])
			return $result;

		$result = $accountData->setImportPayments();

		if (!$result['state'])
			return $result;

		return $output;
	}

	private function setExportAccountInn() {
		$this->accountInn = $this->userModel->profile->inn;
	}

	private function setExportContractors() {
		$query = ContractorHelper::applyAccessByUser(Contractor::find()->orderBy(['created_at' => SORT_DESC]));

		foreach ($query->batch() as $batch) {
			foreach ($batch as $model)
				$this->contractors[] = $this->getExportNeedfulAttributes($model);
		}
	}

	private function setExportInvoices() {
		$query = InvoiceHelper::applyAccessByUser(Invoice::find()->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]));

		foreach ($query->batch() as $batch) {
			foreach ($batch as $model) {
				/** @var Invoice $model */

				$this->invoices[] = $this->getExportNeedfulAttributes($model);

				foreach ($model->items as $item)
					$this->invoicesItems[] = $this->getExportNeedfulAttributes($item);
			}
		}
	}

	private function setExportPayments() {
		$query = PaymentHelper::applyAccessByUser(Payment::find()->orderBy(['created_at' => SORT_DESC]));

		foreach ($query->batch() as $batch) {
			foreach ($batch as $model) {
				/** @var Payment $model */

				$this->payments[] = $this->getExportNeedfulAttributes($model);

				foreach ($model->invoiceLinks as $item)
					$this->paymentsLinks[] = $this->getExportNeedfulAttributes($item);
			}
		}
	}

	private function setImportAccountInn() {
		$output = [
			'state'  => true,
			'errors' => [],
		];

		$result = $this->userModel->profile->updateAttributes(['inn' => $this->accountInn]);

		if (!$result && $this->userModel->profile->inn != $this->accountInn) {
			$output['state'] = false;
			$output['errors'][] = "Не удалось импортировать ИНН аккаунта. Импорт отменен.";

			return $output;
		}

		return $output;
	}

	private function setImportContractors() {
		$output = [
			'state'  => true,
			'errors' => [],
		];

		foreach (array_reverse($this->contractors) as $contractorRaw) {
			$contractorModel = new Contractor(array_merge(
					['user_id' => $this->userModel->id],
					$this->getImportNeedfulAttributes($contractorRaw))
			);

			if (!$contractorModel->save()) {
				$output['state'] = false;
				$output['errors'][] = "Не удалось импортировать контрагента: $contractorModel->name. Импорт отменен.";

				return $output;
			}

			$this->contractorMap[$contractorRaw['id']] = $contractorModel->id;
		}

		return $output;
	}

	private function setImportInvoices() {
		$output = [
			'state'  => true,
			'errors' => [],
		];

		foreach (array_reverse($this->invoices) as $invoiceRaw) {
			$invoiceModel = new Invoice(array_merge(
					[
						'user_id'       => $this->userModel->id,
						'contractor_id' => ArrayHelper::getValue($this->contractorMap, $invoiceRaw['contractor_id']),
					],
					$this->getImportNeedfulAttributes($invoiceRaw))
			);

			if (!$invoiceModel->save()) {
				$output['state'] = false;
				$output['errors'][] = "Не удалось импортировать счет: $invoiceModel->name. Импорт отменен.";

				return $output;
			}

			$this->invoiceMap[$invoiceRaw['id']] = $invoiceModel->id;
		}

		foreach (array_reverse($this->invoicesItems) as $invoiceItemRaw) {
			$invoiceItemModel = new InvoiceItem(array_merge(
					['invoice_id' => ArrayHelper::getValue($this->invoiceMap, $invoiceItemRaw['invoice_id'])],
					$this->getImportNeedfulAttributes($invoiceItemRaw))
			);

			if (!$invoiceItemModel->save()) {
				$output['state'] = false;
				$output['errors'][] = "Не удалось импортировать позицию счета: $invoiceItemModel->name. Импорт отменен.";

				return $output;
			}
		}

		return $output;
	}

	private function setImportPayments() {
		$output = [
			'state'  => true,
			'errors' => [],
		];

		foreach (array_reverse($this->payments) as $paymentRaw) {
			$paymentModel = new Payment(array_merge(
					[
						'user_id'       => $this->userModel->id,
						'contractor_id' => ArrayHelper::getValue($this->contractorMap, $paymentRaw['contractor_id']),
					],
					$this->getImportNeedfulAttributes($paymentRaw))
			);

			if (!$paymentModel->save()) {
				$output['state'] = false;
				$output['errors'][] = "Не удалось импортировать поступление: $paymentModel->name. Импорт отменен.";

				return $output;
			}

			$this->paymentMap[$paymentRaw['id']] = $paymentModel->id;
		}

		foreach (array_reverse($this->paymentsLinks) as $paymentLinkRaw) {
			$paymentLinkModel = new PaymentLinkToInvoice(array_merge(
					[
						'payment_id' => ArrayHelper::getValue($this->paymentMap, $paymentLinkRaw['payment_id']),
						'invoice_id' => ArrayHelper::getValue($this->invoiceMap, $paymentLinkRaw['invoice_id']),
					],
					$this->getImportNeedfulAttributes($paymentLinkRaw))
			);

			if (!$paymentLinkModel->save()) {
				$output['state'] = false;
				$output['errors'][] = "Не удалось импортировать связку поступления. Импорт отменен.";

				return $output;
			}
		}

		return $output;
	}

	/**
	 * @param Model $model
	 *
	 * @return array
	 */
	private function getExportNeedfulAttributes($model) {
		$needlessAttributes = ['user_id', 'created_at', 'updated_at'];

		$attributes = $model->attributes;

		foreach ($attributes as $key => $value) {
			if (array_search($key, $needlessAttributes) !== false) {
				unset($attributes[$key]);
			}
		}

		return $attributes;
	}

	/**
	 * @param array $attributes
	 *
	 * @return array
	 */
	private function getImportNeedfulAttributes($attributes) {
		$needlessAttributes = ['id', 'invoice_id', 'contractor_id', 'payment_id'];

		foreach ($attributes as $key => $value) {
			if (array_search($key, $needlessAttributes) !== false) {
				unset($attributes[$key]);
			}
		}

		return $attributes;
	}
}
