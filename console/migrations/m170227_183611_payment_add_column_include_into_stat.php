<?php

use yii\db\Migration;

class m170227_183611_payment_add_column_include_into_stat extends Migration {
	public function safeUp() {
		$this->addColumn('{{%payment}}', 'is_include_into_stat', $this->boolean()->notNull()->defaultValue(1));
	}

	public function safeDown() {
		$this->dropColumn('{{%payment}}', 'is_include_into_stat');
	}
}
