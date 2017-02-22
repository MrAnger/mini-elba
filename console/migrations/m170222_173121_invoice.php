<?php

use yii\db\Schema;
use yii\db\Migration;

class m170222_173121_invoice extends Migration {

	public function init() {
		$this->db = 'db';
		parent::init();
	}

	public function safeUp() {
		$tableOptions = 'ENGINE=InnoDB';
		$this->createTable('{{%invoice}}', [
			'id'            => $this->primaryKey(10)->unsigned(),
			'user_id'       => $this->integer(11)->notNull(),
			'contractor_id' => $this->integer(10)->unsigned()->notNull(),
			'name'          => $this->string(250)->notNull(),
			'summary'       => $this->decimal(20, 2)->notNull()->defaultValue('0.00'),
			'total_paid'    => $this->decimal(20, 2)->notNull()->defaultValue('0.00'),
			'is_paid'       => $this->boolean()->notNull()->defaultValue(0),
			'comment'       => $this->text()->null()->defaultValue(null),
			'created_at'    => $this->timestamp()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
			'updated_at'    => $this->timestamp()->notNull()->defaultValue('0000-00-00 00:00:00'),
		], $tableOptions);
		$this->createIndex('user_id_name', '{{%invoice}}', 'user_id,name', true);
		$this->createIndex('FK_invoice_contractor', '{{%invoice}}', 'contractor_id', false);
		$this->addForeignKey('fk_invoice_contractor_id', '{{%invoice}}', 'contractor_id', 'contractor', 'id', 'CASCADE', 'CASCADE');
		$this->addForeignKey('fk_invoice_user_id', '{{%invoice}}', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
	}

	public function safeDown() {

		$this->dropForeignKey('fk_invoice_contractor_id', '{{%invoice}}');
		$this->dropForeignKey('fk_invoice_user_id', '{{%invoice}}');
		$this->dropTable('{{%invoice}}');

	}
}
