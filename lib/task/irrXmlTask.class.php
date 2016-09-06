<?php

class irrXmlTask extends sfBaseTask
{
  protected
    $_org_name =  'Contact Real Estate',
    $_org_email = 'kre@kre.ru',
    $_domain    = 'http://www.kre.ru',
    $_partner   = 'irr',
    $_counters  = array(),

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
    $this->name             = 'irrXml';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [irrXml|INFO] task does things.
Call it with:

  [php symfony irrXml|INFO]
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


        if($type == 'flats' && $arguments['page'] == 1){
          file_put_contents(sfConfig::get('sf_web_dir').'/export/irr/count.txt', '0');
        }
        $totalCount = file_get_contents(sfConfig::get('sf_web_dir').'/export/irr/count.txt');

        $w = new XMLWriter();
        $w->openUri($filename);
        $w->setIndentString(str_repeat(" ", 2));
        $w->setIndent(true);
        $w->startDocument('1.0','utf-8');
        $w->startElement('users');
          $w->startElement('user');
             $w->startElement('match');
                $w->startElement('user-id');
                   $w->text('6324889');
                 $w->endElement();//user-id
                $w->endElement();//match
            $this->logSection('>>>', sprintf('I will use "%s" method', $method));

            foreach($ids as $id) {
              $totalCount++;
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
                  $w->startElement('store-ad');
                    $w->startAttribute('validfrom');
                      $w->text(date("Y-m-d\TH:i:s"));
                    $w->endAttribute();
                    $w->startAttribute('validtill');
                    if ($lot->id == '6770') {
                      $w->text(date("Y-m-d\TH:i:s", mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y")+10)));
                    }
                    else {
                        $w->text(date("Y-m-d\TH:i:s", mktime(date("H"), date("i"), date("s"), date("m")  , date("d")+2, date("Y"))));
                      }
                    $w->endAttribute();
                    $w->startAttribute('source-id');
                      $w->Text($lot->id);
                    $w->endAttribute();
                    $w->startAttribute('file-id');
                      $w->text($totalCount);
                    $w->endAttribute();
                    $w->startAttribute('category');
                      $w->text($this->getRubrName($w, $lot));
                    $w->endAttribute();
                    // !!! Rock-n-roll begins here!
                    $this->$method($w, $lot);
                  $w->endElement();//store-ad
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

          $w->endElement();//user
        $w->endElement();//users
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
      file_put_contents(sfConfig::get('sf_web_dir').'/export/irr/count.txt', $totalCount);
    }
  }

  protected function doFlats(XMLWriter $w, $lot)
  {
//    $w->startElement('title');
//      $w->writeCdata($this->prepareText($lot->anons, $lot->id));
//    $w->endElement();//title
    $w->startElement('description');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement();
    $w->startElement('custom-fields');
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('meters-total');
          $w->endAttribute();//name
        $w->Text($lot->area_from);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('meters-living');
          $w->endAttribute();//name
        $w->Text((int)$this->getAttr($lot->params, 'about_floorspace'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('kitchen');
          $w->endAttribute();//name
        $w->Text((int)$this->getAttr($lot->params, 'kitchen_area'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('housetype');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'buildtype'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('newbuild');
          $w->endAttribute();//name
        if($lot->type == 'elitenew') {
          $w->Text(1);
        }
        else {
          $w->Text(0);
        }
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('telephony');
          $w->endAttribute();//name
        $w->Text(1);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('internet');
          $w->endAttribute();//name
        $w->Text(1);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('region');
          $w->endAttribute();//name
        $w->Text('Москва');
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('address_city');
          $w->endAttribute();//name
        $w->Text('Москва');
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('address_street');
          $w->endAttribute();//name
        $w->Text($lot->address['street']);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('address_house');
          $w->endAttribute();//name
        $w->Text($this->translateHouseNumber($lot->address));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('balcony');
          $w->endAttribute();//name
        $w->Text((int)(bool)$this->getAttr($lot->params, 'balconies'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('toilet');
          $w->endAttribute();//name
        $w->Text($this->translateBathroom($lot));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('rooms');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'rooms'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('floors');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'floors'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('etage-all');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'about_floor'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('phone');
          $w->endAttribute();//name
        $w->Text($this->_phones[$lot->type]);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('contactname');
          $w->endAttribute();//name
        $w->Text($this->_org_name);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('firm');
          $w->endAttribute();//name
        $w->Text($this->_org_name);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('email');
          $w->endAttribute();//name
        $w->Text($this->_org_email);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('web');
          $w->endAttribute();//name
        $w->Text(sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
      $w->endElement();//field
    $w->endElement();//custom-fields
    $w->startElement('fotos');
      $this->addPhotoes($w, $lot);
    $w->endElement();//fotos
    $w->startElement('price');
        $w->startAttribute('currency');
          $w->Text('RUR');
        $w->endAttribute();//name
        $w->startAttribute('value');
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endAttribute();//name
    $w->endElement();//price

  }

  protected function doNewbuilds(XMLWriter $w, $lot)
  {
//   $w->startElement('title');
//      $w->writeCdata($this->prepareText($lot->combined_name, $lot->id));
//    $w->endElement();//title
    $w->startElement('description');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement();
    $w->startElement('custom-fields');
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('meters-total');
          $w->endAttribute();//name
        $w->Text($lot->area_from);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('meters-living');
          $w->endAttribute();//name
        $w->Text((int)$this->getAttr($lot->params, 'about_floorspace'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('kitchen');
          $w->endAttribute();//name
        $w->Text((int)$this->getAttr($lot->params, 'kitchen_area'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('housetype');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'buildtype'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('newbuild');
          $w->endAttribute();//name
        $w->Text(1);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('telephony');
          $w->endAttribute();//name
        $w->Text(1);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('internet');
          $w->endAttribute();//name
        $w->Text(1);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('region');
          $w->endAttribute();//name
        $w->Text('Москва');
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('address_city');
          $w->endAttribute();//name
        $w->Text('Москва');
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('address_street');
          $w->endAttribute();//name
        $w->Text($lot->address['street']);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('address_house');
          $w->endAttribute();//name
        $w->Text($this->translateHouseNumber($lot->address));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('balcony');
          $w->endAttribute();//name
        $w->Text((int)(bool)$this->getAttr($lot->params, 'balconies'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('toilet');
          $w->endAttribute();//name
        $w->Text($this->translateBathroom($lot));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('rooms');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'rooms'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('floors');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'floors'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('etage-all');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'about_floor'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('phone');
          $w->endAttribute();//name
        $w->Text($this->_phones[$lot->type]);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('contactname');
          $w->endAttribute();//name
        $w->Text($this->_org_name);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('firm');
          $w->endAttribute();//name
        $w->Text($this->_org_name);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('email');
          $w->endAttribute();//name
        $w->Text($this->_org_email);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('web');
          $w->endAttribute();//name
        $w->Text(sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
      $w->endElement();//field
    $w->endElement();//custom-fields
    $w->startElement('fotos');
      $this->addPhotoes($w, $lot);
    $w->endElement();//fotos
     $w->startElement('price');
        $w->startAttribute('currency');
          $w->Text('RUR');
        $w->endAttribute();//name
        $w->startAttribute('value');
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endAttribute();//name
    $w->endElement();//price
  }

  protected function doCommerce(XMLWriter $w, $lot)
  {
//    $w->startElement('title');
//      $w->writeCdata($this->translateObjectAndPart($lot, 'object').", ". $lot->name);
//    $w->endElement();//title
    $w->startElement('description');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement();
    $w->startElement('custom-fields');
     $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('object');
          $w->endAttribute();//name
        $w->Text($this->translateObjectAndPart($lot, 'object'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('square-min');
          $w->endAttribute();//name
        $w->Text($lot->area_from);
      $w->endElement();//field
      if($lot->type == 'comrent'){
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('rent_period');
          $w->endAttribute();//name
        $w->Text('год');
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('unit');
          $w->endAttribute();//name
        $w->Text('за кв.м');
      $w->endElement();//field
      }
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('housetype');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'buildtype'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('telephony');
          $w->endAttribute();//name
        $w->Text(1);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('internet');
          $w->endAttribute();//name
        $w->Text(1);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('cars');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'parking'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('region');
          $w->endAttribute();//name
        $w->Text('Москва');
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('address_city');
          $w->endAttribute();//name
        $w->Text('Москва');
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('etage-all');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'about_floor'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('phone');
          $w->endAttribute();//name
        $w->Text($this->_phones[$lot->type]);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('contactname');
          $w->endAttribute();//name
        $w->Text($this->_org_name);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('firm');
          $w->endAttribute();//name
        $w->Text($this->_org_name);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('email');
          $w->endAttribute();//name
        $w->Text($this->_org_email);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('web');
          $w->endAttribute();//name
        $w->Text(sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
      $w->endElement();//field
    $w->endElement();//custom-fields
    $w->startElement('fotos');
      $this->addPhotoes($w, $lot);
    $w->endElement();//fotos
     $w->startElement('price');
        $w->startAttribute('currency');
          $w->Text('RUR');
        $w->endAttribute();//name
        $w->startAttribute('value');
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endAttribute();//name
    $w->endElement();//price
  }

  protected function doCountry(XMLWriter $w, $lot)
  {
//    $w->startElement('title');
//      $w->writeCdata($this->prepareText($lot->anons, $lot->id));
//    $w->endElement();//title
    $w->startElement('description');
      $w->writeCdata(exportBaseTask::getLotDescription($lot));
    $w->endElement();
    $w->startElement('custom-fields');
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('object');
          $w->endAttribute();//name
        $w->Text($this->translateObjectAndPart($lot, 'object'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('meters-living');
          $w->endAttribute();//name
        $w->Text((int)$this->getAttr($lot->params, 'about_floorspace'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('kitchen');
          $w->endAttribute();//name
        $w->Text((int)$this->getAttr($lot->params, 'kitchen_area'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('housetype');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'buildtype'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('newbuild');
          $w->endAttribute();//name
        $w->Text(1);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('telephony');
          $w->endAttribute();//name
        $w->Text(1);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('internet');
          $w->endAttribute();//name
        $w->Text(1);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('region');
          $w->endAttribute();//name
        $w->Text('Московская');
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('address_city');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'city'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('address_street');
          $w->endAttribute();//name
        $w->Text($lot->address['street']);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('address_house');
          $w->endAttribute();//name
        $w->Text($this->translateHouseNumber($lot->address));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('balcony');
          $w->endAttribute();//name
        $w->Text((int)(bool)$this->getAttr($lot->params, 'balconies'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('toilet');
          $w->endAttribute();//name
        $w->Text($this->translateBathroom($lot));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('rooms');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'rooms'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('floors');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'floors'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('etage-all');
          $w->endAttribute();//name
        $w->Text($this->getAttr($lot->params, 'about_floor'));
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('phone');
          $w->endAttribute();//name
        $w->Text($this->_phones[$lot->type]);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('contactname');
          $w->endAttribute();//name
        $w->Text($this->_org_name);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('firm');
          $w->endAttribute();//name
        $w->Text($this->_org_name);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('email');
          $w->endAttribute();//name
        $w->Text($this->_org_email);
      $w->endElement();//field
      $w->startElement('field');
          $w->startAttribute('name');
            $w->Text('web');
          $w->endAttribute();//name
        $w->Text(sprintf('%s/offers/%s/%s/', $this->_domain, $lot->type, $lot->id));
      $w->endElement();//field
    $w->endElement();//custom-fields
    $w->startElement('fotos');
      $this->addPhotoes($w, $lot);
    $w->endElement();//fotos
     $w->startElement('price');
        $w->startAttribute('currency');
          $w->Text('RUR');
        $w->endAttribute();//name
        $w->startAttribute('value');
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endAttribute();//name
    $w->endElement();//price
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

  private function prepareText($text, $add_text = null)
  {
    $text = strip_tags($text);
    $text = preg_replace('/&[a-z0-9]+;/i', '', $text);
    return $text . " [Лот #" . $add_text . ']';
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

  private function getRubrName(XMLWriter $w, Lot $lot)
  {
    switch($lot->type) {
      case 'elitenew': return '/real-estate/apartments-sale/new';
      case 'penthouse': return $lot->params['market'] != 'Вторичный' ? '/real-estate/apartments-sale/new' : '/real-estate/apartments-sale/secondary';
      case 'eliteflat': return $lot->params['market'] != 'Вторичный' ? '/real-estate/apartments-sale/new' : '/real-estate/apartments-sale/secondary';
      case 'flatrent': return '/real-estate/rent';
      case 'comrent':
        switch($lot->params['objecttype']){
        case 'Особняк': return '/real-estate/commercial/houses';
        //case 'Особняк': return '/real-estate/commercial-sale/eating';
        case 'Склад/складской комплекс': return '/real-estate/commercial/production-warehouses';
        case 'Торговое помещение': return '/real-estate/commercial/retail';
        case 'Офисное помещение': return '/real-estate/commercial/offices';
        }
        return '/real-estate/commercial/misc';
      case 'comsell':
        switch($lot->params['objecttype']){
        case 'Особняк': return '/real-estate/commercial-sale/houses';
        case 'Прочее': return '/real-estate/commercial-sale/misc';
        //case 'Особняк': return '/real-estate/commercial-sale/eating';
        case 'Склад/складской комплекс': return '/real-estate/commercial-sale/production-warehouses';
        case 'Торговое помещение': return '/real-estate/commercial-sale/retail';
        case 'Офисное помещение': return '/real-estate/commercial-sale/offices';
        }
        return '/real-estate/commercial-sale/misc';
      case 'outoftown':
          return $lot->params['objecttype'] == 'Участок' ? '/real-estate/out-of-town/lands' : '/real-estate/out-of-town/houses';
      case 'cottage':
          return '/real-estate/out-of-town-rent';
    }
  }

  private function translateDistrict($lot)
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
    return !isset($wards[$id]) ?: $wards[$id];
  }

  private function getPhotoHash($imageName)
  {
    $imageName = ltrim($imageName, "/.");
    $file = fopen(sfConfig::get('sf_web_dir').'/md5_jpeg.list', "r"); // Открываем файл в режиме чтения
      if ($file){
        while (!feof($file))
        {
          $md5String = fgets($file, 999);
          if (stristr($md5String, $imageName)){
            fclose($file);
            $res = preg_split("/[\s,.]+/", $md5String, 5);
            return $res[4];
          }
        }
      }
    fclose($file);
    return 1;
  }

  private function addPhotoes(XMLWriter $w, Lot $lot)
  {
    if ($lot->getImage('pres')){
      $w->startElement('foto-remote');
        $w->startAttribute('url');
          $w->text($this->_domain . $lot->getImage('pres'));
        $w->endAttribute();
        $w->startAttribute('md5');
          $w->text($this->getPhotoHash($lot->getImage('pres')));
        $w->endAttribute();
      $w->endElement();
    }
    foreach($lot->Photos as $photo) {
      $w->startElement('foto-remote');
        $w->startAttribute('url');
          !$photo->is_pdf && !$photo->is_xls && $w->text($this->_domain . $photo->getImage('full'));
        $w->endAttribute();
        $w->startAttribute('md5');
          !$photo->is_pdf && !$photo->is_xls && $w->text($this->getPhotoHash($photo->getImage('full')));
        $w->endAttribute();
      $w->endElement();
    }
  }
}
