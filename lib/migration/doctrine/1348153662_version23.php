<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version23 extends Doctrine_Migration_Base
{
  public function up()
  {
  $this->changeColumn('query', 'type', 'enum', '', array(
     'values' =>
     array(
    0 => 'eliteflat',
    1 => 'elitenew',
    2 => 'flatrent',
    3 => 'penthouse',
    4 => 'cottage',
    5 => 'outoftown',
    6 => 'comrent',
    7 => 'comsell',
     ),
     'notnull' => '1',
     ));
  }

  public function down()
  {

  }
}
