<?php

class Version5 extends Doctrine_Migration_Base
{
  public function up()
  {
    $this->addColumn('lot', 'hide_price', 'boolean', null, array(
      'default' => '0',
      'notnull' => '1',
    ));
  }

  public function down()
  {
    $this->removeColumn('lot', 'hide_price');
  }
}