<?php

/**
 * claim module configuration.
 *
 * @package    kre
 * @subpackage claim
 * @author     Garin Studio
 * @version    SVN: $Id: configuration.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class claimGeneratorConfiguration extends BaseClaimGeneratorConfiguration
{
  public function getFilterDefaults() {
    $suptype = sfContext::getInstance()->getUser()->getAttribute('suptype');
    if (null !== $suptype) {
      $types = array();
      foreach ($suptype['types'] as $type) {
        foreach (Claim::$_groups as $k => $group_types) {
          if (in_array($type, $group_types)) $types[] = $k;
        }
      }

      return array(
        'types' => array_unique($types, SORT_NUMERIC),
      );
    }
    else {
      return array();
    }
  }


}
