<?php

class dmirXmlTask extends sfBaseTask
{
  protected
    $_org_name =  'Contact Real Estate',
    $_org_email = 'kre@kre.ru',
    $_domain    = 'http://www.kre.ru',
    $_partner   = 'dmir',
    $_counters  = array(),

    $_rubrs = array(
        'flats'     => array('1', '2', '4.1', '4.2', '5.1'),
        'commerce'  => array('7.1', '7.2', '7.3', '7.4'),
        'country'   => array('3.1', '3.2', '4.3', '5.2','6'),
      ),

    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse'),
      'newbuilds' => 'elitenew',
      'commerce'  => array('comrent', 'comsell'),
      'country'   => array('outoftown', 'cottage'),
    ),

    $_map = array(
      'commercial' => array(
        'Торговое помещение'              => 'ТП',
        'Офисное помещение'               => 'офис',
        'Отдельно стоящее здание'         => 'ОСЗ',
        'Готовый арендный бизнес'         => 'готовый бизнес',
        'Особняк'                         => 'здание',
        'Помещение свободного назначения' => 'ПСН',
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
    $this->name             = 'dmirXml';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [dmirXml|INFO] task does things.
Call it with:

  [php symfony dmirXml|INFO]
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

        $w = new XMLWriter();
        $w->openUri($filename);
        $w->setIndentString(str_repeat(" ", 2));
        $w->setIndent(true);
        $w->startDocument('1.0','utf-8');
        $w->startElement('VFPData');
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
              $w->startElement('crstemp');
                // !!! Rock-n-roll begins here!
                $this->$method($w, $lot);
              $w->endElement();//crstemp
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
        $w->endElement();//document
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
    $w->writeElement('object_id', $lot->id);
      $this->getRubrId($w, $lot);
    $w->writeElement('apartm', $this->translateObjectAndPart($lot, 'object'));
    if ($lot->type == 'outoftown') {
      $w->writeElement('region', 'Московская обл.');
    }
    else {
      $w->writeElement('region', 'Москва');
    }
    $w->writeElement('district', $this->translateDistrict($lot));
    $w->writeElement('town', $this->getAttr($lot->params, 'city'));
    $w->writeElement('houseAdr', $lot->address['street']);
    $w->writeElement('metro', $this->getMetro($lot->metro_id));
    $w->writeElement('house', $this->translateHouseNumber($lot->address));
    $w->writeElement('block', $this->getAttr($lot->params, 'block'));
    $w->writeElement('building', $this->getAttr($lot->params, 'building'));
    $w->writeElement('estate', $this->getAttr($lot->params, 'estate'));
      $this->getMetroDistance($w, $lot);
    $w->writeElement('totalarea', $lot->area_from);
    $w->writeElement('livarea', (int)$this->getAttr($lot->params, 'about_floorspace'));
    $w->writeElement('kitarea', (int)$this->getAttr($lot->params, 'kitchen_area'));
    $w->writeElement('housetype', $this->getAttr($lot->params, 'buildtype'));
    if($lot->type == 'elitenew') {
      $w->writeElement('newbuild', '1');
    }
    else {
      $w->writeElement('newbuild', '0');
    }
    $w->startElement('addInf');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement();
    $w->writeElement('balcony', (int)(bool)$this->getAttr($lot->params, 'balconies'));
    $w->writeElement('type_bargain', in_array($lot->type, Lot::$_statuses_rent) ? 'аренда' : 'продажа');
    //$w->writeElement('telephony', 1);
    $w->writeElement('bathroom', $this->translateBathroom($lot));
    $w->writeElement('mortgage',  (int)(bool)$this->getAttr($lot->params, 'the_possibility_of_a_mortgage'));
    $w->writeElement('phone', $this->_phones[$lot->type]);
    if($lot->broker_phone){
      $w->writeElement('phone', $lot->broker_phone);
    }
    $w->writeElement('contactname', $this->_org_name);
    $w->writeElement('firm', $this->_org_name);
    $w->writeElement('email', $this->_org_email);
    $w->writeElement('web', $this->_domain);
    if((int)$lot->price_all_from > '0'){
        $w->writeElement('price_int', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
      }
     else if((int)$lot->price_all_to > '0'){
         $w->writeElement('price_int', (int)Currency::convert($lot->price_all_to, $lot->currency, 'RUR'));
      }
    $w->writeElement('price_valuta', 'RUR');
    $w->writeElement('room_count_all_int', (int)$this->getAttr($lot->params, 'rooms'));
    $w->writeElement('floor_int', (int)$this->getAttr($lot->params, 'about_floor'));
    $w->writeElement('floor_all_int', (int)$this->getAttr($lot->params, 'floors'));
      $this->addPhotoes($w, $lot);
    $w->writeElement('url_offer', sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
    $w->writeElement('pro', 0);
  }

  protected function doNewbuilds(XMLWriter $w, $lot)
  {
    $w->writeElement('object_id', $lot->id);
      $this->getRubrId($w, $lot);
    $w->writeElement('apartm', $this->translateObjectAndPart($lot, 'object'));
    if ($lot->type == 'country') {
      $w->writeElement('region', 'Московская обл.');
    }
    else {
      $w->writeElement('region', 'Москва');
    }
    $w->writeElement('district', $this->translateDistrict($lot));
    $w->writeElement('town', $this->getAttr($lot->params, 'city'));
    $w->writeElement('houseAdr', $lot->address['street']);
    $w->writeElement('metro', $this->getMetro($lot->metro_id));
    $w->writeElement('house', $this->translateHouseNumber($lot->address));
    $w->writeElement('block', $this->getAttr($lot->params, 'block'));
    $w->writeElement('building', $this->getAttr($lot->params, 'building'));
    $w->writeElement('estate', $this->getAttr($lot->params, 'estate'));
      $this->getMetroDistance($w, $lot);
    $w->writeElement('totalarea', $lot->area_from);
    $w->writeElement('livarea', (int)$this->getAttr($lot->params, 'about_floorspace'));
    $w->writeElement('kitarea', (int)$this->getAttr($lot->params, 'kitchen_area'));
    $w->writeElement('housetype', $this->getAttr($lot->params, 'buildtype'));
    $w->writeElement('newbuild', '1');
    $w->startElement('addInf');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement();
    $w->writeElement('balcony', (int)(bool)$this->getAttr($lot->params, 'balconies'));
    $w->writeElement('type_bargain', in_array($lot->type, Lot::$_statuses_rent) ? 'аренда' : 'продажа');
    //$w->writeElement('telephony', 1);
    $w->writeElement('bathroom', $this->translateBathroom($lot));
    $w->writeElement('mortgage',  (int)(bool)$this->getAttr($lot->params, 'the_possibility_of_a_mortgage'));
    $w->writeElement('phone', $this->_phones[$lot->type]);
    if($lot->broker_phone){
      $w->writeElement('phone', $lot->broker_phone);
    }
    $w->writeElement('contactname', $this->_org_name);
    $w->writeElement('firm', $this->_org_name);
    $w->writeElement('email', $this->_org_email);
    $w->writeElement('web', $this->_domain);
     if((int)$lot->price_all_from > '0'){
        $w->writeElement('price_int', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
      }
     else if((int)$lot->price_all_to > '0'){
         $w->writeElement('price_int', (int)Currency::convert($lot->price_all_to, $lot->currency, 'RUR'));
      }
    $w->writeElement('price_valuta', 'RUR');
    $w->writeElement('room_count_all_int', (int)$this->getAttr($lot->params, 'rooms'));
    $w->writeElement('floor_int', $this->getAttr($lot->params, 'floors'));
    $w->writeElement('floor_all_int', $this->getAttr($lot->params, 'about_floor'));
      $this->addPhotoes($w, $lot);
    $w->writeElement('url_offer', sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
    $w->writeElement('pro', 0);
  }

  protected function doCommerce(XMLWriter $w, $lot)
  {
    $w->writeElement('object_id', $lot->id);
      $this->getRubrId($w, $lot);
    $w->writeElement('apartm', $this->translateObjectAndPart($lot, 'object'));
    $w->writeElement('region', 'Москва');
    $w->writeElement('district', $this->translateDistrict($lot));
    $w->writeElement('town', $this->getAttr($lot->params, 'city'));
    $w->writeElement('houseadr', $this->getFullAddress($lot));
    $w->writeElement('metro', $this->getMetro($lot->metro_id));
      $this->getMetroDistance($w, $lot);
    $w->startElement('addInf');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement();
    $w->writeElement('totalarea', $lot->area_from);
    $w->writeElement('type_bargain', in_array($lot->type, Lot::$_statuses_rent) ? 'аренда' : 'продажа');
    //$w->writeElement('telephony', 1);
    $w->writeElement('parking', $this->getAttr($lot->params, 'parking'));
    //$w->writeElement('internet', 1);

    //$w->writeElement('private', 1);
    $w->writeElement('phone', $this->_phones[$lot->type]);
    if($lot->broker_phone){
      $w->writeElement('phone', $lot->broker_phone);
    }
    $w->writeElement('contactname', $this->_org_name);
    $w->writeElement('firm', $this->_org_name);
    $w->writeElement('email', $this->_org_email);
    $w->writeElement('web', $this->_domain);
    if ($lot->type == 'comrent'){
      if((int)$lot->price_from > '0'){
        $w->writeElement('price_int', (int)Currency::convert($lot->price_from, $lot->currency, 'RUR'));
      }
     else if((int)$lot->price_to > '0'){
         $w->writeElement('price_int', (int)Currency::convert($lot->price_to, $lot->currency, 'RUR'));
      }
    }else{
      if((int)$lot->price_all_from > '0'){
        $w->writeElement('price_int', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
      }
     else if((int)$lot->price_all_to > '0'){
         $w->writeElement('price_int', (int)Currency::convert($lot->price_all_to, $lot->currency, 'RUR'));
      }
    }
    $w->writeElement('price_valuta', 'RUR');
      $this->addPhotoes($w, $lot);
    $w->writeElement('url_offer', sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
    $w->writeElement('pro', 0);
  }

  protected function doCountry(XMLWriter $w, $lot)
  {
    if($lot->params['objecttype'] == 'Квартира') {
        return $this->doFlats($w, $lot);
    }
    $w->writeElement('object_id', $lot->id);
      $this->getRubrId($w, $lot);
    $w->writeElement('apartm', $this->translateObjectAndPart($lot, 'object'));
    $w->startElement('addInf');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement();
    $w->writeElement('region', 'Московская обл.');
    $w->writeElement('district', $this->translateDistrict($lot));
    $w->writeElement('town', $this->getAttr($lot->params, 'city'));
    $w->writeElement('houseadr', $this->getFullAddress($lot));
    $w->writeElement('highway',  $this->getWard($lot->ward));
    $w->writeElement('distance_from_MKAD', $this->getAttr($lot->params, 'distance_mkad'));
    $w->writeElement('area_size', $lot->area_from);
    $w->writeElement('electric', $this->getAttr($lot->params, 'service_electricity'));
    $w->writeElement('Gas', $this->getAttr($lot->params, 'service_gas'));
    $w->writeElement('Water', $this->getAttr($lot->params, 'service_water'));
    $w->writeElement('Sewage', $this->getAttr($lot->params, 'service_drainage'));
    //$w->writeElement('internet', 1);
    $w->writeElement('type_bargain', in_array($lot->type, Lot::$_statuses_rent) ? 'аренда' : 'продажа');
    $w->writeElement('phone', $this->_phones[$lot->type]);
    if($lot->broker_phone){
      $w->writeElement('phone', $lot->broker_phone);
    }
    $w->writeElement('contactname', $this->_org_name);
    $w->writeElement('firm', $this->_org_name);
    $w->writeElement('email', $this->_org_email);
    $w->writeElement('web', $this->_domain);
    if((int)$lot->price_all_from > '0'){
        $w->writeElement('price_int', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
      }
    else if((int)$lot->price_all_to > '0'){
        $w->writeElement('price_int', (int)Currency::convert($lot->price_all_to, $lot->currency, 'RUR'));
      }
    $w->writeElement('price_valuta', 'RUR');
      $this->addPhotoes($w, $lot);
    $w->writeElement('url_offer', sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
    $w->writeElement('pro', 0);
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
      return (int)$bathrooms.'с/у';
    }
    elseif(($bathrooms_s == 0 && $bathrooms_u == 1) || (mb_stristr($bathrooms, 'совме', null, 'utf-8') && !mb_stristr($bathrooms, 'разд', null, 'utf-8') )) {
      return 'совм.';
    }
    else {
      return 'разд';
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

  private function translateCommerceType($type)
  {
    $table = array(
      'Торговое помещение'              => 'T',
      'Офисное помещение'               => 'O',
      'Склад/складской комплекс'        => 'W',
    );

    return isset($table[$type]) ? $table[$type] : 'F';
  }

  private function getRubrId(XMLWriter $w, Lot $lot)
  {
    $rubr = 1;

    switch($lot->type) {
      case 'eliteflat':
      case 'penthouse':
        $rubr = 1;
        break;

      case 'flatrent':
        $rubr = '4.1';
        break;

      case 'comrent':
      case 'comsell':
        $rubr = '7.1';
        break;

      case 'outoftown':
        switch ($lot->params['objecttype']) {
          case 'Кваритра':
            $rubr = 2;
          break;
          case 'Участок':
            $rubr = '3.2';
          break;
          default:
            $rubr = '3.1';
        }
        break;

      case 'cottage':
        $rubr = '5.2';
        break;
    }
    $w->writeElement('rubr_id', $rubr);

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
    if($value = $this->getAttr($lot->params, 'distance_metro')) {
      $w->writeElement('time', (int)$value);
      if(mb_stripos($value, 'пешк', null, 'utf-8')!== false ||  mb_stripos($value, 'м.п', null, 'utf-8')!== false) {
        $w->writeElement('timerea', 'П');
      }
      else {
        $w->writeElement('timerea', 'Т');
      }
    }
    elseif($lot->is_country_type && $value = $this->getAttr($lot->params, 'distance_mkad')) {
      $w->writeElement('distance', (int)$value);
      $w->writeElement('distancetype', 'км от МКАД');
    }
  }

  private function getWard($id)
  {
    $wards = sfConfig::get('app_wards');
    return !isset($wards[$id]) ?: $wards[$id];
  }

  private function addPhotoes(XMLWriter $w, Lot $lot)
  {
    $photoStr = '';
    $sep = CHR(13);
    if($photo = $lot->getImage('pres')){ // main image
       $photoStr .= $this->_domain . $photo . $sep;
    }
    foreach($lot->Photos as $photo) { // other images
      !$photo->is_pdf && !$photo->is_xls && $photoStr .= ($this->_domain . $photo->getImage('full'). $sep);
    }
    $w->writeElement('photoweb', $photoStr);
  }
}
