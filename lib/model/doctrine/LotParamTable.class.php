<?php

/**
 * LotParamTable
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class LotParamTable extends Doctrine_Table
{
  /**
    * Returns an instance of this class.
    *
    * @return object LotParamTable
    */
  public static function getInstance()
  {
    return Doctrine_Core::getTable('LotParam');
  }

  public function getRelatedParams($lot_id)
  {
    return $this->createQuery('lp')
            ->leftJoin('lp.Params p')
            ->where('lp.lot_id = ?', $lot_id)
            ->orderBy('lp.param_type_id ASC, lp.position ASC')
            ->execute();
  }

  public function getLotParamsByLotIds($lot_ids)
  {
    if (empty($lot_ids)) return new Doctrine_Collection($this);

    return $this->createQuery()
            ->whereIn('lot_id', $lot_ids)
            ->execute();
  }
}