<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \yii\web\View $this
 */
?>
<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="<?= Yii::$app->urlManager->baseUrl . "/theme/js/html5shiv.js" ?>"></script>
<script src="<?= Yii::$app->urlManager->baseUrl . "/theme/js/respond.min.js" ?>"></script>
<![endif]-->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
	google.charts.load('current', {packages: ['corechart']});
</script>