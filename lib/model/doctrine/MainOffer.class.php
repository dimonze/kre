<?php

/**
 * MainOffer
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    kre
 * @subpackage model
 * @author     Garin Studio
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class MainOffer extends BaseMainOffer
{
  public function getTypeText()
  {
    return Lot::$_types[$this->type];
  }

  public function getLot()
  {
    $lot = $this->getLotObject();
    if ($lot && $lot->status == 'active') {
      return $lot;
    }
    else {
      $lot = Doctrine::getTable('Lot')->getMostRatedLot($this->type);
      $this->lot_anons = mb_stripos($lot->anons, '</p>') ? mb_substr($lot->anons, 0, mb_stripos($lot->anons, '</p>')+4) : $lot->anons;
      return $lot;
    }
  }

  public function setLotObject(Lot $object)
  {
    foreach ($object->getTable()->getColumnNames() as $column) {
      if (!in_array($column, array('id','type','name','anons','is_penthouse','status','shortcut'))) {
        $object->__unset($column);
      }
    }

    $object->_oldValues = array();

    $this->_set('lot_id', $object->id);
    $this->_set('lot_object', $object);
    if (empty($this->lot_anons)) {
      $this->_set('lot_anons', $object->anons);
    }
  }

  public function clearLot()
  {
    $this->_set('lot_id', null);
    $this->_set('lot_anons', null);
    $this->_set('lot_object', null);
  }
}