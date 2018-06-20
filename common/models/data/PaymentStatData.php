<?php

namespace common\models\data;

use common\models\Contractor;
use Yii;
use yii\base\BaseObject;

class PaymentStatData extends BaseObject {
	/**
	 * @var Contractor
	 */
	public $contractor;

	/**
	 * @var integer
	 */
	public $invoiceCount;

	/**
	 * @var float
	 */
	public $summary;

	/**
	 * @var float
	 */
	public $paid;

	/**
	 * @var array
	 */
	public $options = [];
}
