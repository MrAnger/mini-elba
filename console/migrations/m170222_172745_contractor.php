<?php

use yii\db\Schema;
use yii\db\Migration;

class m170222_172745_contractor extends Migration {

	public function init() {
		$this->db = 'db';
		parent::init();
	}

	public function safeUp() {
		$tableOptions = 'ENGINE=InnoDB';
		$this->createTable('{{%contractor}}', [
			'id'         => $this->primaryKey(10)->unsigned(),
			'user_id'    => $this->integer(11)->notNull(),
			'name'       => $this->string(250)->notNull(),
			'inn'        => $this->string(64)->null()->defaultValue(null),
			'created_at' => $this->timestamp()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
			'updated_at' => $this->timestamp()->notNull()->defaultValue('0000-00-00 00:00:00'),
		], $tableOptions);
		$this->createIndex('user_id_name', '{{%contractor}}', 'user_id,name', true);
		$this->createIndex('user_id_inn', '{{%contractor}}', 'user_id,inn', true);
		$this->addForeignKey('fk_contractor_user_id', '{{%contractor}}', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
	}

	public function safeDown() {

		$this->dropForeignKey('fk_contractor_user_id', '{{%contractor}}');
		$this->dropTable('{{%contractor}}');

	}
}
