<?php

namespace frontend\controllers;

use common\helpers\InvoiceHelper;
use common\helpers\PaymentHelper;
use common\models\Contractor;
use common\models\data\DebtorStatData;
use common\models\data\PaymentStatData;
use common\models\Invoice;
use common\models\InvoiceItem;
use common\models\Payment;
use common\models\User;
use common\Rbac;
use frontend\models\PaymentGraphForm;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
		// Список должников
		$debtorStatList = $this->getDebtorStatList();

		// Список выплат
		$paymentStatList = $this->getPaymentStatList();

		// Подготавливаем информацию о финансах для графика
		$paymentGraphForm = new PaymentGraphForm();
		$paymentGraphData = $paymentGraphForm->calculate(Yii::$app->request->getQueryParams());
		$paymentGraphData = $paymentGraphForm->formatData($paymentGraphData);

		return $this->render('index', [
			'debtorStatList'   => $debtorStatList,
			'paymentStatList'  => $paymentStatList,
			'paymentGraphData' => $paymentGraphData,

			'paymentGraphForm' => $paymentGraphForm,
		]);
	}

	public function actionGetPaymentGraphData() {
		Yii::$app->response->format = Response::FORMAT_JSON;

		$paymentGraphForm = new PaymentGraphForm();
		$paymentGraphData = $paymentGraphForm->calculate(Yii::$app->request->getQueryParams());

		return $paymentGraphForm->formatData($paymentGraphData);
	}

	public function actionAuthUser($id) {
		if (!Yii::$app->user->can(Rbac::ADMIN_ACCESS)) {
			throw new ForbiddenHttpException();
		}

		/** @var User $model */
		$model = User::findOne($id);

		if ($model === null) {
			throw new NotFoundHttpException();
		}

		Yii::$app->user->login($model);

		return $this->redirect(['index']);
	}

	/**
	 * @return DebtorStatData[]
	 */
	private function getDebtorStatList() {
		/** @var DebtorStatData[] $output */
		$output = [];

		$query = Invoice::find()
			->byCurrentUser()
			->notPaid()
			->joinWith('contractor')
			->joinWith('items')
			->orderBy([
				'created_at' => SORT_DESC,
				'id'         => SORT_DESC,
			]);

		foreach ($query->batch() as $batch) {
			foreach ($batch as $invoice) {
				/** @var Invoice $invoice */
				if (!array_key_exists($invoice->contractor_id, $output)) {
					$output[$invoice->contractor_id] = new DebtorStatData([
						'contractor'   => $invoice->contractor,
						'invoiceCount' => 0,
						'debtorSum'    => 0,
						'invoiceList'  => [],
						'options'      => [
							'invoiceListUrl' => Url::to(['/invoice/index', 'contractor_id' => $invoice->contractor->id, 'is_paid' => 0]),
						],
					]);
				}

				$output[$invoice->contractor_id]->invoiceList[] = [
					$invoice,
					Url::to(['/invoice/view', 'id' => $invoice->id]),
					array_filter($invoice->items, function (InvoiceItem $model) {
						return ($model->is_paid == 0);
					}),
				];

				$output[$invoice->contractor_id]->invoiceCount++;
				$output[$invoice->contractor_id]->debtorSum += ($invoice->summary - $invoice->total_paid);
			}
		}

		return array_values($output);
	}

	/**
	 * @return PaymentStatData[]
	 */
	private function getPaymentStatList() {
		/** @var PaymentStatData[] $output */
		$output = [];

		$query = Payment::find()
			->byCurrentUser()
			->includedIntoStat()
			->joinWith('contractor contractor')
			->orderBy(['contractor.name' => SORT_ASC]);

		foreach ($query->batch() as $batch) {
			foreach ($batch as $payment) {
				/** @var Payment $payment */
				if (!array_key_exists($payment->contractor_id, $output)) {
					$output[$payment->contractor_id] = new PaymentStatData([
						'contractor'   => $payment->contractor,
						'invoiceCount' => count($payment->contractor->invoices),
						'summary'      => array_sum(ArrayHelper::getColumn($payment->contractor->invoices, 'summary')),
						'paid'         => 0,
						'options'      => [
							'invoiceListUrl' => Url::to(['/invoice/index', 'contractor_id' => $payment->contractor->id]),
							'paymentListUrl' => Url::to(['/payment/index', 'contractor_id' => $payment->contractor->id]),
						],
					]);
				}

				$output[$payment->contractor_id]->paid += $payment->income;
			}
		}

		return array_values($output);
	}

	public function beforeAction($action) {
		$result = parent::beforeAction($action);

		if ($action->id == 'error' && Yii::$app->user->isGuest)
			$this->layout = 'plain';

		return $result;
	}
}
