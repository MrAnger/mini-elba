<?php
namespace frontend\controllers;

use common\helpers\InvoiceHelper;
use common\models\Invoice;
use common\models\InvoiceItem;
use frontend\models\InvoiceSearch;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * @author MrAnger
 */
class InvoiceController extends BaseController {
	public function actionIndex() {
		$searchModel = new InvoiceSearch();

		$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

		$dataProvider->sort->defaultOrder = [
			'id' => SORT_ASC,
		];

		$dataProvider->pagination->pageSize = 30;

		return $this->render('index', [
			'searchModel'  => $searchModel,
			'dataProvider' => $dataProvider,
		]);
	}

	public function actionCreate() {
		$request = Yii::$app->request;

		$model = new Invoice([
			'user_id' => Yii::$app->user->id,
			'summary' => 0,
		]);

		$itemList = [];
		foreach ($model->items as $item)
			$itemList[$item->id] = $item;

		if ($request->isPost) {
			$model->load($request->post());
			$itemList = $this->loadItemList($model);

			$isValidate = Model::validateMultiple(array_merge([$model], $itemList));

			if ($isValidate) {
				$transaction = Yii::$app->db->beginTransaction();

				try {
					$model->save(false);

					foreach ($itemList as $item) {
						$item->invoice_id = $model->id;
						$item->save(false);
					}

					// Удаляем все айтимы не вошедшие в список выше
					InvoiceItem::deleteAll([
						'AND',
						['not in', 'id', ArrayHelper::getColumn($itemList, 'id')],
						['=', 'invoice_id', $model->id],
					]);

					// Запускаем автоматический расчет суммы счета
					$this->calculateInvoiceSummary($model);

					$transaction->commit();

					Yii::$app->session->addFlash('success', 'Счёт успешно создан.');

					return $this->redirect(['view', 'id' => $model->id]);
				} catch (\Exception $e) {
					$transaction->rollBack();

					throw $e;
				}
			}
		}

		return $this->render('create', [
			'model'    => $model,
			'itemList' => $itemList,
		]);
	}

	public function actionUpdate($id) {
		$request = Yii::$app->request;

		$model = $this->findModel($id);

		if (!InvoiceHelper::isAccessAllowed($model))
			throw new ForbiddenHttpException;

		$itemList = [];
		foreach ($model->items as $item)
			$itemList[$item->id] = $item;

		if ($request->isPost) {
			$model->load($request->post());
			$itemList = $this->loadItemList($model);

			$isValidate = Model::validateMultiple(array_merge([$model], $itemList));

			if ($isValidate) {
				$transaction = Yii::$app->db->beginTransaction();

				try {
					$model->save(false);

					foreach ($itemList as $item) {
						$item->invoice_id = $model->id;
						$item->save(false);
					}

					// Удаляем все айтимы не вошедшие в список выше
					InvoiceItem::deleteAll([
						'AND',
						['not in', 'id', ArrayHelper::getColumn($itemList, 'id')],
						['=', 'invoice_id', $model->id],
					]);

					// Запускаем автоматический расчет суммы счета
					$this->calculateInvoiceSummary($model);

					$transaction->commit();

					Yii::$app->session->addFlash('success', 'Счёт успешно изменен.');

					return $this->redirect(['view', 'id' => $model->id]);
				} catch (\Exception $e) {
					$transaction->rollBack();

					throw $e;
				}
			}
		}

		return $this->render('update', [
			'model'    => $model,
			'itemList' => $itemList,
		]);
	}

	public function actionView($id) {
		$model = $this->findModel($id);

		if (!InvoiceHelper::isAccessAllowed($model))
			throw new ForbiddenHttpException;

		return $this->render('view', [
			'model' => $model,
		]);
	}

	public function actionDelete($id) {
		$model = $this->findModel($id);

		if (!InvoiceHelper::isAccessAllowed($model))
			throw new ForbiddenHttpException;

		if (InvoiceHelper::isAvailableDelete($model)) {
			$model->delete();

			Yii::$app->session->addFlash('success', 'Счёт успешно удален.');

			return $this->redirect(Yii::$app->request->referrer);
		}

		throw new MethodNotAllowedHttpException;
	}

	public function actionGetItemPaid($itemId) {
		Yii::$app->response->format = Response::FORMAT_JSON;

		$itemModel = $this->findItemModel($itemId);

		if (!InvoiceHelper::isAccessAllowed($itemModel->invoice))
			throw new ForbiddenHttpException;

		$formatter = Yii::$app->formatter;

		$availableSum = $itemModel->getAvailableTotalPaid();

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
	 * @return Invoice
	 *
	 * @throws NotFoundHttpException
	 */
	protected function findModel($pk) {
		if (($model = Invoice::findOne($pk)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}

	/**
	 * @param mixed $pk
	 *
	 * @return InvoiceItem
	 *
	 * @throws NotFoundHttpException
	 */
	protected function findItemModel($pk) {
		if (($model = InvoiceItem::findOne($pk)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}

	/**
	 * @param Invoice $model
	 *
	 * @return InvoiceItem[]
	 */
	protected function loadItemList($model) {
		$items = [];

		foreach (Yii::$app->request->post('InvoiceItem') as $itemId => $itemData) {
			if ($itemId > 0) {
				$itemModel = $this->findItemModel($itemId);

				if ($itemModel !== null) {
					// Проверяем, действительно ли данный айтим привязан к данному счету
					if ($itemModel->invoice_id == $model->id) {
						$itemModel->load($itemData, '');
						$items[$itemModel->id] = $itemModel;
					}
				}
			} else {
				$itemData['invoice_id'] = -1;

				$itemModel = new InvoiceItem($itemData);

				$items[$itemId] = $itemModel;
			}
		}

		return $items;
	}

	/**
	 * @param Invoice $model
	 */
	protected function calculateInvoiceSummary($model) {
		$summary = 0;
		$isPaid = 0;

		foreach ($model->getItems()->all() as $item) {
			/** @var InvoiceItem $item */
			$summary += $item->summary;
		}

		if ($model->total_paid >= $summary) {
			$isPaid = 1;
		}

		$model->updateAttributes([
			'summary' => $summary,
			'is_paid' => $isPaid,
		]);

		// Устанавливаем верные пометки оплачено у позиций счёта
		foreach ($model->getItems()->all() as $item) {
			/** @var InvoiceItem $item */
			if ($item->total_paid == $item->summary) {
				if (!$item->is_paid) {
					$item->updateAttributes(['is_paid' => 1]);
				}
			} elseif ($item->total_paid > $item->summary) {
				$item->updateAttributes([
					'is_paid'    => 1,
					'total_paid' => $item->summary,
				]);
			} elseif ($item->total_paid < $item->summary) {
				$item->updateAttributes([
					'is_paid' => 0,
				]);
			}
		}
	}
}
