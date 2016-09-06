<?php

/**
 * Query
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    kre
 * @subpackage model
 * @author     Garin Studio
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Query extends BaseQuery
{
  public static function log($data, $type)
  {
    foreach($data as $key => $value) {
      if(empty($value)) {
        unset($data[$key]);
      }
    }

    if(!empty($data)) {
      $q = new Query();
      $q->params = $data;
      $q->type   = $type;
      $q->day    = date('Y-m-d H:i:s');
      $q->save();
    }
  }
}