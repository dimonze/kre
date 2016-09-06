<?php

class CottageSearchForm extends BaseSearchForm
{
  public function configure()
  {
    $this->includeHouseAreas();
    $this->includeArea();
    $this->includeOutOfTown();
  }
}