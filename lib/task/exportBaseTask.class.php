<?php

abstract class exportBaseTask extends sfBaseTask
{
  const
    ORG_NAME  = 'Contact Real Estate',
    ORG_EMAIL = 'kre@kre.ru',
    ORG_SITE  = 'http://www.kre.ru';

  protected
    $_current_type,
    $_xml_writer,
    $_counters = array('total' => 0, 'good' => 0, 'bad' => 0, 'current' => 0);


  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->addArguments(array(
      new sfCommandArgument('type', sfCommandArgument::OPTIONAL, 'Estate type'),
    ));

    $this->namespace = 'export';

    $this->briefDescription    = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    ini_set('memory_limit', '160M');//equal to memory limit on production
    ini_set('max_execution_time', 0);
    setlocale(LC_NUMERIC, 'C');//for dot separated float numbers

    $type = !empty($arguments['type']) ? $arguments['type'] : null;
    if (!is_null($type) && !in_array($type, $this->getAllowedSuptypes()) && !in_array($type, $this->getAllowedTypes())) {
      throw new Exception(sprintf('Unrecognized estate type: "%s"', $type));
    }

    gc_enable();
    $this->logSection('GC is', gc_enabled() ? 'enabled' : 'disabled');

    if (!$options['trace']) {
      sfConfig::set('sf_debug', false); //disable doctrine profiler because of huge memory leak
    }

    sfContext::createInstance($this->configuration);
    Doctrine_Manager::connection()->setAttribute(Doctrine_Core::ATTR_AUTO_FREE_QUERY_OBJECTS, true);

    $this->logSection('start', $type);
    $this->_current_type = $type;
    $ids = $this->getList($type);
    $this->_counters['total'] = count($ids);

    $this->_xml_writer = new XMLWriter();
    $this->_xml_writer->openUri(self::generateFileName(static::PARTNER, $type));
    $this->_xml_writer->setIndentString(str_repeat(" ", 2));
    $this->_xml_writer->setIndent(true);

    $this->writeDocumentStart();

    $this->_xml_writer->writeComment(sprintf(' Generated at %s, contains approx %s lots ', date('Y-m-d H:i:s'), $this->_counters['total']));

    $this->processLots($ids);

    $this->writeDocumentFinish();

    $this->_xml_writer->flush();
    $this->_xml_writer = null;

