<?php

/**
 * @var yii\web\View $this
 * @var \common\models\Payment $model
 */

use yii\widgets\Pjax;
use yii\widgets\DetailView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = Yii::t('app.actions', 'Viewing');

$formatter = Yii::$app->formatter;

$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('app', 'Payments'),
		'url'   => ['index'],
	],
	[
		'label' => "Поступление на " . $formatter->asCurrency($model->income) . " " . $formatter->asDate($model->date) . " от " . $model->contractor->name,
		'url'   => ['view', 'id' => $model->id],
	],
	$this->title,
];

$formatter = Yii::$app->formatter;
?>
<div>

	<p class="text-left">
		<?= Html::a(Yii::t('app.actions', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
	</p>

	<?= DetailView::widget([
		'model'      => $model,
		'attributes' => [
			'date:date',
			[
				'attribute' => 'contractor_id',
				'value'     => $model->contractor->name,
			],
			'income:currency',
			'created_at:datetime',
			'updated_at:datetime',
		],
	]) ?>

	<?php if ($model->description !== null): ?>
		<div>
			<h3>Описание</h3>

			<p><?= $model->description ?></p>
			<hr>
		</div>
	<?php endif; ?>
</div>