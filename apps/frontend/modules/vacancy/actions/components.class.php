<?php

/**
 * vacancy components.
 *
 * @package    kre
 * @subpackage vacancy
 * @author     Garin Studio
 * @version    SVN: $Id: components.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class vacancyComponents extends sfComponents
{
  public function executeList(sfWebRequest $request)
  {
    $type = $request->getParameter('type');
    if (!in_array($type, Doctrine::getTable('Vacancy')->getEnumValues('type'))) {
      return sfView::NONE;
    }

    $this->vacancies = Doctrine::getTable('Vacancy')->getTypeVacancies($type);
    if (!$this->vacancies->count()) return sfView::NONE;
  }
}