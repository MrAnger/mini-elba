<?php

namespace frontend\components;

use common\models\Profile;
use common\models\User;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\db\AfterSaveEvent;

/**
 * @author MrAnger
 */
class StartUp implements BootstrapInterface {
	/**
	 * @inheritDoc
	 */
	public function bootstrap($app) {
		
	}
}