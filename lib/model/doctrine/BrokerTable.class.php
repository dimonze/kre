<?php

/**
 * BrokerTable
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class BrokerTable extends Doctrine_Table
{
  /**
   * Returns an instance of this class.
   *
   * @return object BrokerTable
   */
  public static function getInstance()
  {
    return Doctrine_Core::getTable('Broker');
  }

  public function getBrokersByIds($ids)
  {
    if (empty($ids)) return new Doctrine_Collection($this);

    $this->setAttribute(Doctrine_Core::ATTR_COLL_KEY, 'id');

    return $this->createQuery()
            ->select('id, name, phone, hidden')
            ->whereIn('id', $ids)
            ->execute();
  }

  public function getBackendLotBrokerListQuery($suptype = null)
  {
    $query = $this->createQuery()
            ->orderBy('name ASC');

    if (null != $suptype) {
      $query->andWhere('department = ?', array_search($suptype, Lot::$_suptypes));
    }

    return $query;
  }
}