<?php

/**
 * main_offer module helper.
 *
 * @package    kre
 * @subpackage main_offer
 * @author     Garin Studio
 * @version    SVN: $Id: helper.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class main_offerGeneratorHelper extends BaseMain_offerGeneratorHelper
{
  public function linkToClear($object, $options)
  {
    if ($object->lot_id) {
      return link_to($options['label'], 'main_offer/clear?type='.$object->type, $options['params']);
    }
  }
}
