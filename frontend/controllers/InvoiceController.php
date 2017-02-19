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
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * @author MrAnger
 */
class InvoiceController extends BaseController {
	public function actionIndex() {
		$searchModel = new InvoiceSearch();

		$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

		$dataProvider->sort->defaultOrder = [
			'created_at' => SORT_ASC,
		];

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

					return $this->redirect(['update', 'id' => $model->id]);
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

					return $this->redirect(['update', 'id' => $model->id]);
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

	public function actionDelete($id) {
		$model = $this->findModel($id);

		if (!InvoiceHelper::isAccessAllowed($model))
			throw new ForbiddenHttpException;

		$model->delete();

		Yii::$app->session->addFlash('success', 'Счёт успешно удален.');

		return $this->redirect(Yii::$app->request->referrer);
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
					$itemModel->load($itemData, '');
					$items[$itemModel->id] = $itemModel;
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

		foreach ($model->getItems()->all() as $item) {
			/** @var InvoiceItem $item */
			$summary += $item->summary;
		}

		$model->updateAttributes([
			'summary' => $summary,
		]);
	}
}
