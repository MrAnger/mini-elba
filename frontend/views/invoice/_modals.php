<?php

/**
 * @var yii\web\View $this
 */

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$emptyInvoiceItemModel = new \common\models\InvoiceItem();
?>
<!-- Модальное окно изменения кол-ва оплаченной суммы позиции счета -->
<div id="modal-change-invoice-item-paid" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">Баланс позиции</h4>
			</div>
			<div class="modal-body">
				<?php $form = ActiveForm::begin([
					'enableClientValidation' => false,
					'enableAjaxValidation'   => true,
					'validationUrl'          => ['/invoice/validate-item-paid-form'],
					'action'                 => ['/invoice/item-paid-form'],
					'options'                => [
						'data' => [
							'item-id'       => 0,
							'available-sum' => 0,
						],
					],
				]) ?>

				<?= Html::hiddenInput('itemId', null, [
					'class' => 'js-item-id',
				]) ?>

				<div class="text-center">
					<b class="h3 js-item-name"></b>
				</div>

				<div class="row">
					<div class="col-md-6">
						<?= $form->field($emptyInvoiceItemModel, 'total_paid')
							->label(false)
							->textInput([
								'class' => 'form-control input-sm text-center js-input-paid',
							])
						?>
					</div>
					<div class="col-md-6">
						<p class="js-item-summary text-center text-success" style="font-size: x-large;"></p>
					</div>
				</div>

				<?php $form->end() ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
				<button form="<?= $form->id ?>" type="submit" class="btn btn-primary">Сохранить</button>
			</div>
		</div>
	</div>
</div>