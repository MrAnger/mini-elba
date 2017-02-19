<?php

/**
 * @var yii\web\View $this
 * @var \common\models\Contractor $model
 */

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

?>
<div>
	<?php $form = ActiveForm::begin([
		'enableClientValidation' => true,
	]) ?>

	<div class="row">
		<div class="col-md-6">
			<?= $form->field($model, 'name')
				->textInput([
					'maxlength' => true,
				])
			?>
		</div>
	</div>

	<div class="form-group text-right">
		<?= Html::submitButton(($model->isNewRecord) ? Yii::t('app.actions', 'Create') : Yii::t('app.actions', 'Save'), ['class' => 'btn btn-primary']) ?>
	</div>

	<?php $form->end() ?>
</div>