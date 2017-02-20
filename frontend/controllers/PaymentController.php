<?php
namespace frontend\controllers;

use common\helpers\InvoiceHelper;
use common\helpers\PaymentHelper;
use common\models\Invoice;
use common\models\Payment;
use frontend\models\PaymentSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author MrAnger
 */
class PaymentController extends BaseController {
	public function actionIndex() {
		$searchModel = new PaymentSearch();

		$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

		$dataProvider->sort->defaultOrder = [
			'date' => SORT_DESC,
		];

		$dataProvider->pagination->pageSize = 50;

		return $this->render('index', [
			'searchModel'  => $searchModel,
			'dataProvider' => $dataProvider,
		]);
	}

	public function actionCreate() {
		$request = Yii::$app->request;

		$model = new Payment([
			'user_id' => Yii::$app->user->id,
		]);

		if ($model->load($request->post()) && $model->save()) {
			Yii::$app->session->addFlash('success', 'Поступление успешно создано.');

			return $this->redirect(['view', 'id' => $model->id]);
		}

		return $this->render('create', [
			'model' => $model,
		]);
	}

	public function actionUpdate($id) {
		$request = Yii::$app->request;

		$model = $this->findModel($id);

		if (!PaymentHelper::isAccessAllowed($model))
			throw new ForbiddenHttpException;

		if ($model->load($request->post()) && $model->save()) {
			Yii::$app->session->addFlash('success', 'Поступление успешно изменено.');

			return $this->redirect(['view', 'id' => $model->id]);
		}

		return $this->render('update', [
			'model' => $model,
		]);
	}

	public function actionView($id) {
		$model = $this->findModel($id);

		if (!PaymentHelper::isAccessAllowed($model))
			throw new ForbiddenHttpException;

		return $this->render('view', [
			'model' => $model,
		]);
	}

	public function actionDelete($id) {
		$model = $this->findModel($id);

		if (!PaymentHelper::isAccessAllowed($model))
			throw new ForbiddenHttpException;

		$model->delete();

		Yii::$app->session->addFlash('success', 'Поступление успешно удалено.');

		return $this->redirect(Yii::$app->request->referrer);
	}

	public function actionGetInvoiceLinkData($paymentId) {
		Yii::$app->response->format = Response::FORMAT_JSON;

		$paymentModel = $this->findModel($paymentId);

		if (!PaymentHelper::isAccessAllowed($paymentModel))
			throw new ForbiddenHttpException;

		$formatter = Yii::$app->formatter;

		$availableSum = $paymentModel->getAvailableLinkSum();

		// Получаем список счетов, которые можно связать с этим поступлением
		$invoiceList = [];
		// Сначала добавляем уже привязанные счета
		foreach ($paymentModel->invoiceLinks as $link) {
			$invoiceList[] = [
				'id'         => $link->invoice->id,
				'name'       => $link->invoice->name,
				'total_paid' => $link->invoice->total_paid,
				'summary'    => $link->invoice->summary,
				'linked'     => true,
				'linked_sum' => $link->sum,
			];
		}
		// Далее добавляем те счета, которые можно привязать к этому поступлению
		$invoices = InvoiceHelper::applyAccessByUser(Invoice::find()->where([
			'AND',
			['=', 'contractor_id', $paymentModel->contractor_id],
			['=', 'is_paid', 0],
			['not in', 'in', array_keys($invoiceList)],
		])
			->orderBy(['created_at' => SORT_DESC])
		)
			->all();

		foreach ($invoices as $invoice) {
			/** @var Invoice $invoice */
			$invoiceList[] = [
				'id'         => $invoice->id,
				'name'       => $invoice->name,
				'total_paid' => $invoice->total_paid,
				'summary'    => $invoice->summary,
				'linked'     => false,
				'linked_sum' => 0,
			];
		}

		$output = [
			'availableSum' => $availableSum,
			'payment'      => $paymentModel,
			'invoiceList'  => $invoiceList,
		];

		return $output;
	}

	public function actionItemPaidForm() {
		Yii::$app->response->format = Response::FORMAT_JSON;

		$itemModel = $this->findItemModel(Yii::$app->request->post('itemId'));

		if (!InvoiceHelper::isAccessAllowed($itemModel->invoice))
			throw new ForbiddenHttpException;

		if ($itemModel->load(Yii::$app->request->post()) && $itemModel->validate()) {
			if ($itemModel->total_paid >= $itemModel->summary)
				$itemModel->is_paid = 1;
			else
				$itemModel->is_paid = 0;

			return $itemModel->save(false);
		}

		throw new BadRequestHttpException;
	}

	public function actionValidateItemPaidForm() {
		Yii::$app->response->format = Response::FORMAT_JSON;

		$itemModel = $this->findItemModel(Yii::$app->request->post('itemId'));

		if (!InvoiceHelper::isAccessAllowed($itemModel->invoice))
			throw new ForbiddenHttpException;

		$itemModel->load(Yii::$app->request->post());

		return ActiveForm::validate($itemModel);
	}

	/**
	 * @param mixed $pk
	 *
	 * @return Payment
	 *
	 * @throws NotFoundHttpException
	 */
	protected function findModel($pk) {
		if (($model = Payment::findOne($pk)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}
}
