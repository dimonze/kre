<?php

/**
 * lot components.
 *
 * @package    kre
 * @subpackage lot
 * @author     Garin Studio
 * @version    SVN: $Id: components.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class lotComponents extends sfComponents
{
  public function executeParams()
  {
    if (isset($this->form)) {
      $this->params = $this->form->getObject()->LotParams;
    }
    else {
      $this->params = Doctrine::getTable('LotParam')->getRelatedParams($this->lot_id);
    }
  }

  public function executeParamsNew()
  {
    $this->form = new LotParamNewForm();
  }

  public function executePhotos()
  {
    if (isset($this->form)) {
      $this->photos = $this->form->getObject()->Photos;
    }
    else {
      $this->photos = Doctrine::getTable('Photo')->getRelatedPhotos($this->lot_id);
    }
  }
}