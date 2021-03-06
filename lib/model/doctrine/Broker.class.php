<?php

/**
 * Broker
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    kre
 * @subpackage model
 * @author     Garin Studio
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Broker extends BaseBroker
{
  static public $_phone_show_types = array(
    'both'   => 'офисный + мобильный',
    'broker' => 'мобильный',
    'office' => 'офисный',
  ),
  $_roles = array(
    'broker'  => 'Брокер',
    'manager' => 'Менеджер',
  ),
  $_hidden = array(
    0 => 'Виден',
    1 => 'Скрыт',
  );

  public function getDepartmentName()
  {
    return Lot::$_suptypes[$this->getDepartment()]['name'];
  }

  public function getRoleName()
  {
    return self::$_roles[$this->getRole()];
  }

  public function getHiddenVal()
  {
    return self::$_hidden[(int)$this->hidden];
  }

  public function getPhoneNameFormat()
  {
    return sprintf('%s / %s %s.', $this->phone, strtok($this->name, ' '), mb_substr(strtok(' '), 0, 1, 'utf-8'));
  }

  static public function isAuth() {
    $role = sfContext::getInstance()->getUser()->getAttribute('role');
    return !empty($role)
      ? in_array($role, array_keys(self::$_roles))
      : false;
  }
}
