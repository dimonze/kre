<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version37 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addIndex('lot', 'shortcut', array(
             'fields' => 
             array(
              0 => 'shortcut',
             ),
             'type' => 'unique',
             ));
    }

    public function down()
    {
        $this->removeIndex('lot', 'shortcut', array(
             'fields' => 
             array(
              0 => 'shortcut',
             ),
             'type' => 'unique',
             ));
    }
}