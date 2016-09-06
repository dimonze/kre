<?php

require_once dirname(__FILE__).'/../lib/brokerGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/brokerGeneratorHelper.class.php';

/**
 * broker actions.
 *
 * @package    kre
 * @subpackage broker
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class brokerActions extends autoBrokerActions
{
  public function executeShowhide(sfWebRequest $request) {
    $broker = Doctrine::getTable('Broker')->find($request->getParameter('id'));
    var_dump($broker->hidden);
    $broker->hidden = !!!$broker->hidden;
    $broker->save();
    $this->redirect($request->getReferer());
  }
}
