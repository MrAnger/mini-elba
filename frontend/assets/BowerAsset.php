<?php

namespace frontend\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * @author Cyrill Tekord
 */
class BowerAsset extends AssetBundle {
	public $sourcePath = "@bower";

	public $css = [];

	public $js = [
		//'jquery-sticky/jquery.sticky.js',
	];

	public $depends = [
		'yii\web\JqueryAsset',
	];

	public $jsOptions = [
		'position' => View::POS_HEAD,
	];
}
