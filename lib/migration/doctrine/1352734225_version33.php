<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version33 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->dropForeignKey('lot', 'lot_broker_id_broker_id');
        $this->createForeignKey('lot', 'lot_broker_id_broker_id_1', array(
             'name' => 'lot_broker_id_broker_id_1',
             'local' => 'broker_id',
             'foreign' => 'id',
             'foreignTable' => 'broker',
             'onUpdate' => '',
             'onDelete' => 'set null',
             ));
        $this->addIndex('lot', 'lot_broker_id', array(
             'fields' => 
             array(
              0 => 'broker_id',
             ),
             ));
    }

    public function down()
    {
        $this->createForeignKey('lot', 'lot_broker_id_broker_id', array(
             'name' => 'lot_broker_id_broker_id',
             'local' => 'broker_id',
             'foreign' => 'id',
             'foreignTable' => 'broker',
             ));
        $this->dropForeignKey('lot', 'lot_broker_id_broker_id_1');
        $this->removeIndex('lot', 'lot_broker_id', array(
             'fields' => 
             array(
              0 => 'broker_id',
             ),
             ));
    }
}