<?php

class Version50 extends Doctrine_Migration_Base
{
  public function up()
  {
    Doctrine_Manager::connection()->exec('DELETE FROM `lot_param` WHERE `value` = "Нет" AND `param_id` = 89');
    Doctrine_Manager::connection()->exec('UPDATE `lot_param` SET `value` = "да" WHERE `value` = "Да" AND `param_id` = 89');
  }

  public function down()
  {
    Doctrine_Manager::connection()->exec('UPDATE `lot_param` SET `value` = "Да" WHERE `value` = "да" AND `param_id` = 89');
  }
}
