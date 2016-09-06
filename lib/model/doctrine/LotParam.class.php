<?php

/**
 * LotParam
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    kre
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class LotParam extends BaseLotParam
{
  public function getParamTypeText()
  {
    return $this->_get('param_type_id') ? Param::$_types[$this->_get('param_type_id')] : '';
  }

  public function getValue()
  {
    if (in_array($this->param_id, Param::$convert_array)) {
      return explode(',', $this->_get('value'));
    }

    return $this->_get('value');
  }

  public function setValue($value)
  {
    if (in_array($this->param_id, Param::$convert_array) && is_array($value)) {
      $value = implode(',', $value);
    }
    if (in_array($this->param_id, Param::$convert_boolean)) {
      $value = (int) !!$value;
    }

    return $this->_set('value', $value);
  }
}