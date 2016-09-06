<?php

/**
 * Page filter form.
 *
 * @package    kre
 * @subpackage filter
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PageFormFilter extends BasePageFormFilter
{
  public function configure()
  {
    $this->setWidget('children', new sfWidgetFormDoctrineChoice(array(
      'model'         => 'Page',
      'table_method'  => 'findTree',
      'method'        => 'getIndentedName',
      'add_empty'     => true,
    )));


    $this->setValidator('children', new sfValidatorInteger(array('required' => false)));
  }

    public function addChildrenColumnQuery(Doctrine_Query $query, $field, $value)
  {
    function fetch_subpage_ids(Page $page)
    {
      static $ids = array();

      $ids[] = $page->id;

      $node = $page->getNode();
      if ($node->hasChildren()) {
        foreach ($node->getChildren() as $c) {
          fetch_subpage_ids($c);
        }
      }

      return $ids;
    }

    if (!empty($value) && intval($value) > 1) {
      $page_ids = fetch_subpage_ids(Doctrine::getTable('Page')->findOneBy('id', $value));
      $query->andWhereIn(sprintf('%s.id', $query->getRootAlias()), $page_ids);
    }
  }
}
