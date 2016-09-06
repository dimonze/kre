<?php

/**
 * Claim form.
 *
 * @package    kre
 * @subpackage form
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ClaimForm extends BaseClaimForm
{
  public function configure()
  {
    $this->setWidget('created_at', new sfWidgetFormInputHidden());
    $this->setWidget('updated_at', new sfWidgetFormInputHidden());

    $this->setValidator('phone', new sfValidatorRegex(array(
      'required' => true,
      'pattern' => '/^[0-9\s\+\-\(\)]+$/'
    ), array(
      'invalid'     => 'Введены недопустимые символы',
      'required'    => 'Введите номер телефона, по которому мы можем с Вами связаться.',
    )));
    $this->setValidator('fio', new sfValidatorString(array(
      'max_length'  => 150,
      'required'    => true,
      'trim'        => true,
      'empty_value' => null
    ), array(
      'max_length'  => 'Максимум %max_length% символов.',
      'required'    => 'Введите Ваше имя/отчество.',
    )));
    $this->setValidator('email', new sfValidatorEmail(array(
      'max_length'  => 50,
      'required'    => true,
      'trim'        => true,
      'empty_value' => null
    ), array(
      'max_length'  => 'Максимум %max_length% символов.',
      'invalid'     => 'Введённый адрес электронной почты некорректен.',
      'required'    => 'Введите адрес электронной почты, по которому мы можем с Вами связаться.',
    )));
    $this->setValidator('text', new sfValidatorString(array(
      'max_length'  => 1000,
      'required'    => false,
      'trim'        => true,
    ), array(
      'max_length'  => 'Максимум %max_length% символов.',
    )));


    if (sfConfig::get('sf_app') != 'backend') {
      unset($this['created_at'], $this['updated_at'], $this['status']);
    }
    else {
      $this->getWidget('status')->setOption('choices', Claim::$_statuses);
    }

    $this->widgetSchema->setNameFormat('claim[%s]');
  }
}
