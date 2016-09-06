<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of allianceXmlTask
 *
 * @author dimonze
 */
class tbaXmlTask extends sfBaseTask
{
  protected
    $_org_name =  'Contact Real Estate',
    $_org_email = 'kre@kre.ru',
    $_domain    = 'http://www.kre.ru',
    $_partner   = 'tba',
    $_counters  = array(),
    $_metros  = array(),
    $_location  = array(),
    $_highways  = array(),
    $_estates  = array(),
    $_settlements = array(),
    $_TbaCity  = array(),

    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse'),
      'newbuilds' => 'elitenew',
      'country'   => array('outoftown'),
    ),

    $_map = array(
      'country' => array(
        'Участок'            => 'участок',
        'Таунхаус'           => 'таунхаус',
        'Квартира'           => 'квартира',
        'Коттедж'            => 'дом',
      ),
    ),
    $_phones = array(
      'eliteflat' => '(495)956-7799',
      'penthouse' => '(495)956-7799',
      'flatrent'  => '(495)956-7799',
      'elitenew'  => '(495)956-7799',
      'outoftown' => '(495)956-6056',
      'cottage'   => '(495)956-6056',
      'comrent'   => '(495)956-3797',
      'comsell'   => '(495)956-3797',
    );

  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('type',  sfCommandArgument::OPTIONAL, 'Data type', array('flats', 'newbuilds', 'country')),
      new sfCommandArgument('page',  sfCommandArgument::OPTIONAL, 'Page (for multi-file output)',  1),
      new sfCommandArgument('limit', sfCommandArgument::OPTIONAL, 'Limit (for multi-file output)', 0),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = '';
    $this->name             = 'tbaXml';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [tbaXml|INFO] task does things.
Call it with:

  [php symfony tbaXml|INFO]
