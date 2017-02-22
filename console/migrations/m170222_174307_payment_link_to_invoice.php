<?php

use yii\db\Schema;
use yii\db\Migration;

class m170222_174307_payment_link_to_invoice extends Migration {

	public function init() {
		$this->db = 'db';
		parent::init();
	}

	public function safeUp() {
		$tableOptions = 'ENGINE=InnoDB';
		$this->createTable('{{%payment_link_to_invoice}}', [
			'payment_id' => $this->integer(10)->unsigned(),
			'invoice_id' => $this->integer(10)->unsigned(),
			'sum'        => $this->decimal(20, 2)->unsigned()->notNull(),
		], $tableOptions);
		$this->addPrimaryKey('PRIMARY_KEY', '{{%payment_link_to_invoice}}', ['payment_id', 'invoice_id']);
		$this->createIndex('FK_payment_link_to_invoice_invoice', '{{%payment_link_to_invoice}}', 'invoice_id', false);
		$this->addForeignKey('fk_payment_link_to_invoice_invoice_id', '{{%payment_link_to_invoice}}', 'invoice_id', 'invoice', 'id', 'CASCADE', 'CASCADE');
		$this->addForeignKey('fk_payment_link_to_invoice_payment_id', '{{%payment_link_to_invoice}}', 'payment_id', 'payment', 'id', 'CASCADE', 'CASCADE');
	}

	public function safeDown() {

		$this->dropForeignKey('fk_payment_link_to_invoice_invoice_id', '{{%payment_link_to_invoice}}');
		$this->dropForeignKey('fk_payment_link_to_invoice_payment_id', '{{%payment_link_to_invoice}}');
		$this->dropTable('{{%payment_link_to_invoice}}');

	}
}
