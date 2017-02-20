<?php
namespace frontend\controllers;

use common\helpers\InvoiceHelper;
use common\models\Invoice;
use Yii;
use yii\helpers\Url;

/**
 * @author MrAnger
 */
class SiteController extends BaseController {
	public function getAccessRules() {
		return [
			[
				'allow'   => true,
				'roles'   => ['@', '?'],
				'actions' => ['error'],
			],
			[
				'allow' => true,
				'roles' => ['@'],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function actions() {
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

	public function actionIndex() {
		// Подготавливаем информацию о должниках
		$queryDebtors = Invoice::find()
			->where([
				'is_paid' => 0,
			]);

		InvoiceHelper::applyAccessByUser($queryDebtors);

		$debtorList = [];

		foreach ($queryDebtors->all() as $invoice) {
			/** @var Invoice $invoice */
			if (!isset($debtorList[$invoice->contractor_id])) {
				$debtorList[$invoice->contractor_id] = [
					'contractor'     => $invoice->contractor,
					'invoiceCount'   => 0,
					'debtorSum'      => 0,
					'invoiceListUrl' => Url::to(['/invoice/index', 'contractor_id' => $invoice->contractor->id, 'is_paid' => 0]),
				];
			}

			$debtorList[$invoice->contractor_id]['invoiceCount']++;
			$debtorList[$invoice->contractor_id]['debtorSum'] += ($invoice->summary - $invoice->total_paid);
		}

		// Подготавливаем суммарную статистику за все время
		$invoices = InvoiceHelper::applyAccessByUser(Invoice::find())->all();

		$statList = [];

		foreach ($invoices as $invoice) {
			/** @var Invoice $invoice */
			if (!isset($statList[$invoice->contractor_id])) {
				$statList[$invoice->contractor_id] = [
					'contractor'            => $invoice->contractor,
					'invoiceCount'          => 0,
					'summary'               => 0,
					'total_paid'            => 0,
					'invoiceListUrl'        => Url::to(['/invoice/index', 'contractor_id' => $invoice->contractor->id]),
					'invoicePaidListUrl'    => Url::to(['/invoice/index', 'contractor_id' => $invoice->contractor->id, 'is_paid' => 1]),
				];
			}

			$statList[$invoice->contractor_id]['invoiceCount']++;
			$statList[$invoice->contractor_id]['summary'] += $invoice->summary;
			$statList[$invoice->contractor_id]['total_paid'] += $invoice->total_paid;
		}

		return $this->render('index', [
			'debtorList' => $debtorList,
			'statList'   => $statList,
		]);
	}

	public function beforeAction($action) {
		$result = parent::beforeAction($action);

		if ($action->id == 'error' && Yii::$app->user->isGuest)
			$this->layout = 'plain';

		return $result;
	}
}
