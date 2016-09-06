<?php

class importTask extends sfBaseTask
{
  public
    $phones_old = array(),
    $brokers    = array();
  private $_conn, $_conn_old, $_rewrite_dir;

  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Skip the question'),
      new sfCommandOption('no-cleanup', null, sfCommandOption::PARAMETER_NONE, 'Skip cleanup'),
      new sfCommandOption('cleanup', null, sfCommandOption::PARAMETER_NONE, 'Cleanup only'),
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'Type'),
      new sfCommandOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Limit'),
      new sfCommandOption('id', null, sfCommandOption::PARAMETER_REQUIRED, 'Lot ID'),
    ));

    $this->namespace        = '';
    $this->name             = 'import';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    if (!($options['no-confirmation'] || $options['no-cleanup']) && !$this->ask('All Lot data will be erased. Continue?')) {
      exit;
    }

    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->_conn = $databaseManager->getDatabase($options['connection'])->getConnection();
    $this->_rewrite_dir = sfConfig::get('sf_data_dir') . '/rewrite';

    $this->_conn_old = new PDO(
      'mysql:dbname=kre_old;host=localhost', 'kre', 'kre',
      array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      )
    );


    $tables = array(
      'eliteflat' => array('type' => 'eliteflat'),
      'penthouse' => array('type' => 'eliteflat', 'is_penthouse' => true),
      'elitenew'  => array('type' => 'elitenew', 'has_children' => true),
      'flatrent'  => array('type' => 'flatrent'),
      'comrent'   => array('type' => 'comrent'),
      'comsell'   => array('type' => 'comsell'),
      'cottage'   => array('type' => 'cottage', 'params' => array('decoration' => '--')),
      'outoftown' => array('type' => 'outoftown', 'params' => array('decoration' => '--')),
    );

    if (!$options['no-cleanup']) {
      $this->logSection('status', 'Cleaning data...');
      $this->removeData();
      $this->logSection('status', 'Removing files...');
      $this->removeFiles();
      if ($options['cleanup']) {
        exit;
      }
    }

    if ($type = $options['type']) {
      $table = $tables[$type];
      $tables = array();
      $tables[$type] = $table;
    }

    $this->logSection('status', 'Loading...');
    $this->loadPhones();

    foreach ($tables as $table => $table_options) {
      $this->logSection('status', sprintf('Importing %s...', $table));
      $this->importTable($table, $table_options, $options['limit'] ?: 10000, $options['id']);
      $this->logSection('status', 'Removing tmp files...');
      $this->removeTmp();
    }

    $this->resetMain();
    $this->logSection('done', 'Completed successfully');
  }


  private function removeData()
  {
    foreach (array('lot_param', 'lot', 'photo') as $table) {
      $stmt = $this->_conn->prepare('truncate table ' . $table);
      $stmt->execute();
      $stmt->closeCursor();
    }
  }

  private function removeFiles()
  {
    foreach (array('lot', 'photo') as $dir) {
      $path = sfConfig::get('sf_upload_dir') . '/' . $dir;
      sfToolkit::clearDirectory($path);
    }
    sfToolkit::clearDirectory($this->_rewrite_dir);
  }

  private function removeTmp()
  {
    foreach (glob(sys_get_temp_dir() . '/dl_kre*') as $tmp) {
      unlink($tmp);
    }
  }


  private function resetMain()
  {
    $stmt = $this->_conn->prepare('update main_offer set lot_object = ?');
    $stmt->execute(array(serialize(null)));
    $stmt->closeCursor();
  }


  private function loadPhones()
  {
    $stmt = $this->_conn_old->prepare('select id, header from offers_phones');
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $this->phones_old[$row['id']] = $row['header'];
    }
    $stmt->closeCursor();


    $stmt = $this->_conn->prepare('select id, phone from broker');
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $this->brokers[$row['phone']] = $row['id'];
    }
    $stmt->closeCursor();
  }


  private function importTable($table, array $options, $limit, $id)
  {
    $this->logSection($table, 'starting import');

    if ($id) {
      $where = ' where id = ' . $id;
      $limit = '';
      if ($new_id = LotTable::getNewId($table, $id)) {
        if ($lot = Doctrine::getTable('Lot')->find($new_id)) {
          $lot->delete();
        }
      }
    }
    else {
      $where = '';
      $limit = ' limit ' . $limit;
    }

    $stmt = $this->_conn_old->prepare('select * from ' . $table . $where . $limit);
    $stmt->execute();

    $i = 0;
    $s = $stmt->rowCount();
    $times = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $i++;

      try {
        $avg = count($times) ? array_sum($times) / count($times) : 0;
        $this->logSection($table, sprintf(
          '%d [%d/%d] ETA: %s',
          $row['lot'], $i, $s, $avg ? gmdate('H:i:s', $avg * ($s - $i)) : '-'
        ));

        $filename = sprintf('%s/%s', $this->_rewrite_dir, $table);
        file_put_contents($filename,
          sprintf("%d\t%d\n", $row['id'], $row['lot']) . @file_get_contents($filename)
        );

        $time = microtime(true);
        $this->import($row, $table, $options);
        $times[] = microtime(true) - $time;
      }
      catch (Exception $e) {
        $this->logSection('error', $e->getMessage(), 512, 'ERROR');
      }
    }
    $stmt->closeCursor();
  }

  private function import(array $original, $table, array $options)
  {
    $values = $this->cleanUp($original, $table);
    $data = array_merge(array('status' => 'inactive', 'params' => array()), $options);

    foreach ($this->getFields() as $o_key => $n_key) {
      if (isset($values[$o_key])) {
        $data[$n_key] = $this->processValue($table, $o_key, $values[$o_key]);
        unset($values[$o_key]);
      }
    }

    $params = call_user_func_array('array_merge', Param::$_map[$data['type']]);
    foreach ($params as $field) {
      $key = $field['field'];
      if (isset($values[$key])) {
        $data['params'][$key] = $this->processValue($table, $key, $values[$key]);
        unset($values[$key]);
      }
    }

    $this->extractAddress($values, $data);
    $this->extractPhones($values, $data);
    $this->setCoords($values, $data);
    $this->setImage($values, $data);
    $images = $this->prepareImages($values, $data);

    unset($values['id'], $values['title']);
    if ($values) {
      throw new Exception('Unknown fields: ' . implode(', ', array_keys($values)));
    }

    foreach(array('price', 'price_all', 'area') as $key) {
      if(!empty($data[$key . '_from']) && !empty($data[$key . '_to'])) {
        if ($data[$key . '_from'] == $data[$key . '_to']) {
          unset($data[$key . '_to']);
        }
      }
    }

    $lot = new Lot();

    // 'cause setParams depends on $lot->type
    $lot->type = $data['type'];
    $lot->is_penthouse = !empty($data['is_penthouse']);
    $lot->fromArray($data);

    try {
      $lot->save();
      $this->importImages($lot, $images);
    }
    catch (Doctrine_Exception $e) {
      if ($lot->is_penthouse && false !== strpos($e->getMessage(), '1062 Duplicate entry')) {
        $stmt = $this->_conn->prepare('update lot set is_penthouse = ? where id = ?');
        $stmt->execute(array(true, $lot->id));
        $stmt->closeCursor();
      }
      else {
        throw $e;
      }
    }

    $lot->free(true);
  }

  private function cleanUp(array $values, $table)
  {
    unset(
      $values['main_alt'], $values['show_title'],
      // comrent
      $values['minspace'], $values['maxspace'], $values['theallsquare'],
      // outoftown
      $values['infra_env']
    );

    if (!empty($values['roomsfrom'])) {
      $values['rooms'] = $values['roomsfrom'];
    }

    if (isset($values['space']) && strpos($values['space'], '-')) {
      list($values['spacefrom'], $values['spaceto']) = explode('-', $values['space']);
    }

    if ($values['coord_x'] + $values['coord_y'] <= 1) {
      unset($values['coord_x'], $values['coord_y']);
    }

    return array_filter($values);
  }

  private function processValue($table, $param, $value)
  {
    $methods = array(
      sprintf('process_%s_%s_value', $table, $param),
      sprintf('process_%s_value', $param)
    );

    foreach ($methods as $method) {
      $method = sfInflector::camelize($method);
      if (is_callable(array($this, $method))) {
        return $this->$method($value);
      }
    }

    return $value;
  }


  private function extractAddress(array &$values, array &$data)
  {
    $address = array();

    if (isset($values['xml_town'])) {
      $address['city'] = $values['xml_town'];
    }

    if (isset($values['street'])) {
      $address['street'] = $values['street'];
    }
    if (isset($values['xml_house'])) {
      $address['house'] = $values['xml_house'];
    }
    if (isset($values['xml_korp'])) {
      $address['building'] = $values['xml_korp'];
    }
    if (isset($values['xml_str'])) {
      $address['construction'] = $values['xml_str'];
    }

    if ($address) {
      $data['address'] = $address;
    }

    unset(
      $values['street'], $values['xml_house'],
      $values['xml_korp'], $values['xml_str'],
      $values['xml_town']
    );
  }

  private function extractPhones(array &$values, array &$data)
  {
    $data['show_phone'] = 'broker';

    foreach (array('phone2', 'phone') as $field) {
      if (isset($values[$field]) && isset($this->phones_old[$values[$field]])) {
        $phone = $this->phones_old[$values[$field]];
        if (isset($this->brokers[$phone])) {
          $data['broker_id'] = $this->brokers[$phone];
        }
        elseif (0 === strpos($phone, '(495)')) {
          $data['show_phone'] = 'broker' == $data['show_phone'] ? 'both' : 'office';
        }
      }
    }

    unset($values['phone'], $values['phone2']);
  }

  private function setCoords(array &$values, array &$data)
  {
    if (!empty($data['lat']) && !empty($data['lng'])) {
      return true;
    }
    if (empty($data['address']['street']) || empty($data['address']['house'])) {
      return false;
    }

    $q = urlencode(sprintf(
      'город Москва, %s, дом %s %s %s',
      $data['address']['street'], $data['address']['house'],
      !empty($data['address']['building']) ? ', корпус ' . $data['address']['building'] : '',
      !empty($data['address']['construction']) ? ', строение ' . $data['address']['construction'] : ''
    ));
    $url = sprintf(
      'http://geocode-maps.yandex.ru/1.x/?geocode=%s&results=1&key=%s&format=json',
      $q, 'AFSc8U0BAAAAFz4gBwIARHvsE1k9c3pWG0BT4oAmr3oPZWcAAAAAAAAAAACAnZ30_0RaHedtEyv8-C9cCZRlWQ=='
    );

    do {
      $geo = file_get_contents($url);
    } while (!$geo && sleep(1));
    $geo = json_decode($geo);

    if (isset($geo->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos)) {
      list($data['lng'], $data['lat']) = explode(' ', $geo->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
    }
  }

  private function setImage(array &$values, array &$data)
  {
    $type = empty($data['is_penthouse']) ? $data['type'] : 'penthouse';

    $file = sprintf(
      '/var/www/kre/web/uploads_old/%s/%1$s_%d_%d/photo_%1$s_%d_main_p.jpg',
      $type, floor($values['id'] / 100) * 100, ceil(($values['id'] + 1) / 100) * 100 - 1,
      $values['id']
    );

    if (!file_exists($file)) {
      $file = str_replace('_main_p.jpg', '_main_b.jpg', $file);
    }

    if (file_exists($file)) {
      $data['file'] = $this->wrapFile($file);
    }
  }

  private function prepareImages(array &$values, array &$data)
  {
    $images = array();
    $type = empty($data['is_penthouse']) ? $data['type'] : 'penthouse';

    for ($i = 1; $i <= 20; $i++) {
      if (isset($values['photo_type_' . $i])) {
        $file = sprintf(
          '/var/www/kre/web/uploads_old/%s/%1$s_%d_%d/photo_%1$s_%d_%d.jpg',
          $type, floor($values['id'] / 100) * 100, ceil(($values['id'] + 1) / 100) * 100 - 1,
          $values['id'], $i
        );

        if (file_exists($file)) {
          $images[] = array(
            'name'          => isset($values['photo_' . $i]) ? $values['photo_' . $i] : '',
            'photo_type_id' => $values['photo_type_' . $i],
            'file'          => $this->wrapFile($file),
            'position'      => $i,
          );
        }
      }
      unset($values['photo_' . $i], $values['photo_type_' . $i]);
    }

    return $images;
  }

  private function importImages(Lot $lot, array $images)
  {
    foreach ($images as $image) {
      $photo = new Photo();
      $photo->fromArray(array_merge($image, array('lot_id' => $lot->id)));
      $photo->save();
      $photo->free(true);
    }
  }



  private function wrapFile($file)
  {
    $target = tempnam(sys_get_temp_dir(), 'dl_kre_');
    $info = getimagesize($file);

    if (copy($file, $target)) {
      return new sfValidatedFile(
        basename($file),
        $info['mime'],
        $target,
        filesize($file)
      );
    }
  }



  protected function processCurrencyValue($value)
  {
    $currencies = array(1 => 'USD', 2 => 'EUR', 3 => 'RUR');
    return isset($currencies[$value]) ? $currencies[$value] : null;
  }

  protected function processActiveValue($value)
  {
    return 'active';
  }

  protected function processDecorationValue($value)
  {
    $value = mb_strtolower($value);
    return (!$value || false !== strpos($value, 'без отделки') || false !== strpos($value, 'под отделку'))
      ? 'без отделки'
      : 'с отделкой';
  }

  protected function processCottageDecorationValue($value)
  {
    return $this->processDecorationValue($value);
  }

  protected function processOutoftownDecorationValue($value)
  {
    return $this->processCottageDecorationValue($value);
  }

  protected function processAboutDecorationValue($value)
  {
    return $this->processDecorationValue($value);
  }

  protected function processOutoftownObjecttypeValue($value)
  {
    $map = array(
      1 => 'Квартира',
      2 => 'Таунхаус',
      3 => 'Коттедж',
      4 => 'Участок',
      5 => 'Коттеджный посёлок',
    );
    return isset($map[$value]) ? $map[$value] : null;
  }

  protected function processComsellObjecttypeValue($value)
  {
    $map = array(
      1 => 'Офисное помещение',
      2 => 'Особняк',
      3 => 'Торговое помещение',
      4 => 'Помещение свободного назначения',
      5 => 'Склад/складской комплекс',
      6 => 'Промышленный комплекс',
      7 => 'Земельный участок',
      8 => 'Прочее',
      9 => 'Отдельно стоящее здание',
    );
    return isset($map[$value]) ? $map[$value] : null;
  }

  protected function processComrentObjecttypeValue($value)
  {
    return $this->processComsellObjecttypeValue($value);
  }

  protected function processLeadValue($value)
  {
    return self::processHtml($value);
  }

  protected function processTextValue($value)
  {
    return self::processHtml($value);
  }

  protected function processComsellDistrictValue($value)
  {
    $map = array(
      15 => 16, 16 => 17, 19 => 20, 20 => 21, 21 => 22, 22 => 23,
      23 => 24, 24 => 25, 25 => 26, 26 => 28, 27 => 29
    );
    return isset($map[$value]) ? $map[$value] : $value;
  }


  private function getFields()
  {
    return array (
      'lot' => 'id',
      'type' => 'type',
      'header' => 'name',
      'metro' => 'metro_id',
      'district' => 'district_id',
      'ward' => 'ward',
      'ward2' => 'ward2',

      'coord_x' => 'lng',
      'coord_y' => 'lat',

      'pricefrom' => 'price_from',
      'priceto' => 'price_to',

      'priceallfrom' => 'price_all_from',
      'priceallto' => 'price_all_to',

      'space' => 'area_from',
      'spacefrom' => 'area_from',
      'spaceto' => 'area_to',

      'currency' => 'currency',

      'newprice' => 'new_price',
      'newobj' => 'new_object',

      'lead' => 'anons',
      'text' => 'description',

      'rating' => 'rating',
      'active' => 'status',
      'exportable' => 'exportable',
    );
  }


  public static function processHtml($value)
  {
    if (!preg_match('/^\s*<p/i', $value)) {
      $value = "<p>$value</p>";
    }

    $value =  preg_replace_callback(
      '/<font([^>]*)>/iU',
      function($matches) {
        if (preg_match('/color=(\'|")?([a-z0-9#]+)(\'|")?/i', $matches[0], $_matches)) {
          return sprintf('<span style="color: %s">', $_matches[2]);
        }
        return '<span>';
      },
      str_ireplace('</font>', '</span>', $value)
    );

    $value = preg_replace('/\s*(<p[^>]*>(\s*&nbsp;\s*)*<\/p>\s*)+\s*$/i', '', $value);

    return $value;
  }
}
