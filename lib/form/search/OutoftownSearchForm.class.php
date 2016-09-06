<?php

class OutoftownSearchForm extends BaseSearchForm
{
  public function configure()
  {
    $this->includeDecoration();
    $this->includeHouseAreas();
    $this->includeArea();
    $this->includeOutOfTown();
    $this->includeMarket();

    $objecttypes = Param::$_widget_properties['outoftown']['objecttype']['values'];
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