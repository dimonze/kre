<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version2 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('lot', 'has_children', 'boolean', '25', array(
             'default' => '0',
             'notnull' => '1',
             ));
    }

    public function down()
    {
        $this->removeColumn('lot', 'has_children');
    }
}