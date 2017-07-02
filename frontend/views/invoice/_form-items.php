<?php

/**
 * @var yii\web\View $this
 * @var \common\models\Invoice $model
 * @var \common\models\InvoiceItem[] $itemList
 * @var \yii\widgets\ActiveForm $form
 */

use common\models\InvoiceItem;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

\yii\jui\JuiAsset::register($this);

$emptyInvoiceItemModel = new InvoiceItem([
	'invoice_id' => $model->id,
]);
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<div class="pull-left">
			<b style="font-size: large;">Позиции счета</b>
		</div>

		<div class="pull-right">
			<a href="#" class="btn btn-success btn-xs js-add-item" title="Добавить">
				<i class="glyphicon glyphicon-plus-sign" style="top: 2px;"></i>
			</a>
		</div>
		<div class="clearfix"></div>
	</div>
	<div class="panel-body">
		<table class="table">
			<thead>
			<th class="text-left">
				<small>Название</small>
			</th>
			<th class="text-center" style="width: 100px;">
				<small>Кол-во</small>
			</th>
			<th class="text-center" style="width: 100px;">
				<small>Ед. измерения</small>
			</th>
			<th class="text-center" style="width: 100px;">
				<small>Цена за ед.</small>
			</th>
			<th class="text-center" style="width: 120px;">
				<small>Всего</small>
			</th>
			</thead>
			<tbody id="invoice-position-list" class="js-items-holder">
			<?php foreach ($itemList as $itemId => $item): ?>
				<tr class="item <?= ($item->hasErrors()) ? 'has-error' : '' ?>" data-id="<?= $itemId ?>">
					<td>
						<?= $form->field($emptyInvoiceItemModel, "[$itemId]name")
							->label(false)
							->textInput([
								'class'        => 'form-control input-sm js-input-name js-autocomplete-item',
								'value'        => $item->name,
								'autocomplete' => 'off',
								'data'         => [
									'autocomplete-source' => Url::to(['item-autocomplete', 'attribute' => 'name'], true),
								],
							]) ?>
						<div class="text-danger"><?= implode('<br>', $item->getErrors('name')) ?></div>
					</td>
					<td>
						<?= $form->field($emptyInvoiceItemModel, "[$itemId]quantity")
							->label(false)
							->textInput([
								'class'        => 'form-control input-sm price-input js-input-quantity',
								'value'        => $item->quantity,
								'autocomplete' => 'off',
							]) ?>
						<div class="text-danger"><?= implode('<br>', $item->getErrors('quantity')) ?></div>
					</td>
					<td>
						<?= $form->field($emptyInvoiceItemModel, "[$itemId]unit")
							->label(false)
							->textInput([
								'class'        => 'form-control input-sm js-input-unit js-autocomplete-item',
								'value'        => $item->unit,
								'autocomplete' => 'off',
								'data'         => [
									'autocomplete-source' => Url::to(['item-autocomplete', 'attribute' => 'unit'], true),
								],
							]) ?>
						<div class="text-danger"><?= implode('<br>', $item->getErrors('unit')) ?></div>
					</td>
					<td>
						<?= $form->field($emptyInvoiceItemModel, "[$itemId]price")
							->label(false)
							->textInput([
								'class'        => 'form-control input-sm price-input js-input-price js-autocomplete-item',
								'value'        => $item->price,
								'autocomplete' => 'off',
								'data'         => [
									'autocomplete-source' => Url::to(['item-autocomplete', 'attribute' => 'price'], true),
								],
							]) ?>
						<div class="text-danger"><?= implode('<br>', $item->getErrors('price')) ?></div>
					</td>
					<td style="position: relative;">
						<?= $form->field($emptyInvoiceItemModel, "[$itemId]summary")
							->label(false)
							->textInput([
								'class'        => 'form-control input-sm price-input js-input-summary',
								'autocomplete' => 'off',
								'value'        => $item->summary,
							]) ?>
						<div class="text-danger"><?= implode('<br>', $item->getErrors('summary')) ?></div>
						<a class="js-item-delete" href="#" title="Удалить"
						   style="position: absolute; top: 20px; right: -10px;">
							<span class="glyphicon glyphicon-trash"></span>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<div class="panel-footer">
		<div class="text-right">
			<span class="js-summary" style="font-size: xx-large;">
				<?= Yii::$app->formatter->asDecimal($model->summary, 2) ?>
			</span>
		</div>
	</div>
</div>
<script id="item-layout" type="text/html">
	<tr class="item" data-id="tIDt">
		<td>
			<?= $form->field($emptyInvoiceItemModel, "[tIDt]name")
				->label(false)
				->textInput([
					'class'        => 'form-control input-sm js-input-name js-autocomplete-item',
					'autocomplete' => 'off',
					'data'         => [
						'autocomplete-source' => Url::to(['item-autocomplete', 'attribute' => 'name'], true),
					],
				]) ?>
		</td>
		<td>
			<?= $form->field($emptyInvoiceItemModel, "[tIDt]quantity")
				->label(false)
				->textInput([
					'class'        => 'form-control input-sm price-input js-input-quantity',
					'autocomplete' => 'off',
				]) ?>
		</td>
		<td>
			<?= $form->field($emptyInvoiceItemModel, "[tIDt]unit")
				->label(false)
				->textInput([
					'class'        => 'form-control input-sm js-input-unit js-autocomplete-item',
					'autocomplete' => 'off',
					'data'         => [
						'autocomplete-source' => Url::to(['item-autocomplete', 'attribute' => 'unit'], true),
					],
				]) ?>
		</td>
		<td>
			<?= $form->field($emptyInvoiceItemModel, "[tIDt]price")
				->label(false)
				->textInput([
					'class'        => 'form-control input-sm price-input js-input-price js-autocomplete-item',
					'autocomplete' => 'off',
					'data'         => [
						'autocomplete-source' => Url::to(['item-autocomplete', 'attribute' => 'price'], true),
					],
				]) ?>
		</td>
		<td style="position: relative;">
			<?= $form->field($emptyInvoiceItemModel, "[tIDt]summary")
				->label(false)
				->textInput([
					'class'        => 'form-control input-sm price-input js-input-summary',
					'autocomplete' => 'off',
				]) ?>
			<a class="js-item-delete" href="#" title="Удалить" style="position: absolute; top: 20px; right: -10px;">
				<span class="glyphicon glyphicon-trash"></span>
			</a>
		</td>
	</tr>
</script>