<?php

/**
 * @var yii\web\View $this
 * @var \common\models\Payment $model
 */

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
	Yii::t('app.actions', 'Editing'),
];

?>
<?= $this->render('_form', [
	'model' => $model,
]) ?>