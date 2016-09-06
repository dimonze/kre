<?php

class EliteflatSearchForm extends BaseSearchForm
{
  public function configure()
  {
    $this->includeArea();
    $this->includeEstate();
    $this->includeDecoration();
    $this->includeBalcony();
    $this->includeParking();
    $this->includeMarket();
  }
}