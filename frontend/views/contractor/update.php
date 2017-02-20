<?php

/**
 * @var yii\web\View $this
 * @var \common\models\Contractor $model
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = Yii::t('app.actions', 'Editing');

$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('app', 'Contractors'),
		'url'   => ['index'],
	],
	$model->name,
	$this->title,
];

?>
<?= $this->render('_form', [
	'model' => $model,
]) ?>