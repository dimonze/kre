<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version19 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->removeColumn('lot', 'active');
    }

    public function down()
    {
        $this->addColumn('lot', 'active', 'boolean', '25', array(
             ));
    }
}