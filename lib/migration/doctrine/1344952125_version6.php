<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version6 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createTable('broker', array(
             'id' => 
             array(
              'type' => 'integer',
              'unsigned' => '1',
              'primary' => '1',
              'autoincrement' => '1',
              'length' => '2',
             ),
             'name' => 
             array(
              'type' => 'string',
              'length' => '255',
             ),
             'phone' => 
             array(
              'type' => 'string',
              'length' => '24',
             ),
             'department' => 
             array(
              'type' => 'enum',
              'values' => 
              array(
              0 => 'city',
              1 => 'commercial',
              2 => 'country',
              3 => 'rent',
              ),
              'notnull' => '1',
              'length' => '',
             ),
             ), array(
             'type' => 'InnoDB',
             'primary' => 
             array(
              0 => 'id',
             ),
             'collate' => 'utf8_general_ci',
             'charset' => 'utf8',
             ));
        $this->removeColumn('lot', 'phone');
        $this->addColumn('lot', 'broker_id', 'integer', '2', array(
             'unsigned' => '1',
             ));
    }

    public function down()
    {
        $this->dropTable('broker');
        $this->addColumn('lot', 'phone', 'array', '', array(
             ));
        $this->removeColumn('lot', 'broker_id');
    }
}