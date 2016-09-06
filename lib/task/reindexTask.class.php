<?php

class reindexTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = '';
    $this->name             = 'reindex';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    sfContext::createInstance($this->configuration);

    $ids = Doctrine::getTable('Lot')->createQuery()
      ->active()
      ->select('id')
      ->execute(array(), Doctrine::HYDRATE_SINGLE_SCALAR);

    $streets = array();
    foreach(array_keys(Lot::$_types) as $type) {
      $streets[$type] = UniqueList::getInstance('streets_' . $type);
      $streets[$type]->clear();
    }

    $cities = array();
    foreach(array_keys(Lot::$_types) as $type) {
      $cities[$type] = UniqueList::getInstance('cities_' . $type);
      $cities[$type]->clear();
    }

    foreach ($ids as $id) {
      $lot = Doctrine::getTable('Lot')->find($id);

      if ($lot->address) {
        if (!empty($lot->address['street'])) {
          $streets[$lot->type]->add(Tools::prepareStreet($lot->address['street']), false);
          if('penthouse' == $lot->type) {
            $streets['eliteflat']->add(Tools::prepareStreet($lot->address['street']), false);
          }
        }
        if (!empty($lot->address['city'])) {
          $cities[$type]->add($lot->address['city'], false);
        }
      }

      $lot->free(true);
    }

    foreach(array_keys(Lot::$_types) as $type) {
      $streets[$type]->save();
      $cities[$type]->save();
    }
  }
}
