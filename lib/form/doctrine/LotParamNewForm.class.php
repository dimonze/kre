<?php

class LotParamNewForm extends BaseFormDoctrine
{
  public function configure()
  {
    parent::configure();

    $this->setWidget('param_id', new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model'   => 'Param',
      'url'     => sfContext::getInstance()->getController()->genUrl('@default?module=lot&action=matchParam'),
    )));
    $this->setWidget('param_type_id', new sfWidgetFormChoice(array(
      'choices' => array('' => '') + Param::$_types,
    )));
    $this->setWidget('value', new sfWidgetFormInputText());

    //without validators

    $this->getWidget('param_type_id')->setAttribute('class', 'param-type-select');
    $this->getWidget('value')->setAttribute('class', 'wide');

    $this->getWidgetSchema()->setNameFormat('lot[LotParamsNew][1][%s]');

    $this->getValidatorSchema()->setOption('allow_extra_fields', true);
    $this->getValidatorSchema()->setOption('filter_extra_fields', false);
  }

  public function getModelName()
  {
    return 'LotParam';
  }
}
