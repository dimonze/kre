<?php

class FlatrentSearchForm extends BaseSearchForm
{
  public function configure()
  {
    $this->includeEstate();

    $this->setWidget('rooms_from', new sfWidgetFormInputText());
    $this->setWidget('rooms_to', new sfWidgetFormInputText());

    $this->setValidator('rooms_from', new sfValidatorNumber(array('required' => false)));
    $this->setValidator('rooms_to', new sfValidatorNumber(array('required' => false)));
  }
}