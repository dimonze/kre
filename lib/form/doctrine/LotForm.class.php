<?php

/**
 * Lot form.
 *
 * @package    kre
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class LotForm extends BaseLotForm
{
  public function configure()
  {
    $suptype = sfContext::getInstance()->getUser()->getAttribute('suptype');
    $types = array();
    if(null !== $suptype) {
      foreach($suptype['types'] as $type) {
        $types[$type] = Lot::$_types[$type];
      }
    }
    else{
      $types = array('' => '') + Lot::getTypesReal();
    }
    $this->setWidget('lat', new sfWidgetFormInputHidden());
    $this->setWidget('lng', new sfWidgetFormInputHidden());
    $this->setWidget('file', new sfWidgetFormInputFileEditable(array(
      'file_src'      => $this->getObject()->getIsImageStored() ? $this->getObject()->getImage('item') : false,
      'edit_mode'     => $this->getObject()->getIsImageStored(),
      'is_image'      => true,
      'with_delete'   => true,
      'template'      => '%file%<br />%input%<br />%delete% удалить',
    )));
    $this->setWidget('type_real', new sfWidgetFormChoice(array(
      'choices' => $types,
    )));
    $this->setWidget('pid', new sfWidgetFormDoctrineChoice(array(
      'model'         => 'Lot',
      'table_method'  => 'getSupLotsQuery',
      'add_empty'     => true,
    )));
    $this->setWidget('pid_hid', new sfWidgetFormInputHidden());
    $metro = sfConfig::get('app_subways');
    asort($metro);
    $this->setWidget('metro_id', new sfWidgetFormChoice(array(
      'choices'       => array('' => '') + $metro,
    )));
    $this->setWidget('district_id', new sfWidgetFormChoice(array(
      'choices'       => array('' => '') + sfConfig::get('app_districts', array()),
    )));
    $this->setWidget('ward', new sfWidgetFormChoice(array(
      'choices'       => array('' => '') + sfConfig::get('app_wards', array()),
    )));
    $this->setWidget('ward2', new sfWidgetFormChoice(array(
      'choices'       => array('' => '') + sfConfig::get('app_wards', array()),
    )));
    $this->setWidget('status', new sfWidgetFormChoice(array(
      'choices'       =>  Lot::$_status,
    )));
    $this->setWidget('broker_id', new sfWidgetFormDoctrineChoice(array(
      'model'     => 'Broker',
      'method'    => 'getPhoneNameFormat',
      'add_empty' => true,
      'query'     => Doctrine::getTable('Broker')->getBackendLotBrokerListQuery($suptype),
    )));
    $this->setWidget('show_phone', new sfWidgetFormChoice(array(
      'choices' => Broker::$_phone_show_types,
    )));
//    $choices = array();
//    $this->setWidget('phone', new sfWidgetFormChoice(array(
//      'choices'       => array_combine(sfConfig::get('app_phones', array()), sfConfig::get('app_phones', array())),
//      'multiple'      => true,
//    )));
    $this->setWidget('area', new sfWidgetFormInputRange());
    $this->setWidget('price', new sfWidgetFormInputRange());
    $this->setWidget('price_all', new sfWidgetFormInputRange());
    $this->setWidget('anons', new sfWidgetFormTextareaTinyMCE($this->_tinymce_mini_options));
    $this->setWidget('description', new sfWidgetFormTextareaTinyMCE($this->_tinymce_options));
    $this->setWidget('special_text', new sfWidgetFormTextareaTinyMCE($this->_tinymce_options));
    $this->setWidget('hidden_text', new sfWidgetFormTextareaTinyMCE($this->_tinymce_mini_options));
    $this->setWidget('seo_description', new sfWidgetFormTextarea());
    $this->setWidget('priority', new sfWidgetFormInputCheckbox());
    if (!is_null($suptype)) {
      $this->setWidget('is_special', new sfWidgetFormInputHidden());
      $this->setWidget('shortcut', new sfWidgetFormInputHidden());
    }
    else {
      $this->setWidget('is_special', new sfWidgetFormInputCheckbox());
    }

    $this->setWidget('clone_me', new sfWidgetFormInputHidden());


    $address = $this->getObject()->address;
    foreach(Param::$_addressStructure as $key=>$label) {
      $widget = new sfWidgetFormInput();
      $widget->setDefault(!empty($address[$key]) ? $address[$key] : '')
        ->setLabel($label);
      $this->setWidget('address_' . $key, $widget);

      $this->setValidator('address_' . $key, new sfValidatorString(array('required' => false)));
    }


    $this->setValidator('type_real', new sfValidatorChoice(array(
      'choices'       => array_keys($this->getWidget('type_real')->getOption('choices')),
      'required'      => false,
    )));
    $this->setValidator('file', new sfValidatorFile(array(
      'required'      => false,
      'mime_types'    => 'web_images',
    )));
    $this->setValidator('file_delete', new sfValidatorBoolean(array('required' => false)));
    $this->setValidator('pid', new sfValidatorDoctrineChoice(array(
      'model'         => 'Lot',
      'required'      => false,
    )));
    $this->setValidator('pid_hid', new sfValidatorDoctrineChoice(array(
      'model'         => 'Lot',
      'required'      => false,
    )));
    $this->setValidator('metro_id', new sfValidatorChoice(array(
      'choices'       => array_keys($this->getWidget('metro_id')->getOption('choices')),
      'required'      => false,
    )));
    $this->setValidator('district_id', new sfValidatorChoice(array(
      'choices'       => array_keys($this->getWidget('district_id')->getOption('choices')),
      'required'      => false,
    )));
    $this->setValidator('ward', new sfValidatorChoice(array(
      'choices'       => array_keys($this->getWidget('ward')->getOption('choices')),
      'required'      => false,
    )));
    $this->setValidator('ward2', new sfValidatorChoice(array(
      'choices'       => array_keys($this->getWidget('ward2')->getOption('choices')),
      'required'      => false,
    )));
    $this->setValidator('show_phone', new sfValidatorChoice(array(
      'choices'       => array_keys(Broker::$_phone_show_types),
    )));
//    $this->setValidator('phone', new sfValidatorChoice(array(
//      'choices'       => array_keys($this->getWidget('phone')->getOption('choices')),
//      'multiple'      => true,
//      'required'      => false,
//    )));
    $this->setValidator('area', new sfValidatorNumberRange(array('required' => false)));
    $this->setValidator('price', new sfValidatorIntegerRange(array('required' => false)));
    $this->setValidator('price_all', new sfValidatorIntegerRange(array('required' => false)));
    $this->setValidator('params', new sfValidatorPass(array('required' => false)));
    $this->setValidator('anons', new sfValidatorString(
      array('required' => false, 'empty_value' => null)
    ));
    $this->setValidator('special_text', new sfValidatorString(
      array('required' => false, 'empty_value' => null)
    ));
    $this->setValidator('hidden_text', new sfValidatorString(
      array('required' => false, 'empty_value' => null)
    ));

    $this->setValidator('priority', new sfValidatorCallback(array(
        'callback'  => array($this, 'validatorPriority'),
      )));

    $this->setValidator('shortcut', new sfValidatorString(array(
      'required' => false,
      'empty_value' => null
    )));

    $this->setValidator('clone_me', new sfValidatorString(array(
      'required' => false,
      'empty_value' => null
    )));


    $this->getValidator('name')->setOption('trim', true);
    $this->getValidator('seo_title')->setOption('trim', true);
    $this->getValidator('seo_description')->setOption('trim', true);
    $this->getValidator('seo_keywords')->setOption('trim', true);

    $this->getValidator('description')->setOption('empty_value', null);
    $this->getValidator('address')->setOption('empty_value', null);
    //$this->getValidator('phone')->setOption('empty_value', null);
    $this->getValidator('seo_title')->setOption('empty_value', null);
    $this->getValidator('seo_description')->setOption('empty_value', null);
    $this->getValidator('seo_keywords')->setOption('empty_value', null);
    $this->getWidgetSchema()->setHelp('area', 'м<sup>2</sup>');


    $this->embedPhotosForm();

    $this->mergePostValidator(new sfValidatorCallback(array(
        'callback'  => array($this, 'validatorPremiumCian'),
      )));

    unset($this['type'], $this['new_price'], $this['new_object'], $this['created_at'], $this['updated_at']);
  }

  public function validatorPriority(sfValidatorCallback $validator, $values)
  {
    if(Doctrine::getTable('Lot')->getPriorityLotCount() > 1999 ){
      throw new sfValidatorError($validator, 'Для данного лота невозможно проставить галку. Лимит в 2000 лотов превышен');
    }
    return true;
  }

  public function validatorPremiumCian(sfValidatorCallback $validator, $values)
  {
    if (isset($values['params']['premium_cian']) && $values['params']['premium_cian'] == 'да') {
      if (Doctrine::getTable('Lot')->getPremiumCianCount($values['id']) > 179) {
        throw new sfValidatorError($validator, 'Для данного лота невозможно проставить галку. Лимит в 180 премиум лотов превышен');
      }
    }
    return $values;
  }

  public function processValues($values)
  {
    $values['address'] = array();
    foreach(Param::$_addressStructure as $key=>$value) {
      if(!empty($values['address_' . $key])) {
        $values['address'][$key] = $values['address_' . $key];
      }
      if(isset($values['address_' . $key])) {
        unset($values['address_' . $key]);
      }
    }

    $values['area_from']  = !empty($values['area'][0]) ? $values['area'][0] : 0;
    $values['area_to']    = !empty($values['area'][1]) ? $values['area'][1] : 0;
    $values['price_from'] = !empty($values['price'][0]) ? $values['price'][0] : 0;
    $values['price_to']   = !empty($values['price'][1]) ? $values['price'][1] : 0;
    $values['price_all_from'] = !empty($values['price_all'][0]) ? $values['price_all'][0] : 0;
    $values['price_all_to']   = !empty($values['price_all'][1]) ? $values['price_all'][1] : 0;

    $values['updated_at'] = date('Y-m-d H:i:s');//всегда обновляем дату, т.к. из-за кастомной обработки LotPаrams отследить изменения невозможно

    if ($values['clone_me'] == '1') {
      $this->getObject()->_should_be_cloned = true;
      unset($values['clone_me']);
    }

    return parent::processValues($values);
  }

  public function updateObjectEmbeddedForms($values, $forms = null)
  {
    if (null === $forms) $forms = $this->embeddedForms;

    unset($forms['Photos'], $forms['PhotosNew']);

    foreach ($forms as $name => $form) {
      if (!isset($values[$name]) || !is_array($values[$name])) {
        continue;
      }

      if ($form instanceof sfFormObject) {
        $form->updateObject($values[$name]);
      }
      else {
        $this->updateObjectEmbeddedForms($values[$name], $form->getEmbeddedForms());
      }
    }
  }

  public function saveEmbeddedForms($con = null, $forms = null)
  {
    if (null === $con) $con = $this->getConnection();
    if (null === $forms) $forms = $this->embeddedForms;

    unset($forms['Photos'], $forms['PhotosNew']);

    foreach ($forms as $form) {
      if ($form instanceof sfFormDoctrine) {
        foreach ($form->getObject()->getTable()->getRelations() as $relationName => $relation) {;
          if ($relation instanceof Doctrine_Relation_Association) {
            call_user_func(array($form, sprintf('save%sList', $relationName)), $con);
          }
        }

        $form->getObject()->save($con);
        $form->saveEmbeddedForms($con);
      }
      else {
        $this->saveEmbeddedForms($con, $form->getEmbeddedForms());
      }
    }
  }

  public function getJavaScripts()
  {
    return array_merge(parent::getJavaScripts(), array(
      'http://api-maps.yandex.ru/2.0/?load=package.full&mode=debug&lang=ru-RU',
      'backend-ymap.js',
    ));
  }


  protected function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    $this->setDefault('area', array($this->getObject()->area_from, $this->getObject()->area_to));
    $this->setDefault('price', array($this->getObject()->price_from, $this->getObject()->price_to));
    $this->setDefault('price_all', array($this->getObject()->price_all_from, $this->getObject()->price_all_to));
    $this->setDefault('pid_hid', $this->getObject()->pid);
  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->updatePhotos();
    $this->savePhotosNew();
  }

  private function updatePhotos()
  {
    $photos = $this->getValue('Photos');
    $forms  = $this->getEmbeddedForm('Photos')->getEmbeddedForms();

    foreach ($photos as $i => $photo) {
      if ($photo['file_delete'] && !$photo['file']) {
        $forms[$i]->getObject()->delete();
      }
      elseif ($photo['file']) {
        $forms[$i]->getObject()->file = $photo['file'];
      }
    }
  }

  private function savePhotosNew()
  {
    $photos = $this->getValue('PhotosNew');
    if (!$photos['file']) return;

    foreach ($photos['file'] as $fileObject) {
      $photo = new Photo();
      $photo->lot_id  = $this->getObject()->id;
      $photo->file    = $fileObject;
      $photo->photo_type_id = $photos['photo_type_id'];

      $photo->save();
      $photo->free();
      unset($photo);
    }
  }

  private function embedPhotosForm()
  {
    $this->getObject()->Photos = Doctrine::getTable('Photo')->getRelatedPhotos($this->getObject()->id);

    $this->embedRelation('Photos', 'PhotoForm');

    $photos_new_form = new PhotoNewForm();
    $photos_new_form->setDefault('lot_id', $this->getObject()->id);
    $this->embedForm('PhotosNew', $photos_new_form);
  }

  public function renderHiddenFields($recursive = true)
  {
    return parent::renderHiddenFields(false);
  }
}
