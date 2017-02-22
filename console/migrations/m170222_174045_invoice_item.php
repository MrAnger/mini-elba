<?php

use yii\db\Schema;
use yii\db\Migration;

class m170222_174045_invoice_item extends Migration {

	public function init() {
		$this->db = 'db';
		parent::init();
	}

	public function safeUp() {
		$tableOptions = 'ENGINE=InnoDB';
		$this->createTable('{{%invoice_item}}', [
			'id'         => $this->primaryKey(10)->unsigned(),
			'invoice_id' => $this->integer(10)->unsigned()->notNull(),
			'name'       => $this->string(250)->notNull(),
			'quantity'   => $this->decimal(10, 2)->null()->defaultValue(null),
			'unit'       => $this->string(10)->null()->defaultValue(null),
			'price'      => $this->decimal(10, 2)->null()->defaultValue(null),
			'summary'    => $this->decimal(20, 2)->notNull()->defaultValue('0.00'),
			'total_paid' => $this->decimal(20, 2)->notNull()->defaultValue('0.00'),
			'is_paid'    => $this->boolean()->notNull()->defaultValue(0),
		], $tableOptions);
		$this->createIndex('invoice_id_name', '{{%invoice_item}}', 'invoice_id,name', true);
		$this->addForeignKey('fk_invoice_item_invoice_id', '{{%invoice_item}}', 'invoice_id', 'invoice', 'id', 'CASCADE', 'CASCADE');
	}

	public function safeDown() {

		$this->dropForeignKey('fk_invoice_item_invoice_id', '{{%invoice_item}}');
		$this->dropTable('{{%invoice_item}}');

	}
}
