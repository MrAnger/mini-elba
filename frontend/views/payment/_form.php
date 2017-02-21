<?php

/**
 * @var yii\web\View $this
 * @var \common\models\Payment $model
 */

use common\models\Contractor;
use common\helpers\ContractorHelper;
use kartik\widgets\DatePicker;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$contractorNameList = ArrayHelper::map(ContractorHelper::applyAccessByUser(Contractor::find()->select(['id', 'name']))->all(), 'id', 'name');
?>
<div>
	<?php $form = ActiveForm::begin([
		'enableClientValidation' => false,
	]) ?>

	<div class="row">
		<div class="col-md-6">
			<?= $form->field($model, 'date')
				->widget(DatePicker::className())
			?>
		</div>

		<div class="col-md-6">
			<?= $form->field($model, 'contractor_id')
				->widget(Select2::className(), [
					'data'          => $contractorNameList,
					'options'       => ['placeholder' => 'Введите название контрагента ...'],
					'pluginOptions' => [
						'allowClear' => true,
					],
				])
			?>
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			<?= $form->field($model, 'income')
				->textInput() ?>
		</div>

		<div class="col-md-6">
			<?= $form->field($model, 'document_number')
				->textInput() ?>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<?= $form->field($model, 'description')->textarea([
				'rows' => 5,
			]) ?>
		</div>
	</div>

	<div class="form-group text-right">
		<?= Html::submitButton(($model->isNewRecord) ? Yii::t('app.actions', 'Create') : Yii::t('app.actions', 'Save'), ['class' => 'btn btn-primary']) ?>
	</div>

	<?php $form->end() ?>
</div>