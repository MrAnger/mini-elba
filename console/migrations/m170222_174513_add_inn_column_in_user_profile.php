<?php

use yii\db\Migration;

class m170222_174513_add_inn_column_in_user_profile extends Migration {
	public function safeUp() {
		$this->addColumn('{{%profile}}', 'inn', $this->string(64)->null());
	}

	public function safeDown() {
		$this->dropColumn('{{%profile}}', 'inn');
	}
}
