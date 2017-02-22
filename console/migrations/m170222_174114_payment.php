<?php

use yii\db\Schema;
use yii\db\Migration;

class m170222_174114_payment extends Migration {

	public function init() {
		$this->db = 'db';
		parent::init();
	}

	public function safeUp() {
		$tableOptions = 'ENGINE=InnoDB';
		$this->createTable('{{%payment}}', [
			'id'              => $this->primaryKey(10)->unsigned(),
			'user_id'         => $this->integer(11)->notNull(),
			'contractor_id'   => $this->integer(10)->unsigned()->notNull(),
			'date'            => $this->date()->notNull(),
			'document_number' => $this->integer(11)->null()->defaultValue(null),
			'income'          => $this->decimal(20, 2)->null()->defaultValue(null),
			'outcome'         => $this->decimal(20, 2)->null()->defaultValue(null),
			'description'     => $this->text()->null()->defaultValue(null),
			'created_at'      => $this->timestamp()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
			'updated_at'      => $this->timestamp()->notNull()->defaultValue('0000-00-00 00:00:00'),
		], $tableOptions);
		$this->createIndex('base_unique', '{{%payment}}', 'user_id,contractor_id,date,document_number,income,outcome', true);
		$this->createIndex('FK_payment_contractor', '{{%payment}}', 'contractor_id', false);
		$this->addForeignKey('fk_payment_contractor_id', '{{%payment}}', 'contractor_id', 'contractor', 'id', 'CASCADE', 'CASCADE');
		$this->addForeignKey('fk_payment_user_id', '{{%payment}}', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
	}

	public function safeDown() {

		$this->dropForeignKey('fk_payment_contractor_id', '{{%payment}}');
		$this->dropForeignKey('fk_payment_user_id', '{{%payment}}');
		$this->dropTable('{{%payment}}');

	}
}
