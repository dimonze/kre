<?php

class mailXmlTask extends sfBaseTask
{
  protected
    $_org_name =  'Contact Real Estate',
    $_org_email = 'kre@kre.ru',
    $_domain    = 'http://www.kre.ru',
    $_partner   = 'mail',
    $_provider  = 'p8583',
    $_counters  = array(),
    $_expire    = null,


    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse','flatrent'),
      'newbuilds' => 'elitenew',
      'commerce'  => array('comrent', 'comsell'),
      'country'   => array('outoftown', 'cottage'),
    ),

    $_map = array(
      'commercial' => array(
        'Торговое помещение'              => 'ТП',
        'Офисное помещение'               => 'офис',
        'Отдельно стоящее здание'         => 'другое',
        'Готовый арендный бизнес'         => 'готовый бизнес',
        'Особняк'                         => 'здание',
        'Помещение свободного назначения' => 'другое',
        'Склад/складской комплекс'        => 'склад',
        'Промышленный комплекс'           => 'производство',
        'Земельный участок'               => 'земля',
        'Прочее'                          => 'нежилое помещение',
      ),
      'country' => array(
        'Участок'            => 'участок',
        'Таунхаус'           => 'таунхаус',
        'Коттедж'            => 'коттедж',
        'Коттеджный поселок' => 'коттеджный поселок',
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
    $this->name             = 'mailXml';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [mailXml|INFO] task does things.
Call it with:

  [php symfony mailXml|INFO]
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
    $this->_expire = date('Y-m-d', strtotime("+14 days"));
    foreach($types as $type) {
      $method = sprintf('do%s', ucfirst($type));
      $container = 'root';
      $i = array('total' => 0, 'good' => 0, 'bad' => 0, 'current' => 0);
      if(is_callable(array($this, $method))) {
        $ids = $this->getList($this->_suptypes[$type], $arguments['page'], $arguments['limit']);
        $i['total'] = count($ids);
        $filename = Tools::getFileNameForXmlFile($this->_partner, $type, $arguments['page']);
        $this->logSection('Prepare', sprintf('file %s', $filename));

        $w = new XMLWriter();
        $w->openUri($filename);
        $w->setIndentString(str_repeat(" ", 2));
        $w->setIndent(true);
        $w->startDocument('1.0','windows-1251');
        $w->startElement($container);
          $w->writeComment(sprintf(' Generated at %s, contains approx %s lots ', date('Y-m-d H:i:s'), $i['total']));
          $this->logSection('>>>', sprintf('I will use "%s" method', $method));

          foreach($ids as $id) {
            $i['current']++;
            $lot = Doctrine::getTable('Lot')->find($id);
            try {
              if(!$this->translateObjectAndPart($lot, 'object') || ((int)$lot->area_from <= 0 && (int)$lot->area_to <= 0)) {
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
              $w->startElement('offer');
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

        $w->endElement();//container
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
    $w->writeElement('id', $lot->id);
    $w->writeElement('direction', 'предложение');
    $w->writeElement('provider', $this->_provider);
    $w->writeElement('lastupdated', $this->getLastUpdated($lot));
    $w->writeElement('expiration', $this->_expire);
    $w->writeElement('part', $this->translateObjectAndPart($lot, 'part'));
    $w->writeElement('bargain', in_array($lot->type, Lot::$_statuses_rent) ? 'аренда' : 'продажа');
    $w->writeElement('object', $this->translateObjectAndPart($lot, 'object'));
    $w->writeElement('country', 'Россия');
    $w->writeElement('street', $lot->address['street']);
    $w->writeElement('house', $this->translateHouseNumber($lot->address));
    $w->writeElement('address', $this->getFullAddress($lot));
    $w->writeElement('state', 'Московская область');
    $w->writeElement('town', 'Москва');
    $w->writeElement('district', $this->translateDistrict($lot));
    $w->writeElement('subway', $this->getMetro($lot->metro_id));
    $this->getMetroDistance($w, $lot);
    $w->startElement('description');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement();
    $w->startElement('rooms');
      $w->writeElement('room', $this->getAttr($lot->params, 'rooms'));
    $w->endElement();//rooms
    $w->startElement('prices');
      if((int)$lot->price_all_from > '0'){
        $w->writeElement('price', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
      }
      if((int)$lot->price_all_to > '0'){
         $w->writeElement('price', (int)Currency::convert($lot->price_all_to, $lot->currency, 'RUR'));
      }
    $w->endElement();//prices
    $w->writeElement('currency', 'RUR');
    $w->writeElement('priceunit', 'total');
    $w->writeElement('priceperiod', ($lot->type == 'flatrent' ? 'month' : 'total'));
    $w->writeElement('floor', (int)$this->getAttr($lot->params, 'about_floor'));
    $w->writeElement('nfloor', (int)$this->getAttr($lot->params, 'floors'));
    $w->startElement('areas');
      $this->addArea($w, $lot);
    $w->endElement();
    $w->startElement('images');
      $this->addPhotoes($w, $lot);
    $w->endElement();//images
    $w->writeElement('phone', $this->_phones[$lot->type]);
    $w->writeElement('name', $this->_org_name);
    $w->writeElement('company', $this->_org_name);
    $w->writeElement('email', $this->_org_email);
    $w->writeElement('url', sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
  }

  protected function doNewbuilds(XMLWriter $w, $lot)
  {
    $w->writeElement('id', $lot->id);
    $w->writeElement('direction', 'предложение');
    $w->writeElement('provider', $this->_provider);
    $w->writeElement('lastupdated', $this->getLastUpdated($lot));
    $w->writeElement('expiration', $this->_expire);
    $w->writeElement('part', $this->translateObjectAndPart($lot, 'part'));
    $w->writeElement('bargain', in_array($lot->type, Lot::$_statuses_rent) ? 'аренда' : 'продажа');
    $w->writeElement('object', $this->translateObjectAndPart($lot, 'object'));
    $w->writeElement('country', 'Россия');
    $w->writeElement('street', $lot->address['street']);
    $w->writeElement('house', $this->translateHouseNumber($lot->address));
    $w->writeElement('address', $this->getFullAddress($lot));
    $w->writeElement('state', 'Московская область');
    $w->writeElement('town', 'Москва');
    $w->writeElement('district', $this->translateDistrict($lot));
    $w->writeElement('subway', $this->getMetro($lot->metro_id));
    $this->getMetroDistance($w, $lot);
    $w->startElement('description');
    $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement();
    $w->startElement('prices');
      if((int)$lot->price_all_from > '0'){
        $w->writeElement('price', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
      }
      if((int)$lot->price_all_to > '0'){
         $w->writeElement('price', (int)Currency::convert($lot->price_all_to, $lot->currency, 'RUR'));
      }
    $w->endElement();//prices
    $w->writeElement('currency', 'RUR');
    $w->writeElement('priceunit', 'total');
    $w->writeElement('priceperiod', 'total');
    $w->writeElement('housetype', $this->getAttr($lot->params, 'buildtype'));
    $w->writeElement('floor', (int)$this->getAttr($lot->params, 'about_floor'));
    $w->writeElement('nfloor', (int)$this->getAttr($lot->params, 'floors'));
    $w->startElement('areas');
      $this->addArea($w, $lot);
    $w->endElement();
    if($lot->type == 'elitenew') {
      $w->writeElement('newbuilding', '1');
    }
    $w->startElement('images');
      $this->addPhotoes($w, $lot);
    $w->endElement();//images
    $w->writeElement('phone', $this->_phones[$lot->type]);
    $w->writeElement('name', $this->_org_name);
    $w->writeElement('company', $this->_org_name);
    $w->writeElement('email', $this->_org_email);
    $w->writeElement('url', sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
  }

  protected function doCommerce(XMLWriter $w, $lot)
  {
    $w->writeElement('id', $lot->id);
    $w->writeElement('direction', 'предложение');
    $w->writeElement('provider', $this->_provider);
    $w->writeElement('lastupdated', $this->getLastUpdated($lot));
    $w->writeElement('expiration', $this->_expire);
    $w->writeElement('part', $this->translateObjectAndPart($lot, 'part'));
    $w->writeElement('bargain', in_array($lot->type, Lot::$_statuses_rent) ? 'аренда' : 'продажа');
    $w->writeElement('object', $this->translateObjectAndPart($lot, 'object'));
    $w->writeElement('country', 'Россия');
    $w->writeElement('street', $lot->address['street']);
    $w->writeElement('house', $this->translateHouseNumber($lot->address));
    $w->writeElement('address', $this->getFullAddress($lot));
    $w->writeElement('state', 'Московская область');
    $w->writeElement('town', 'Москва');
    $w->writeElement('district', $this->translateDistrict($lot));
    $w->writeElement('subway', $this->getMetro($lot->metro_id));
      $this->getMetroDistance($w, $lot);
    $w->startElement('description');
    $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement();
    $w->startElement('rooms');
    $w->writeElement('room', $this->getAttr($lot->params, 'rooms'));
    $w->endElement();//rooms
    $w->startElement('prices');
      if((int)$lot->price_all_from > '0'){
        $w->writeElement('price', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
      }
      if((int)$lot->price_all_to > '0'){
         $w->writeElement('price', (int)Currency::convert($lot->price_all_to, $lot->currency, 'RUR'));
      }
    $w->endElement();//prices
    $w->writeElement('currency', 'RUR');
    $w->writeElement('priceunit', 'total');
    $w->writeElement('priceperiod', ($lot->type == 'comrent' ? 'year' : 'total'));
    $w->writeElement('housetype', $this->getAttr($lot->params, 'buildtype'));
    $w->writeElement('floor', (int)$this->getAttr($lot->params, 'floor'));
    $w->writeElement('nfloor', (int)$this->getAttr($lot->params, 'floors'));
    $w->startElement('areas');
    $this->addArea($w, $lot);
    $w->endElement();
    $w->startElement('images');
    $this->addPhotoes($w, $lot);
    $w->endElement();//images
    $w->writeElement('phone', $this->_phones[$lot->type]);
    $w->writeElement('name', $this->_org_name);
    $w->writeElement('company', $this->_org_name);
    $w->writeElement('email', $this->_org_email);
    $w->writeElement('url', sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
  }

  protected function doCountry(XMLWriter $w, $lot)
  {
    $w->writeElement('id', $lot->id);
    $w->writeElement('direction', 'предложение');
    $w->writeElement('provider', $this->_provider);
    $w->writeElement('lastupdated', $this->getLastUpdated($lot));
    $w->writeElement('expiration', $this->_expire);
    $w->writeElement('part', $this->translateObjectAndPart($lot, 'part'));
    $w->writeElement('bargain', in_array($lot->type, Lot::$_statuses_rent) ? 'аренда' : 'продажа');
    $w->writeElement('object', $this->translateObjectAndPart($lot, 'object'));
    $w->writeElement('country', 'Россия');
    $w->writeElement('street', $lot->address['street']);
    $w->writeElement('house', $this->translateHouseNumber($lot->address));
    $w->writeElement('address', $this->getFullAddress($lot));
    $w->writeElement('state', 'Московская область');
    $w->writeElement('town', 'Москва');
    $w->writeElement('district', $this->translateDistrict($lot));
    $this->getMetroDistance($w, $lot);
    $w->startElement('description');
    $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement();
    $w->startElement('rooms');
    $w->writeElement('room', $this->getAttr($lot->params, 'rooms'));
    $w->endElement();//rooms
    $w->startElement('prices');
      if((int)$lot->price_all_from > '0'){
        $w->writeElement('price', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
      }
      if((int)$lot->price_all_to > '0'){
         $w->writeElement('price', (int)Currency::convert($lot->price_all_to, $lot->currency, 'RUR'));
      }
    $w->endElement();//prices
    $w->writeElement('currency', 'RUR');
    $w->writeElement('priceunit', 'total');
    $w->writeElement('priceperiod', ($lot->type == 'cottage' ? 'month' : 'total'));
    if($nfloor = (int)$this->getAttr($lot->params, 'floors')){
      $w->writeElement('nfloor', $nfloor);
    }
    $w->startElement('areas');
    $this->addArea($w, $lot);
    $w->endElement();
    $w->startElement('images');
    $this->addPhotoes($w, $lot);
    $w->endElement();//images
    $w->writeElement('phone', $this->_phones[$lot->type]);
    $w->writeElement('name', $this->_org_name);
    $w->writeElement('company', $this->_org_name);
    $w->writeElement('email', $this->_org_email);
    $w->writeElement('url', sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
  }

  private function getList($type, $page, $limit)
  {
    $add_param = array();
    $query = Doctrine::getTable('Lot')->createQuery()
      ->andWhere('hide_price = ?')
      ->andWhere('price_all_from > ?')
      ->andWhere('exportable = ?')
      ->orderBy('priority desc')
      ->active()
      ->type($type == 'eliteflat' ? array('eliteflat', 'penthouse') : $type)
      ->select('id');
    if(in_array($type, array('cottage', 'outoftown'))) {
      $query->andWhere('has_children != ?');
      $add_param[] = '1';
    }
    // And now we can paginate output
    if ($limit > 0) {
      $query->limit($limit);
      $query->offset($limit * ($page-1));
    }
    return $query->execute(array_merge(array(0, 0, 1), $add_param), Doctrine::HYDRATE_SINGLE_SCALAR);
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

  private function prepareText($text, $add_text = null)
  {
    $text = strip_tags($text);
    $text = preg_replace('/&[a-z0-9]+;/i', '', $text);
    return $text . " [Лот #" . $add_text . '] ';
  }

  private function addPhotoes(XMLWriter $w, Lot $lot)
  {
    if($photo = $lot->getImage('pres')){ // main image
      $w->writeElement('image', $this->_domain . $photo);
    }
    foreach($lot->Photos as $photo) { // other images
      !$photo->is_pdf && !$photo->is_xls && $w->writeElement('image', $this->_domain . $photo->getImage('full'));
    }
  }

  private function translateHouseNumber($address)
  {
    $result = array();
    if(!empty($address['house'])) {
      $result[] = 'д. '. $address['house'];
    }
    if(!empty($address['building'])) {
      $result[] = 'к. '.$address['building'];
    }
    if(!empty($address['construction'])) {
      $result[] = 'стр. '.$address['construction'];
    }
    return implode(', ', $result);
  }

  private function getLastUpdated(Lot $lot)
  {
    if($lot->currency != 'RUR' && date('w') < 6){
      $last_update = strtotime('today');
    } else{
      $last_update = strtotime($lot->updated_at) > 0 ? strtotime($lot->updated_at) : strtotime('yesterday');
    }
    return date('Y-m-d', $last_update);
  }

  private function translateObjectAndPart($lot, $what = 'object')
  {
    $data = array();
    switch($lot->type) {
      case 'eliteflat':
      case 'penthouse':
      case 'flatrent':
        $data['object'] = 'квартира';
        $data['part'] = 'жилая';
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
    $addr[] = 'Россия';
    if(in_array($lot->type, array('cottage', 'outoftown'))) {
      $addr[] = 'Московская область';
      foreach(array('district', 'city', '') as $elem) {
        if(!empty($lot->address[$elem])) {
          $addr[] = $lot->address[$elem];
        }
      }
    }
    else {
      $addr[] = 'Москва';
    }
    if(!empty($lot->address['street'])) {
      $addr[] = $lot->address['street'];
    }
    array_merge($addr, explode(', ', $this->translateHouseNumber($lot->address)));
    return implode(', ', $addr);
  }

  private function getMetroDistance(XMLWriter $w, Lot $lot)
  {
    if($lot->is_country_type && $distance = $this->getAttr($lot->params, 'distance_mkad')) {
      $w->writeElement('distance', (int)$distance);
      $w->writeElement('distancetype', 'км от МКАД');
    }
    elseif($value = $this->getAttr($lot->params, 'distance_metro')) {
      $w->writeElement('distance', (int)$value);
      if(mb_stripos($value, 'пешк', null, 'utf-8')!== false ||  mb_stripos($value, 'м.п', null, 'utf-8')!== false) {
        $w->writeElement('distancetype', 'минут пешком');
      }
      else {
        $w->writeElement('distancetype', 'минут транспортом');
      }
    }
  }

  private function addArea(XMLWriter $w, Lot $lot)
  {
    if($lot->is_country_type) {
      $plot = $this->getAttr($lot->params, 'spaceplot');
      if($plot) {
        foreach(explode('-', $plot) as $area) {
          if($area = (int)$area) {
            $w->startElement('area');
              $w->writeAttribute('type', 'plot');
              $w->text($area);
            $w->endElement();//area
          }
        }
      }
      $w->startElement('area');
      $w->writeAttribute('type', 'live');
      $w->text((int)$lot->area_from);
      $w->endElement();

      if((int)$lot->area_to > 0) {
        $w->startElement('area');
        $w->writeAttribute('type', 'live');
        $w->text((int)$lot->area_to);
        $w->endElement();
      }
    }
    else {
      $w->startElement('area');
        $w->writeAttribute('type', 'total');
        $w->text((int)$lot->area_from);
      $w->endElement();

      if((int)$lot->area_to > 0) {
        $w->startElement('area');
          $w->writeAttribute('type', 'total');
          $w->text((int)$lot->area_to);
        $w->endElement();
      }
    }
  }

}
