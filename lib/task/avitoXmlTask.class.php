<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cottageXmlTask
 *
 * @author dimonze
 */
class avitoXmlTask extends sfBaseTask
{
  protected
    $_org_name =  'Contact Real Estate',
    $_org_email = 'kre@kre.ru',
    $_domain    = 'http://www.kre.ru',
    $_partner   = 'avito',
    $_counters  = array(),
    $_AvitoCity  = array(),
    $_AvitoDirect  = array(),


    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse'),
      'newbuilds' => 'elitenew',
      'commerce'  => array('comsell'),
      'country'   => array('outoftown'),
    ),

    $_map = array(
      'commercial' => array(
        'Торговое помещение'              => 'Торговое помещение',
        'Офисное помещение'               => 'Офисное помещение',
        'Помещение свободного назначения' => 'Помещение свободного назначения',
        'Склад/складской комплекс'        => 'Складское помещение',
        'Промышленный комплекс'           => 'Производственное помещение',

      ),
      'country' => array(
        'Участок'            => 'Поселений (ИЖС)',
        'Таунхаус'           => 'таунхаус',
        'Коттедж'            => 'Коттедж',
        'Квартира'           => 'квартира',
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
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addArguments(array(
      new sfCommandArgument('type',  sfCommandArgument::OPTIONAL, 'Data type', array('flats', 'newbuilds', 'commerce', 'country')),
      new sfCommandArgument('page',  sfCommandArgument::OPTIONAL, 'Page (for multi-file output)',  1),
      new sfCommandArgument('limit', sfCommandArgument::OPTIONAL, 'Limit (for multi-file output)', 0),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = '';
    $this->name             = 'avitoXml';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [avitoXml|INFO] task does things.
Call it with:

  [php symfony avitoXml|INFO]
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

    $types = is_array($arguments['type']) ? $arguments['type'] : array($arguments['type']);
    foreach($types as $type) {
      $method = sprintf('do%s', ucfirst($type));
      $i = array('total' => 0, 'good' => 0, 'bad' => 0, 'current' => 0);
      if(is_callable(array($this, $method))) {
        $ids = $this->getList($this->_suptypes[$type], $arguments['page'], $arguments['limit']);
        $i['total'] = count($ids);
        $filename = Tools::getFileNameForXmlFile($this->_partner, $type, $arguments['page']);
        $this->logSection('Prepare', sprintf('file %s', $filename));
        $this->getExternalArrays();
        $w = new XMLWriter();
        $w->openUri($filename);
        $w->setIndentString(str_repeat(" ", 2));
        $w->setIndent(true);
        $w->startDocument('1.0','utf-8');
        $w->startElement('Ads');
          $w->startAttribute('target');
            $w->text('Avito.ru');
          $w->endAttribute();//target
          $w->startAttribute('formatVersion');
            $w->text(2);
          $w->endAttribute();//formatVersion
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
              $w->startElement('Ad');
                // !!! Rock-n-roll begins here!
                $this->$method($w, $lot);
              $w->endElement();//Ad
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
        $w->endElement();//Ads
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
    $w->writeElement('Id', $lot->id);
    $w->writeElement('Category', 'Квартиры');
    $w->writeElement('ObjectType', $this->translateObjectAndPart($lot, 'object'));
    $w->writeElement('OperationType', in_array($lot->type, Lot::$_statuses_rent) ? 'Сдам' : 'Продам');
    $w->writeElement('DateBegin', $this->getCreationDate($lot));
    if (!in_array($lot->type, Lot::$_statuses_rent))
       $w->writeElement('MarketType', $this->getAttr($lot->params, 'market') != 'Вторичка' ? 'Новостройка' : 'Вторичка');
      if ($lot->type == 'outoftown') {
        $w->writeElement('Region', 'Московская область');
        $w->writeElement('City', $this->getNearestCity($lot));
        $w->writeElement('Locality', $lot->address['city']);
      }
      else {
        $w->writeElement('Region', 'Москва');
      }
      $w->writeElement('Street', $lot->address['street']);
      $w->writeElement('Subway', $this->getMetro($lot->metro_id));
    $w->writeElement('Price', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
    $this->addPhotoes($w, $lot);
    $w->writeElement('Square', (int)$lot->area_from);
    $w->writeElement('SaleRooms', (int)$this->getAttr($lot->params, 'rooms'));
    $w->writeElement('Rooms', (int)$this->getAttr($lot->params, 'rooms'));
    $w->writeElement('Floor', (int)$this->getAttr($lot->params, 'about_floor'));
    if((int)$this->getAttr($lot->params, 'floors') > 0 && (int)$this->getAttr($lot->params, 'floors') >= (int)$this->getAttr($lot->params, 'about_floor')){
      $w->writeElement('Floors', (int)$this->getAttr($lot->params, 'floors'));
    }
    if(in_array($this->getAttr($lot->params, 'buildtype'), array('кирпичный','панельный','блочный','монолитный','деревянный'))){
      $w->writeElement('HouseType', $this->getAttr($lot->params, 'buildtype'));
    }
    $w->startElement('Description');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement(); //Description
    $w->writeElement('ContactPhone', $this->_phones[$lot->type]);
    $w->writeElement('AdStatus', 'Free');
  }

  protected function doNewbuilds(XMLWriter $w, $lot)
  {
    $w->writeElement('Id', $lot->id);
    $w->writeElement('Category', $this->translateObjectAndPart($lot, 'part'));
    $w->writeElement('ObjectType', $this->translateObjectAndPart($lot, 'object'));
    $w->writeElement('OperationType', in_array($lot->type, Lot::$_statuses_rent) ? 'Сдам' : 'Продам');
    if (!in_array($lot->type, Lot::$_statuses_rent))
        $w->writeElement('MarketType', $this->getAttr($lot, 'market') != 'Вторичка' ? 'Новостройка' : 'Вторичка');
    $w->writeElement('DateBegin', $this->getCreationDate($lot));
      $w->writeElement('Region', 'Москва');
      $w->writeElement('Street', $lot->address['street']);
      $w->writeElement('Subway', $this->getMetro($lot->metro_id));
    $w->writeElement('Price', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
    $this->addPhotoes($w, $lot);
    $w->writeElement('Square', (int)$lot->area_from);
    $w->writeElement('Rooms', (int)$this->getAttr($lot->params, 'rooms'));
    $w->writeElement('Floor', (int)$this->getAttr($lot->params, 'about_floor'));
    if((int)$this->getAttr($lot->params, 'floors') > 0 && (int)$this->getAttr($lot->params, 'floors') >= (int)$this->getAttr($lot->params, 'about_floor')){
      $w->writeElement('Floors', (int)$this->getAttr($lot->params, 'floors'));
    }
    $w->startElement('Description');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement(); //Description
    $w->writeElement('ContactPhone', $this->_phones[$lot->type]);
    $w->writeElement('AdStatus', 'Free');
  }

  protected function doCommerce(XMLWriter $w, $lot)
  {
    $w->writeElement('Id', $lot->id);
    $w->writeElement('Category', 'Коммерческая недвижимость');
    $w->writeElement('ObjectType', $this->translateObjectAndPart($lot, 'object'));
    $w->writeElement('OperationType', in_array($lot->type, Lot::$_statuses_rent) ? 'Сдам' : 'Продам');
    $w->writeElement('DateBegin', $this->getCreationDate($lot));
      if ($lot->type == 'outoftown') {
        $w->writeElement('Region', 'Московская область');
      }
      else {
        $w->writeElement('Region', 'Москва');
      }
      //$w->writeElement('City', $this->translateDistrict($lot));
      $w->writeElement('Street', $lot->address['street']);
      $w->writeElement('Subway', $this->getMetro($lot->metro_id));
    $w->writeElement('Price', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
    $this->addPhotoes($w, $lot);
    $w->writeElement('Square', (int)$lot->area_from);
    $w->writeElement('BuildingClass', $this->getAttr($lot->params, 'buildclass'));
    $w->writeElement('Floor', (int)$this->getAttr($lot->params, 'about_floor'));
    if((int)$this->getAttr($lot->params, 'floors') > 0 && (int)$this->getAttr($lot->params, 'floors') >= (int)$this->getAttr($lot->params, 'about_floor')){
      $w->writeElement('Floors', (int)$this->getAttr($lot->params, 'floors'));
    }
    $w->startElement('Description');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement(); //Description
    $w->writeElement('ContactPhone', $this->_phones[$lot->type]);
    $w->writeElement('AdStatus', 'Free');
  }

  protected function doCountry(XMLWriter $w, $lot)
  {
    $w->writeElement('Id', $lot->id);
    if($lot->params['objecttype'] == 'Участок')
    $w->writeElement('Category', 'Земельные участки');
    else
    $w->writeElement('Category', 'Дома, дачи, коттеджи');
    $w->writeElement('ObjectType', $this->translateObjectAndPart($lot, 'object'));
    $w->writeElement('OperationType', in_array($lot->type, Lot::$_statuses_rent) ? 'Сдам' : 'Продам');
    $w->writeElement('DateBegin', $this->getCreationDate($lot));
    $w->writeElement('Region', 'Московская область');
      //$w->writeElement('DirectionRoad', $this->getWard($lot) . ' шоссе');
      $w->writeElement('City', $this->getNearestCity($lot));
      $w->writeElement('Locality', $lot->address['city']);
      $w->writeElement('Street', $lot->address['street']);
    $w->writeElement('Price', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
    $this->addPhotoes($w, $lot);
    $w->writeElement('Square', (int)$lot->area_from);
    $w->writeElement('LandArea', (float)$lot->params['spaceplot'] > 0 && (float)$lot->params['spaceplot'] < 1 ? 1 : (int)$lot->params['spaceplot'] );
    if((int)$this->getAttr($lot->params, 'floors') > 0 && (int)$this->getAttr($lot->params, 'floors') >= (int)$this->getAttr($lot->params, 'about_floor')){
      $w->writeElement('Floors', (int)$this->getAttr($lot->params, 'floors'));
    }
    $w->writeElement('DistanceToCity', $this->getAttr($lot->params, 'distance_mkad'));
    $w->startElement('Description');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement(); //Description
    $w->writeElement('ContactPhone', $this->_phones[$lot->type]);
    $w->writeElement('AdStatus', 'Free');
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
    if(is_array($elem)) {
      return $elem[$attr];
    }
    elseif(is_object($elem)) {
      return $elem->$attr;
    }
    return false;
  }

  private function translateHouseNumber($address)
  {
    $result = '';
    if(!empty($address['house'])) {
      $result .= $address['house'];
    }
    if(!empty($address['building'])) {
      $result .= 'к'.$address['building'];
    }
    if(!empty($address['construction'])) {
      $result .= 'с'.$address['construction'];
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
      case 'penthouse':
      case 'flatrent':
        $data['object'] = 'квартира';
        $data['part'] = 'Квартиры';
      break;

      case 'elitenew':
        $data['object'] = 'новостройка';
        $data['part'] = 'Квартиры';
      break;

      case 'comrent':
      case 'comsell':
        $data['object'] = $this->_map['commercial'][$lot->params['objecttype']];
        $data['part'] = 'Коммерческая недвижимость';
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

  private function getWard(Lot $lot)
  {
    $geo = (array)$this->_AvitoDirect;

    foreach ($geo["wards"] as $value) {
      if ($value['name'] == $this->getNearestCity($lot)) {
        return $value['ward'];
      }
    }
   return NULL;
  }

  private function addPhotoes(XMLWriter $w, Lot $lot)
  {
    $w->startElement('Images');
    if($photo = $lot->getImage('pres_')){ // main image
       $w->startElement('Image');
        $w->startAttribute('url');
             $w->text($this->_domain . $photo);
        $w->endAttribute();//url
        $w->endElement();//Image
    }
    foreach($lot->Photos as $photo) { // other images
      if(!$photo->is_pdf && !$photo->is_xls){
        $w->startElement('Image');
        $w->startAttribute('url');
             $w->text($this->_domain . $photo->getImage('full_'));
        $w->endAttribute();//url
        $w->endElement();//Image
      }
    }
    $w->endElement();//Images
  }

  /*private function getNearestCity(Lot $lot)
  {
    $url = 'http://www.kre.ru/export/NearCity.xml';
    $_content = curl_init($url);
    curl_setopt($_content, CURLOPT_RETURNTRANSFER, 1);
    $geo = curl_exec($_content);
    curl_close($_content);
    $geo = (array)simplexml_load_string($geo);
     //var_dump($geo);
     $geoArray = array();

     foreach($geo['Nearest'] as $value)
     {
       $res = explode(',',  $value);
       $geoArray[$res[0]] = $res[1];
     }
     //var_dump($geoArray);
    return $geoArray[$lot->address['city']];
  }*/

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

  private function getExternalArrays()
  {
    $url = 'http://www.kre.ru/export/AvitoCity.xml';
    $_content = curl_init($url);
    curl_setopt($_content, CURLOPT_RETURNTRANSFER, 1);
    $geo = curl_exec($_content);
    curl_close($_content);
    $geo = (array)simplexml_load_string($geo);
    $this->_AvitoCity  = $geo;

    $url1 = 'http://www.kre.ru/export/AvitoDirect.xml';
    $_content1 = curl_init($url1);
    curl_setopt($_content1, CURLOPT_RETURNTRANSFER, 1);
    $geo1 = curl_exec($_content1);
    curl_close($_content1);
    $geo1 = (array)simplexml_load_string($geo1);
    $this->_AvitoDirect  = $geo1;
  }

  private function getNearestCity(Lot $lot)
  {
    $geo = (array)$this->_AvitoCity;

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
   return (string)$city;
  }

  private function getCreationDate(Lot $lot)
  {
    $object_last_update = $lot->new_object != '0000-00-00' ? (strtotime(sprintf('%s - %s', $lot->new_object, $this->_delta))) : 0;
    $price_last_update  = $lot->new_price  != '0000-00-00' ? (strtotime(sprintf('%s - %s', $lot->new_price,  $this->_delta))) : 0;
    $last_update = $object_last_update > $price_last_update ? $object_last_update : $price_last_update;
    return date('Y-m-d', ($last_update ? $last_update : strtotime('yesterday')));
  }
}
