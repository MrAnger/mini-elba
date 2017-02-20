<?php

/**
 * @var yii\web\View $this
 * @var \common\models\Invoice $model
 */

use yii\widgets\Pjax;
use yii\widgets\DetailView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = Yii::t('app.actions', 'Viewing');

$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('app', 'Invoices'),
		'url'   => ['index'],
	],
	[
		'label' => $model->name,
		'url'   => ['view', 'id' => $model->id],
	],
	$this->title,
];

$formatter = Yii::$app->formatter;

$debts = $model->summary - $model->total_paid;
$debtString = ($debts) ? "<br><span class='text-danger'>Задолженность: " . $formatter->asCurrency($debts) . "</span>" : null;
?>
<div>

	<p class="text-left">
		<?= Html::a(Yii::t('app.actions', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
	</p>

	<?= DetailView::widget([
		'model'      => $model,
		'attributes' => [
			'name',
			[
				'attribute' => 'contractor_id',
				'value'     => $model->contractor->name,
			],
			[
				'label'  => 'Баланс',
				'format' => 'raw',
				'value'  => "<i class='" . (($model->is_paid) ? 'text-success' : 'text-danger') . "'>" . $formatter->asCurrency($model->total_paid) . "</i> / <b>" . $formatter->asCurrency($model->summary) . "</b>$debtString",
			],
			'created_at:datetime',
			'updated_at:datetime',
		],
	]) ?>

	<?php if ($model->comment !== null): ?>
		<div>
			<h3>Комментарий</h3>

			<p><?= $model->comment ?></p>
		</div>
	<?php endif; ?>

	<?php $pjax = Pjax::begin([
		'id'      => 'invoice-item-list',
		'timeout' => 8000,
	]) ?>

	<div class="panel panel-default">
		<div class="panel-heading">
			<b style="font-size: large;">Позиции счета</b>
		</div>
		<div class="panel-body">
			<table class="table">
				<thead>
				<th class="text-left">
					<small>Название</small>
				</th>
				<th class="text-center" style="width: 100px;">
					<small>Кол-во</small>
				</th>
				<th class="text-center" style="width: 100px;">
					<small>Ед. измерения</small>
				</th>
				<th class="text-center" style="width: 100px;">
					<small>Цена за ед.</small>
				</th>
				<th class="text-right" style="width: 240px;">
					<small>Всего</small>
				</th>
				</thead>
				<tbody class="js-items-holder">
				<?php foreach ($model->items as $item): ?>
					<tr class="item" data-id="<?= $item->id ?>">
						<td>
							<?= $item->name ?>
						</td>
						<td class="text-center">
							<?= ($item->quantity !== null) ? $formatter->asDecimal($item->quantity) : '' ?>
						</td>
						<td class="text-center">
							<?= $item->unit ?>
						</td>
						<td class="text-center">
							<?= ($item->price !== null) ? $formatter->asCurrency($item->price) : '' ?>
						</td>
						<td class="text-right">
							<a href="<?= Url::to(['/invoice/get-item-paid', 'itemId' => $item->id]) ?>"
							   class="js-change-item-paid <?= (($item->is_paid) ? 'text-success' : 'text-danger') ?>"
							   title="Оплачено <?= ($item->is_paid) ? "" : "(Задолженность: " . $formatter->asCurrency($item->summary - $item->total_paid) . ")" ?>">
								<i><?= $formatter->asCurrency($item->total_paid) ?></i>
							</a> /
							<b><?= $formatter->asCurrency($item->summary) ?></b>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="panel-footer">
			<div class="text-right">
			<span style="font-size: xx-large;">
				<?= Yii::$app->formatter->asCurrency($model->summary) ?>
			</span>
			</div>
		</div>
	</div>

	<?php $pjax::end() ?>

	<?= $this->render('_modals') ?>
</div>