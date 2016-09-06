<?php

/**
 * LotParams form.
 *
 * @package    kre
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class LotParamsForm extends sfForm
{
  public function configure()
  {
    $type = $this->getOption('type');
    $map = Param::$_map[$type];

    foreach ($map as $group => $fields) {
      foreach ($fields as $field) {
        if(!empty(Param::$_widget_properties[$type][$field['field']]['type'])){
          $properties = Param::$_widget_properties[$type][$field['field']];
          switch($properties['type']) {
            case 'choice':
              $this->widgetSchema[$field['field']] = new sfWidgetFormChoice(array(
                'choices' => array('' => '') + array('--' => '-- Не наследовать') + array_combine($properties['values'],$properties['values'])
              ));
              $this->validatorSchema[$field['field']] = new sfValidatorString();
              $this->widgetSchema[$field['field']]->setLabel($field['name']);
            break;
          
            case 'choiceCurrency':
              $this->widgetSchema[$field['field']] = new sfWidgetFormChoice(array(
                'choices' => array_combine($properties['values'],$properties['values'])
              ));
              $this->validatorSchema[$field['field']] = new sfValidatorString();
              $this->widgetSchema[$field['field']]->setLabel($field['name']);
            break;

            case 'multichoice':
              $this->widgetSchema[$field['field']] = new sfWidgetFormChoice(array(
                'choices'  => array('--' => '-- Не наследовать') + array_combine($properties['values'], $properties['values']),
                'label'    => $field['name'],
                'multiple' => true,
              ));
              $this->validatorSchema[$field['field']] = new sfValidatorChoice(array(
                'choices'  => $properties['values'],
                'required' => false,
              ));
            break;

            case 'boolean':
              $checked = $this->getOption($field['field']);
              $this->widgetSchema[$field['field']] = new sfWidgetFormInputCheckbox(array(), array(
                  'checked' => null === $checked ? false : (bool)$checked
              ));
              $this->validatorSchema[$field['field']] = new sfValidatorString();
              $this->widgetSchema[$field['field']]->setLabel($field['name']);
            break;

            case 'textarea':
              $this->widgetSchema[$field['field']] = new sfWidgetFormTextarea(array(), array(
                'cols' => null,
                'rows' => 7,
                'limit' => 500
              ));
              $this->validatorSchema[$field['field']] = new sfValidatorString(array('trim' => true));
              $this->widgetSchema[$field['field']]->setLabel($field['name']);
            break;
          }
        }
        else {
          $this->widgetSchema[$field['field']] = new sfWidgetFormInput();
          $this->validatorSchema[$field['field']] = new sfValidatorString(array('trim' => true));
          $this->widgetSchema[$field['field']]->setLabel($field['name']);
        }
      }
    }

    $this->widgetSchema->setNameFormat('LotParams[%s]');
  }
}