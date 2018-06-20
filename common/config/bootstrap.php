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

Yii::$container->set('yii\debug\Module', [
	'allowedIPs' => [
		'127.0.0.1', '::1',
		'83.246.151.89',
	],
]);

Yii::$container->set('yii\gii\Module', [
	'allowedIPs' => [
		'127.0.0.1', '::1',
		'83.246.151.89',
	],
]);