<?php
namespace frontend\controllers;

use common\helpers\ContractorHelper;
use common\models\Contractor;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * @author MrAnger
 */
class ContractorController extends BaseController {
	public function actionIndex() {
		$query = Contractor::find();

		ContractorHelper::applyAccessByUser($query);

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		$dataProvider->sort->defaultOrder = [
			'name' => SORT_ASC,
		];

		return $this->render('index', [
			'dataProvider' => $dataProvider,
		]);
	}

	public function actionCreate() {
		$request = Yii::$app->request;

		$model = new Contractor([
			'user_id' => Yii::$app->user->id,
		]);

		if ($model->load($request->post()) && $model->save()) {
			Yii::$app->session->addFlash('success', 'Контрагент успешно создан.');

			return $this->redirect(['update', 'id' => $model->id]);
		}

		return $this->render('create', [
			'model' => $model,
		]);
	}

	public function actionUpdate($id) {
		$request = Yii::$app->request;

		$model = $this->findModel($id);

		if (!ContractorHelper::isAccessAllowed($model))
			throw new ForbiddenHttpException;

		if ($model->load($request->post()) && $model->save()) {
			Yii::$app->session->addFlash('success', 'Контрагент успешно изменен.');

			return $this->redirect(['update', 'id' => $model->id]);
		}

		return $this->render('update', [
			'model' => $model,
		]);
	}

	public function actionDelete($id) {
		$model = $this->findModel($id);

		if (!ContractorHelper::isAccessAllowed($model))
			throw new ForbiddenHttpException;

		$model->delete();

		Yii::$app->session->addFlash('success', 'Контрагент успешно удален.');

		return $this->redirect(Yii::$app->request->referrer);
	}

	/**
	 * @param mixed $pk
	 *
	 * @return Contractor
	 *
	 * @throws NotFoundHttpException
	 */
	protected function findModel($pk) {
		if (($model = Contractor::findOne($pk)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}
}
