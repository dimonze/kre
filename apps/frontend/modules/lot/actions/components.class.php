<?php

/**
 * lot components.
 *
 * @package    kre
 * @subpackage lot
 * @author     Garin Studio
 * @version    SVN: $Id: components.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $el
 */
class lotComponents extends sfComponents
{
  public function executeBest()
  {
    $this->lots = Doctrine::getTable('Lot')->getBestLots();
  }

  public function executeChildren()
  {
    $children = Doctrine::getTable('Lot')->getChildren($this->lot->id);

    if (!count($children)) {
      return sfView::NONE;
    }

    $this->children = array();
    foreach ($children as $child) {
      if (!isset($this->children[$child->type_real])) {
        $this->children[$child->type_real] = array();
      }

      $child->set('Parent', $this->lot);
      $this->children[$child->type_real][] = $child;
    }
  }
}