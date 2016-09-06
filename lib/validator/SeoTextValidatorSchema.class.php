<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SeoTextValidatorSchema
 *
 * @author dimonze
 */
class SeoTextValidatorSchema extends sfValidatorSchema
{
  protected function configure($options = array(), $messages = array())
  {
    $this->addMessage('hrurl', 'Wrong value. Use value without special characters,'
            . 'alowed only [A-z][А-я][0-9], also could be used dash: [some_value]');
    $this->addMessage('url', 'Wrong value. When short link (ЧПУ) enabled, use value with parametrs, '
            . 'e.g.: /offers/[:type]/?[param1]=[value]&[param2]=[value]&...');
  }
 
  protected function doClean($values)
  {
    $errorSchema = new sfValidatorErrorSchema($this);
 
    foreach($values as $key => $value)
    {
      $errorSchemaLocal = new sfValidatorErrorSchema($this);
      if($key == 'url'){
        
        if (!preg_match('/\?/', $value) && $values['hrurl'] != '')
        {
          $errorSchemaLocal->addError(new sfValidatorError($this,  $this->getMessage('url')), 'URL');
        } 
        if (SeoText::IsUrlExist($value, $values['id']))
        {
          $errorSchemaLocal->addError(new sfValidatorError($this, "\"$value\"" . ' already exist'), 'URL');
        }
        $value = preg_replace("/http:\/\/".sfContext::getInstance()->getRequest()->getHost(). "/", '', $value);
        $value = preg_replace('/\&curren/', '&_curren', $value);
        $value = urldecode($value);
        $value = preg_replace('/\s/', '+', $value);
        $values[$key] = $value;
        
      }
      if($key == 'hrurl'){ 
        if (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)(\/)]/", $value))
        {
          $errorSchemaLocal->addError(new sfValidatorError($this, $this->getMessage('hrurl')), 'ЧПУ');
        }
        if ($value != '' && SeoText::IsHrurlExist($value, $values['id']))
        {
          $errorSchemaLocal->addError(new sfValidatorError($this, "\"$value\"" . ' already exist'), 'ЧПУ');
        }
        if(!count($errorSchemaLocal))
        {
          if(!preg_match('/\//', $value) && $value != ''){
            $tmp = preg_split('/\//', $values['url']);
            $value = '/' . $tmp[1] . '/' . $tmp[2] . '/' . $value . '/';
            $values[$key] = $value;
          }
        }
      }      
 
      // в этой внедрённой форме есть некоторые ошибки
      if (count($errorSchemaLocal))
      {
        $errorSchema->addError($errorSchemaLocal, (string) $key);
      }
    }
 
    // передаём ошибку в главную форму
    if (count($errorSchema))
    {
      throw new sfValidatorErrorSchema($this, $errorSchema);
    }
 
    return $values;
  }
}