    $this->logSection('result', sprintf('%d total, %d added and %d excluded', $this->_counters['total'], $this->_counters['good'], $this->_counters['bad']));
    $this->logSection('done', self::rollOutFile(static::PARTNER, $type));
    $this->logSection('memory', sprintf('usage %.3f MB, peak usage %.3f MB', memory_get_usage(true)/1048576, memory_get_peak_usage(true)/1048576));
  }

  protected function processLots(&$ids)
  {
    foreach (array_chunk($ids, 50) as $ids_chunk) {
      $lots = Doctrine::getTable('Lot')
              ->createQuery('l')
              ->joinParams()
              ->whereIn('l.id', $ids_chunk)
              ->execute();

      Lot::fillLotsWithParents($lots);
      Lot::fillLotsWithPhotos($lots);

      foreach ($lots as $lot) {
        $this->_counters['current']++;
        try {
          $this->validateLot($lot);
          $this->writeDocumentLotFromArray($this->getDataArray($lot));
          $this->_counters['good']++;
          $this->logSection($lot->id, sprintf('processed %s of %s (%s%%)', $this->_counters['current'], $this->_counters['total'], round($this->_counters['current']/$this->_counters['total']*100, 2)));
        }
        catch (Exception $e){
          $this->_counters['bad']++;
          $this->logSection($lot->id, $e->getMessage(), null, 'ERROR');
        }
      }

      //освобождаем ресурсы после окончания цикла
      //т.к. если какой-то из лотов в массиве является так же и парентом какого-либо лота, то он пропадает
      foreach ($lots as &$lot) { $lot->free(true); }
      $lots->free(true);

      $this->_xml_writer->flush();
      gc_collect_cycles();
    }
  }

  protected function getDataMethod($type)
  {
    return sprintf('getDataArray%s', sfInflector::camelize($this->getSuptype($type)));
  }

  protected function getAllowedSuptypes()
  {
    return array_keys($this->_suptypes);
  }

  protected function getAllowedTypes()
  {
    $t = new RecursiveIteratorIterator(new RecursiveArrayIterator(array_values($this->_suptypes)));
    return iterator_to_array($t, false);
  }

  protected function getSuptype($type)
  {
    $s = array_filter($this->_suptypes, function($v) use ($type) { return in_array($type, (array) $v); });
    if (empty($s)) {
      throw new Exception(sprintf('cant\'t match suptype for "%s"', $type));
    }

    return current(array_keys($s));
  }


  private function getList($type = null)
  {
    if (empty($type)) {
      $types = $this->getAllowedTypes();
    }
    elseif (in_array($type, $this->getAllowedSuptypes())) {
      $types = (array) $this->_suptypes[$type];
    }
    elseif (in_array($type, $this->getAllowedTypes())) {
      $types = array($type);
    }

    $query = Doctrine::getTable('Lot')
            ->createQuery('l')
            ->select('l.id')
            ->active()
            ->andWhere('l.hide_price = false')
            ->andWhere('l.price_all_from > ?', 0)
            ->andWhere('l.exportable = true')
            ->andWhereIn('l.type', $types)
            ->orderBy('l.priority DESC, l.type ASC, l.id DESC');

    if (in_array('penthouse', $types) && !in_array('eliteflat', $types)) {
      $query->andWhere('l.is_penthouse = true');
    }
    if (array_intersect($types, array('cottage', 'outoftown'))) {//я не знаю зачем это
      $query->andWhere('CASE WHEN l.type IN ("cottage", "outoftown") THEN l.has_children != 1 ELSE true END');
    }
    if ($this->name == 'cian' && $this->_current_type == 'country') {
      $query->joinParams()->groupBy('l.id')
              ->having('SUM(p.param_id = ? AND p.value = ?)', array(81, 'да'));//export_to_cian
    }

    $query->setHydrationMode(Doctrine::HYDRATE_SINGLE_SCALAR);

    return $query->execute();
  }

  private function writeDocumentLotFromArray(array $data)
  {
    foreach ($data as $tag => $content) {
      if (is_array($content)
              && !count(array_filter(array_keys($content), 'is_string'))//is indexed array
              && !count(array_filter(array_values($content), 'is_array'))) {//containing string
        foreach ($content as $c) {
          $this->writeDocumentLotFromArray(array($tag => $c));
        }
        continue;
      }


      $this->_xml_writer->startElement($tag);
      if (is_array($content) && isset($content['attributes'])) {
        foreach ($content['attributes'] as $name => $value) {
          $this->_xml_writer->writeAttribute($name, $value);
        }
      }
      elseif (!is_array($content) || !isset($content['data'])) {
        $content = array('data' => $content);
      }

      if (isset($content['data'])) {
        if (is_array($content['data'])) {
          if (!count(array_filter(array_keys($content['data']), 'is_string'))) { //is indexed array
            foreach ($content['data'] as $c) {
              $this->writeDocumentLotFromArray($c);
            }
          }
          else {
            $this->writeDocumentLotFromArray($content['data']);
          }
        }
        elseif (preg_match('/[<>&]/', $content['data'])) {
          $this->_xml_writer->writeCdata($content['data']);
        }
        else {
          $this->_xml_writer->text($content['data']);
        }
      }
      $this->_xml_writer->endElement();
    }

    unset($data);
  }


  public static function cleanText($text, $limit = null)
  {
    $text = preg_replace('/(?:\<br\/*\>){1,2}|(?:\<\/p\>\s*\<p\>)/ui', PHP_EOL, $text);
    $text = strip_tags($text);
    $text = str_replace('&nbsp;', ' ', $text);
    $text = str_replace('&apos;', "'", $text);
    $text = preg_replace('/ +/u', ' ', $text);
    $text = trim($text);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');

    if ($limit && mb_strlen($text) > $limit) {
      preg_match('/^.{'.($limit - 50).'}[^.!;?]*(?:[.!;?]|$)/iusm', $text, $matches);
      if (empty($matches[0]) || mb_strlen($matches[0]) > $limit) {
        $text = mb_substr($text, 0, $limit - 3).'...';
      }
      else {
        $text = $matches[0];
      }
    }

    return $text;
  }

  public static function calculateTheDistance($φA, $λA, $φB, $λB)
  {
    // перевести координаты в радианы
    $lat1 = $φA * M_PI / 180;
    $lat2 = $φB * M_PI / 180;
    $long1 = $λA * M_PI / 180;
    $long2 = $λB * M_PI / 180;

    // косинусы и синусы широт и разницы долгот
    $cl1 = cos($lat1);
    $cl2 = cos($lat2);
    $sl1 = sin($lat1);
    $sl2 = sin($lat2);
    $delta = $long2 - $long1;
    $cdelta = cos($delta);
    $sdelta = sin($delta);

    // вычисления длины большого круга
    $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
    $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;

    //
    $ad = atan2($y, $x);
    $dist = $ad * 6371;

    return $dist;
  }

  public static function findSimilarString($v, array $dictionary)
  {
    $v = mb_strtolower($v);
    //если в строке есть числовой индекс в конце,
    //то сохраняем его в переменную, а из строки убираем
    preg_match('/(\d+)$/', $v, $matches);
    if (!empty($matches[1])) {
      $number1 = $matches[1];
      $v = preg_replace('/[\s\-]*'.$number1.'/', '', $v);
    }
    else {
      $number1 =  null;
    }

    foreach ($dictionary as $i => $d) {
      //отделяем числовой индекс от строки, если он есть
      preg_match('/(\d+)$/', $d, $matches);
      if (!empty($matches[1])) {
        $number2 = $matches[1];
        $d = preg_replace('/[\s\-]*'.$number2.'/', '', $d);
      }
      else {
        $number2 =  null;
      }

      //если числовые индексы совпадают и разница в строках не превышает 2 символа
      if ($number1 === $number2 && levenshtein($v, mb_strtolower($d)) <= 2) return $dictionary[$i];
    }

    return null;
  }

  public static function isValueMeansNo($v)
  {
    if (is_array($v)) $v = implode(' ', $v);

    return in_array(mb_strtolower($v), array('нет', '--', '-'))
            || mb_stripos($v, 'отсутст') !== false
            || preg_match('/(?:^|\s)без(?:$|\s)/iu', $v);
  }


  protected static function getLotUrl($lot)
  {
    return sprintf('%s/offers/%s/%s/', self::ORG_SITE, $lot->type, $lot->id);
  }

  protected static function getLotCreationDate($lot)
  {
    $date = strtotime($lot->created_at) > 0 ? strtotime($lot->created_at) : strtotime('yesterday');
    return date(static::DATE_FORMAT, $date);
  }

  protected static function getLotUpdationDate($lot)
  {
    $date = strtotime($lot->updated_at) > 0 ? strtotime($lot->updated_at) : strtotime('yesterday');
    return date(static::DATE_FORMAT, $date);
  }

  protected static function getLotOperationType($lot)
  {
    return $lot->is_rent_type ? 'аренда' : 'продажа';
  }

  protected static function getLotPrice($lot)
  {
    if (!empty($lot->price_all_from) && $lot->price_all_from > 0)
      return (float) $lot->price_all_from;

    if ($lot->is_land_type) {
      if (!empty($lot->price_from) && self::getLotAreaLand($lot) && $lot->price_from > 0)
        return round($lot->price_from * self::getLotAreaLand($lot));
    }
    else {
      if (!empty($lot->price_from) && self::getLotAreaTotal($lot) && $lot->price_from > 0)
        return round($lot->price_from * self::getLotAreaTotal($lot));
    }

    if (!empty($lot->price_all_to) && $lot->price_all_to > 0)
      return (float) $lot->price_all_to;

    if ($lot->is_land_type) {
      if (!empty($lot->price_to) && self::getLotAreaLand($lot) && $lot->price_to > 0)
        return round($lot->price_to * self::getLotAreaLand($lot));
    }
    else {
      if (!empty($lot->price_to) && self::getLotAreaTotal($lot) && $lot->price_to > 0)
        return round($lot->price_to * self::getLotAreaTotal($lot));
    }

    return null;
  }

  protected static function getLotPriceMeter($lot)
  {
    if (!empty($lot->price_from) && $lot->price_from > 0)
      return (float) $lot->price_from;

    if ($lot->is_land_type) {
      if (!empty($lot->price_all_from) && self::getLotAreaLand($lot) && $lot->price_all_from > 0)
        return round($lot->price_all_from / self::getLotAreaLand($lot));
    }
    else {
      if (!empty($lot->price_all_from) && self::getLotAreaTotal($lot) && $lot->price_all_from > 0)
        return round($lot->price_all_from / self::getLotAreaTotal($lot));
    }

    if (!empty($lot->price_to) && $lot->price_to > 0)
      return (float) $lot->price_to;

    if ($lot->is_land_type) {
      if (!empty($lot->price_all_to) && self::getLotAreaLand($lot) && $lot->price_all_to > 0)
        return round($lot->price_all_to / self::getLotAreaLand($lot));
    }
    else {
      if (!empty($lot->price_all_to) && self::getLotAreaTotal($lot) && $lot->price_all_to > 0)
        return round($lot->price_all_to / self::getLotAreaTotal($lot));
    }

    return null;
  }

  protected static function getLotPriceArray($lot)
  {
    $data = array();
    if (!empty($lot->price_all_from) && $lot->price_all_from > 0) {
      $data[] = (float) $lot->price_all_from;
    }
    elseif ($lot->is_land_type && !empty($lot->price_from) && self::getLotAreaLand($lot) && $lot->price_from > 0) {
      $data[] = round($lot->price_from * self::getLotAreaLand($lot));
    }
    elseif (!empty($lot->price_from) && self::getLotAreaTotal($lot) && $lot->price_from > 0) {
      $data[] = round($lot->price_from * self::getLotAreaTotal($lot));
    }

    if (!empty($lot->price_all_to) && $lot->price_all_to > 0) {
      $data[] = (float) $lot->price_all_to;
    }
    elseif ($lot->is_land_type && !empty($lot->price_to) && self::getLotAreaLand($lot) && $lot->price_to > 0) {
      $data[] = round($lot->price_to * self::getLotAreaLand($lot));
    }
    elseif (!empty($lot->price_to) && self::getLotAreaTotal($lot) && $lot->price_to > 0) {
      $data[] = round($lot->price_to * self::getLotAreaTotal($lot));
    }

    return !empty($data) ? array_unique($data, SORT_NUMERIC) : null;
  }

  protected static function getLotPriceMeterArray($lot)
  {
    $data = array();
    if (!empty($lot->price_from) && $lot->price_from > 0) {
      $data[] = (float) $lot->price_from;
    }
    elseif ($lot->is_land_type && !empty($lot->price_all_from) && self::getLotAreaLand($lot) && $lot->price_all_from > 0) {
      $data[] = round($lot->price_all_from / self::getLotAreaLand($lot));
    }
    elseif (!empty($lot->price_all_from) && self::getLotAreaTotal($lot) && $lot->price_all_from > 0) {
      $data[] = round($lot->price_all_from / self::getLotAreaTotal($lot));
    }

    if (!empty($lot->price_to) && $lot->price_to > 0) {
      $data[] = (float) $lot->price_to;
    }
    elseif ($lot->is_land_type && !empty($lot->price_all_to) && self::getLotAreaLand($lot) && $lot->price_all_to > 0) {
      $data[] = round($lot->price_all_to / self::getLotAreaLand($lot));
    }
    elseif (!empty($lot->price_all_to) && self::getLotAreaTotal($lot) && $lot->price_all_to > 0) {
      $data[] = round($lot->price_all_to / self::getLotAreaTotal($lot));
    }

    return !empty($data) ? array_unique($data, SORT_NUMERIC) : null;
  }

  protected static function getLotAreaTotal($lot)
  {
    $v = self::getLotAreaTotalArray($lot);
    return is_array($v) ? $v[0] : $v;
  }

  protected static function getLotAreaLand($lot)
  {
    $v = self::getLotAreaLandArray($lot);
    return is_array($v) ? $v[0] : $v;
  }

  protected static function getLotAreaLiving($lot)
  {
    if (!empty($lot->params['about_floorspace'])) {
      $v = $lot->params['about_floorspace'];
    }
    elseif (!empty($lot->params['space'])) {
      $v = $lot->params['space'];
    }

    if (isset($v)) {
      $v = str_replace(',', '.', $v);
      $v = trim(preg_replace('/кв\.м\.*/iu', ' ', $v));

      if (is_numeric($v) && $v > 0) return (float) $v;
    }

    return null;
  }

  protected static function getLotAreaKitchen($lot)
  {
    if (!empty($lot->params['kitchen_area'])) {
      $v = $lot->params['kitchen_area'];
      $v = str_replace(',', '.', $v);
      $v = trim(preg_replace('/кв\.м\.*/iu', ' ', $v));

      if (is_numeric($v) && $v > 0) return (float) $v;
    }

    return null;
  }

  protected static function getLotAreaTotalArray($lot)
  {
    $data = array();
    if (!empty($lot->area_from) && $lot->area_from > 0) {
      $data[] = (float) $lot->area_from;
    }
    if (!empty($lot->area_to) && $lot->area_to > 0) {
      $data[] = (float) $lot->area_to;
    }

    return !empty($data) ? array_unique($data, SORT_NUMERIC) : null;
  }

  protected static function getLotAreaLandArray($lot)
  {
    if (!empty($lot->params['spaceplot'])) {
      $v = $lot->params['spaceplot'];

      if (is_numeric(str_replace(',', '.', $v))) return array((float) $v);

      $data = array();
      foreach (preg_split('/(?:\s*[-–]\s*|\s+до\s+|,\s+|\/)/iu', $v) as $vv) {
        $vv = trim(preg_replace('/сот(?:ка|ки|ок)*\.*/iu', ' ', $vv));
        $vv = trim(preg_replace('/^от\s+/iu', ' ', $vv));
        $vv = str_replace(',', '.', $vv);
        if (preg_match('/[\d\s]Га\.*/iu', $vv)) {
          $vv = trim(preg_replace('/Га\.*/iu', ' ', $vv));
          if (is_numeric($vv)) $vv = (float) $vv * 100;//convert to сотки
        }
        if (preg_match('/[\d\s]кв\.м\.*/iu', $vv)) {
          $vv = trim(preg_replace('/кв\.м\.*/iu', ' ', $vv));
          if (is_numeric($vv)) $vv = (float) $vv / 100;//convert to сотки
        }

        if (is_numeric($vv)) $data[] = (float) $vv;
      }

      if (!empty($data)) return array_unique($data, SORT_NUMERIC);
    }

    return null;
  }

  protected static function getLotPhone($lot)
  {
    switch ($lot->type) {
      case 'eliteflat': return sprintf(static::PHONE_FORMAT, 956, 77, 99);
      case 'penthouse': return sprintf(static::PHONE_FORMAT, 956, 77, 99);
      case 'flatrent':  return sprintf(static::PHONE_FORMAT, 956, 77, 99);
      case 'elitenew':  return sprintf(static::PHONE_FORMAT, 956, 77, 99);
      case 'outoftown': return sprintf(static::PHONE_FORMAT, 956, 60, 56);
      case 'cottage':   return sprintf(static::PHONE_FORMAT, 956, 60, 56);
      case 'comrent':   return sprintf(static::PHONE_FORMAT, 956, 37, 97);
      case 'comsell':   return sprintf(static::PHONE_FORMAT, 956, 37, 97);
    }
  }

  protected static function getLotRegion($lot)
  {
    if (isset($lot->address['region'], $lot->address['city']) && $lot->address['region'] == 'Московская область' && $lot->address['city'] == 'Москва') {
      return 'Москва';
    }
    elseif (!empty($lot->address['region']) && in_array($lot->address['region'], array('Москва', 'Московская область'))) {
      return $lot->address['region'];
    }
    elseif (!empty($lot->address['city'])) {
      if ($lot->address['city'] == 'Москва') return 'Москва';
      else return 'Московская область';
    }
    elseif ($lot->is_country_type) {
      return 'Московская область';
    }
    else {
      return 'Москва';
    }
  }

  protected static function getLotRegionDistrict($lot)
  {
    if (!empty($lot->params['district_of'])) {
      return $lot->params['district_of'];
    }
    elseif (!empty($lot->address['district'])) {
      return $lot->address['district'];
    }

    return null;
  }

  protected static function getLotCity($lot)
  {
    if (self::getLotRegion($lot) == 'Москва') {
      return 'Москва';
    }
    elseif (!empty($lot->params['locality'])) {
      return $lot->params['locality'];
    }
    elseif (!empty($lot->address['city'])) {
      return $lot->address['city'];
    }

    return null;
  }

  protected static function getLotDistrict($lot)
  {
    if (empty($lot->district_id)) return null;
    $districts = sfConfig::get('app_districts', array());
    return isset($districts[$lot->district_id]) ? $districts[$lot->district_id] : null;
  }

  protected static function getLotCityArea($lot)
  {
    if (in_array($lot->district_id, array_merge(range(1, 19), array(31)))) {
      return 'Центральный АО';
    }
    elseif ($lot->district_id == 30) {
      return 'Западный АО';
    }
    else {
      return exportBaseTask::getLotDistrict($lot);
    }

    return null;
  }

  protected static function getLotCityDistrict($lot)
  {
    if (in_array($lot->district_id, array_merge(range(1, 19), array(30, 31)))) {
      return exportBaseTask::getLotDistrict($lot);
    }

    return null;
  }

  protected static function getLotEstate($lot)
  {
    if (!empty($lot->params['estate'])) {
      $v = $lot->params['estate'];
      $v = preg_replace('/^(?:Ж\/*К\s)*(?:"|«)*|(?:"|»)(?:$|[^\wа-я])/iu', '', $v);
      $v = preg_replace('/\s\([a-z\s]+\)$/i', '', $v);
      return trim($v);
    }
    preg_match('/(?:^|\s)Ж\/*К\s*(?:«|")*(.+?)(?:[,."»\(]|$)/siu', $lot->name, $matches);
    if (!empty($matches[1])) {
      return trim($matches[1]);
    }

    return null;
  }

  protected static function getLotDistanceMkad($lot)
  {
    if (!empty($lot->params['distance_mkad'])) {
      $v = $lot->params['distance_mkad'];
      $v = str_replace(',', '.', $v);
      $v = trim(preg_replace('/км\.*/iu', ' ', $v));

      if (is_numeric($v) && $v > 0) return $v;
    }

    return null;
  }

  protected static function getLotMetroDistanceWalk($lot)
  {
    if (!empty($lot->params['the_distance_from_the_metro_in_minutes'])) {
      $v = $lot->params['the_distance_from_the_metro_in_minutes'];
    }
    elseif (!empty($lot->params['distance_metro'])) {
      $v = $lot->params['distance_metro'];
    }
    else {
      return null;
    }

    if (preg_match('/пешком|(?:^|[.\s])п\./iu', $v) || is_numeric($v)) {
      preg_match('/^(\d+)/', $v, $matches);
      if (!empty($matches[1])) return $matches[1];
    }

    return null;
  }

  protected static function getLotMetroDistanceTransport($lot)
  {
    if (!empty($lot->params['the_distance_from_the_metro_in_minutes'])) {
      $v = $lot->params['the_distance_from_the_metro_in_minutes'];
    }
    elseif (!empty($lot->params['distance_metro'])) {
      $v = $lot->params['distance_metro'];
    }
    else {
      return null;
    }

    if (preg_match('/транспорт|(?:^|[.\s])тр\./iu', $v)) {
      preg_match('/^(\d+)/', $v, $matches);
      if (!empty($matches[1])) return $matches[1];
    }

    return null;
  }

  #need return to protected after deploy
  public static function getLotDescription($lot, $limit = null)
  {
    $limit = is_numeric($limit) ? $limit - 8 - mb_strlen($lot->id) : null;
    $text = !empty($lot->anons) ? $lot->anons : $lot->description;
    return self::cleanText($text, $limit) . sprintf(' [Лот #%d]', $lot->id);
  }

  protected static function getLotBuiltYear($lot)
  {
    if (!empty($lot->params['year'])) {
      preg_match('/(1[7-9]\d{2}|20[0-1]\d)/', $lot->params['year'], $matches);
      if (!empty($matches[1])) return $matches[1];
    }

    return null;
  }

  protected static function getLotBuiltQuarter($lot)
  {
    if (!empty($lot->params['year'])) {
      preg_match('/([1-4]|I{1,3}V*)(?:-й)*\sкв(?:\.|артал)/iu', $lot->params['year'], $matches);
      if (!empty($matches[1])) {
        if (!is_numeric($matches[1])) {
          $matches[1] = str_replace(array('IV', 'III', 'II', 'I'), array(4, 3, 2, 1), $matches[1]);
        }

        if (is_numeric($matches[1])) return $matches[1];
      }
    }

    return null;
  }

  protected static function getLotNbRooms($lot)
  {
    if (!empty($lot->params['rooms']) && ctype_digit($lot->params['rooms']) && $lot->params['rooms'] > 0) {
      return (int) $lot->params['rooms'];
    }

    return null;
  }

  protected static function getLotNbBedrooms($lot)
  {
    if (!empty($lot->params['about_count_bedrooms']) && ctype_digit($lot->params['about_count_bedrooms']) && $lot->params['about_count_bedrooms'] > 0) {
      return (int) $lot->params['about_count_bedrooms'];
    }

    return null;
  }

  protected static function getLotNbBalconies($lot)
  {
    if (!empty($lot->params['balconies'])) {
      $v = $lot->params['balconies'];
      if (self::isValueMeansNo($v))   return 0;
      if (ctype_digit($v) && $v > 0)  return (int) $v;
      return true;//значит есть, но не понятно сколько
    }
    if (!empty($lot->params['about_balcony'])) {
      if (in_array('балкон', $lot->params['about_balcony'])) return true;
    }

    return null;
  }

  protected static function getLotNbLoggias($lot)
  {
    if (!empty($lot->params['number_of_loggias'])) {
      $v = $lot->params['number_of_loggias'];
      if (self::isValueMeansNo($v))   return 0;
      if (ctype_digit($v) && $v > 0)  return (int) $v;
      return true;//значит есть, но не понятно сколько
    }
    if (!empty($lot->params['about_balcony'])) {
      if (in_array('лоджия', $lot->params['about_balcony'])) return true;
    }

    return null;
  }

  protected static function getLotNbBathrooms($lot)
  {
    $s = self::getLotNbBathroomsSeparate($lot);
    $c = self::getLotNbBathroomsCombined($lot);

    if ($s > 0 || $c > 0) return (int) $s + (int) $c;

    if (!empty($lot->params['about_toilet'])) {
      preg_match('/^(\d+)(?:[\s\w+]|,\s*\D|$)/iu', $lot->params['about_toilet'], $matches);
      if (!empty($matches[1])) return (int) $matches[1];
    }

    return null;
  }

  protected static function getLotNbBathroomsSeparate($lot)
  {
    if (!empty($lot->params['number_of_separate_toilets'])) {
      $v = $lot->params['number_of_separate_toilets'];
      if (ctype_digit($v) && $v > 0) return (int) $v;
    }

    if (!empty($lot->params['about_toilet'])) {
      $v = $lot->params['about_toilet'];
      preg_match('/^(\d+)\s*разд/iu', $v, $matches);
      if (!empty($matches[1]))                    return (int) $matches[1];
      if (mb_stripos($v, 'раздельный') !== false) return 1;
    }

    return null;
  }

  protected static function getLotNbBathroomsCombined($lot)
  {
    if (!empty($lot->params['number_of_bathrooms_combined'])) {
      $v = $lot->params['number_of_bathrooms_combined'];
      if (ctype_digit($v) && $v > 0) return (int) $v;
    }

    if (!empty($lot->params['about_toilet'])) {
      $v = $lot->params['about_toilet'];
      preg_match('/^(\d+)\s*совм/iu', $v, $matches);
      if (!empty($matches[1]))                      return (int) $matches[1];
      if (mb_stripos($v, 'совмещенный') !== false)  return 1;
    }

    return null;
  }

  protected static function getLotNbFloors($lot)
  {
    if (!empty($lot->params['floors']) && ctype_digit($lot->params['floors']) && $lot->params['floors'] > 0) {
      return (int) $lot->params['floors'];
    }

    return null;
  }

  protected static function getLotFloor($lot)
  {
    if (!empty($lot->params['about_floor']) && ctype_digit($lot->params['about_floor'])) {
      return (int) $lot->params['about_floor'];
    }
    if (!empty($lot->params['floor']) && ctype_digit($lot->params['floor'])) {
      return (int) $lot->params['floor'];
    }

    return null;
  }

  protected static function getLotCeilingHeight($lot)
  {
    if (!empty($lot->params['roomheight'])) {
     $v = $lot->params['roomheight'];
     $v = str_replace(',', '.', $v);

     if (is_numeric($v) && $v > 0) return $v;
    }

    return null;
  }

  protected static function getLotIsMortgage($lot)
  {
    if (!empty($lot->params['the_possibility_of_a_mortgage'])) {
      return !self::isValueMeansNo($lot->params['the_possibility_of_a_mortgage']);
    }

    return null;
  }

  protected static function getLotIsElevator($lot)
  {
    if (!empty($lot->params['lift'])) {
      $v = $lot->params['lift'];
    }
    elseif (!empty($lot->params['passenger_elevators'])) {
      $v = $lot->params['passenger_elevators'];
    }
    elseif (!empty($lot->params['cargo_elevators'])) {
      $v = $lot->params['cargo_elevators'];
    }
    if (isset($v)) {
      return !self::isValueMeansNo($v);
    }

    return null;
  }

  protected static function getLotIsTelephone($lot)
  {
    $v = '';
    if (!empty($lot->params['service_tele'])) {
      $v .= $lot->params['service_tele'];
    }
    if (!empty($lot->params['other'])) {
      $v .= !empty($v) ? ' ' : '';
      $v .= $lot->params['other'];
    }
    if (!empty($lot->params['service_inner'])) {
      $v .= !empty($v) ? ' ' : '';
      $v .= $lot->params['service_inner'];
    }

    if (!empty($v) && mb_stripos($v, 'телефон') !== false) return true;

    return null;
  }

  protected static function getLotIsInternet($lot)
  {
    $v = '';
    if (!empty($lot->params['service_tele'])) {
      $v .= $lot->params['service_tele'];
    }
    if (!empty($lot->params['other'])) {
      $v .= !empty($v) ? ' ' : '';
      $v .= $lot->params['other'];
    }
    if (!empty($lot->params['service_inner'])) {
      $v .= !empty($v) ? ' ' : '';
      $v .= $lot->params['service_inner'];
    }
    if (!empty($lot->params['infra_additional'])) {
      $v .= !empty($v) ? ' ' : '';
      $v .= $lot->params['infra_additional'];
    }

    if (!empty($v) && mb_stripos($v, 'интернет') !== false) return true;

    return null;
  }

  protected static function getLotIsParking($lot)
  {
    if (!empty($lot->params['infra_parking'])) {
      return !self::isValueMeansNo($lot->params['infra_parking']);
    }

    return null;
  }

  protected static function getLotIsWater($lot)
  {
    if (!empty($lot->params['service_water'])) {
      return !self::isValueMeansNo($lot->params['service_water']);
    }

    return null;
  }

  protected static function getLotIsSewerage($lot)
  {
    if (!empty($lot->params['service_drainage'])) {
      return !self::isValueMeansNo($lot->params['service_drainage']);
    }

    return null;
  }

  protected static function getLotIsElectricity($lot)
  {
    if (!empty($lot->params['service_electricity'])) {
      return !self::isValueMeansNo($lot->params['service_electricity']);
    }

    return null;
  }

  protected static function getLotIsGas($lot)
  {
    if (!empty($lot->params['service_gas'])) {
      return !self::isValueMeansNo($lot->params['service_gas']);
    }

    return null;
  }

  protected static function getLotNearestCity($lot, array $dictionary)
  {
    if (empty($dictionary)) {
      throw new Exception('cities dictionary is empty');
    }

    $current  = null;
    $lowest   = null;
    $nearest  = null;

    foreach ($dictionary as $city => $coords) {
      $current = self::calculateTheDistance($lot->lat, $lot->lng, $coords['lat'], $coords['lng']);
      if (is_null($lowest) || $current <= $lowest) {
        $lowest = $current;
        $nearest = $city;
      }
    }

    return $nearest;
  }


  private static function generateFileName($partner, $type = null)
  {
    $path = sfConfig::get('sf_web_dir');
    foreach (array('texport', $partner) as $dir) {
      $path .= DIRECTORY_SEPARATOR . $dir;

      if (!is_dir($path)) {
        mkdir($path, 0775);
      }
      elseif (!is_writable($path)) {
        throw new Exception(sprintf('Directory witing permission denied: %s', $path));
      }
    }

    $filename = empty($type) ? 'export' : $type;

    return sprintf('%s/%s.xml.tmp', $path, $filename);
  }

  private static function rollOutFile($partner, $type = null)
  {
    $filename = self::generateFileName($partner, $type);
    $savename = preg_replace('/\.[a-z]+$/i', '', $filename);

    if (!rename($filename, $savename)) {
      throw new Exception(sprintf('Unable to save file as %s', $savename));
    }

    return $savename;
  }


  abstract protected function writeDocumentStart();

  abstract protected function writeDocumentFinish();

  //abstract protected function validateLot(Lot &$lot);

  //abstract protected function getDataArray(Lot $lot);
}
