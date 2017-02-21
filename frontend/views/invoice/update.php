<?php

/**
 * @var yii\web\View $this
 * @var \common\models\Invoice $model
 * @var \common\models\InvoiceItem[] $itemList
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = $model->name;

$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('app', 'Invoices'),
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
	'model'    => $model,
	'itemList' => $itemList,
]) ?>