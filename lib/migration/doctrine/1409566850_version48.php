<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version48 extends Doctrine_Migration_Base
{
    public function up()
    {
      $sql = 'INSERT INTO `param` (`id`, `name`) VALUES ("89", "Премиум размещение на ЦИАНЕ")';
      Doctrine_Manager::connection()->exec($sql);
    }
    
    public function down()
    {
      $sql = 'DELETE FROM `param` WHERE `param`.`id` = 89';
      Doctrine_Manager::connection()->exec($sql);
    }
}