<?php

namespace common\models\data;

use common\models\Contractor;
use Yii;
use yii\base\BaseObject;

class DebtorStatData extends BaseObject {
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
	public $debtorSum;

	/**
	 * @var array
	 */
	public $invoiceList;

	/**
	 * @var array
	 */
	public $options = [];
}
