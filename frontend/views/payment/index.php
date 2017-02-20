<?php

/**
 * @var yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \frontend\models\PaymentSearch $searchModel
 */

use common\models\Contractor;
use common\helpers\ContractorHelper;
use common\models\Payment;
use common\helpers\PaymentHelper;
use yii\widgets\Pjax;
use kartik\widgets\DatePicker;
use kartik\select2\Select2;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = Yii::t('app', 'Payments');

$this->params['breadcrumbs'] = [
	$this->title,
];

$contractorNameList = ArrayHelper::map(ContractorHelper::applyAccessByUser(Contractor::find()->select(['id', 'name']))->all(), 'id', 'name');
?>
<div>

	<p class="text-right">
		<?= Html::a(Yii::t('app.actions', 'Create'), ['create'], ['class' => 'btn btn-success']) ?>
	</p>

	<?php $pjax = Pjax::begin([
		'timeout' => 8000,
	]) ?>

	<?= GridView::widget([
		'filterModel'  => $searchModel,
		'dataProvider' => $dataProvider,
		'columns'      => [
			[
				'attribute'     => 'date',
				'format'        => 'date',
				'filter'        => DatePicker::widget([
					'model'     => $searchModel,
					'attribute' => 'date',
				]),
				'filterOptions' => [
					'class' => 'date-column',
				],
				'headerOptions' => [
					'class' => 'date-column',
				],
				'options'       => [
					'class' => 'date-column',
				],
			],
			[
				'attribute'     => 'contractor_id',
				'format'        => 'raw',
				'value'         => function (Payment $model) {
					$formatter = Yii::$app->formatter;

					$html = $model->contractor->name;

					if (count($model->invoiceLinks) > 0) {
						$html .= "<div>";

						foreach ($model->invoiceLinks as $link) {
							$invoice = $link->invoice;

							$html .= Html::a("$invoice->name - " . $formatter->asCurrency($link->sum), '#', [
									'class' => 'js-link-payment-to-invoice',
								]) . "<br>";
						}

						$html .= "</div>";
					} else {
						$html .= "<br>" . Html::a('Связать со счётом', '#', [
								'class' => 'btn btn-primary btn-xs js-link-payment-to-invoice',
							]);
					}

					return $html;
				},
				'filter'        => Select2::widget([
					'model'         => $searchModel,
					'attribute'     => 'contractor_id',
					'data'          => $contractorNameList,
					'options'       => ['placeholder' => 'Введите название контрагента ...'],
					'pluginOptions' => [
						'allowClear' => true,
					],
				]),
				'filterOptions' => [
					'class' => 'contractor-column',
				],
				'headerOptions' => [
					'class' => 'contractor-column',
				],
				'options'       => [
					'class' => 'contractor-column',
				],
			],
			[
				'attribute'     => 'income',
				'format'        => 'raw',
				'value'         => function (Payment $model) {
					$formatter = Yii::$app->formatter;

					$linkedSum = PaymentHelper::getLinkedSum($model);

					$html = "<i title='Связано' class='" . (($linkedSum < $model->income) ? 'text-danger' : 'text-success') . "'>" . $formatter->asCurrency($linkedSum) . "</i> / <b title='Сумма поступления'>" . $formatter->asCurrency($model->income) . "</b>";

					return $html;
				},
				'filterOptions' => [
					'class' => 'income-column',
				],
				'headerOptions' => [
					'class' => 'income-column',
				],
				'options'       => [
					'class' => 'income-column',
				],
			],
			[
				'attribute'     => 'description',
				'filterOptions' => [
					'class' => 'description-column',
				],
				'headerOptions' => [
					'class' => 'description-column',
				],
				'options'       => [
					'class' => 'description-column',
				],
			],
			[
				'class'         => 'yii\grid\ActionColumn',
				'template'      => '{view} {update} {delete}',
				'filterOptions' => [
					'class' => 'action-column',
				],
				'headerOptions' => [
					'class' => 'action-column',
				],
				'options'       => [
					'class' => 'action-column',
				],
			],
		],
	]) ?>

	<?php $pjax::end() ?>
</div>