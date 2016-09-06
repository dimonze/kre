<?php

/**
 * form components.
 *
 * @package    kre
 * @subpackage form
 * @author     Garin Studio
 * @version    SVN: $Id: components.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $el
 */
class formComponents extends sfComponents
{
  public function executeContactForm()
  { 
    $this->route = $this->getContext()->getRouting()->getCurrentRouteName();
  }
}