<?php

use yii\db\Migration;

/**
 * Handles the creation of table `payment`.
 */
class m161207_102010_create_payment_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('payment', [
            'id' => $this->primaryKey(),

            'user_id' => $this->integer()->notNull(),
            'amount' => $this->float(2)->notNull()->defaultValue(0),
            'currency' => $this->string(3)->notNull(),
            'gateway_id' => $this->string(),
            'status' => $this->integer()->notNull(),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-payment_user_id',
            'payment',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk-payment_user_id', 'payment');
        $this->dropTable('payment');
    }
}
