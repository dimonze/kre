<?php

/**
 * Claim filter form.
 *
 * @package    kre
 * @subpackage filter
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ClaimFormFilter extends BaseClaimFormFilter
{
  public function configure()
  {
    $this->setWidget('types', new sfWidgetFormChoice(array(
      'choices'   => array('' =>  '') + Claim::$_types,
      'multiple'  => true,
    )));

    $this->setValidator('types', new sfValidatorChoice(array(
      'choices'   => array_keys(Claim::$_types),
      'multiple'  => true,
      'required'  => false,
    )));


    $this->getWidget('lot_id')->setOption('empty_label', 'без номера');
    $this->getWidget('description')->setOption('empty_label', 'без описания');
    $this->getWidget('created_at')->setOption('from_date', new sfWidgetFormI18nDate(array('culture' => 'ru')));
    $this->getWidget('created_at')->setOption('to_date', new sfWidgetFormI18nDate(array('culture' => 'ru')));
    $this->getWidget('created_at')->setOption('template', 'от %from_date% до %to_date%');
    $this->getWidget('updated_at')->setOption('from_date', new sfWidgetFormI18nDate(array('culture' => 'ru')));
    $this->getWidget('updated_at')->setOption('to_date', new sfWidgetFormI18nDate(array('culture' => 'ru')));
    $this->getWidget('updated_at')->setOption('template', 'от %from_date% до %to_date%');
  }

  protected function addTypesColumnQuery(Doctrine_Query $query, $field, $value)
  {
    if (!empty($value)) {
      $and_where = array();
      foreach ($value as $k) {
        if (!empty($k)) {
          $and_where[] = sprintf('(%s.types LIKE "%%i:%d;%%")', $query->getRootAlias(), $k);
        }
      }

      if (count($and_where)) {
        $query->andWhere('('.implode(' OR ', $and_where).')');
      }
    }
  }
}
