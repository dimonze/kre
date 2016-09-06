<?php

class maindorXmlTask extends sfBaseTask
{
  protected
    $_org_name =  'Contact Real Estate',
    $_org_email = 'kre@kre.ru',
    $_domain    = 'http://www.kre.ru',
    $_partner   = 'maindoor',
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
    $this->name             = 'maindoorXml';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [maindoorXml|INFO] task does things.
Call it with:

  [php symfony maindoorXml|INFO]
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
        $w->startElement('maindoor');
          $w->startElement('objects');
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
                $w->startElement('object');
                  // !!! Rock-n-roll begins here!
                  $this->$method($w, $lot);
                $w->endElement();//object
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
          $w->endElement();//objects
        $w->endElement();//maindoor
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
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Название на русском');
          $w->endAttribute();//name
          $w->startAttribute('code');
            $w->Text('name_ru');
          $w->endAttribute();//code
        $w->Text($this->prepareText($lot->name, $lot->id));
      $w->endElement();//param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Внутренний id объекта на русском');
          $w->endAttribute();//name
          $w->startAttribute('code');
            $w->Text('md_ref_id_ru');
          $w->endAttribute();//code
        $w->Text($lot->id);
      $w->endElement();//param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Координаты на карте');
          $w->endAttribute();//name
          $w->startAttribute('code');
            $w->Text('map');
          $w->endAttribute();//code
        $w->Text($this->coords($lot->lat, $lot->lng));
      $w->endElement();//param
			if($lot->params['objecttype'] == 'flatrent'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Цена (аренда)');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('price_rent');
            $w->endAttribute();//code
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endElement();//param
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Валюта (аренда)');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('currency_rent');
            $w->endAttribute();//code
          $w->Text('Рубли');
        $w->endElement();//param
      }else{
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Цена');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('price');
            $w->endAttribute();//code
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endElement();//param
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Валюта');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('currency');
            $w->endAttribute();//code
          $w->Text('Рубли');
        $w->endElement();//param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Цена показывается по запросу?');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('price_on_request');
          $w->endAttribute(); //code
        $w->Text('нет');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Общая площадь (м2)');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('area');
          $w->endAttribute(); //code
        $w->Text((int)$lot->area_from);
      $w->endElement(); //param
      if($lot->params['objecttype'] == 'Участок'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Общая площадь (м2)');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('area');
            $w->endAttribute(); //code
          $w->Text((int)$this->getAttr($lot->params, 'spaceplot'));
        $w->endElement(); //param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Подробное описание на русском');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('detail_text_ru');
          $w->endAttribute(); //code
        $w->Text(exportBaseTask::getLotDescription($lot));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Текст анонса на русском');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('preview_text_ru');
          $w->endAttribute(); //code
        $w->Text(exportBaseTask::getLotDescription($lot));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Страна');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('Страна');
          $w->endAttribute(); //code
        $w->Text('411');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Регион');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('region');
          $w->endAttribute(); //code
        $w->Text('79948');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Город');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('city');
          $w->endAttribute(); //code
        $w->Text('3133');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Агенство');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('company');
          $w->endAttribute(); //code
        $w->Text($this->_org_name);
      $w->endElement(); //param
      if($this->translateObjectAndPart($lot, 'object') == 'квартира'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Количество спален');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('bedroom_count');
            $w->endAttribute(); //code
          $w->Text($this->getAttr($lot->params, 'about_count_bedrooms'));
        $w->endElement(); //param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Тип недвижимости');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('realty_type');
          $w->endAttribute(); //code
        $w->Text($this->translateObjectAndPart($lot, 'object'));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Вид недвижимости');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('realty_form');
          $w->endAttribute(); //code
      if($lot->params['market'] == 'Вторичный'){
        $w->Text('75');
      }else{
        $w->Text('76');
      }
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Расположение');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('realty_location');
          $w->endAttribute(); //code
        $w->Text('80');
      $w->endElement(); //param
      if($lot->params['objecttype'] == 'flatrent'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Тип предложения');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('section_id');
            $w->endAttribute(); //code
          $w->Text('18');
        $w->endElement(); //param
      }else{
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Тип предложения');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('section_id');
            $w->endAttribute(); //code
          $w->Text('17');
        $w->endElement(); //param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Адрес на русском');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('address_ru');
          $w->endAttribute(); //code
        $w->Text($this->getFullAddress($lot));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Рекламируется?');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('advertised');
          $w->endAttribute(); //code
        $w->Text('нет');
      $w->endElement(); //param
      $this->addPhotoes($w, $lot);

  }

  protected function doNewbuilds(XMLWriter $w, $lot)
  {
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Название на русском');
          $w->endAttribute();//name
          $w->startAttribute('code');
            $w->Text('name_ru');
          $w->endAttribute();//code
        $w->Text($this->prepareText($lot->name, $lot->id));
      $w->endElement();//param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Внутренний id объекта на русском');
          $w->endAttribute();//name
          $w->startAttribute('code');
            $w->Text('md_ref_id_ru');
          $w->endAttribute();//code
        $w->Text($lot->id);
      $w->endElement();//param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Координаты на карте');
          $w->endAttribute();//name
          $w->startAttribute('code');
            $w->Text('map');
          $w->endAttribute();//code
        $w->Text($this->coords($lot->lat, $lot->lng));
      $w->endElement();//param
			if($lot->params['objecttype'] == 'flatrent'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Цена (аренда)');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('price_rent');
            $w->endAttribute();//code
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endElement();//param
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Валюта (аренда)');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('currency_rent');
            $w->endAttribute();//code
          $w->Text('Рубли');
        $w->endElement();//param
      }else{
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Цена');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('price');
            $w->endAttribute();//code
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endElement();//param
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Валюта');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('currency');
            $w->endAttribute();//code
          $w->Text('Рубли');
        $w->endElement();//param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Цена показывается по запросу?');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('price_on_request');
          $w->endAttribute(); //code
        $w->Text('нет');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Общая площадь (м2)');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('area');
          $w->endAttribute(); //code
        $w->Text((int)$lot->area_from);
      $w->endElement(); //param
      if($lot->params['objecttype'] == 'Участок'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Общая площадь (м2)');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('area');
            $w->endAttribute(); //code
          $w->Text((int)$this->getAttr($lot->params, 'spaceplot'));
        $w->endElement(); //param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Подробное описание на русском');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('detail_text_ru');
          $w->endAttribute(); //code
        $w->Text(exportBaseTask::getLotDescription($lot));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Текст анонса на русском');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('preview_text_ru');
          $w->endAttribute(); //code
        $w->Text(exportBaseTask::getLotDescription($lot));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Страна');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('Страна');
          $w->endAttribute(); //code
        $w->Text('411');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Регион');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('region');
          $w->endAttribute(); //code
        $w->Text('79948');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Город');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('city');
          $w->endAttribute(); //code
        $w->Text('3133');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Агенство');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('company');
          $w->endAttribute(); //code
        $w->Text($this->_org_name);
      $w->endElement(); //param
      if($this->translateObjectAndPart($lot, 'object') == 'квартира'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Количество спален');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('bedroom_count');
            $w->endAttribute(); //code
          $w->Text($this->getAttr($lot->params, 'about_count_bedrooms'));
        $w->endElement(); //param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Тип недвижимости');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('realty_type');
          $w->endAttribute(); //code
        $w->Text($this->translateObjectAndPart($lot, 'object'));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Вид недвижимости');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('realty_form');
          $w->endAttribute(); //code
        $w->Text('76');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Расположение');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('realty_location');
          $w->endAttribute(); //code
        $w->Text('80');
      $w->endElement(); //param
      if($lot->params['objecttype'] == 'flatrent'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Тип предложения');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('section_id');
            $w->endAttribute(); //code
          $w->Text('18');
        $w->endElement(); //param
      }else{
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Тип предложения');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('section_id');
            $w->endAttribute(); //code
          $w->Text('17');
        $w->endElement(); //param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Адрес на русском');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('address_ru');
          $w->endAttribute(); //code
        $w->Text($this->getFullAddress($lot));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Рекламируется?');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('advertised');
          $w->endAttribute(); //code
        $w->Text('нет');
      $w->endElement(); //param
      $this->addPhotoes($w, $lot);
  }

  protected function doCommerce(XMLWriter $w, $lot)
  {
     $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Название на русском');
          $w->endAttribute();//name
          $w->startAttribute('code');
            $w->Text('name_ru');
          $w->endAttribute();//code
        $w->Text($this->prepareText($lot->name, $lot->id));
      $w->endElement();//param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Внутренний id объекта на русском');
          $w->endAttribute();//name
          $w->startAttribute('code');
            $w->Text('md_ref_id_ru');
          $w->endAttribute();//code
        $w->Text($lot->id);
      $w->endElement();//param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Координаты на карте');
          $w->endAttribute();//name
          $w->startAttribute('code');
            $w->Text('map');
          $w->endAttribute();//code
        $w->Text($this->coords($lot->lat, $lot->lng));
      $w->endElement();//param
			if($lot->params['objecttype'] == 'flatrent'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Цена (аренда)');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('price_rent');
            $w->endAttribute();//code
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endElement();//param
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Валюта (аренда)');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('currency_rent');
            $w->endAttribute();//code
          $w->Text('Рубли');
        $w->endElement();//param
      }else{
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Цена');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('price');
            $w->endAttribute();//code
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endElement();//param
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Валюта');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('currency');
            $w->endAttribute();//code
          $w->Text('Рубли');
        $w->endElement();//param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Цена показывается по запросу?');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('price_on_request');
          $w->endAttribute(); //code
        $w->Text('нет');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Общая площадь (м2)');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('area');
          $w->endAttribute(); //code
        $w->Text((int)$lot->area_from);
      $w->endElement(); //param
      if($lot->params['objecttype'] == 'Участок'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Общая площадь (м2)');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('area');
            $w->endAttribute(); //code
          $w->Text((int)$this->getAttr($lot->params, 'spaceplot'));
        $w->endElement(); //param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Подробное описание на русском');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('detail_text_ru');
          $w->endAttribute(); //code
        $w->Text(exportBaseTask::getLotDescription($lot));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Текст анонса на русском');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('preview_text_ru');
          $w->endAttribute(); //code
        $w->Text(exportBaseTask::getLotDescription($lot));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Страна');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('Страна');
          $w->endAttribute(); //code
        $w->Text('411');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Регион');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('region');
          $w->endAttribute(); //code
        $w->Text('79948');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Город');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('city');
          $w->endAttribute(); //code
        $w->Text('3133');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Агенство');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('company');
          $w->endAttribute(); //code
        $w->Text($this->_org_name);
      $w->endElement(); //param
      if($this->translateObjectAndPart($lot, 'object') == 'квартира'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Количество спален');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('bedroom_count');
            $w->endAttribute(); //code
          $w->Text($this->getAttr($lot->params, 'about_count_bedrooms'));
        $w->endElement(); //param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Тип недвижимости');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('realty_type');
          $w->endAttribute(); //code
        $w->Text($this->translateObjectAndPart($lot, 'object'));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Вид недвижимости');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('realty_form');
          $w->endAttribute(); //code
      if($lot->params['market'] == 'Вторичный'){
        $w->Text('75');
      }else{
        $w->Text('76');
      }
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Расположение');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('realty_location');
          $w->endAttribute(); //code
        $w->Text('80');
      $w->endElement(); //param
      if($lot->params['objecttype'] == 'commrent'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Тип предложения');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('section_id');
            $w->endAttribute(); //code
          $w->Text('18');
        $w->endElement(); //param
      }else{
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Тип предложения');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('section_id');
            $w->endAttribute(); //code
          $w->Text('17');
        $w->endElement(); //param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Адрес на русском');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('address_ru');
          $w->endAttribute(); //code
        $w->Text($this->getFullAddress($lot));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Рекламируется?');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('advertised');
          $w->endAttribute(); //code
        $w->Text('нет');
      $w->endElement(); //param
      $this->addPhotoes($w, $lot);
  }

  protected function doCountry(XMLWriter $w, $lot)
  {
         $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Название на русском');
          $w->endAttribute();//name
          $w->startAttribute('code');
            $w->Text('name_ru');
          $w->endAttribute();//code
        $w->Text($this->prepareText($lot->name, $lot->id));
      $w->endElement();//param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Внутренний id объекта на русском');
          $w->endAttribute();//name
          $w->startAttribute('code');
            $w->Text('md_ref_id_ru');
          $w->endAttribute();//code
        $w->Text($lot->id);
      $w->endElement();//param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Координаты на карте');
          $w->endAttribute();//name
          $w->startAttribute('code');
            $w->Text('map');
          $w->endAttribute();//code
        $w->Text($this->coords($lot->lat, $lot->lng));
      $w->endElement();//param
			if($lot->params['objecttype'] == 'flatrent'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Цена (аренда)');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('price_rent');
            $w->endAttribute();//code
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endElement();//param
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Валюта (аренда)');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('currency_rent');
            $w->endAttribute();//code
          $w->Text('Рубли');
        $w->endElement();//param
      }else{
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Цена');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('price');
            $w->endAttribute();//code
          $w->Text((int)Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        $w->endElement();//param
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Валюта');
            $w->endAttribute();//name
            $w->startAttribute('code');
              $w->Text('currency');
            $w->endAttribute();//code
          $w->Text('Рубли');
        $w->endElement();//param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Цена показывается по запросу?');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('price_on_request');
          $w->endAttribute(); //code
        $w->Text('нет');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Общая площадь (м2)');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('area');
          $w->endAttribute(); //code
        $w->Text((int)$lot->area_from);
      $w->endElement(); //param
      if($lot->params['objecttype'] == 'Участок'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Общая площадь (м2)');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('area');
            $w->endAttribute(); //code
          $w->Text((int)$this->getAttr($lot->params, 'spaceplot'));
        $w->endElement(); //param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Подробное описание на русском');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('detail_text_ru');
          $w->endAttribute(); //code
        $w->Text(exportBaseTask::getLotDescription($lot));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Текст анонса на русском');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('preview_text_ru');
          $w->endAttribute(); //code
        $w->Text(exportBaseTask::getLotDescription($lot));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Страна');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('Страна');
          $w->endAttribute(); //code
        $w->Text('411');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Регион');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('region');
          $w->endAttribute(); //code
        $w->Text('79948');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Город');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('city');
          $w->endAttribute(); //code
        $w->Text('3133');
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Агенство');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('company');
          $w->endAttribute(); //code
        $w->Text($this->_org_name);
      $w->endElement(); //param
      if($this->translateObjectAndPart($lot, 'object') == 'квартира'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Количество спален');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('bedroom_count');
            $w->endAttribute(); //code
          $w->Text($this->getAttr($lot->params, 'about_count_bedrooms'));
        $w->endElement(); //param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Тип недвижимости');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('realty_type');
          $w->endAttribute(); //code
        $w->Text($this->translateObjectAndPart($lot, 'object'));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Вид недвижимости');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('realty_form');
          $w->endAttribute(); //code
      if($lot->params['market'] == 'Вторичный'){
        $w->Text('75');
      }else{
        $w->Text('76');
      }
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Расположение');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('realty_location');
          $w->endAttribute(); //code
        $w->Text('80');
      $w->endElement(); //param
      if($lot->params['objecttype'] == 'cottage'){
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Тип предложения');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('section_id');
            $w->endAttribute(); //code
          $w->Text('18');
        $w->endElement(); //param
      }else{
        $w->startElement('param');
            $w->startAttribute('name');
              $w->Text('Тип предложения');
            $w->endAttribute(); //name
            $w->startAttribute('code');
              $w->Text('section_id');
            $w->endAttribute(); //code
          $w->Text('17');
        $w->endElement(); //param
      }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Адрес на русском');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('address_ru');
          $w->endAttribute(); //code
        $w->Text($this->getFullAddress($lot));
      $w->endElement(); //param
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Рекламируется?');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('advertised');
          $w->endAttribute(); //code
        $w->Text('нет');
      $w->endElement(); //param
      $this->addPhotoes($w, $lot);
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
      ->select('id')
      ->orderBy('new_object DESC');
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
    return $text . " [Лот #" . $add_text . '] ';
  }

  private function coords($text, $add_text = null)
  {
    return $text .',' . $add_text;
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
     switch($lot->type) {
      case 'elitenew': return '/real-estate/apartments-sale/new';
      case 'penthouse': return $lot->params['market'] != 'Вторичный' ? '/real-estate/apartments-sale/new' : '/real-estate/apartments-sale/secondary';
      case 'eliteflat': return $lot->params['market'] != 'Вторичный' ? '/real-estate/apartments-sale/new' : '/real-estate/apartments-sale/secondary';
      case 'flatrent': return '/real-estate/rent';
      case 'comrent':
        switch($lot->params['objecttype']){
        case 'Особняк': return '/real-estate/commercial-sale/houses';
        //case 'Особняк': return '/real-estate/commercial-sale/eating';
        case 'Склад/складской комплекс': return '/real-estate/commercial-sale/production-warehouses';
        case 'Торговое помещение': return '/real-estate/commercial-sale/retail';
        case 'Офисное помещение': return '/real-estate/commercial-sale/offices';
        }
      case 'comsell':
        switch($lot->params['objecttype']){
        case 'Особняк': return '/real-estate/commercial-sale/houses';
        case 'Прочее': return '/real-estate/commercial-sale/misc';
        //case 'Особняк': return '/real-estate/commercial-sale/eating';
        case 'Склад/складской комплекс': return '/real-estate/commercial-sale/production-warehouses';
        case 'Торговое помещение': return '/real-estate/commercial-sale/retail';
        case 'Офисное помещение': return '/real-estate/commercial-sale/offices';
        }
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

  private function addPhotoes(XMLWriter $w, Lot $lot)
  {
    $photoStr = '';
    $sep = ';';
    if($photo = $lot->getImage('pres')){ // main image
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Фото для анонса');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('preview_photo');
          $w->endAttribute(); //code
        $w->Text($photoStr .= $this->_domain . $photo . $sep);
      $w->endElement(); //param
    }
    foreach($lot->Photos as $photo) { // other images
      $tag = 'photoweb';
      !$photo->is_pdf && !$photo->is_xls && $photoStr .= ($this->_domain . $photo->getImage('full'). $sep);
    }
      $w->startElement('param');
          $w->startAttribute('name');
            $w->Text('Фотографии');
          $w->endAttribute(); //name
          $w->startAttribute('code');
            $w->Text('photos');
          $w->endAttribute(); //code
        $w->Text($photoStr);
      $w->endElement(); //param

  }
}
