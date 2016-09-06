<?php

class OrderCatalogForm extends sfForm
{
  public function configure()
  {
    $this->version = array('Электронная', 'Печатная');
    $this->budget   = array(
      'дешевле 1&nbsp;000&nbsp;000$',
      '1&nbsp;000&nbsp;000$–3&nbsp;000&nbsp;000$',
      '3&nbsp;000&nbsp;000$–5&nbsp;000&nbsp;000$',
      'дороже 5&nbsp;000&nbsp;000$'
    );
    
    $this->setWidgets(array(
      'fio'      => new sfWidgetFormInput(),
      'email'    => new sfWidgetFormInput(),
      'phone'    => new sfWidgetFormInput(),
      'version'  => new sfWidgetFormChoice(array(
        'choices' => $this->version
      )),
      'budget'   => new sfWidgetFormChoice(array(
        'choices' => $this->budget
      )),
      'address'  => new sfWidgetFormTextarea(),
    ));

    $this->setValidator('fio', new sfValidatorString(array(
      'max_length'  => 150,
      'required'    => true,
      'trim'        => true,
    ), array(
      'max_length'  => 'ФИО не может быть больше %max_length% символов.',
      'required'    => 'Введите Ваше имя/отчество.',
    )));
    $this->setValidator('email', new sfValidatorEmail(array(
      'max_length'  => 150,
      'required'    => true,
      'trim'        => true,
    ), array(
      'max_length'  => 'Слишком длинный адрес электронной почты. Допустимо %max_length% символов.',
      'invalid'     => 'Введённый адрес электронной почты некорректен.',
      'required'    => 'Введите адрес электронной почты, по которому мы можем с Вами связаться.',
    )));
    $this->setValidator('phone',  new sfValidatorPhone(array(
      'required' => true
    ), array(
      'min'         => 'Телефонный номер должен соответствовать формату +7 (495) 956-77-99',
      'max'         => 'Телефонный номер должен соответствовать формату +7 (495) 956-77-99',
      'invalid'     => 'Введённый номер телефона некорректен. Телефонный номер должен соответствовать формату +7 (495) 956-77-99',
      'required'    => 'Введите номер телефона, по которому мы можем с Вами связаться.',
    )));
    $this->setValidator('version',  new sfValidatorChoice(array(
        'choices'     => array_keys($this->version),
        'required'    => true,
      ),
      array(
        'required'  => 'Выберите версию',
      )
    ));
    $this->setValidator('budget',  new sfValidatorChoice(array(
        'choices'     => array_keys($this->budget),
        'required'    => true,
      ),
      array(
        'required'  => 'Выберите бюджет',
      )
    ));
    $this->setValidator('address', new sfValidatorString(array(
      'max_length'  => 500,
      'required'    => false,
      'trim'        => true,
    ), array(
      'max_length'  => 'Слишком много текста. Допустимо %max_length% символов.',
    )));

    $this->getWidgetSchema()->setNameFormat('ordercatalog[%s]');

  }
  
  public function getBudgetHuman($key = null)
  {
    if (!empty($this->budget[$key])) { 
      return $this->budget[$key];
    }
    return $this->budget[0];
  }
  
  public function getVersionHuman($key = null)
  {
    if (!empty($this->version[$key])) { 
      return $this->version[$key];
    }
    return $this->version[0];
      
  }
}