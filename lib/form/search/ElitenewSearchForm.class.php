<?php

class ElitenewSearchForm extends BaseSearchForm
{
  public function configure()
  {
    $this->includeArea();
    $this->includeEstate();
    $this->includeParking();
  }
}