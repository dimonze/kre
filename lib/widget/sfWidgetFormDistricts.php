<?php

class sfWidgetFormDistricts extends sfWidgetForm
{
  public function __construct($options = array(), $attributes = array())
  {
    $this->addRequiredOption('choices');

    parent::__construct($options, $attributes);
  }

  public function render($name, $values = array(), $attributes = array(), $errors = array())
  {
    if ('[]' != substr($name, -2)) {
      $name .= '[]';
    }

    $html = '';
    if (!empty($values)) {
      $html .= implode(', ', array_intersect_key($this->getOption('choices'), array_flip($values)));
      foreach ($values as $district_id) {
        $html .= $this->renderTag('input', array(
          'type'  => 'hidden',
          'name'  => $name,
          'value' => $district_id,
          'id'    => false,
        ));
      }
    }

    return $html;
  }
}