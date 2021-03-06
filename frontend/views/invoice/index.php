<?php

/**
 * @var yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \frontend\models\InvoiceSearch $searchModel
 */

use common\models\Contractor;
use common\helpers\ContractorHelper;
use common\models\Invoice;
use common\helpers\InvoiceHelper;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = Yii::t('app', 'Invoices');

$this->params['breadcrumbs'] = [
	$this->title,
];

$invoiceNameList = ArrayHelper::map(InvoiceHelper::applyAccessByUser(Invoice::find()->select(['id', 'name']))->all(), 'name', 'name');
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
				'attribute' => 'name',
				'format'    => 'raw',
				'value'     => function (Invoice $model) {
					$formatter = Yii::$app->formatter;

					$html = Html::a("<b>$model->name</b>", ['view', 'id' => $model->id]);

					$html .= "<br><small>" . $formatter->asDate($model->created_at) . " / " . $formatter->asDate($model->updated_at) . "</small>";

					return $html;
				},
				'filter'    => Select2::widget([
					'model'         => $searchModel,
					'attribute'     => 'name',
					'data'          => $invoiceNameList,
					'options'       => ['placeholder' => 'Введите название счёта ...'],
					'pluginOptions' => [
						'allowClear' => true,
					],
				]),
			],
			[
				'attribute'     => 'contractor_id',
				'format'        => 'raw',
				'value'         => function (Invoice $model) {
					return $model->contractor->name;
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
				'attribute'     => 'is_paid',
				'label'         => 'Оплачено / Сумма',
				'format'        => 'raw',
				'value'         => function (Invoice $model) {
					$formatter = Yii::$app->formatter;

					$html = "<i class='" . (($model->is_paid) ? 'text-success' : 'text-danger') . "'>" . $formatter->asCurrency($model->total_paid) . "</i> / <b>" . $formatter->asCurrency($model->summary) . "</b>";

					if ($model->total_paid > $model->summary) {
						$html .= "<br><b class='text-danger'>Переизбыток: " . $formatter->asCurrency($model->summary - $model->total_paid) . "</b>";
					}

					return $html;
				},
				'filter'        => Select2::widget([
					'model'         => $searchModel,
					'attribute'     => 'is_paid',
					'data'          => [0 => 'Не оплаченные', 1 => 'Оплаченные'],
					'options'       => ['placeholder' => ''],
					'pluginOptions' => [
						'allowClear' => true,
					],
				]),
				'filterOptions' => [
					'class' => 'paid-status-column',
				],
				'headerOptions' => [
					'class' => 'paid-status-column',
				],
				'options'       => [
					'class' => 'paid-status-column',
				],
			],
			[
				'class'          => 'yii\grid\ActionColumn',
				'template'       => '{view} {update} {delete}',
				'filterOptions'  => [
					'class' => 'action-column',
				],
				'headerOptions'  => [
					'class' => 'action-column',
				],
				'options'        => [
					'class' => 'action-column',
				],
				'visibleButtons' => [
					'delete' => function (Invoice $model, $key, $index) {
						return InvoiceHelper::isAvailableDelete($model);
					},
				],
			],
		],
	]) ?>

	<?php $pjax::end() ?>
</div>