<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \yii\web\View $this
 */
?>
<div class="fast-buttons">
	<div class="trigger">
		•<br>•<br>•
	</div>

	<ul>
		<li>
			<?= Html::a("<i class='fa fa-money'></i> <span class='text'>Добаваить поступление</span>", ['/payment/create'], [
				'class' => 'btn btn-success btn-xs',
			]) ?>
		</li>
		<li>
			<?= Html::a("<i class='fa fa-book'></i> <span class='text'>Добаваить счёт</span>", ['/invoice/create'], [
				'class' => 'btn btn-success btn-xs',
			]) ?>
		</li>
		<li>
			<?= Html::a("<i class='fa fa-users'></i> <span class='text'>Добаваить контрагента</span>", ['/contractor/create'], [
				'class' => 'btn btn-success btn-xs',
			]) ?>
		</li>
	</ul>
</div>