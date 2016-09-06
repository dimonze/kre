<?php

class sfValidatorPhone extends sfValidatorInteger
{
  protected function configure($options = array(), $messages = array())
  {
    parent::configure();

    $this->setOption('min', 70000000000);
    $this->setOption('max', 79999999999);
  }

  protected function doClean($value)
  {
    if (is_array($value)) {
      $value = '7'.$value[0].$value[1];
    }
    $value = preg_replace('/\D+/', '', $value);

    // epic fix for production :D don't use intval()
    $clean = floatval($value);

    if (strval($clean) != $value)
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    if ($this->hasOption('max') && $clean > $this->getOption('max'))
    {
      throw new sfValidatorError($this, 'max', array('value' => $value, 'max' => $this->getOption('max')));
    }

    if ($this->hasOption('min') && $clean < $this->getOption('min'))
    {
      throw new sfValidatorError($this, 'min', array('value' => $value, 'min' => $this->getOption('min')));
    }

    return $clean;
  }
}