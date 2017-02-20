<?php
namespace frontend\controllers;

use common\helpers\PaymentHelper;
use common\models\Payment;
use frontend\models\PaymentSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

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

	public function actionGetItemPaid($itemId) {
		Yii::$app->response->format = Response::FORMAT_JSON;

		$itemModel = $this->findItemModel($itemId);

		if (!InvoiceHelper::isAccessAllowed($itemModel->invoice))
			throw new ForbiddenHttpException;

		$formatter = Yii::$app->formatter;

		$availableSum = $itemModel->invoice->total_paid;

		foreach ($itemModel->invoice->items as $item) {
			if ($item->id == $itemModel->id)
				continue;

			$availableSum -= $item->total_paid;
		}

		if ($availableSum > $itemModel->summary) {
			$availableSum = $itemModel->summary;
		}

		$output = [
			'item'             => $itemModel->attributes,
			'formattedSummary' => $formatter->asCurrency($itemModel->summary),
			'availableSum'     => $availableSum,
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
