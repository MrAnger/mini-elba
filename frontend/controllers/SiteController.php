<?php
namespace frontend\controllers;

use common\helpers\InvoiceHelper;
use common\helpers\PaymentHelper;
use common\models\Contractor;
use common\models\Invoice;
use common\models\InvoiceItem;
use common\models\Payment;
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
		$payments = PaymentHelper::applyAccessByUser(Payment::find())->all();

		$statList = [];

		foreach ($payments as $payment) {
			/** @var Payment $payment */
			if (!isset($statList[$payment->contractor_id])) {
				$statList[$payment->contractor_id] = [
					'contractor'     => $payment->contractor,
					'invoiceCount'   => 0,
					'summary'        => 0,
					'total_paid'     => 0,
					'invoiceListUrl' => Url::to(['/invoice/index', 'contractor_id' => $payment->contractor->id]),
					'paymentListUrl' => Url::to(['/payment/index', 'contractor_id' => $payment->contractor->id]),
				];
			}

			$statList[$payment->contractor_id]['total_paid'] += $payment->income;
		}

		foreach ($statList as $statItem) {
			/** @var Contractor $contractor */
			$contractor = $statItem['contractor'];

			$statList[$contractor->id]['invoiceCount'] = $contractor->getInvoices()->count();
			$statList[$contractor->id]['summary'] += $contractor->getInvoices()->sum('summary');
		}

		// Подготавливаем расширенную информацию о должниках
		$debtorDetailList = [];

		/** @var Invoice[] $invoiceNotPaidList */
		$invoiceNotPaidList = InvoiceHelper::applyAccessByUser(Invoice::find()->where(['is_paid' => 0]))
			->all();

		foreach ($invoiceNotPaidList as $invoice) {
			if (!isset($debtorDetailList[$invoice->contractor_id])) {
				$debtorDetailList[$invoice->contractor_id] = [
					'contractor'     => $invoice->contractor,
					'invoiceListUrl' => Url::to(['/invoice/index', 'contractor_id' => $invoice->contractor->id, 'is_paid' => 0]),
					'debtorSum'      => 0,
					'invoiceList'    => [],
				];
			}

			$invoiceData = [
				'invoice'    => $invoice,
				'invoiceUrl' => Url::to(['/invoice/view', 'id' => $invoice->id]),
				'items'      => $invoice->getItems()
					->andWhere(['=', 'is_paid', 0])
					->all(),
			];

			$debtorDetailList[$invoice->contractor_id]['invoiceList'][] = $invoiceData;
			$debtorDetailList[$invoice->contractor_id]['debtorSum'] += $invoice->summary - $invoice->total_paid;
		}

		return $this->render('index', [
			'debtorList'       => $debtorList,
			'debtorDetailList' => $debtorDetailList,
			'statList'         => $statList,
		]);
	}

	public function beforeAction($action) {
		$result = parent::beforeAction($action);

		if ($action->id == 'error' && Yii::$app->user->isGuest)
			$this->layout = 'plain';

		return $result;
	}
}
