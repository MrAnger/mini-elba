<?php

Yii::$container->set('yii\validators\DateValidator', [
	'format' => 'yyyy-MM-dd',
]);

Yii::$container->set('mranger\ckeditor\CKEditor', [
	'preset' => 'default',
]);

Yii::$container->set('kartik\widgets\DatePicker', [
	'pluginOptions' => [
		'autoclose' => true,
		'format'    => 'yyyy-mm-dd',
	],
]);
