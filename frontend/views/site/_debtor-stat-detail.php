<?php

/**
 * @var yii\web\View $this
 * @var \common\models\data\DebtorStatData[] $debtorStatList
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$debtorTotalSum = array_sum(ArrayHelper::getColumn($debtorStatList, 'debtorSum'));

$formatter = Yii::$app->formatter;
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <span style="font-size: x-large;">Расширенная информация по должникам</span>
    </div>
    <div class="panel-body">
        <table class="table table-hover">
            <thead>
            <th class="text-left">
                <small>Должник</small>
            </th>
            <th class="text-center" style="width: 100px;">
                <small></small>
            </th>
            <th class="text-center" style="width: 200px;">
                <small>Сумма задолженности</small>
            </th>
            </thead>
            <tbody>
			<?php foreach ($debtorStatList as $debtorData): ?>
                <tr>
                    <td class="text-left">
                        <b><?= $debtorData->contractor->name ?></b>
                    </td>
                    <td class="text-center">
                        <a href="<?= ArrayHelper::getValue($debtorData, 'options.invoiceListUrl') ?>">
                            <b><?= Yii::t('app', '{delta, plural, =1{1 invoice} other{# invoices}}', ['delta' => $debtorData->invoiceCount]); ?></b>
                        </a>
                    </td>
                    <td class="text-center text-danger">
                        <b><?= $formatter->asCurrency($debtorData->debtorSum) ?></b>
                    </td>
                </tr>
				<?php foreach ($debtorData->invoiceList as $invoiceData): ?>
					<?php
					/**
					 * @var \common\models\Invoice $invoice
					 * @var string $invoiceUrl
					 * @var \common\models\InvoiceItem[] $invoiceItems
					 */
					list($invoice, $invoiceUrl, $invoiceItems) = $invoiceData;
					?>
                    <tr>
                        <td colspan="2" style="border-top: none; padding-left: 25px;">
							<?= Html::a($invoice->name, $invoiceUrl) ?> -
                            <span class='text-danger'>
                                <?= $formatter->asCurrency($invoice->summary - $invoice->total_paid) ?>
                            </span>
                        </td>
                        <td class="text-center" style="border-top: none;"></td>
                    </tr>

					<?php foreach ($invoiceItems as $item): ?>
                        <tr>
                            <td colspan="2" style="border-top: none; padding-left: 50px;">
								<?= $item->name ?>
                            </td>
                            <td class="text-center" style="border-top: none;">
                                <i class="text-danger">
									<?= $formatter->asCurrency($item->summary - $item->total_paid) ?>
                                </i>
                            </td>
                        </tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
			<?php endforeach; ?>
            </tbody>
            <tfoot>
            <th></th>
            <th class="text-center"><b style="font-size: x-large;">Всего:</b></th>
            <th class="text-center">
                <b class="text-danger" style="font-size: x-large;">
					<?= $formatter->asCurrency($debtorTotalSum) ?>
                </b>
            </th>
            </tfoot>
        </table>
    </div>
</div>