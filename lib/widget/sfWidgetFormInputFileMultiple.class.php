<?php
class sfWidgetFormInputFileMultiple extends sfWidgetFormInputFile
{
  /**
   * Configures the current widget.
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInputFile
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    
    $this->setAttribute('multiple', true);
  }
  
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    return parent::render($name . '[]', $value, $attributes, $errors);
  }
}