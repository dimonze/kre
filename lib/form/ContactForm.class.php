<?php

class ContactForm extends sfForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'fio'   => new sfWidgetFormInput(),
      'email' => new sfWidgetFormInput(),
      'text'  => new sfWidgetFormTextarea(),
    ));

    $this->setValidator('fio', new sfValidatorString(array(
      'max_length'  => 150,
      'required'    => true,
      'trim'        => true,
    ), array(
      'max_length'  => 'Не больше %max_length% символов.',
      'required'    => 'Введите Ваше имя/отчество.',
    )));
    $this->setValidator('email', new sfValidatorEmail(array(
      'max_length'  => 50,
      'required'    => true,
      'trim'        => true,
    ), array(
      'max_length'  => 'Не больше %max_length% символов.',
      'invalid'     => 'Введённый адрес электронной почты некорректен.',
      'required'    => 'Введите ваш адрес электронной почты.',
    )));
    $this->setValidator('text', new sfValidatorString(array(
      'max_length'  => 500,
      'required'    => false,
      'trim'        => true,
    ), array(
      'max_length'  => 'Не больше %max_length% символов.',
    )));

    $this->getWidgetSchema()->setNameFormat('contacts[%s]');

    $this->disableLocalCSRFProtection();
  }
}