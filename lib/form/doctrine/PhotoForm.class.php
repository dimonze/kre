<?php

/**
 * Photo form.
 *
 * @package    kre
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PhotoForm extends BasePhotoForm
{
  public function configure()
  {
    $this->setWidget('lot_id', new sfWidgetFormInputHidden());
    $this->setWidget('position', new sfWidgetFormInputHidden());
    $this->setWidget('photo_type_id', new sfWidgetFormChoice(array(
      'choices'       => array('' => '') + Photo::$_types,
    )));
    $this->setWidget('file', new sfWidgetFormInputFileEditable(array(
      'file_src'      => !$this->isNew() ? $this->getObject()->getImage('thumb') : false,
      'edit_mode'     => !$this->isNew(),
      'is_image'      => true,
      'with_delete'   => true,
      'template'      => '%file%<br />%input%<br />%delete% удалить',
    )));


    $this->setValidator('photo_type_id', new sfValidatorChoice(array(
      'choices'       => array_keys($this->getWidget('photo_type_id')->getOption('choices')),
      'required'      => false,
    )));
    $this->setValidator('file', new sfValidatorFile(array('required' => false, 'mime_types' => 'web_images')));
    $this->setValidator('file_delete', new sfValidatorBoolean(array('required' => false)));


    $this->getValidator('name')->setOption('trim', true);
    $this->getValidator('name')->setOption('empty_value', null);


    $this->getWidgetSchema()->setLabels(array(
      'file'          => false,
      'name'          => 'Подпись',
      'photo_type_id' => 'Тип',
    ));
  }
}
