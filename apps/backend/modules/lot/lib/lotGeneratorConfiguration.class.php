<?php

/**
 * lot module configuration.
 *
 * @package    kre
 * @subpackage lot
 * @author     Garin Studio
 * @version    SVN: $Id: configuration.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class lotGeneratorConfiguration extends BaseLotGeneratorConfiguration
{
  public function getFilterDefaults() {
    $suptype = sfContext::getInstance()->getUser()->getAttribute('suptype');
    if (null !== $suptype) {
      return array(
        'type' => $suptype['types'],
      );
    }
    else {
      return array();
    }
  }
}