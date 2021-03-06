<?php

namespace frontend\controllers;

use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Session;

/**
 * @author MrAnger
 */
abstract class BaseController extends Controller {
	/**
	 * @var Request
	 */
	public $request;

	/**
	 * @var Session
	 */
	public $session;

	public function init() {
		parent::init();

		$this->request = Yii::$app->request;

		$this->session = Yii::$app->session;
	}

	/**
	 * @return array
	 */
	public function behaviors() {
		return [
			'accessControl' => [
				'class' => AccessControl::className(),
				'rules' => $this->getAccessRules(),
			],
			'verbFilter'    => [
				'class'   => VerbFilter::className(),
				'actions' => $this->getVerbs(),
			],
		];
	}

	/**
	 * Возвращает массив правил доступа. Переопределив данный метод можно дополнить или полностью заменить правила.
	 *
	 * @return array
	 */
	public function getAccessRules() {
		return [
			[
				'allow' => true,
				'roles' => ['@'],
			],
		];
	}

	/**
	 * @return array
	 */
	public function getVerbs() {
		return [
			'delete' => ['POST'],
		];
	}

	public function beforeAction($action) {
		$result = parent::beforeAction($action);

		if (!Yii::$app->user->isGuest) {
			/** @var User $user */
			$user = Yii::$app->user->identity;

			if ($user->profile->inn === null) {
				$profileUrl = Url::to(['/user/settings/profile'], true);

				Yii::$app->session->addFlash('danger', 'Не указан ИНН! Пожалуйста, укажите ИНН в настройках вашего профиля. ' . Html::a($profileUrl, $profileUrl));
			}
		}

		return $result;
	}
}