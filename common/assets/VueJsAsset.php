<?php

namespace common\assets;

use yii\web\AssetBundle;

/**
 * @author Cyrill Tekord
 */
class VueJsAsset extends AssetBundle {
	public $sourcePath = "@bower/vue/dist";

	public $js = [
		YII_DEBUG
			? 'vue.js'
			: 'vue.min.js'
	];
}