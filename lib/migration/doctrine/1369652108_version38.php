<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version38 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('lot', 'hidden_text', 'text');
    }

    public function down()
    {
        $this->removeColumn('lot', 'hidden_text');
    }
}