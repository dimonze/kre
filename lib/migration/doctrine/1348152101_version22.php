<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version22 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('query', 'type', 'enum', '', array(
             'values' => 
             array(
              0 => 'eliteflat',
              1 => 'elitenew',
              2 => 'flatrent',
              3 => 'cottage',
              4 => 'outoftown',
              5 => 'comrent',
              6 => 'comsell',
             ),
             'notnull' => '1',
             ));
    }

    public function down()
    {
        $this->removeColumn('query', 'type');
    }
}