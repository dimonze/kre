<?php

class geoTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = '';
    $this->name             = 'geo';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase($options['connection'])->getConnection();

    $stmt_up = $conn->prepare('update lot set lat = ?, lng = ? where id = ?');
    
    $stmt = $conn->prepare('select id, address from lot');
    $stmt->execute();
    while ($row = $stmt->fetch(Doctrine::FETCH_ASSOC)) {
      if ($row['address'] && ($address = unserialize($row['address']))) {
        if ($coords = $this->getCoords($address)) {
          $stmt_up->execute(array($coords[0], $coords[1], $row['id']));
          $this->logSection($row['id'], sprintf('%s %s', $coords[0], $coords[1]));
        }
        else {
          $this->logSection($row['id'], 'not found', NULL, 'QUESTION');
        }
      }
    }
  }

  private function getCoords(array $address)
  {
    if (empty($address['street']) || empty($address['house'])) {
      return false;
    }

    $q = urlencode(sprintf(
      'город Москва, %s, дом %s %s %s',
      $address['street'], $address['house'],
      !empty($address['building']) ? ', корпус ' . $address['building'] : '',
      !empty($address['construction']) ? ', строение ' . $address['construction'] : ''
    ));
    $url = sprintf(
      'http://geocode-maps.yandex.ru/1.x/?geocode=%s&results=1&key=%s&format=json',
      $q, 'AFSc8U0BAAAAFz4gBwIARHvsE1k9c3pWG0BT4oAmr3oPZWcAAAAAAAAAAACAnZ30_0RaHedtEyv8-C9cCZRlWQ=='
    );

    do {
      $geo = file_get_contents($url);
    } while (!$geo);
    $geo = json_decode($geo);

    if (isset($geo->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos)) {
      return array_reverse(explode(' ', $geo->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos));
    }
  }
}
