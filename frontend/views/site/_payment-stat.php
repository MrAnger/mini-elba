<?php

/**
 * @var yii\web\View $this
 * @var \common\models\data\PaymentStatData[] $paymentStatList
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$formatter = Yii::$app->formatter;
?>
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
                <small>Получено</small>
            </th>
            </thead>
            <tbody>
			<?php foreach ($paymentStatList as $statItem): ?>
                <tr>
                    <td class="text-left"><?= $statItem->contractor->name ?></td>
                    <td class="text-center">
                        <a href="<?= ArrayHelper::getValue($statItem, 'options.invoiceListUrl') ?>">
							<?= Yii::t('app', '{delta, plural, =1{1 invoice} other{# invoices}}', ['delta' => $statItem->invoiceCount]); ?>
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="<?= ArrayHelper::getValue($statItem, 'options.paymentListUrl') ?>">
                            <i class="text-success">
								<?= $formatter->asCurrency($statItem->paid) ?>
                            </i>
                        </a>
                    </td>
                </tr>
			<?php endforeach; ?>

			<?php if (empty($paymentStatList)): ?>
                <tr>
                    <td class="text-center" colspan="3">Статистики нет...</td>
                </tr>
			<?php endif; ?>
            </tbody>
        </table>
    </div>
</div>