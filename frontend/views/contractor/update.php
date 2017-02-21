<?php

/**
 * @var yii\web\View $this
 * @var \common\models\Contractor $model
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = $model->name;

$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('app', 'Contractors'),
		'url'   => ['index'],
	],
	$model->name,
	Yii::t('app.actions', 'Editing'),
];

?>
<?= $this->render('_form', [
	'model' => $model,
]) ?>