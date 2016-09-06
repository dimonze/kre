<?php

class rbkXmlTask extends sfBaseTask
{
  protected
    $_org_name =  'Contact Real Estate',
    $_org_email = 'kre@kre.ru',
    $_domain    = 'http://www.kre.ru',
    $_partner   = 'rbk',
    $_counters  = array(),

    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse','flatrent'),
      'newbuilds' => 'elitenew',
      'commerce'  => array('comrent', 'comsell'),
      'country'   => array('outoftown', 'cottage'),
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
    $this->name             = 'rbkXml';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [rbkXml|INFO] task does things.
Call it with:

  [php symfony rbkXml|INFO]
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
      $container = $type == 'commerce' ? 'country': $type; //Yes, I'm shocked too.
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
        $w->startElement('document');
          $w->writeComment(sprintf(' Generated at %s, contains not more %s lots ', date('Y-m-d H:i:s'), $i['total']));
          $w->startElement($container);
          $this->logSection('>>>', sprintf('I will use "%s" method', $method));

          foreach($ids as $id) {
            $i['current']++;
            $lot = Doctrine::getTable('Lot')->find($id);
            try {
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
                // !!! Rock-n-roll begins here!
                $this->$method($w, $lot);
              $i['good']++;
              $this->logSection($id, sprintf('added. %s of %s (%s%%)',
                $i['current'],
                $i['total'],
                round($i['current']/$i['total']*100, 2)
              ));
              // echo 'Before: ' . memory_get_usage() /1024 /1024 . ' Mb' . PHP_EOL;
            }
            catch (Exception $e){
              $this->logSection($id, sprintf('bad "%s" parameter', $e->getMessage()), null, 'ERROR');
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

          $w->endElement();//$container
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
    $bad = false;
    if(!isset($lot->params['about_floorspace']) || !($live = $this->getAttr($lot->params, 'about_floorspace'))){
      (int)$live = (int)$lot->area_from * 0.5;
    }
    if($bad) {
      throw new Exception($bad);
    }

    $w->startElement('offer');
    $w->writeElement('id', $lot->id);
    $w->writeElement('deal_type', $lot->type == 'flatrent' ? 'R' : 'S');
    $w->startElement('address');
      $w->startElement('region');
        $w->writeAttribute('type', 'M');
        $w->text('Москва');
      $w->endElement();//region
      $w->writeElement('metro',   $this->getMetro($lot->metro_id));
      $w->writeElement('street',  $this->getAttr($lot->address, 'street'));
      $w->writeElement('houseNo', $this->translateHouseNumber($lot->address));
    $w->endElement();//address
    $w->writeElement('price', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
    $w->writeElement('currency', 'RUR');
    $w->startElement('area');
      $w->writeElement('total', (int)$lot->area_from);
      $w->writeElement('live', (int)$live);
      if((int)$this->getAttr($lot->params, 'kitchen_area') > 1) {
        $w->writeElement('kitchen', (int)$this->getAttr($lot->params, 'kitchen_area'));
      }
    $w->endElement();//area
    $w->startElement('description');
//      $w->startElement('short');
//        $w->writeCdata($this->prepareText($lot->anons, $lot->id));
//      $w->endElement();//short
      $w->startElement('full');
        $w->writeCdata(exportBaseTask::getLotDescription($lot));
      $w->endElement();//full
    $w->endElement();//description
    $this->addPhotoes($w, $lot);
    $w->writeElement('rooms', $this->getAttr($lot->params, 'rooms'));
    $w->writeElement('floors_count', (int)$this->getAttr($lot->params, 'about_floor'));
    if((int)$this->getAttr($lot->params, 'floors') > 0 && (int)$this->getAttr($lot->params, 'floors') > (int)$this->getAttr($lot->params, 'about_floor')){
      $w->writeElement('floors', (int)$this->getAttr($lot->params, 'floors'));
    }
    $w->startElement('options');
      $w->writeElement('lift', $this->translateLift($lot));
      $w->writeElement('balcon', (int)(bool)$this->getAttr($lot->params, 'balconies'));
      $w->writeElement('loggia', (int)(bool)$this->getAttr($lot->params, 'number_of_loggias'));
      $w->writeElement('bathroom', $this->translateBathroom($lot));
    $w->endElement();//options
    $w->startElement('contact');
      $w->writeElement('name', $this->_org_name);
      $w->writeElement('info', $this->_phones[$lot->type]);
    $w->endElement();//contact
    $w->endElement();//offer
  }

  protected function doNewbuilds(XMLWriter $w, $lot)
  {
    $w->startElement('offer');
    $w->writeElement('id', $lot->id);
    $w->writeElement('id_house', $lot->id*10000);
    $w->startElement('address');
      $w->startElement('region');
        $w->writeAttribute('type', 'M');
        $w->text('Москва');
      $w->endElement();//region
      $w->writeElement('metro',   $this->getMetro($lot->metro_id));
      $w->writeElement('street',  $this->getAttr($lot->address, 'street'));
      $w->writeElement('houseNo', $this->translateHouseNumber($lot->address));
    $w->endElement();//address
    $w->startElement('price');
      $w->writeElement('from', (int)Currency::convert($lot->price_from, $lot->currency, 'RUR'));
      $w->writeElement('to', (int)Currency::convert($lot->price_to, $lot->currency, 'RUR'));
    $w->endElement();//price
    $w->writeElement('currency', 'RUR');
    $w->startElement('area');
      $w->writeElement('from', (int)$lot->area_from);
      $w->writeElement('to', (int)$lot->area_to);
    $w->endElement();//area
    $w->writeElement('rooms', $this->getAttr($lot->params, 'rooms'));
    $w->startElement('ending');
      $w->writeElement('year', $this->getAttr($lot->params, 'year'));
    $w->endElement();//ending
    $w->startElement('contact');
      $w->writeElement('name', $this->_org_name);
      $w->writeElement('info', $this->_phones[$lot->type]);
    $w->endElement();//contact
    $w->startElement('description');
//      $w->startElement('short');
//        $w->writeCdata($this->prepareText($lot->anons, $lot->id));
//      $w->endElement();//short
      $w->startElement('full');
        $w->writeCdata(exportBaseTask::getLotDescription($lot));
      $w->endElement();//full
    $w->endElement();//description
    $this->addPhotoes($w, $lot);
    $w->writeElement('flatcount', $this->getAttr($lot->params, 'flats'));
    $w->writeElement('ipoteka', (int)(bool)$this->getAttr($lot->params, 'the_possibility_of_a_mortgage'));
    $w->endElement();//offer
  }

  protected function doCommerce(XMLWriter $w, $lot)
  {
    $w->startElement('offer');
    $w->writeElement('id', $lot->id);
    $w->writeElement('deal_type', $lot->type == 'comrent' ? 'R' : 'S');
    $w->writeElement('commerce_type', $this->translateCommerceType($this->getAttr($lot->params, 'objecttype')));
    $w->startElement('address');
      $w->startElement('region');
        $w->writeAttribute('type', 'M');
        $w->text('Москва');
      $w->endElement();//region
      $w->writeElement('metro',   $this->getMetro($lot->metro_id));
      $w->writeElement('street',  $this->getAttr($lot->address, 'street'));
      $w->writeElement('houseNo', $this->translateHouseNumber($lot->address));
    $w->endElement();//address
    $w->startElement('price');
      $w->writeElement('total', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
      $w->writeElement('area', (int)Currency::convert($lot->price_from, $lot->currency, 'RUR'));
    $w->endElement();//price
    $w->writeElement('currency', 'RUR');
    $w->startElement('area');
      $w->writeElement('vacant', (int)$lot->area_from);
      $w->writeElement('total', (int)$lot->area_from);
    $w->endElement();//area
    $w->startElement('description');
      $w->startElement('short');
        $w->writeCdata(exportBaseTask::getLotDescription($lot));
      $w->endElement();//short
      $w->startElement('full');
        $w->writeCdata(exportBaseTask::getLotDescription($lot));
      $w->endElement();//full
    $w->endElement();//description
    $this->addPhotoes($w, $lot);
    $w->startElement('contact');
      $w->writeElement('name', $this->_org_name);
      $w->writeElement('info', $this->_phones[$lot->type]);
    $w->endElement();//contact
    $w->endElement();//offer
  }

  protected function doCountry(XMLWriter $w, $lot)
  {
    $w->startElement('offer');
    $w->writeElement('id', $lot->id);
    $w->writeElement('deal_type', $lot->type == 'cottage' ? 'R' : 'S');
    $w->writeElement('realty_type', $this->translateRealtyTypeCountry($this->getAttr($lot->params, 'objecttype')));
    $w->startElement('address');
      $w->startElement('region');
        $w->writeAttribute('type', 'R');
        $w->text('Московская область');
      $w->endElement();//region
      $w->writeElement('district', $this->getAttr($lot->params, 'locality'));
      $w->writeElement('highway',  $this->getWard($lot->ward));
      $w->writeElement('range',    $this->getAttr($lot->params, 'distance_mkad'));
//      $w->writeElement('street',   $this->getAttr($lot->address, 'street'));
//      $w->writeElement('houseNo',  $this->translateHouseNumber($lot->address));
    $w->endElement();//address
    $w->writeElement('price', (int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
    $w->writeElement('currency', 'RUR');
    $w->startElement('area');
      if($plot = (int)$this->getAttr($lot->params, 'spaceplot')) {
        $w->writeElement('plot', (int)$plot);
      }
      if($total = (int)$lot->area_from) {
        $w->writeElement('total', (int)$total);
      }
    $w->endElement();//area
    $w->startElement('description');
      $w->startElement('short');
        $w->writeCdata(exportBaseTask::getLotDescription($lot));
      $w->endElement();//short
      $w->startElement('full');
        $w->writeCdata(exportBaseTask::getLotDescription($lot));
      $w->endElement();//full
    $w->endElement();//description
    $this->addPhotoes($w, $lot);
    $w->startElement('contact');
    $w->writeElement('name', $this->_org_name);
    $w->writeElement('info', $this->_phones[$lot->type]);
    $w->endElement();//contact
    $w->endElement();//offer
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
    // This is fuck with memory_limit in production
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

  private function translateLift($lot) {
    return (int)(bool)(
      $this->getAttr($lot->params, 'lift')
      .(bool)$this->getAttr($lot->params, 'passenger_elevators')
      .(bool)$this->getAttr($lot->params, 'cargo_elevators'));
  }

  private function prepareText($text, $add_text = null)
  {
    $text = strip_tags($text);
    $text = preg_replace('/&[a-z0-9]+;/i', '', $text);
    return $text . " [Лот #" . $add_text . '] ';
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

  private function translateRealtyTypeCountry($type)
  {
    $table = array(
      'Участок' => 'A',
      'Таунхаус' => 'K',
      'Коттедж' => 'K',
      'Коттеджный поселок' => 'K',
    );
    return isset($table[$type]) ? $table[$type] : 'K';
  }

  private function getWard($id)
  {
    $wards = sfConfig::get('app_wards');
    return !isset($wards[$id]) ?: $wards[$id];
  }

  private function addPhotoes(XMLWriter $w, Lot $lot)
  {
    if($photo = $lot->getImage('pres')){ // main image
      $w->writeElement('photo', $this->_domain . $photo);
    }
    foreach($lot->Photos as $photo) { // other images
      $tag = $photo->photo_type_id == '3' ? 'plan' : 'photo';
      !$photo->is_pdf && !$photo->is_xls && $w->writeElement($tag, $this->_domain . $photo->getImage('full'));
    }
  }
}
