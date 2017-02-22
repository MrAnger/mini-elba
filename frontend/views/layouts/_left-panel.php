<?php

use common\Rbac;
use common\models\Feedback;
use common\models\Review;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \yii\web\View $this
 */

$user = Yii::$app->user;

/** @var \common\models\User $userModel */
$userModel = $user->identity;

$mainMenuItems = [
	[
		'label' => 'Деньги',
		'url'   => ['/payment/index'],
		'icon'  => 'fa fa-money',
	],
	[
		'label' => 'Счета',
		'url'   => ['/invoice/index'],
		'icon'  => 'fa fa-book',
	],
	[
		'label' => 'Контрагенты',
		'url'   => ['/contractor/index'],
		'icon'  => 'fa fa-users',
	],
];

?>
<?php
/** @var \common\components\UserBuddy $userBuddy */
$userBuddy = Yii::$app->userBuddy;

/** @var \common\models\User $userIdentity */
$userIdentity = $user->getIdentity();

$roleList = $userBuddy->getTranslatedRoleListForUser($user->id)
?>
<div class="media profile-left">
	<a class="pull-left" href="<?= Url::to(['/user/settings/profile']) ?>">
		<?= Html::img($userModel->profile->getAvatarUrl(48), [
			'class' => 'img-rounded',
			'alt'   => $userModel->username,
		]) ?>
	</a>

	<div class="media-body">
		<h4 class="media-heading"><b><?= $userIdentity->displayName ?></b><br/><?= $userIdentity->email ?></h4>
		<small class="text-muted"><?= implode(", ", $roleList) ?></small>

		<div class="row">
			<div class="col-md-6">
				<div class="file-upload" data-upload-url="<?= Url::to(['/account-data/import']) ?>"
					 data-callback-name="accountDataImportCallback"
					 data-upload-confirm="Все ваши данные удаляться и будут заменены данными из файла импорта. Продолжить?"
					 style="display: block;">
					<button class="btn btn-warning btn-xs" style="width: 100%;">Импорт</button>
					<input name="file" type="file">
				</div>
			</div>
			<div class="col-md-6">
				<?= Html::a('Экспорт', ['/account-data/export'], [
					'class'               => 'btn btn-success btn-xs js-export',
					'style'               => 'display: block;',
					'data-export-confirm' => 'Все ваши данные будут сохранены в файл. Продолжить?',
				]) ?>
			</div>
		</div>
	</div>
</div>
<!-- media -->

<h5 class="leftpanel-title">Меню</h5>
<ul class="nav nav-pills nav-stacked">
	<li>
		<a href="<?= Yii::$app->homeUrl ?>">
			<i class="fa fa-home"></i> <span>Главная страница</span>
		</a>
	</li>
	<?php foreach ($mainMenuItems as $item): ?>
		<?php if (isset($item['items'])): ?>
			<li class="parent">
				<a href="#">
					<i class="<?= ArrayHelper::getValue($item, 'icon', 'fa fa-bars') ?>"></i>
					<span><?= $item['label'] ?></span>

					<?php if (ArrayHelper::getValue($item, 'count', 0) > 0): ?>
						<i class="badge"><?= ArrayHelper::getValue($item, 'count', 0) ?></i>
					<?php endif; ?>
				</a>

				<ul class="children">
					<?php foreach ($item['items'] as $item): ?>
						<li>
							<a href="<?= Url::to($item['url']) ?>">
								<i class="<?= ArrayHelper::getValue($item, 'icon', 'fa fa-home') ?>"></i>
								<span><?= $item['label'] ?></span>

								<?php if (ArrayHelper::getValue($item, 'count', 0) > 0): ?>
									<i class="badge"><?= ArrayHelper::getValue($item, 'count', 0) ?></i>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach ?>
				</ul>
			</li>
			<?php continue;endif ?>
		<li>
			<a href="<?= Url::to($item['url']) ?>">
				<i class="<?= ArrayHelper::getValue($item, 'icon', 'fa fa-home') ?>"></i>
				<span><?= $item['label'] ?></span>
			</a>
		</li>
	<?php endforeach ?>
</ul>
<script type="text/javascript">
	function accountDataImportCallback(response) {
		if (response.state) {
			location.reload();
		} else {
			alert(response.errors.join("\n"));
		}
	}

	function accountDataExportCallback(response) {
		if (response.state) {
			location.reload();
		} else {
			alert(response.errors.join("\n"));
		}
	}
</script>