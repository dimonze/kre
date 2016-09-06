<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version14 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createForeignKey('lot', 'lot_pid_lot_id', array(
             'name' => 'lot_pid_lot_id',
             'local' => 'pid',
             'foreign' => 'id',
             'foreignTable' => 'lot',
             'onUpdate' => '',
             'onDelete' => 'cascade',
             ));
        $this->addIndex('lot', 'lot_pid', array(
             'fields' => 
             array(
              0 => 'pid',
             ),
             ));
    }

    public function down()
    {
        $this->dropForeignKey('lot', 'lot_pid_lot_id');
        $this->removeIndex('lot', 'lot_pid', array(
             'fields' => 
             array(
              0 => 'pid',
             ),
             ));
    }
}