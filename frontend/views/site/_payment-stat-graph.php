<?php

/**
 * @var yii\web\View $this
 * @var array $paymentGraphData
 * @var \frontend\models\PaymentGraphForm $paymentGraphForm
 */

use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

\common\assets\VueJsAsset::register($this);
\common\assets\GoogleChartsAsset::register($this);
$this->registerJs(<<<JS
    google.charts.load('current', {packages: ['corechart']});
JS
	, \yii\web\View::POS_HEAD);
?>
<div id="payment-graph" class="panel panel-default" data-graph='<?= Json::encode($paymentGraphData) ?>'>
    <div class="panel-heading">
        <span style="font-size: x-large;">График поступления средств</span>
    </div>
    <div class="panel-body">
        <div>
			<?php $form = \yii\widgets\ActiveForm::begin([
				'enableClientValidation' => false,
				'action'                 => ['/site/get-payment-graph-data'],
			]) ?>

            <div class="row">
                <div class="col-md-offset-2 col-md-4">
					<?= $form->field($paymentGraphForm, 'contractorIds')
						->label(false)
						->checkboxList($paymentGraphForm->getContractorDropdownList(), [
							'class' => 'checkbox',
						])
					?>
                </div>
                <div class="col-md-3">
					<?= $form->field($paymentGraphForm, 'dateRange')
						->label(false)
						->widget(\kartik\daterange\DateRangePicker::className())
					?>
                </div>
            </div>

			<?php $form->end() ?>
        </div>

        <div class="js-chart" style="height: 500px;"></div>

        <div class="row text-center" style="font-size: x-large;">
            <div class="col-md-3">
                <b>Минимум:</b> <i v-html="info.minFormatted"></i>
            </div>

            <div class="col-md-3">
                <b>Максимум:</b> <i v-html="info.maxFormatted"></i>
            </div>

            <div class="col-md-3">
                <b>Среднее:</b> <i v-html="info.avgFormatted"></i>
            </div>

            <div class="col-md-3">
                <b>Всего:</b> <i v-html="info.totalFormatted"></i>
            </div>
        </div>
    </div>
</div>