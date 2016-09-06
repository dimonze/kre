<?php

/**
 * LotLog filter form.
 *
 * @package    kre
 * @subpackage filter
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class LotLogFormFilter extends BaseLotLogFormFilter
{
  public function configure()
  {
    $this->setWidget('lot_id',new sfWidgetFormInput());
    $this->setWidget('created_by', new sfWidgetFormChoice(array(
      'choices'   => array('' => '', 'kre' => 'kre', 'eklinina' => 'eklinina', 'akozyreva' => 'akozyreva', 'Alsou' => 'Alsou',
          'VipritskayaMK' => 'VipritskayaMK', 'seo' => 'seo'),
    )));

    $this->setValidator('created_by', new sfValidatorChoice(array(
      'choices'   => array_keys($this->getWidget('created_by')->getOption('choices')),
      'required'  => false,
    )));

    $this->getWidget('created_at')->setOption('from_date', new sfWidgetFormI18nDate(array('culture' => 'ru')));
    $this->getWidget('created_at')->setOption('to_date', new sfWidgetFormI18nDate(array('culture' => 'ru')));
    $this->getWidget('created_at')->setOption('template', 'от %from_date% до %to_date%');
  }

  protected function addCreatedByColumnQuery(Doctrine_Query $query, $field, $value)
  {
    if (!empty($value)) {
      $query->addWhere(sprintf('%s.created_by = ?', $query->getRootAlias()), $value);
    }
  }
}
