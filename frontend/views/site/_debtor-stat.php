<?php

/**
 * @var yii\web\View $this
 * @var \common\models\data\DebtorStatData[] $debtorStatList
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$formatter = Yii::$app->formatter;
?>
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
			<?php foreach ($debtorStatList as $debtorData): ?>
                <tr>
                    <td class="text-left"><?= $debtorData->contractor->name ?></td>
                    <td class="text-center">
                        <a href="<?= ArrayHelper::getValue($debtorData, 'options.invoiceListUrl') ?>">
							<?= Yii::t('app', '{delta, plural, =1{1 invoice} other{# invoices}}', ['delta' => $debtorData->invoiceCount]); ?>
                        </a>
                    </td>
                    <td class="text-center text-danger">
						<?= $formatter->asCurrency($debtorData->debtorSum) ?>
                    </td>
                </tr>
			<?php endforeach; ?>

			<?php if (empty($debtorStatList)): ?>
                <tr>
                    <td class="text-center" colspan="4">Должников нет...</td>
                </tr>
			<?php endif; ?>
            </tbody>
        </table>
    </div>
</div>