<?php

/**
 * VacancyTable
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class VacancyTable extends Doctrine_Table
{
  /**
   * Returns an instance of this class.
   *
   * @return object VacancyTable
   */
  public static function getInstance()
  {
    return Doctrine_Core::getTable('Vacancy');
  }

  public function getTypeVacancies($type)
  {
    return $this->createQuery()
            ->select('id, type, name')
            ->where('type = ?', $type)
            ->orderBy('id DESC')
            ->execute();
  }
}