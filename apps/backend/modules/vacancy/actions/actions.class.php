<?php

require_once dirname(__FILE__).'/../lib/vacancyGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/vacancyGeneratorHelper.class.php';

/**
 * vacancy actions.
 *
 * @package    kre
 * @subpackage vacancy
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class vacancyActions extends autoVacancyActions
{
  public function executeShow(sfWebRequest $request)
  {
    $this->forward('vacancy', 'edit');
  }
}
