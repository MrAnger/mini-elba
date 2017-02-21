<?php

/**
 * @var yii\web\View $this
 * @var array $debtorList
 * @var array $statList
 */

$this->title = 'Личный кабинет';

$formatter = Yii::$app->formatter;
?>
<div class="row">
	<?php if (count($debtorList) > 0): ?>
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<span style="font-size: x-large;">Должники</span>
				</div>
				<div class="panel-body">
					<table class="table">
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
								<td class="text-center">
									<?= $formatter->asCurrency($debtorData['debtorSum']) ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if (count($statList) > 0): ?>
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<span style="font-size: x-large;">Суммарная статистика</span>
				</div>
				<div class="panel-body">
					<table class="table">
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
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
