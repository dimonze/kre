<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version25 extends Doctrine_Migration_Base
{
    public function up()
    {
      $this->changeColumn('lot', 'area_from', 'decimal', '10', array( 'scale' => '2'));
      $this->changeColumn('lot', 'area_to', 'decimal', '10', array( 'scale' => '2'));
    }

    public function down()
    { }
}