<?php

/**
 * @var \yii\web\View $this
 * @var string $content
 */

use frontend\assets\FrontendAsset;
use frontend\assets\ThemeAsset;
use frontend\assets\BowerAsset;
use yii\helpers\Html;

BowerAsset::register($this);
ThemeAsset::register($this);
FrontendAsset::register($this);

?>
<?php $this->beginContent('@app/views/layouts/plain.php') ?>
<header>
	<?= $this->render('_header') ?>
</header>

<section>
	<div class="mainwrapper">
		<div class="leftpanel">
			<?= $this->render('_left-panel') ?>
		</div>

		<div class="mainpanel">
			<div class="pageheader">
				<?= $this->render('_page-header') ?>
			</div>
			<div class="contentpanel site-body">
				<?= \common\widgets\Alert::widget() ?>

				<?= $content ?>
			</div>
		</div>
	</div>
</section>

<?= $this->render('_fast-buttons') ?>

<?php $this->endContent() ?>
