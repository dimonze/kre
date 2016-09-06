<?php

require_once dirname(__FILE__).'/../lib/pageGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/pageGeneratorHelper.class.php';

/**
 * page actions.
 *
 * @package    kre
 * @subpackage page
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class pageActions extends autoPageActions
{
  public function executeShow(sfWebRequest $request)
  {
    $this->forward('page', 'edit');
  }

  public function executeDelete(sfWebRequest $request)
  {
    if (in_array($this->getRoute()->getObject()->id, array(2,6,11,16,19,22,26,27,56,57,58,59))) {
      $this->getUser()->setFlash('error', 'Не-а, эту страницу удалить нельзя..');
      $this->redirect('@page');
    }

    return parent::executeDelete($request);
  }

  public function executePublish(sfWebRequest $request)
  {
    $object = Doctrine::getTable('Page')->find($request->getParameter('id'));
    $object->is_active = true;
    $object->save();

    $this->redirect('@page');
  }

  public function executeUnpublish(sfWebRequest $request)
  {
    $object = Doctrine::getTable('Page')->find($request->getParameter('id'));
    $object->is_active = false;
    $object->save();

    $this->redirect('@page');
  }

  public function executePromote(sfWebRequest $request)
  {
    $object = Doctrine::getTable('Page')->find($request->getParameter('id'));
    $node = $object->getNode();
    if ($node->hasPrevSibling()) {
      $prev = $node->getPrevSibling();
      $node->moveAsPrevSiblingOf($prev);
    }

    $this->redirect('@page');
  }

  public function executeDemote(sfWebRequest $request)
  {
    $object = Doctrine::getTable('Page')->find($request->getParameter('id'));
    $node = $object->getNode();
    if ($node->hasNextSibling()) {
      $next = $node->getNextSibling();
      $node->moveAsNextSiblingOf($next);
    }

    $this->redirect('@page');
  }
}
