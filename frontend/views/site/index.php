<?php

/**
 * @var yii\web\View $this
 * @var array $debtorList
 * @var array $debtorDetailList
 * @var array $statList
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Личный кабинет';

$formatter = Yii::$app->formatter;

$debtorDetailTotalSum = 0;
foreach($debtorDetailList as $debtorData)
		$debtorDetailTotalSum += $debtorData['debtorSum'];
?>
<div class="row">
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<span style="font-size: x-large;">Должники</span>
			</div>
			<div class="panel-body">
				<table class="table table-hover">
					<thead>
					<th class="text-left">
						<small>Должник</small>
					</th>
					<th class="text-center" style="width: 100px;">
						<small>Кол-во счетов</small>
					</th>
					<th class="text-center" style="width: 200px;">
						<small>Сумма задолженности</small>
					</th>
					</thead>
					<tbody>
					<?php foreach ($debtorList as $debtorData): ?>
						<tr>
							<td class="text-left"><?= $debtorData['contractor']->name ?></td>
							<td class="text-center">
								<a href="<?= $debtorData['invoiceListUrl'] ?>">
									<?= Yii::t('app', '{delta, plural, =1{1 invoice} other{# invoices}}', ['delta' => $debtorData['invoiceCount']]); ?>
								</a>
							</td>
							<td class="text-center text-danger">
								<?= $formatter->asCurrency($debtorData['debtorSum']) ?>
							</td>
						</tr>
					<?php endforeach; ?>

					<?php if (empty($debtorList)): ?>
						<tr>
							<td class="text-center" colspan="4">Должников нет...</td>
						</tr>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<span style="font-size: x-large;">Суммарная статистика</span>
			</div>
			<div class="panel-body">
				<table class="table table-hover">
					<thead>
					<th class="text-left">
						<small>Контрактор</small>
					</th>
					<th class="text-center" style="width: 100px;">
						<small>Кол-во счетов</small>
					</th>
					<th class="text-center" style="width: 230px;">
						<small>Баланс</small>
					</th>
					</thead>
					<tbody>
					<?php foreach ($statList as $statItem): ?>
						<tr>
							<td class="text-left"><?= $statItem['contractor']->name ?></td>
							<td class="text-center">
								<a href="<?= $statItem['invoiceListUrl'] ?>">
									<?= Yii::t('app', '{delta, plural, =1{1 invoice} other{# invoices}}', ['delta' => $statItem['invoiceCount']]); ?>
								</a>
							</td>
							<td class="text-center">
								<a href="<?= $statItem['paymentListUrl'] ?>">
									<i class="text-success" title="Оплачено">
										<?= $formatter->asCurrency($statItem['total_paid']) ?>
									</i>
								</a>
								/
								<a href="<?= $statItem['invoiceListUrl'] ?>">
									<b title="Всего"><?= $formatter->asCurrency($statItem['summary']) ?></b>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>

					<?php if (empty($statList)): ?>
						<tr>
							<td class="text-center" colspan="3">Статистики нет...</td>
						</tr>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<?php if (!empty($debtorDetailList)): ?>
		<div class="col-md-12">
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
						<?php foreach ($debtorDetailList as $debtorData): ?>
							<tr>
								<td class="text-left">
									<b><?= $debtorData['contractor']->name ?></b>
								</td>
								<td class="text-center">
									<a href="<?= $debtorData['invoiceListUrl'] ?>">
										<b><?= Yii::t('app', '{delta, plural, =1{1 invoice} other{# invoices}}', ['delta' => count($debtorData['invoiceList'])]); ?></b>
									</a>
								</td>
								<td class="text-center text-danger">
									<b><?= $formatter->asCurrency($debtorData['debtorSum']) ?></b>
								</td>
							</tr>
							<?php foreach ($debtorData['invoiceList'] as $invoiceData): ?>
								<?php
								/** @var \common\models\Invoice $invoice */
								$invoice = $invoiceData['invoice'];
								/** @var \common\models\InvoiceItem[] $invoiceItems */
								$invoiceItems = $invoiceData['items'];
								?>
								<tr>
									<td colspan="2" style="border-top: none; padding-left: 25px;">
										<?= Html::a($invoice->name, $invoiceData['invoiceUrl']) ?>
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
								<?= $formatter->asCurrency($debtorDetailTotalSum) ?>
							</b>
						</th>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>