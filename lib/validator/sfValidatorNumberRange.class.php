<?php

class sfValidatorNumberRange extends sfValidatorNumber
{
  protected function doClean($values)
  {
    $clean = array();

    if (is_array($values)) {
      for ($i=0; $i<2; $i++) {
        $value = str_replace(',', '.', $values[$i]);
        $clean[$i] = isset($values[$i]) ? parent::doClean($value) : 0;
      }
    }
    else {
      $clean = parent::doClean($values);
    }

    return $clean;
  }
}