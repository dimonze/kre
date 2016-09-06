<?php

/**
 * Lot filter form.
 *
 * @package    kre
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class LotFormFilter extends BaseLotFormFilter
{
  public function configure()
  {
    $suptype = sfContext::getInstance()->getUser()->getAttribute('suptype');
    $types = array();

    if(null !== $suptype) {
      foreach($suptype['types'] as $type) {
        $types[$type] = Lot::$_types[$type];
      }
    }
    else{
      $types = array('' => '') + Lot::$_types;
    }

    $object_types = array('' => '');
    if(in_array('outoftown', array_keys($types))){
      $object_types = array_merge($object_types, Param::$_widget_properties['outoftown']['objecttype']['values']);
    }
    if(in_array('comrent', array_keys($types))){
      $object_types = array_merge($object_types, Param::$_widget_properties['outoftown']['objecttype']['values']);
    }

    $this->setWidget('type', new sfWidgetFormChoice(array(
      'choices'  => $types,
      'multiple' => true,
    )));    
    $this->setWidget('pid', new sfWidgetFormDoctrineChoice(array(
      'model'         => 'Lot',
      'table_method'  => 'getSupLotsQuery',
      'method'        => 'getNameWithId',
      'add_empty'     => true,
    )));
    $this->setWidget('metro_id', new sfWidgetFormChoice(array(
      'choices'       => array('' => '') + sfConfig::get('app_subways', array()),
    )));
    $this->setWidget('district_id', new sfWidgetFormChoice(array(
      'choices'       => array('' => '') + sfConfig::get('app_districts', array()),
    )));
    $this->setWidget('id', new sfWidgetFormInput());
    $this->setWidget('ward', new sfWidgetFormChoice(array(
      'choices'       => array('' => '') + sfConfig::get('app_wards', array()),
    )));
    
    $this->setWidget('market', new sfWidgetFormChoice(array(
      'choices'       =>  array('' => '','empty' => 'пустое', 'Первичный' => 'Первичный', 'Вторичный' => 'Вторичный'),
    )));
    
    $this->setWidget('premium_cian', new sfWidgetFormChoice(array(
      'choices'       =>  array('' => ' ', 'Да' => 'да', 'Нет' => 'нет'),
    )));
    
    $this->setWidget('locality', new sfWidgetFormInput());
    $this->setWidget('cottageVillage', new sfWidgetFormInput());
    $this->setWidget('objecttype', new sfWidgetFormChoice(array(
      'choices'       => array_combine($object_types, $object_types),
    )));
    $this->setWidget('rooms', new sfWidgetFormInput());
    $this->setWidget('estate', new sfWidgetFormInput());
    $this->setWidget('price', new sfWidgetFormInput());
    $this->setWidget('price_land', new sfWidgetFormInput());
    $this->setWidget('firstId', new sfWidgetFormInput());
    $this->setWidget('lastId', new sfWidgetFormInput());



    $this->setValidator('id', new sfValidatorInteger(array('required' => false)));
    $this->setValidator('pid', new sfValidatorInteger(array('required' => false)));
    $this->setValidator('type', new sfValidatorChoice(array(
      'choices'       => array_keys($types),
      'required'      => (null !== $suptype),
      'multiple' => true,
    )));
    $this->setValidator('metro_id', new sfValidatorChoice(array(
      'choices'       => array_keys($this->getWidget('metro_id')->getOption('choices')),
      'required'      => false,
    )));
    $this->setValidator('district_id', new sfValidatorChoice(array(
      'choices'       => array_keys($this->getWidget('district_id')->getOption('choices')),
      'required'      => false,
    )));
    $this->setValidator('ward', new sfValidatorChoice(array(
      'choices'       => array_keys($this->getWidget('ward')->getOption('choices')),
      'required'      => false,
    )));

    $this->setValidator('locality', new sfValidatorString(array(
      'required' => false,
    )));
    $this->setValidator('cottageVillage', new sfValidatorString(array(
      'required' => false,
    )));
    $this->setValidator('market', new sfValidatorChoice(array(
      'choices'       => array_keys($this->getWidget('market')->getOption('choices')),
      'required'      => false,
    )));
    $this->setValidator('objecttype', new sfValidatorChoice(array(
      'choices'       => array_keys($this->getWidget('objecttype')->getOption('choices')),
      'required'      => false,
    )));
    $this->setValidator('rooms', new sfValidatorString(array(
      'required' => false,
    )));
    
    $this->setValidator('premium_cian', new sfValidatorChoice(array(
      'choices'       => array_keys($this->getWidget('premium_cian')->getOption('choices')),
      'required' => false,
    )));
    
    $this->setValidator('estate', new sfValidatorString(array(
      'required' => false,
    )));
    $this->setValidator('price', new sfValidatorString(array(
      'required' => false,
    )));
    $this->setValidator('price_land', new sfValidatorString(array(
      'required' => false,
    )));
    
    $this->setValidator('firstId', new sfValidatorString(array(
      'required' => false,
    )));
    
    $this->setValidator('lastId', new sfValidatorString(array(
      'required' => false,
    )));

    $this->getWidget('status')->setOption('choices', array('' => '') + Lot::$_status);
    $this->getWidget('priority')->setOption('choices', array('' => ' ', 1 => 'да', 0 => 'нет'));
    $this->getWidget('exportable')->setOption('choices', array('' => ' ', 1 => 'да', 0 => 'нет'));
    $this->getWidget('is_special')->setOption('choices', array('' => ' ', 1 => 'да', 0 => 'нет'));
    //$this->getWidget('market')->setOption('choices', array('null', 'Первичный', 'Вторичный'));
  } 
  
  protected function addLocalityColumnQuery(Doctrine_Query $query, $field, $value)
  {
    $name = 'lcq';
    if (!empty($value)) {
      $query->leftJoin(sprintf('%s.LotParams %s', $query->getRootAlias(), $name));
      $query->addWhere(sprintf('(%s.param_id = 43 AND %s.value = ?)',$name, $name), $value);
    }
  }

  protected function addCottageVillageColumnQuery(Doctrine_Query $query, $field, $value)
  {
    $name = 'cvcq';
    if (!empty($value)) {
      $query->leftJoin(sprintf('%s.LotParams %s', $query->getRootAlias(), $name));
      $query->addWhere(sprintf('(%s.param_id = 44 AND %s.value = ?)',$name, $name), $value);
    }
  }

  protected function addEstateColumnQuery(Doctrine_Query $query, $field, $value)
  {
    $name = 'ecq';
    if (!empty($value)) {
      $query->leftJoin(sprintf('%s.LotParams %s', $query->getRootAlias(), $name));
      $query->addWhere(sprintf('(%s.param_id = 2 AND %s.value = ?)',$name, $name), $value);
    }
  }

  protected function addPremiumCianColumnQuery(Doctrine_Query $query, $field, $value)
  {
    $name = 'pcq';
    if (!empty($value)) {
      $query->leftJoin(sprintf('%s.LotParams %s', $query->getRootAlias(), $name));
      $query->addWhere(sprintf('(%s.param_id = 89 AND %s.value = ?)',$name, $name), $value);
    }
  }
  
  protected function addObjecttypeColumnQuery(Doctrine_Query $query, $field, $value)
  {
    $name = 'ocq';
    if (!empty($value)) {
      $query->leftJoin(sprintf('%s.LotParams %s', $query->getRootAlias(), $name));
      $query->addWhere(sprintf('(%s.param_id = 1 AND %s.value = ?)',$name, $name), $value);
    }
  }
  
   protected function addMarketColumnQuery(Doctrine_Query $query, $field, $value)
  {
    $name = 'mcq';
    if (!empty($value)) {
      if ($value == 'empty') { 
        $query->leftJoin(sprintf('%s.LotParams %s ON %s.lot_id = %s.id AND %s.param_id = 79', $query->getRootAlias(), $name, $name, $query->getRootAlias(), $name));
        $query->leftJoin(sprintf('%s.LotParams %s ON %s.lot_id = %s.pid AND %s.param_id = 79', $query->getRootAlias(), 'pid', 'pid', $query->getRootAlias(), 'pid'));
        $query->addWhere(sprintf('%s.param_id IS NULL AND %s.param_id IS NULL', $name, 'pid'));
      }else{
        $query->leftJoin(sprintf('%s.LotParams %s ON %s.lot_id = %s.id AND %s.param_id = 79', $query->getRootAlias(), $name, $name, $query->getRootAlias(), $name));
        $query->leftJoin(sprintf('%s.LotParams %s ON %s.lot_id = %s.pid AND %s.param_id = 79', $query->getRootAlias(), 'pid', 'pid', $query->getRootAlias(), 'pid'));
        $query->addWhere(sprintf('(%s.param_id IS NULL AND %s.value = ?) OR (%s.value = ?)', $name, 'pid', $name), array($value, $value));
      }
    }
  }

  protected function addRoomsColumnQuery(Doctrine_Query $query, $field, $value)
  {
    $name = 'rcq';
    if (!empty($value)) {
      $query->leftJoin(sprintf('%s.LotParams %s', $query->getRootAlias(), $name));
      $query->addWhere(sprintf('(%s.param_id = 47 AND %s.value = ?)',$name, $name), $value);
    }
  }

  protected function addPriceColumnQuery(Doctrine_Query $query, $field, $value)
  {
    if (!empty($value)) {
      $query->addWhere(sprintf('%s.price_from = ?', $query->getRootAlias()), $value);
    }
  }
  
  protected function addFirstIdColumnQuery(Doctrine_Query $query, $field, $value)
  {
    if (!empty($value)) {
      $query->addWhere(sprintf('%s.id >= ?', $query->getRootAlias()), $value);
    }
  }
  
  protected function addLastIdColumnQuery(Doctrine_Query $query, $field, $value)
  {
    if (!empty($value)) {
      $query->addWhere(sprintf('%s.id <= ?', $query->getRootAlias()), $value);
    }
  }
  
  protected function addPriceLandColumnQuery(Doctrine_Query $query, $field, $value)
  {
    if (!empty($value)) {
      $query->addWhere(sprintf('%s.price_land_from = ?', $query->getRootAlias()), $value);
    }
  }

  protected function addDistrictIdColumnQuery(Doctrine_Query $query, $field, $value)
  {
    if (!empty($value)) {
      $query->addWhere(sprintf('%s.district_id = ?', $query->getRootAlias()), $value);
    }
  }

  protected function addMetroIdColumnQuery(Doctrine_Query $query, $field, $value)
  {
    if (!empty($value)) {
      $query->addWhere(sprintf('%s.metro_id = ?', $query->getRootAlias()), $value);
    }
  }

  protected function addWardColumnQuery(Doctrine_Query $query, $field, $value)
  {
    if (!empty($value)) {
      $query->addWhere(sprintf('(%s.ward = ? or %s.ward2 = ?)', $query->getRootAlias(), $query->getRootAlias()), array($value, $value));
    }
  }

  protected function addPIdColumnQuery(Doctrine_Query $query, $field, $value)
  {
    if (!empty($value)) {
      $query->addWhere(sprintf('%s.pid = ?', $query->getRootAlias()), $value);
    }
  }

  protected function addIdColumnQuery(Doctrine_Query $query, $field, $value)
  {
    if (!empty($value)) {
      $query->addWhere(sprintf('%s.id = ?', $query->getRootAlias()), $value);
    }
  }

  protected function addTypeColumnQuery(Doctrine_Query $query, $field, $value)
  {
    if (is_array($value) && $value[0]) {
      $query
        ->select(sprintf('%s.*', $query->getRootAlias()))
        ->addSelectActualType()
        ->addHaving(sprintf('actual_type in ("%s")', implode('","', $value)));
    }
  }
}
