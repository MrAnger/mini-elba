<?php

/**
 * @var yii\web\View $this
 */

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$emptyInvoiceItemModel = new \common\models\InvoiceItem();
$emptyPaymentLinkModel = new \common\models\PaymentLinkToInvoice();
?>
<!-- Модальное окно изменения кол-ва оплаченной суммы позиции счета -->
<div id="modal-link-payment-to-invoice" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">Выберите счета, которые оплачивают это поступление</h4>
			</div>
			<div class="modal-body">
				<?php $form = ActiveForm::begin([
					'enableClientValidation' => false,
					'action'                 => ['/payment/invoice-link-form'],
					'options'                => [
						'data' => [
							'payment'      => false,
							'invoice-list' => \yii\helpers\Json::encode([]),
						],
					],
				]) ?>

				<div class="js-payment-description"></div>
				<div class="js-total-sum-left"></div>

				<table class="table">
					<thead>
					<th style="width: 15px;"></th>
					<th style="width: 55%;">
						<small>Счёт</small>
					</th>
					<th class="text-right">
						<small>Оплачено / Сумма документа</small>
					</th>
					</thead>
					<tbody class="js-invoices-holder">
					</tbody>
				</table>

				<script class="js-invoice-template" type="text/html">
					<tr class="item" data-id="">
						<td>
							<input class="js-checked" type="checkbox">
						</td>
						<td>
							<b class="js-name"></b>

							<div class="js-sum-left">
								<small class="value"></small>
							</div>
						</td>
						<td class="text-right">
							<div style="display: inline-block; width: 35%;">
								<input type="text" class="form-control input-sm js-input-sum">
								<i class="js-fake-input-sum"></i>
							</div>
							<div style="display: inline-block;">
								/ <b class="js-invoice-summary"></b>
							</div>
						</td>
					</tr>
				</script>

				<?php $form->end() ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
				<button form="<?= $form->id ?>" type="submit" class="btn btn-primary">Сохранить</button>
			</div>
		</div>
	</div>
</div>