EOF;

  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->_args = $arguments;
    ini_set('memory_limit', '2G');
    ini_set('max_execution_time', 0);
    gc_enable();
    $this->logSection('GC is', gc_enabled() ? 'enabled' : 'disabled');
    sleep(1);
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    sfContext::createInstance(sfProjectConfiguration::getApplicationConfiguration('frontend', 'prod', true));
    $this->connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    $this->getExternalArrays();
    $types = is_array($arguments['type']) ? $arguments['type'] : array($arguments['type']);
    foreach($types as $type) {
      $method = sprintf('do%s', ucfirst($type));
      $i = array('total' => 0, 'good' => 0, 'bad' => 0, 'current' => 0);
      if(is_callable(array($this, $method))) {
        $ids = $this->getList($this->_suptypes[$type], $arguments['page'], $arguments['limit']);
        $i['total'] = count($ids);
        $filename = Tools::getFileNameForXmlFile($this->_partner, $type, $arguments['page']);
        $this->logSection('Prepare', sprintf('file %s', $filename));
        $this->getExternalArrays2();

        $w = new XMLWriter();
        $w->openUri($filename);
        $w->setIndentString(str_repeat(" ", 2));
        $w->setIndent(true);
        $w->startDocument('1.0','utf-8');
        $w->startElement('offers');
          $w->startAttribute('date');
            $w->text(date("Y-m-d\TH:i:s").'+04:00');
          $w->endAttribute();//date
          $w->writeComment(sprintf(' Generated at %s, contains approx %s lots ', date('Y-m-d H:i:s'), $i['total']));
          $this->logSection('>>>', sprintf('I will use "%s" method', $method));

          foreach($ids as $id) {
            $i['current']++;
            $lot = Doctrine::getTable('Lot')->find($id);
            try {
              if(!$this->translateObjectAndPart($lot, 'object')) {
                  $this->logSection($id, sprintf('bad  objecttype %', null), null, 'ERROR');
                  $i['bad']++;
                  continue;
              }
              if($lot->type == 'comsell' && (int)$lot->price_all_from > 0 && (int)$lot->price_all_to > 0){
                $this->logSection($id, sprintf('bad price_all_to %', null), null, 'ERROR');
                  $i['bad']++;
                  continue;
              }
              if($lot->type == 'comsell' && ((int)$lot->area_from > 0 && (int)$lot->area_to > 0)){
                $this->logSection($id, sprintf('bad area_to %', null), null, 'ERROR');
                  $i['bad']++;
                  continue;
              }
              if(!$lot->lng || !$lot->lng)
              {
                $this->logSection($id, sprintf('bad coords %', null), null, 'ERROR');
                  $i['bad']++;
                  continue;
              }
              $w->startElement('offer');
                $w->startAttribute('internal-id');
                  $w->text($lot->id);
                $w->endAttribute();//date
                // !!! Rock-n-roll begins here!
                $this->$method($w, $lot);
              $w->endElement();//offer
              $i['good']++;
              $this->logSection($id, sprintf('added. %s of %s (%s%%)',
                $i['good'],
                $i['total'],
                round($i['good']/$i['total']*100, 2)
              ));
              // echo 'Before: ' . memory_get_usage() /1024 /1024 . ' Mb' . PHP_EOL;
            }
            catch (Exception $e){
              $this->logSection($id, sprintf('bad %', $e->getMessage()), null, 'ERROR');
              $i['bad']++;
            }
            $lot->free(true);
            $lot = null;
            unset($lot);
            $w->flush();
            unset($ids[$id]);
            gc_collect_cycles();
            // echo 'After: ' . memory_get_usage() /1024 /1024 . ' Mb' . PHP_EOL;
          }
          $ids = null;
        $w->endElement();//offers
        $w->endDocument();
        $w->flush();
        unset($w);
        $this->logSection('result', sprintf('%d total, %d added and %d excluded', $i['total'], $i['good'], $i['bad']));
        $this->logSection('done', 'and write to file');
        Tools::rollOutXmlFile($this->_partner, $type, $arguments['page']);
      }
      else {
        $this->logSection('not run', sprintf("method %s() does not exist", $method), null, 'ERROR');
      }
    }
  }

  protected function doFlats(XMLWriter $w, $lot)
  {
    $w->writeElement('type', 'продажа');
    $w->writeElement('category', $this->translateObjectAndPart($lot, 'object'));
    $w->writeElement('url', sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
    $w->writeElement('creation-date', $this->getCreationDate($lot));
    $w->writeElement('last-update-date', $this->getLastUpdated($lot));
    $w->startElement('location');
      if ($lot->type == 'outoftown') {
        $w->writeElement('region', 'Московская область');
      }
      else {
        $w->writeElement('region', 'Москва');
      }
      $w->writeElement('sub-locality-name', $this->checkJournal($this->translateDistrict($lot), 'location'));
      $w->writeElement('sub-locality-original-name', $this->translateDistrict($lot));
      if(!empty($lot->params['estate']) && $lot->params['estate'] > 1){
        $w->writeElement('building-original-name', $this->checkJournal($this->getAttr($lot->params, 'estate'), 'estates'));
      }
      $w->writeElement('address', $lot->address['street']);
      $w->startElement('metro');
        $w->writeAttribute('name', $this->checkJournal($this->getMetro($lot->metro_id), 'metros'));
        $w->writeAttribute('distance_walk', (int)$this->getAttr($lot->params, 'distance_metro'));
      $w->endElement(); //metro
      $w->writeElement('house_humber', $lot->address['house']);
      $w->writeElement('Latitude', $lot->lat);
      $w->writeElement('Longitude', $lot->lng);
      $w->writeElement('house_building', $this->getAttr($lot->params, 'building'));
    $w->endElement(); //location
    $w->startElement('price');
    $w->writeAttribute('currency', preg_replace("/RUR/", "RUB", $lot->currency));
    if((int)$lot->price_all_from > '0'){
        $w->writeAttribute('value', (int)$lot->price_all_from);
      }
     else if((int)$lot->price_all_to > '0'){
        $w->writeAttribute('value', (int)$lot->price_all_to);
      }
    $w->endElement(); //price
    $w->writeElement('Price_hidden', '0');
    $this->addPhotoes($w, $lot);
    if($lot->area_from > 0){
      $w->writeElement('Area', $lot->area_from);
    }
    $w->writeElement('rooms', (int)$this->getAttr($lot->params, 'rooms'));
    $w->writeElement('Floor', (int)$this->getAttr($lot->params, 'about_floor'));
    if((int)$this->getAttr($lot->params, 'floors') > 0 && (int)$this->getAttr($lot->params, 'floors') > (int)$this->getAttr($lot->params, 'about_floor')){
      $w->writeElement('floors-total', (int)$this->getAttr($lot->params, 'floors'));
    }
    $w->writeElement('Parking', $this->getAttr($lot->params, 'parking'));
//    $w->startElement('preview');
//      $w->writeCdata(strip_tags($lot->anons));
//    $w->endElement(); //preview
    $w->startElement('description');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement(); //description
  }

  protected function doNewbuilds(XMLWriter $w, $lot)
  {
    $w->writeElement('type', 'продажа');
    $w->writeElement('category', $this->translateObjectAndPart($lot, 'object'));
    $w->writeElement('url', sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
    $w->writeElement('creation-date', $this->getCreationDate($lot));
    $w->writeElement('last-update-date', $this->getLastUpdated($lot));
    $w->startElement('location');
      if ($lot->type == 'outoftown') {
        $w->writeElement('region', 'Московская область');
      }
      else {
        $w->writeElement('region', 'Москва');
      }
      $w->writeElement('sub-locality-name', $this->checkJournal($this->translateDistrict($lot), 'location'));
      $w->writeElement('sub-locality-original-name', $this->translateDistrict($lot));
      if(!empty($lot->params['estate']) && $lot->params['estate'] > 1){
        $w->writeElement('building-original-name', $this->checkJournal($this->getAttr($lot->params, 'estate'), 'estates'));
      }
      $w->writeElement('address', $lot->address['street']);
      $w->startElement('metro');
        $w->writeAttribute('name', $this->checkJournal($this->getMetro($lot->metro_id), 'metros'));
        $w->writeAttribute('distance_walk', (int)$this->getAttr($lot->params, 'distance_metro'));
      $w->endElement(); //metro
      $w->writeElement('house_humber', $this->translateHouseNumber($lot->address));
      $w->writeElement('Latitude', $lot->lat);
      $w->writeElement('Longitude', $lot->lng);
      $w->writeElement('house_building', $this->getAttr($lot->params, 'building'));
    $w->endElement(); //location
    $w->startElement('price');
    $w->writeAttribute('currency', preg_replace("/RUR/", "RUB", $lot->currency));
    if((int)$lot->price_all_from > '0'){
        $w->writeAttribute('value', (int)$lot->price_all_from);
      }
     else if((int)$lot->price_all_to > '0'){
        $w->writeAttribute('value', (int)$lot->price_all_to);
      }
    $w->endElement(); //price
    $w->writeElement('Price_hidden', '0');
    $this->addPhotoes($w, $lot);
    if($lot->area_from > 0){
      $w->writeElement('Area', $lot->area_from);
    }
    $w->writeElement('rooms', (int)$this->getAttr($lot->params, 'rooms'));
    $w->writeElement('Floor', (int)$this->getAttr($lot->params, 'about_floor'));
    if((int)$this->getAttr($lot->params, 'floors') > 0){
      $w->writeElement('floors-total', (int)$this->getAttr($lot->params, 'floors'));
    }
    $w->writeElement('Parking', $this->getAttr($lot->params, 'parking'));
//    $w->startElement('preview');
//      $w->writeCdata(strip_tags($lot->anons));
//    $w->endElement(); //preview
    $w->startElement('description');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement(); //description
  }

  protected function doCountry(XMLWriter $w, $lot)
  {
    $w->writeElement('type', 'продажа');
    $w->writeElement('category', $this->translateObjectAndPart($lot, 'object'));
    $w->writeElement('url', sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
    $w->writeElement('creation-date', $this->getCreationDate($lot));
    $w->writeElement('last-update-date', $this->getLastUpdated($lot));
    $w->writeElement('contact_phone', $this->_phones[$lot->type]);
    $w->startElement('location');
      $w->writeElement('region', 'Московская область');
      $w->writeElement('locality-name',
              $this->getNearestCity($lot));
      $w->writeElement('sub-locality-original-name', $lot->address['city']);
      $w->startElement('highway');
        $w->writeAttribute('distance', $this->getAttr($lot->params, 'distance_mkad'));
        $w->writeAttribute('name',
                $this->checkJournal($this->getWard($lot->ward), 'highways')
                ? $this->checkJournal($this->getWard($lot->ward), 'highways')
                : $this->getWard($lot->ward));
      $w->endElement(); //highway
      $w->writeElement('Latitude', $lot->lat);
      $w->writeElement('Longitude', $lot->lng);
    $w->endElement(); //location
    $w->startElement('price');
    $w->writeAttribute('currency', preg_replace("/RUR/", "RUB", $lot->currency));
    if((int)$lot->price_all_from > '0'){
        $w->writeAttribute('value', (int)$lot->price_all_from);
      }
     else if((int)$lot->price_all_to > '0'){
        $w->writeAttribute('value', (int)$lot->price_all_to);
      }
    $w->endElement(); //price
    $w->writeElement('Price_hidden', '0');
    $this->addPhotoes($w, $lot);
    if($lot->area_from > 0){
      $w->writeElement('Area', $lot->area_from);
    }
    if((float)$lot->params['spaceplot'] > 0){
      $w->writeElement('lot-area', $lot->params['spaceplot']);
    }
//    $w->startElement('preview');
//      $w->writeCdata(strip_tags($lot->anons));
//    $w->endElement(); //preview
    $w->startElement('description');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement(); //description
  }

  private function getList($type, $page, $limit)
  {
    $query = Doctrine::getTable('Lot')->createQuery()
      ->andWhere('hide_price = ?')
      ->andWhere('price_all_from > ?')
      ->andWhere('exportable = ?')
      ->orderBy('priority desc')
      ->active()
      ->type($type == 'eliteflat' ? array('eliteflat', 'penthouse') : $type)
      ->select('id');
    if(in_array($type, array('cottage', 'outoftown'))) {
      $query->andWhere('pid IS NULL');
    }
    // This is fuck with memory_limit in production
    // And now we can paginate output
    if ($limit > 0) {
      $query->limit($limit);
      $query->offset($limit * ($page-1));
    }
    return $query->execute(array(0, 0, 1), Doctrine::HYDRATE_SINGLE_SCALAR);
  }

  private function getMetro($id)
  {
    $metro = sfConfig::get('app_subways');
    if(!$id || !isset($metro[$id])) {
      return false;
    }
    return $metro[$id];
  }

  private function getAttr($elem, $attr)
  {
    if($attr == 'floors')
    {
      return array_pop(preg_split("/[\s-]+/", $elem[$attr]));
    }
    if(is_array($elem)) {
      return !isset($elem[$attr]) ?: $elem[$attr];
    }
    elseif(is_object($elem)) {
      return !isset($elem->$attr) ?: $elem->$attr;
    }
    return false;
  }

  private function translateHouseNumber($address)
  {
    $result = '';
    if(!empty($address['house'])) {
      $result .= $address['house'];
    }
    return $result;
  }

  private function translateBathroom($lot)
  {
    $bathrooms   = $this->getAttr($lot->params, 'about_tolets');
    $bathrooms_u = (int)$this->getAttr($lot->params, 'number_of_bathrooms_combined');
    $bathrooms_s = (int)$this->getAttr($lot->params, 'number_of_separate_toilets');

    if((int)$bathrooms > 1 || $bathrooms_u + $bathrooms_s > 1) {
      return 'D';
    }
    elseif(($bathrooms_s == 0 && $bathrooms_u == 1) || (mb_stristr($bathrooms, 'совме', null, 'utf-8') && !mb_stristr($bathrooms, 'разд', null, 'utf-8') )) {
      return 'U';
    }
    else {
      return 'S';
    }
  }

  private function prepareText($text, $add_text = null)
  {
    $text = strip_tags($text);
    $text = preg_replace('/&[a-z0-9]+;/i', '', $text);
    return $text . " [Лот #" . $add_text . '] ';
  }

  private function translateObjectAndPart($lot, $what = 'object')
  {
    $data = array();
    switch($lot->type) {
      case 'eliteflat':
      case 'flatrent':
        $data['object'] = 'квартира';
        $data['part'] = 'жилая';
      break;

      case 'penthouse':
        $data['object'] = 'пентхаус';
      break;

      case 'elitenew':
        $data['object'] = 'новостройка';
        $data['part'] = 'жилая';
      break;

      case 'comrent':
      case 'comsell':
        $data['object'] = $this->_map['commercial'][$lot->params['objecttype']];
        $data['part'] = 'коммерческая';
      break;

      case 'outoftown':
      case 'cottage':
      $data['object'] = $this->_map['country'][$lot->params['objecttype']];
        $data['part'] = 'загородная';
      break;
    }
    return $data[$what];
  }

  private function translateDistrict($lot)
  {
    if($lot->district_id){
      if($lot->district_id <= 19 || $lot->district_id == 31) {
        return 'Центральный АО';
      }
      elseif($lot->district_id == 30) {
        return 'Западный АО';
      }
      else {
        $districts = sfConfig::get('app_districts');
        return !isset($districts[$lot->district_id]) ?: $districts[$lot->district_id];
      }
    }
    elseif($lot->ward) {
      $wards = sfConfig::get('app_wards');
      return !isset($wards[$lot->ward]) ?: $wards[$lot->ward];
    }
  }

  private function getFullAddress($lot)
  {
    $addr = array();
    if(in_array($lot->type, array('cottage', 'outoftown'))) {
      foreach(array('district', 'city', '') as $elem) {
        if(!empty($lot->address[$elem])) {
          $addr[] = $lot->address[$elem];
        }
      }
    }
    if(!empty($lot->address['street'])) {
      $addr[] = $lot->address['street'];
    }
    array_merge($addr, explode(', ', $this->translateHouseNumber($lot->address)));
    return implode(', ', $addr);
  }

  private function getMetroDistance(XMLWriter $w, Lot $lot)
  {
    if($value = $this->getAttr($lot->params, 'distance_metro')) {
      $w->writeElement('time', (int)$value);
      if(mb_stripos($value, 'пешк', null, 'utf-8')!== false ||  mb_stripos($value, 'м.п', null, 'utf-8')!== false) {
        $w->writeElement('timerea', 'минут пешком');
      }
      else {
        $w->writeElement('timerea', 'минут транспортом');
      }
    }
    elseif($lot->is_country_type && $distance = $this->getAttr($lot->params, 'distance_mkad')) {
      $w->writeElement('distance', (int)$value);
      $w->writeElement('distancetype', 'км от МКАД');
    }
  }

  private function getWard($id)
  {
    $wards = sfConfig::get('app_wards');
    return !isset($wards[$id]) ? : $wards[$id] . ' шоссе';
  }

  private function addPhotoes(XMLWriter $w, Lot $lot)
  {
   if($this->translateObjectAndPart($lot, 'object') == 'коттеджный поселок' || $this->translateObjectAndPart($lot, 'object') == 'новостройка'){
    if ($photo = $lot->getImage('pres_')) { // main image
      $w->writeElement('Image', $this->_domain . $photo);
    }
    foreach ($lot->Photos as $photo) { // other images
        if (!$photo->is_pdf && !$photo->is_xls) {
          $w->writeElement('Image', $this->_domain . $photo->getImage('full_'));
        }
      }
    } else{
      if ($photo = $lot->getImage('pres')) { // main image
      $w->writeElement('Image', $this->_domain . $photo);
    }
    foreach ($lot->Photos as $photo) { // other images
        if (!$photo->is_pdf && !$photo->is_xls) {
          $w->writeElement('Image', $this->_domain . $photo->getImage('full'));
        }
      }
    }
  }

   private function calculateTheDistance ($φA, $λA, $φB, $λB)
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

  private function getExternalArrays2()
  {
    $url = 'http://www.kre.ru/export/TbaCity.xml';
    $_content = curl_init($url);
    curl_setopt($_content, CURLOPT_RETURNTRANSFER, 1);
    $geo = curl_exec($_content);
    curl_close($_content);
    $geo = (array)simplexml_load_string($geo);
    $this->_TbaCity  = $geo;

  }

  private function getNearestCity(Lot $lot)
  {
    /*$geo = (array)$this->_TbaCity;

    $current = 0;
    $lowest =  round($this->calculateTheDistance((string)$lot->lat, (string)$lot->lng, (string)$geo["City"][0]['lat'], (string)$geo["City"][0]['lng']), 2);
    $city = null;


    foreach ($geo["City"] as $value) {
      $current = $this->calculateTheDistance((string)$lot->lat, (string)$lot->lng, (string)$value["lat"], (string)$value["lng"]);
      if($current <= $lowest) {
        $lowest = $current;
        $city = $value["name"];
      }
    }
   return (string)$city;*/
    return !empty($lot->params['settlements_tba']) ? $this->checkJournal($lot->params['settlements_tba'], 'settlements') 
            : $this->checkJournal($lot->address['city'], 'settlements');
  }

  private function getCreationDate(Lot $lot)
  {
    $creation_date = strtotime($lot->created_at) > 0 ? strtotime($lot->created_at) : strtotime('yesterday');
    return date('Y-m-d\TH:i:s+04:00', $creation_date);
  }

  private function getLastUpdated(Lot $lot)
  {
    $last_update = strtotime($lot->updated_at) > 0 ? strtotime($lot->updated_at) : strtotime('yesterday');
    return date('Y-m-d\TH:i:s+04:00', $last_update);
  }

  private function checkJournal($value, $jornalType)
  {
    $type = '_'.$jornalType;
    $valueArray = preg_split("/[\s]+/", (string)$value);
    //var_dump($this->$type);
    foreach ($this->$type as $_res) {
      foreach ($_res as $res) {
        if(in_array($res['name'], $valueArray) || in_array($res['id'], $valueArray)){
          return $res['id'];
        }else if((string)$value == (string)$res['name'] || (string)mb_strtolower($value) == (string)$res['id']) {
          return $res['id'];
        }
      }
    }
    return false;
  }

  private function getExternalArrays()
  {
    $url = 'http://topba.ru/assets/directory.php?type=metros';
    $_content = curl_init($url);
    curl_setopt($_content, CURLOPT_RETURNTRANSFER, 1);
    $geo = curl_exec($_content);
    curl_close($_content);
    $result = (array)simplexml_load_string($geo);
    $this->_metros  = $result;

    $url1 = 'http://topba.ru/assets/directory.php?type=locations';
    $_content1 = curl_init($url1);
    curl_setopt($_content1, CURLOPT_RETURNTRANSFER, 1);
    $geo1 = curl_exec($_content1);
    curl_close($_content1);
    $result1 = (array)simplexml_load_string($geo1);
    $this->_location  = $result1;

    $url2 = 'http://topba.ru/assets/directory.php?type=highways';
    $_content2 = curl_init($url2);
    curl_setopt($_content2, CURLOPT_RETURNTRANSFER, 1);
    $geo2 = curl_exec($_content2);
    curl_close($_content2);
    $result2 = (array)simplexml_load_string($geo2);
    $this->_highways  = $result2;

    $url3 = 'http://topba.ru/assets/directory.php?type=estates';
    $_content3 = curl_init($url3);
    curl_setopt($_content3, CURLOPT_RETURNTRANSFER, 1);
    $geo3 = curl_exec($_content3);
    curl_close($_content3);
    $result3 = (array)simplexml_load_string($geo3);
    $this->_estates  = $result3;

    $url4 = 'http://topba.ru/assets/directory.php?type=settlements';
    $_content4 = curl_init($url4);
    curl_setopt($_content4, CURLOPT_RETURNTRANSFER, 1);
    $geo4 = curl_exec($_content4);
    curl_close($_content4);
    $result4 = (array)simplexml_load_string($geo4);
    $this->_settlements  = $result4;
  }
}
