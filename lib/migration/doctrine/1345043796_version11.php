<?php

class Version11 extends Doctrine_Migration_Base
{
  public function up()
  {
    $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
    $stmt = $conn->prepare('
      update lot set is_penthouse = ?, type = ? where type = ?
    ');
    $stmt->execute(array(true, 'eliteflat', 'penthouse'));
    $stmt->closeCursor();
  }

  public function down()
  {
  }
}
