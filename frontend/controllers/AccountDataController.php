<?php
namespace frontend\controllers;

use common\models\AccountData;
use common\models\Contractor;
use common\models\User;
use Yii;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * @author MrAnger
 */
class AccountDataController extends BaseController {
	public function actionExport() {
		/** @var User $userModel */
		$userModel = Yii::$app->user->identity;

		$accountData = new AccountData();

		Yii::$app->response->sendContentAsFile($accountData->exportData(), "$userModel->username - backup(" . date_format(date_create(), 'd-m-Y') . ") - " . time() . ".json");
	}

	public function actionImport() {
		Yii::$app->response->format = Response::FORMAT_JSON;

		$request = Yii::$app->request;

		/** @var User $userModel */
		$userModel = Yii::$app->user->identity;

		$output = [
			'state'  => true,
			'errors' => [],
		];

		$uploadedFile = UploadedFile::getInstanceByName('file');

		if ($uploadedFile === null) {
			$output['state'] = false;
			$output['errors'][] = 'Не был получен файл импорта.';

			return $output;
		}

		$accountData = new AccountData();

		$transaction = Yii::$app->db->beginTransaction();
		try {
			// Удаляем старые записи, перед импортом
			$userModel->profile->updateAttributes(['inn' => null]);
			Contractor::deleteAll(['user_id' => $userModel->id]);

			$output = $accountData->importData($uploadedFile->tempName);

			if ($output['state'] === true) {
				$transaction->commit();

				Yii::$app->session->addFlash('success', 'Импорт успешно произведён.');
			} else {
				$transaction->rollBack();
			}

			return $output;
		} catch (\Exception $e) {
			$transaction->rollBack();

			$output['state'] = false;
			$output['errors'][] = 'При импорте произошла ошибка. Изменения не были применены.';

			return $output;
		}
	}
}
