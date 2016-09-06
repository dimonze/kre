<?php

class PhotoNewForm extends PhotoForm
{
  public function configure()
  {
    parent::configure();

    $this->setWidget('file', new sfWidgetFormInputFileMultiple(array('label' => 'Файлы<div class="warn">не более 16 МБ</div>')));

    $this->setValidator('file', new sfValidatorCallback(array(
      'callback' => array($this, 'validatorFile'),
      'required' => false,
    )));

    $this->getValidator('lot_id')->setOption('required', false);
    unset($this['name']);
  }

  public function validatorFile(sfValidatorCallback $validator, $value)
  {
    $validator_file = new sfValidatorFile(array(
      'required'      => false,
      'mime_types'    => array(
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/x-png',
        'image/gif',

        'application/pdf',

        'application/vnd.ms-excel',                                          //.xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', //.xlsx See: http://goo.gl/Vi3rg (MSDN)
      ),
      'max_size'      => 16 * 1024 * 1024,
    ));


    $files = array();
    foreach ($value as $file) {
      try {
        $files[] = $validator_file->clean($file);
      }
      catch (sfValidatorError $e) { }
    }

    return array_filter($files);
  }

  protected function doUpdateObject($values)
  {
    foreach ($values as $value) {
      $this->getObject()->fromArray($value);
    }
  }
}