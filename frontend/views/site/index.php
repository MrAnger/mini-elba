<?php

/**
 * @var yii\web\View $this
 * @var \common\models\data\DebtorStatData[] $debtorStatList
 * @var \common\models\data\PaymentStatData $paymentStatList
 * @var array $paymentGraphData
 * @var \frontend\models\PaymentGraphForm $paymentGraphForm
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Личный кабинет';
?>
<div>
    <div class="row">
        <div class="col-md-6">
			<?= $this->render('_debtor-stat', ['debtorStatList' => $debtorStatList]) ?>
        </div>

        <div class="col-md-6">
			<?= $this->render('_payment-stat', ['paymentStatList' => $paymentStatList]) ?>
        </div>
    </div>

	<?php if (!empty($debtorStatList)): ?>
        <div class="row">
            <div class="col-md-12">
				<?= $this->render('_debtor-stat-detail', ['debtorStatList' => $debtorStatList]) ?>
            </div>
        </div>
	<?php endif; ?>

	<?php if (count($paymentGraphData['graphData'][0]) > 1): ?>
        <div class="row">
            <div class="col-md-12">
				<?= $this->render('_payment-stat-graph', [
					'paymentGraphData' => $paymentGraphData,
					'paymentGraphForm' => $paymentGraphForm,
				]) ?>
            </div>
        </div>
	<?php endif; ?>
</div>