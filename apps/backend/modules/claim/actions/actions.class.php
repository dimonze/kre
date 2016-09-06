<?php

require_once dirname(__FILE__).'/../lib/claimGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/claimGeneratorHelper.class.php';

/**
 * claim actions.
 *
 * @package    kre
 * @subpackage claim
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class claimActions extends autoClaimActions
{
  public function executeChangeStatus(sfWebRequest $request)
  {
    if ($request->hasParameter('id') && $request->hasParameter('status') && in_array($request->getParameter('status'), array_keys(Claim::$_statuses))) {
      $object = Doctrine::getTable('Claim')->find($request->getParameter('id'));

      if ($object) {
        $object->status = $request->getParameter('status');
        $object->save();
      }
    }

    return sfView::NONE;
  }
}
