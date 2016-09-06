<?php

/**
 * page module helper.
 *
 * @package    kre
 * @subpackage page
 * @author     Garin Studio
 * @version    SVN: $Id: helper.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class pageGeneratorHelper extends BasePageGeneratorHelper
{
  private $_hideable_sections;

  public function linkToPublish($object, $params)
  {
    if ($this->canBeHidden($object) && !$object->is_active) {
      return '<li class="sf_admin_action_publish">' .
                link_to(__($params['label'], array(), 'sf_admin'),
                        'page/publish?id=' . $object->id, $params['params']
                       ) .
             '</li>';
    }
  }

  public function linkToUnpublish($object, $params)
  {
    if ($this->canBeHidden($object) && $object->is_active) {
      return '<li class="sf_admin_action_unpublish">' .
                link_to(__($params['label'], array(), 'sf_admin'),
                        'page/unpublish?id=' . $object->id, $params['params']
                       ) .
             '</li>';
    }
  }

  public function linkToPromote($object, $params)
  {
    if ($object->level > 1) {
      return '<li class="sf_admin_action_promote">' .
                link_to(__($params['label'], array(), 'sf_admin'),
                        'page/promote?id=' . $object->id, $params['params']
                       ) .
             '</li>';
    }
  }

  public function linkToDemote($object, $params)
  {
    if ($object->level > 1) {
      return '<li class="sf_admin_action_demote">' .
                link_to(__($params['label'], array(), 'sf_admin'),
                        'page/demote?id=' . $object->id, $params['params']
                       ) .
             '</li>';
    }
  }

  private function canBeHidden($object)
  {
    if (!$this->_hideable_sections) {
      $this->_hideable_sections = Doctrine::getTable('Page')->createQuery()
        ->whereIn('id', array(Page::NEWS_ID, Page::REVIEW_ID, Page::SERVICE_ID))
        ->execute();
    }

    foreach ($this->_hideable_sections as $section) {
      if ($section->lft < $object->lft && $section->rgt > $object->rgt) {
        return true;
      }
    }

    return false;
  }
}
