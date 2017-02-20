<?php

/**
 * @var yii\web\View $this
 * @var \common\models\Payment $model
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = Yii::t('app.actions', 'Editing');

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

?>
<?= $this->render('_form', [
	'model' => $model,
]) ?>