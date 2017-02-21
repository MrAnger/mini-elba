<?php

/**
 * @var yii\web\View $this
 * @var \common\models\Payment $model
 */

use common\helpers\PaymentHelper;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = $model->name;

$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('app', 'Payments'),
		'url'   => ['index'],
	],
	[
		'label' => $model->name,
		'url'   => ['view', 'id' => $model->id],
	],
	Yii::t('app.actions', 'Viewing'),
];

$formatter = Yii::$app->formatter;

$linkedSum = PaymentHelper::getLinkedSum($model);

$incomeString = "<i title='Связано' class='" . (($linkedSum < $model->income) ? 'text-danger' : 'text-success') . "'>" . $formatter->asCurrency($linkedSum) . "</i> / <b title='Сумма поступления'>" . $formatter->asCurrency($model->income) . "</b>";
?>
<div>

	<p class="text-left">
		<?= Html::a(Yii::t('app.actions', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
	</p>

	<?php $pjax = Pjax::begin([
		'id'      => 'pjax-payment-view',
		'timeout' => 8000,
	]) ?>

	<div class="row">
		<div class="col-md-6">
			<?= DetailView::widget([
				'model'      => $model,
				'attributes' => [
					'date:date',
					[
						'attribute' => 'contractor_id',
						'value'     => $model->contractor->name,
					],
					[
						'attribute' => 'income',
						'format'    => 'raw',
						'value'     => $incomeString,
					],
					'created_at:datetime',
					'updated_at:datetime',
				],
			]) ?>
		</div>
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<b style="font-size: large;">Связанные счета</b>
				</div>
				<div class="panel-body">
					<?php if (count($model->invoiceLinks) > 0): ?>
						<?php foreach ($model->invoiceLinks as $link): ?>
							<?php $invoice = $link->invoice; ?>
							<?= Html::a("$invoice->name - " . $formatter->asCurrency($link->sum), ['/payment/get-invoice-link-data', 'paymentId' => $model->id], [
								'class' => 'js-link-payment-to-invoice',
								'data'  => [
									'update-pjax' => '#pjax-payment-view',
								],
							]) . "<br>" ?>
						<?php endforeach; ?>
					<?php else: ?>
						<?= Html::a('Связать со счётом', ['/payment/get-invoice-link-data', 'paymentId' => $model->id], [
							'class' => 'btn btn-primary btn-xs js-link-payment-to-invoice',
							'data'  => [
								'update-pjax' => '#pjax-payment-view',
							],
						]); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<?php if ($model->description !== null): ?>
		<div>
			<h3>Описание</h3>

			<p><?= $model->description ?></p>
			<hr>
		</div>
	<?php endif; ?>

	<?php $pjax::end() ?>

	<?= $this->render('_modals') ?>
</div>