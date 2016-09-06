<?php

class Version12 extends Doctrine_Migration_Base
{

  public function up()
  {
    $this->changeColumn('lot', 'type', 'enum', '', array(
      'values' =>
      array(
        'eliteflat',
        'elitenew',
        'flatrent',
        'cottage',
        'outoftown',
        'comrent',
        'comsell',
      ),
      'notnull' => '1',
    ));
  }

  public function down()
  {
    $this->changeColumn('lot', 'type', 'enum', '', array(
      'values' =>
      array(
        'eliteflat',
        'elitenew',
        'penthouse',
        'flatrent',
        'cottage',
        'outoftown',
        'comrent',
        'comsell',
      ),
      'notnull' => '1',
    ));
  }
}
