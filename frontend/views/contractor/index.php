<?php

/**
 * @var yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 */

use common\helpers\ContractorHelper;
use common\models\Contractor;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = Yii::t('app', 'Contractors');

$this->params['breadcrumbs'] = [
	$this->title,
];
?>
<div>

	<p class="text-right">
		<?= Html::a(Yii::t('app.actions', 'Create'), ['create'], ['class' => 'btn btn-success']) ?>
	</p>

	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'columns'      => [
			[
				'attribute' => 'name',
				'format'    => 'raw',
				'value'     => function (Contractor $model) {
					$formatter = Yii::$app->formatter;

					$html = "<b>$model->name</b>";

					if ($model->inn !== null)
						$html .= "<br><b>ИНН:</b> <i>$model->inn</i>";

					$html .= "<br><small>" . $formatter->asDate($model->created_at) . " / " . $formatter->asDate($model->updated_at) . "</small>";

					return $html;
				},
			],
			[
				'class'          => 'yii\grid\ActionColumn',
				'template'       => '{update} {delete}',
				'visibleButtons' => [
					'delete' => function (Contractor $model, $key, $index) {
						return ContractorHelper::isAvailableDelete($model);
					},
				],
			],
		],
	]) ?>
</div>