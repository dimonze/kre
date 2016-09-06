<?php

class realestateXmlTask extends sfBaseTask
{
  protected
    $_org_name =  'Contact Real Estate',
    $_org_email = 'kre@kre.ru',
    $_domain    = 'http://www.kre.ru',
    $_partner   = 'realestate',
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
        'Торговое помещение'              => 'Торгово-офисный комплекс',
        'Офисное помещение'               => 'Торгово-офисный комплекс',
        'Отдельно стоящее здание'         => 'Отдельно стоящее строение',
        'Готовый арендный бизнес'         => 'Торговый центр',
        'Особняк'                         => 'Отдельно стоящее строение',
        'Помещение свободного назначения' => 'Административное здание',
        'Склад/складской комплекс'        => 'Торгово-офисный комплекс',
        'Промышленный комплекс'           => 'производство',
        'Земельный участок'               => 'Рынок/Ярмарка',
        'Прочее'                          => 'Административное здание',
      ),
      'country' => array(
        'Участок'            => 'Отдельный участок земли',
        'Таунхаус'           => 'таунхаус',
        'Коттедж'            => 'коттедж',
        'Коттеджный поселок' => 'коттеджный поселок',
        'Квартира'           => 'квартира',
      ),
    ),

    $_phones = array(
      'eliteflat' => '(495)956-7799',
      'penthouse' => '(495)956-7799',
      'elitenew'  => '(495)956-7799',
      'outoftown' => '(495)956-6056',
      'cottage'   => '(495)956-6056',
      'comrent'   => '(495)956-3797',
      'comsell'   => '(495)956-3797',
    );



  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('type',  sfCommandArgument::OPTIONAL, 'Data type', array('flats', 'newbuilds', 'commerce', 'country')),
      new sfCommandArgument('page',  sfCommandArgument::OPTIONAL, 'Page (for multi-file output)',  1),
      new sfCommandArgument('limit', sfCommandArgument::OPTIONAL, 'Limit (for multi-file output)', 0),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = '';
    $this->name             = 'realestateXml';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [realestateXml|INFO] task does things.
