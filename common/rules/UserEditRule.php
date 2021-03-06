<?php

namespace common\rules;

use common\Rbac;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\Item;
use yii\rbac\Rule;

class UserEditRule extends Rule {
	public $name = 'isUserEditAllowed';

	/**
	 * @param integer $currentUserId
	 * @param Item $item
	 * @param array $params
	 *
	 * @return bool
	 */
	public function execute($currentUserId, $item, $params) {
		$authManager = Yii::$app->authManager;

		$editableUserId = ArrayHelper::getValue($params, 'userId', false);
		if (!$editableUserId)
			return false;

		$currentUserIsMaster = $authManager->checkAccess($currentUserId, Rbac::ROLE_MASTER);
		$editableUserIsMaster = $authManager->checkAccess($editableUserId, Rbac::ROLE_MASTER);
		$editableUserIsAdmin = $authManager->checkAccess($editableUserId, Rbac::ROLE_ADMIN);

		if ($currentUserIsMaster) {
			return true;
		} else {
			if ($editableUserIsMaster) {
				return false;
			} elseif ($editableUserIsAdmin) {
				return ($currentUserId == $editableUserId);
			} else {
				return true;
			}
		}
	}
}