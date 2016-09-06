<?php

/**
 * Broker form.
 *
 * @package    kre
 * @subpackage form
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class BrokerForm extends BaseBrokerForm
{
  public function configure()
  {
    $choices = array();
    foreach(Lot::$_suptypes as $name=>$value){
      $choices[$name] = $value['name'];
    }
    $this->setWidget('department', new sfWidgetFormChoice(array(
      'choices' => $choices,
    )));
    $this->setWidget('role', new sfWidgetFormChoice(array(
      'choices' => Broker::$_roles,
    )));
    //$this->setWidget('password', new sfWidgetFormInputPassword());

    $this->setValidator('name', new sfValidatorString(array(
      'max_length' => 255,
      'required' => true,
    )));
    $this->setValidator('phone', new sfValidatorString(array(
      'max_length' => 24,
      'required' => true,
    )));
    $this->setValidator('department', new sfValidatorChoice(array(
      'choices' => array_keys($choices),
    )));
    $this->setValidator('role', new sfValidatorChoice(array(
      'choices' => array_keys(Broker::$_roles),
    )));
    $this->setValidator('email', new sfValidatorEmail(array(
      'max_length' => 128,
      'required' => true,
    )));
    $this->setValidator('login', new sfValidatorString(array(
        'max_length' => 64,
        'required' => true,
    )));
    $this->setValidator('password', new sfValidatorString(array(
      'max_length' => 40,
      'required' => true,
    )));
  }
}