Call it with:

  [php symfony realestateXml|INFO]
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
        $w->startElement('ForRealEstate');
          $w->startAttribute('FromUserId');
            $w->Text('Contact Real Estate');
          $w->endAttribute();//FromUserId
          $w->startAttribute('currency');
            $w->Text('RUR');
          $w->endAttribute();//currency
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
              $w->startElement('Estate');
                $w->startAttribute('type');
                  $w->Text($this->getRubrName($w, $lot));
                $w->endAttribute();//type
                // !!! Rock-n-roll begins here!
                $this->$method($w, $lot);
              $w->endElement();//Estate
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

          $w->endElement();//ForRealEstate
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
      $w->startElement('ExternalId');
        $w->Text($lot->id);
      $w->endElement();//ExternalId
      $w->startElement('Name');
        $w->writeCdata($this->prepareText($lot->name, $lot->id));
      $w->endElement();//Name
      $w->startElement('Description');
        $w->writeCdata(exportBaseTask::getLotDescription($lot));
      $w->endElement();
      $w->startElement('Address');
        $w->Text($this->getAddress($lot));
      $w->endElement();//Address
      $w->startElement('Region');
        $w->Text($this->translateRegion($lot));
      $w->endElement();//Region
      $w->startElement('SubRegion');
        $w->Text($this->translateDistrict($lot));
      $w->endElement();//SubRegion
      $w->startElement('Metro');
        $w->Text($this->getMetro($lot->metro_id));
      $w->endElement();//Metro
      $this->getMetroDistance($w, $lot);
      $w->startElement('BuildingType');
        $w->Text($this->translateBuildType($lot));
      $w->endElement();//BuildingType
      $w->startElement('Floor');
        $w->Text((int)$this->getAttr($lot->params, 'about_floor'));
      $w->endElement();//Floor
      $w->startElement('Floors');
        $w->Text((int)$this->getAttr($lot->params, 'floors'));
      $w->endElement();//Floors
      $w->startElement('RoomsCount');
        $w->Text((int)$this->getAttr($lot->params, 'rooms'));
      $w->endElement();//RoomsCount
      $w->startElement('TotalArea');
        $w->Text((int)$lot->area_from);
      $w->endElement();//TotalArea
      $w->startElement('RoomsArea');
        $w->Text((int)$this->getAttr($lot->params, 'about_floorspace'));
      $w->endElement();//RoomsArea
      $w->startElement('KitchenArea');
        $w->Text((int)$this->getAttr($lot->params, 'kitchen_area'));
      $w->endElement();//KitchenArea
      $w->startElement('BathroomType');
        $w->Text($this->translateBathroom($lot));
      $w->endElement();//BathroomType
      if($lot->type == 'flatrent'){
        $w->startElement('PriceRent');
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endElement();//PriceRent
      }else{
        $w->startElement('PriceTotal');
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endElement();//PriceTotal
      }
      $w->startElement('Images');
        $this->addPhotoes($w, $lot);
      $w->endElement();//Images
  }

  protected function doNewbuilds(XMLWriter $w, $lot)
  {
    return $this->doFlats($w, $lot);
  }

  protected function doCommerce(XMLWriter $w, $lot)
  {
    	$w->startElement('ExternalId');
        $w->Text($lot->id);
      $w->endElement();//ExternalId
      $w->startElement('Name');
        $w->writeCdata($lot->name);
      $w->endElement();//Name
      $w->startElement('Description');
        $w->writeCdata(exportBaseTask::getLotDescription($lot));
      $w->endElement();
      $w->startElement('Address');
        $w->Text($this->getAddress($lot));
      $w->endElement();//Address
      $w->startElement('Region');
        $w->Text($this->translateRegion($lot));
      $w->endElement();//Region
      $w->startElement('Metro');
        $w->Text($this->getMetro($lot->metro_id));
      $w->endElement();//Metro
      $this->getMetroDistance($w, $lot);
      $w->startElement('Highway');
        $w->Text($this->getWard($lot->ward));
      $w->endElement();//Highway
      $this->translateOfficeRetailType($w, $lot, 'object');
      $w->startElement('BuildingClass');
        $w->Text($this->getAttr($lot->params, 'buildclass'));
      $w->endElement();//BuildingClass
      $w->startElement('TotalArea');
        $w->Text((int)$lot->area_from);
      $w->endElement();//TotalArea
      $w->startElement('RoomArea');
        $w->startElement('From');
         $w->Text((int)$lot->area_from);
        $w->endElement();//From
        $w->startElement('To');
          $w->Text((int)$lot->area_to);
        $w->endElement();//To
      $w->endElement();//RoomsArea
       $w->startElement('PricePerM2');
        $w->startElement('From');
         $w->Text((int)Currency::convert($lot->price_from, $lot->currency, 'RUR'));
        $w->endElement();//From
        $w->startElement('To');
        if ((int)Currency::convert($lot->price_to, $lot->currency, 'RUR') > 0){
          $w->Text((int)Currency::convert($lot->price_to, $lot->currency, 'RUR'));
        }else{
          $w->Text((int)Currency::convert($lot->price_from, $lot->currency, 'RUR'));
        }
        $w->endElement();//To
      $w->endElement();//PricePerM2
      $w->startElement('PriceTotal');
        $w->startElement('From');
         $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endElement();//From
        $w->startElement('To');
        if ((int)Currency::convert($lot->price_all_to, $lot->currency, 'RUR') > 0){
          $w->Text((int)Currency::convert($lot->price_all_to, $lot->currency, 'RUR'));
        }else{
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        }
        $w->endElement();//To
      $w->endElement();//PriceTotal
      $w->startElement('Floors');
        $w->Text((int)$this->getAttr($lot->params, 'floors'));
      $w->endElement();//Floors
      $w->startElement('Images');
        $this->addPhotoes($w, $lot);
      $w->endElement();//Images
  }

  protected function doCountry(XMLWriter $w, $lot)
  {
      $w->startElement('ExternalId');
        $w->Text($lot->id);
      $w->endElement();//ExternalId
      $w->startElement('Name');
        $w->writeCdata($lot->name);
      $w->endElement();//Name
      $w->startElement('Description');
        $w->writeCdata(exportBaseTask::getLotDescription($lot));
      $w->endElement();
      $w->startElement('Address');
        $w->Text($this->getAddress($lot));
      $w->endElement();//Address
      $w->startElement('StateRegion');
        $w->Text($lot->address['district']);
      $w->endElement();//StateRegion
      $w->startElement('StateDirection');
        $w->Text($this->wardToDirection($lot->ward));
      $w->endElement();//StateDirection
      $w->startElement('Highway');
        $w->Text($this->getWard($lot->ward));
      $w->endElement();//Highway
      $w->startElement('Metro');
        $w->Text($this->getMetro($lot->metro_id));
      $w->endElement();//Metro
      $this->getMetroDistance($w, $lot);
      if ($lot->params['objecttype'] == 'Коттеджный поселок'){
        $w->startElement('CottageStatusType');
          $w->Text($this->translateObjectAndPart($lot, 'object'));
        $w->endElement();//CottageStatusType
      }
      $w->startElement('CottageType');
        $w->Text($this->translateObjectAndPart($lot, 'object'));
      $w->endElement();//CottageType
      $w->startElement('CottageArea');
        $w->Text((int)$lot->area_from);
      $w->endElement();//CottageArea
      $w->startElement('LandArea');
        $w->Text((int)$this->getAttr($lot->params, 'spaceplot'));
      $w->endElement();//LandArea
      if ($lot->params['objecttype'] == 'Участок'){
        $w->startElement('LandType');
          $w->Text($this->translateObjectAndPart($lot, 'object'));
        $w->endElement();//LandType
        $w->startElement('PricePer100M2');
          $w->Text(round((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR')/(int)$this->getAttr($lot->params, 'spaceplot'), 2));
        $w->endElement();//PricePer100M2
        $w->startElement('TotalPrice');
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endElement();//TotalPrice
      }else if($lot->params['objecttype'] == 'Коттеджный поселок'){
        $w->startElement('TotalPrice');
          $w->startElement('From');
            $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
          $w->endElement();//From
          $w->startElement('To');
          if((int)Currency::convert($lot->price_all_to, $lot->currency, 'RUR') > 0){
            $w->Text((int)Currency::convert($lot->price_all_to, $lot->currency, 'RUR'));
          }else {
            $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
          }
          $w->endElement();//To
       $w->endElement();//TotalPrice  cottage
      }else if ($lot->type == 'cottage'){
        $w->startElement('PriceRent');
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endElement();//TotalPrice
      }else{
        $w->startElement('TotalPrice');
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endElement();//TotalPrice
      }
      $w->startElement('Images');
        $this->addPhotoes($w, $lot);
      $w->endElement();//Images
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
      return !isset($elem[$attr]) ?: $elem[$attr];
    }
    elseif(is_object($elem)) {
      return !isset($elem->$attr) ?: $elem->$attr;
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
      $w->startElement('Image');
        $w->startAttribute('description');
          $w->Text('main');
        $w->endAttribute();//description
        $w->startAttribute('isMain');
          $w->Text('True');
        $w->endAttribute();//isMain
        $w->Text($this->_domain . $photo);
      $w->endElement();//Image
    }
    foreach($lot->Photos as $photo) { // other images
      $w->startElement('Image');
        $w->startAttribute('description');
          $w->Text($photo->getImageName());
        $w->endAttribute();//description
        $w->startAttribute('isMain');
          $w->Text('False');
        $w->endAttribute();//isMain
        !$photo->is_pdf && !$photo->is_xls && $w->Text($this->_domain . $photo->getImage('full'));
      $w->endElement();//Image
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

  private function translateOfficeRetailType(XMLWriter $w, $lot, $what = 'object')
  {
    $data = array();
    switch($lot->type) {

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
    if ($lot->params['objecttype'] == 'Офисное помещение'){
     $w->startElement('OfficeType');
        $w->Text($data[$what]);
      $w->endElement();//OfficeType
    }else{
      $w->startElement('RetailType');
        $w->Text($data[$what]);
      $w->endElement();//OfficeType
    }
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

  private function translateRegion($lot)
  {
    if($lot->district_id){
      if($lot->district_id <= 19 || $lot->district_id == 31) {
        return 'Центральный';
      }
      elseif($lot->district_id == 30) {
        return 'Западный';
      }
      else {
        $districts = sfConfig::get('app_districts');
        $res = preg_split("/[\s,.]+/", (!isset($districts[$lot->district_id]) ?: $districts[$lot->district_id]), 2);
        return $res[0];
      }
    }
    elseif($lot->ward) {
      $wards = sfConfig::get('app_wards');
      $res = preg_split("/[\s,.]+/", (!isset($wards[$lot->ward]) ?: $wards[$lot->ward]), 2);
      return $res[0];
    }
  }

  private function getRubrName(XMLWriter $w, Lot $lot)
  {
    switch($lot->type) {
      case 'elitenew':
      case 'penthouse':
      case 'eliteflat': return 'FlatSale';
      case 'flatrent': return 'FlatRent';
      case 'comrent':
        switch($lot->params['objecttype']){
        case 'Особняк': return 'RetailRent';
        case 'Склад/складской комплекс': return 'StoreRent';
        case 'Торговое помещение': return 'RetailSale';
        case 'Офисное помещение': return 'OfficeRent';
        default : return 'RetailRent';
        }
      case 'comsell':
        switch($lot->params['objecttype']){
        case 'Особняк': return 'RetailSale';
        case 'Прочее': return 'RetailSale';
        case 'Склад/складской комплекс': return 'StoreSale';
        case 'Торговое помещение': return 'RetailSale';
        case 'Офисное помещение': return 'OfficeSale';
        default : return 'RetailSale';
        }
      case 'outoftown':
          return $lot->params['objecttype'] == 'Участок' ? 'Land' : ($lot->params['objecttype'] == 'Коттеджный поселок' ? 'CottageVillage' : 'CottageSale');
      case 'cottage':
          return 'CottageRent';
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
   return implode(', ', $addr) . ',' . $this->translateHouseNumber($lot->address);

  }

  private function getAddress($lot)
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
   return implode(', ', $addr) . ',' . $this->translateHouseNumber($lot->address);

  }

  private function getMetroDistance(XMLWriter $w, Lot $lot)
  {

    if($lot->is_country_type && $distance = $this->getAttr($lot->params, 'distance_mkad')) {
      $w->writeElement('DistanceFromMKAD', (int)$distance);
    }
    elseif($value = $this->getAttr($lot->params, 'distance_metro')) {
      $w->startElement('TimeFromMetro');
      if(mb_stripos($value, 'пешк', null, 'utf-8')!== false ||  mb_stripos($value, 'м.п', null, 'utf-8')!== false) {
        $w->startAttribute('byTransport');
          $w->Text('False');
        $w->endAttribute();//byTransport
      }
      else {
        $w->startAttribute('byTransport');
          $w->Text('True');
        $w->endAttribute();//byTransport
      }
        $w->Text((int)$value);
      $w->endElement();//TimeFromMetro
    }

  }

  private function getWard($id)
  {
    $wards = sfConfig::get('app_wards');
    return (!isset($wards[$id]) ?: $wards[$id]) . ' шоссе';
  }

  public function wardToDirection($ward)
{
  $map = array(
    'Север'         => array(1,6,19,21),
    'Северо-Восток' => array(26,27),
    'Восток'        => array(5,7,18),
    'Юго-Восток'    => array(17,23),
    'Юг'            => array(3,10,24),
    'Юго-Запад'     => array(2,9,11,25),
    'Запад'         => array(8,14,15,22),
    'Северо-Запад'  => array(4,12,13,16,20),
  );

  foreach($map as $direction => $wards) {
    if(in_array($ward, $wards)) {
      return $direction;
    }
  }

  return false;
}

  private function addArea(XMLWriter $w, Lot $lot)
  {

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

  private function translateBuildType($lot)
  {

    switch ($this->getAttr($lot->params, 'buildtype')) {
      case 'Жилой дом':
      case 'жилой дом, новое строительство':
      case'Особняк':
      case 'многофункциональный комплекс':
      case 'Клубный дом':
      case 'Современный':
      case 'сталинский':
      case 'Евро дом':
      case 'Новый':
      case 'Уникальный дом - памятник в Москве (построен по проекту архитекторов Сергея Ткаченко и Олега Дубровского)':
      case 'ЖК «Спутник»':
      case 'ЖК':
      case 'кирпич':
        return '(не указан)';
      case 'Элитный дом, класс В':
      case 'Новый элитный дом.':
        return 'Премиум';
      case 'Жилой комплекс класса  De Luxe':
      case 'De Luxe':
      case 'дом класса А':
        return 'De Luxe';
      case 'Полная реконструкция':
        return 'Реконструкция';
      case 'Дореволюционный особняк.':
      case 'Отреставрированный, исторический особняк':
      case 'Памятник архитектуры. Бывший доходный дом':
        return 'Старинный';
      case 'дом ЦК':
        return 'Ведомственный(40х-90х)';
      default:
        return '(не указан)';
    }
  }

}


