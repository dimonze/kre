<?php

class Version10 extends Doctrine_Migration_Base
{
  public function up()
  {
    $this->addColumn('lot', 'is_penthouse', 'boolean', null, array(
      'notnull' => '1',
      'default' => '0',
    ));
  }

  public function down()
  {
    $this->removeColumn('lot', 'is_penthouse');
  }
}