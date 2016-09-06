<?php

require_once dirname(__FILE__).'/../lib/main_offerGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/main_offerGeneratorHelper.class.php';

/**
 * main_offer actions.
 *
 * @package    kre
 * @subpackage main_offer
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class main_offerActions extends autoMain_offerActions
{
  public function executeShow(sfWebRequest $request)
  {
    $this->forward('main_offer', 'edit');
  }

  public function executeSet(sfWebRequest $request)
  {
    $lot = Doctrine::getTable('Lot')->find($request->getParameter('lot_id'));
    $this->forward404Unless($lot);

    $offer = Doctrine::getTable('MainOffer')->findOneby('type', $lot->type);
    $offer->clearLot();
    $offer->lot_object = $lot;
    $offer->save();

    $this->redirect($request->getReferer());
  }

  public function executeClear(sfWebRequest $request)
  {
    $object = Doctrine::getTable('MainOffer')->find($request->getParameter('type'));
    if ($object) {
      $object->clearLot();
      $object->save();
    }

    $this->redirect($request->getReferer());
  }
}
