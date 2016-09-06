<?php

class CommercialSearchForm extends BaseSearchForm
{
  public function configure()
  {
    $this->includeArea();

    $objecttypes = Param::$_widget_properties['comsell']['objecttype']['values'];
    $this->setWidget('objecttype', new sfWidgetFormChoice(array(
      'choices'  => array_combine($objecttypes, $objecttypes),
      'multiple' => true,
      'expanded' => true,
    )));
    $this->setValidator('objecttype', new sfValidatorChoice(array(
      'required' => false,
      'choices'  => $objecttypes,
      'multiple' => true,
    )));
  }
}