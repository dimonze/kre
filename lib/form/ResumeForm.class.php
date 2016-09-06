<?php

class ResumeForm extends sfForm
{
  public function configure()
  {
    $this->setValidator('vacancy_id', new sfValidatorDoctrineChoice(array(
      'model'       => 'Vacancy',
      'required'    => true,
    )));
    $this->setValidator('fio', new sfValidatorString(array(
      'max_length'  => 150,
      'required'    => true,
    ), array(
      'max_length'  => 'Максимум %max_length% символов.',
      'required'    => 'Введите Ваше имя/отчество.',
    )));
    $this->setValidator('email', new sfValidatorEmail(array(
      'max_length'  => 50,
      'required'    => true,
    ), array(
      'max_length'  => 'Максимум %max_length% символов.',
      'invalid'     => 'То, что Вы ввели, не является корректным e-mail адресом.',
      'required'    => 'Введите Ваш адрес электронной почты, по которому мы можем с Вами связаться.',
    )));
    $this->setValidator('phone', new sfValidatorString(array(
      'max_length'  => 20,
      'required'    => false,
    ), array(
      'max_length'  => 'Максимум %max_length% символов.',
    )));
    $this->setValidator('text', new sfValidatorString(array(
      'max_length'  => 500,
      'required'    => false,
    ), array(
      'max_length'  => 'Максимум %max_length% символов.',
    )));
    $this->setValidator('file', new sfValidatorFile(array(
      'max_size'    => 5242880,//5mb
      'mime_types'  => array(
        'application/msword',
        'application/pdf',
        'application/mspowerpoint',
        'application/powerpoint',
        'application/vnd.ms-powerpoint',
        'application/x-mspowerpoint',
        'text/plain',
        'text/richtext',
        'application/rtf',
        'application/x-rtf',
        'application/octet-stream',
        'application/x-iwork-keynote-sffkey',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      ),
      'required'    => false,
    ), array(
      'max_size'    => 'Слишком большой размер файла. Максимум %max_size% байт.',
      'mime_types'  => 'Недопустимый тип файла %mime_type%.',
      'partial'     => 'Файл был загружен частично. Нужно повторить.',
      'no_tmp_dir'  => 'Ошибка сервера: отсутствует временная папка.',
      'cant_write'  => 'Ошибка сервера: невозможно записать файл.',
      'extension'   => 'Ошибка сервера: загрузка прекращена из-за недопустимого расширения файла.',
    )));


    $this->getWidgetSchema()->setNameFormat('resume[%s]');

    $this->disableLocalCSRFProtection();
  }
}