<?php

class cianXmlTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('type', sfCommandArgument::OPTIONAL, 'Data type', array('eliteflat', 'outoftown', 'comsell')),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = '';
    $this->name             = 'cianXml';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [cianXml|INFO] task does things.
Call it with:

  [php symfony cianXml|INFO]
EOF;

    $this->_data = array(
      'phones' => array(
        'eliteflat' => '74959567799',
        'penthouse' => '74959567799',
        'outoftown' => '74959566056',
        'cottage'   => '74959566056',
        'comsell'   => '74959563797',
      )
    );
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfConfig::set('sf_debug', false);
    ini_set('memory_limit', '2G');
    ini_set('max_execution_time', 0);
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    sfContext::createInstance(sfProjectConfiguration::getApplicationConfiguration('frontend', 'prod', true));
    Doctrine_Manager::connection()->setAttribute(Doctrine_Core::ATTR_AUTO_FREE_QUERY_OBJECTS, true);
    $this->connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $types = is_array($arguments['type']) ? $arguments['type'] : array($arguments['type']);
    foreach($types as $type) {
      $method = sprintf('make%sFile', ucfirst($type));
      if(is_callable(array($this, $method))) {
        $this->logSection('run', $method);
        $this->$method();
      }
      else {
        $this->logSection('not run', sprintf("method %s() does not exist", $method), null, 'ERROR');
      }
    }
  }

  protected function makeEliteflatFile()
  {
    $ids = Doctrine::getTable('Lot')->createQuery()
      ->andWhere('hide_price = ?')
      ->andWhere('price_all_from > ?')
      ->andWhere('exportable = ?')
      ->orderBy('priority desc')
      ->active()
      ->type(array('eliteflat', 'penthouse'))
      ->select('id')
      ->execute(array(0, 0, 1), Doctrine::HYDRATE_SINGLE_SCALAR);
    $sum = count($ids);
    $w = Tools::getXmlWriter('cian', 'eliteflat');
    $w->startDocument('1.0','windows-1251');
    $w->writeComment(sprintf(' Generated at %s, contains approx %s lots ', date('Y-m-d H:i:s'), $sum));
      $w->startElement('flats_for_sale');
      $i = 0;
      $j = 0;
      foreach($ids as $id) {
        $i++;
        $lot = Doctrine::getTable('Lot')->find($id);
        $bad = false;
        if(!isset($lot->params['construction']) || !($type = $this->translateTypeEliteflat($lot))) {
          $bad = 'construction';
        }

        if($bad) {
          var_dump($lot->id, $lot->params['construction']);
          $this->logSection($id, sprintf('bad %s. excluded.', $bad), null, 'ERROR');
          $lot->free(true);
          $j++;
          continue;
        }
        $this->logSection($id, sprintf('start. %s of %s (%s%%)', $i, $sum, round($i/$sum*100, 2)));
        $w->startElement('offer');
          $w->writeElement('id', $lot->id);
          $w->writeElement('rooms_num', $this->getParam($lot, 'rooms') > 6 ? 6 : (int)$this->getParam($lot, 'rooms'));
          $w->startElement('area');
            $w->writeAttribute('total', $lot->area_from);
            $w->writeAttribute('kitchen', (float)$this->getParam($lot, 'kitchen_area'));
            $w->writeAttribute('living', $this->toDouble($this->getParam($lot, 'about_floorspace')));
            $w->writeAttribute('rooms', $this->getParam($lot, 'area_of_each_room'));
          $w->endElement();//area
          $w->startElement('price');
            $w->writeAttribute('currency', $lot->currency);
            $w->text(preg_replace('/\D/', '', $lot->price_all_from));
          $w->endElement();//price
          $w->startElement('options');
            $w->writeAttribute('object_type', ($this->getParam($lot, 'market') ? $this->getParam($lot, 'market') : ($lot->pid ? 2 : 1)));
            $w->writeAttribute('sale_type','F');
            $w->writeAttribute('phone','yes');
            $w->writeAttribute('lift_p', $this->getParam($lot, 'passenger_elevators') ? $this->getParam($lot, 'passenger_elevators') : 0);
            $w->writeAttribute('lift_g', $this->getParam($lot, 'cargo_elevators') ? $this->getParam($lot, 'cargo_elevators') : 0);
            $w->writeAttribute('balcon', $this->getParam($lot, 'balconies') ? $this->getParam($lot, 'balconies') : 0);
            $w->writeAttribute('lodgia', $this->getParam($lot, 'number_of_loggias') ? $this->getParam($lot, 'number_of_loggias'): 0);
            $w->writeAttribute('su_s', $this->getParam($lot, 'number_of_bathrooms_combined') ? $this->getParam($lot, 'number_of_bathrooms_combined') : 0);
            $w->writeAttribute('su_r', $this->getParam($lot, 'number_of_separate_toilets') ? $this->getParam($lot, 'number_of_separate_toilets') : 0);
            $w->writeAttribute('windows', $this->getParam($lot, 'where_to_go_out_the_window') ? $this->getParam($lot, 'where_to_go_out_the_window') : 1);//TODO
            $w->writeAttribute('ipoteka', (int)(bool)$this->getParam($lot, 'the_possibility_of_a_mortgage'));//TODO
          $w->endElement();//options
          $w->startElement('floor');
            $w->writeAttribute('total', (int)$this->getParam($lot, 'floors'));
            $w->writeAttribute('type', $type);
            $w->text((int)$this->getParam($lot, 'about_floor'));
          $w->endElement();//floor
          $w->startElement('note');
            $w->writeCdata($this->exportNote($lot));
          $w->endElement();//note
          $w->writeElement('phone', $this->_data['phones'][$lot->type]);
          if(mb_strtolower($lot->params['premium_cian']) == 'да'){
            $w->writeElement('premium', '1');
          }
          $w->startElement('address');
            $w->writeAttribute('admin_area', 1);
            $w->writeAttribute('locality', 'Москва');
            $w->writeAttribute('street', !empty($lot->address['street']) ? $lot->address['street'] : '');
            $w->writeAttribute('house_str', $this->translateHouseNumber($lot));
          $w->endElement();//address
          if($this->getMetro($lot)){
          $w->startElement('metro');
            $w->writeAttribute('id', $this->getMetro($lot));
          $w->endElement();//metro
          }
          if($photo = $lot->getImage('pres')){ // main image
            $w->writeElement('photo', 'http://www.kre.ru' . $photo);
          }
          foreach($lot->Photos as $photo) {
            if(!$photo->is_pdf && !$photo->is_xls) {
              $w->writeElement('photo', 'http://www.kre.ru' . $photo->getImage('full'));
            }
          }
        $w->endElement();//offer
        $w->flush();
        $lot->free(true);

        $lot = null;
      }
      $w->endElement();//flats_for_sale
    $w->endDocument();
    $w->flush();
    $this->logSection('result', sprintf('%d total, %d added and %d excluded', $i, ($i-$j), $j));
    $this->logSection('done', 'and write to file');
    Tools::rollOutXmlFile('cian', 'eliteflat');

    $w = null;
    $ids = null;
  }

  protected function makeOutoftownFile()
  {
    $ids = Doctrine::getTable('Lot')->createQuery()
            ->andWhere('hide_price = ?')
            ->andWhere('price_all_from > ?')
            ->andWhere('exportable = ?')
            ->orderBy('priority desc')
            ->andWhere('has_children != ?')
            ->active()
            ->type(array('outoftown', 'cottage'))
            ->select('id')
            ->execute(array(0, 0, 1, 1), Doctrine::HYDRATE_SINGLE_SCALAR);

    $sum = count($ids);
    $w = Tools::getXmlWriter('cian', 'outoftown');
    $w->startDocument('1.0', 'windows-1251');
    $w->writeComment(sprintf(' Generated at %s, contains approx %s lots ', date('Y-m-d H:i:s'), $sum));
    $w->startElement('suburbian');
    $i = 0;
    $j = 0;
    foreach (array_chunk($ids, 50) as $ids_chunk)
    {
      $lots = Doctrine::getTable('Lot')
              ->createQuery('l')
              ->joinParams()
              ->whereIn('l.id', $ids_chunk)
              ->execute();

      Lot::fillLotsWithParents($lots);
      Lot::fillLotsWithPhotos($lots);
      
      //$lot = Doctrine::getTable('Lot')->find($id);

      foreach ($lots as $lot)
      {
        $i++;
        $bad = false;
        if ($lot->type == 'cottage' && $lot->params['export_to_cian'] != 'да') {
          $bad = 'export_to_cian';
        }
        if (!isset($lot->params['spaceplot'])) {
          $bad = 'spaceplot';
        }
        if (!isset($lot->params['objecttype']) || !($realty_type = $this->translateRealtyTypeOutoftown($lot->params['objecttype']))) {
          $bad = 'objecttype';
        }
        if (!isset($lot->params['type_of_land']) || !($land_type = $this->translateLandType($lot->params['type_of_land']))) {
          $bad = 'type_of_land';
        }
        if (!isset($lot->ward) || !($route = $this->translateRouteId($lot->ward))) {
          $bad = 'ward';
        }

        if ($bad) {
          $this->logSection($lot->id, sprintf('bad %s. excluded.', $bad), null, 'ERROR');
          $lot->free(true);
          $j++;
          continue;
        }
        $this->logSection($lot->id, sprintf('start. %s of %s (%s%%)', $i, $sum, round($i / $sum * 100, 2)));

        $w->startElement('offer');
        $w->writeElement('id', $lot->id);
        if ($lot->type == 'cottage') {
          $w->writeElement('deal_type', 'R'); // R – аренда
          $w->writeElement('realty_type', $realty_type);
          $w->startElement('area');
          $w->writeAttribute('region', $this->toDouble($lot->params['spaceplot']));
          $w->writeAttribute('living', $lot->area_from);
          $w->endElement(); //area
          $w->writeElement('land_type', $land_type);
          $w->startElement('price');
          $w->writeAttribute('for_day', '0');
          $w->writeAttribute('currency', $lot->currency);
          $w->text(preg_replace('/\D/', '', $lot->price_all_from));
          $w->endElement(); //price
          $w->writeElement('phone', $this->_data['phones'][$lot->type]);
          $w->startElement('com');
          $w->writeAttribute('agent', 0);
          $w->writeAttribute('client', 50);
          $w->endElement(); //com
          $w->startElement('floor');
          $w->writeAttribute('total', (int) $this->getParam($lot, 'floors'));
          $w->endElement(); //floor
          $w->startElement('address');
              $w->writeAttribute('route', $route);
              $w->writeAttribute('mcad', round($lot->params['distance_mkad']));
              $w->writeAttribute('admin_area', 2);
              $w->writeAttribute('locality', $this->translateLocalityOutoftown($lot->params));
              //$w->writeAttribute('street', !empty($lot->address['street']) ? $lot->address['street'] : '');
              //$w->writeAttribute('house_str',  '');
            $w->endElement();//address
          } else {
            $w->writeElement('deal_type', 'S'); // S = 'sale'
            $w->writeElement('realty_type', $realty_type);
            $w->startElement('area');
              $w->writeAttribute('region', $this->toDouble($lot->params['spaceplot']));
              $w->writeAttribute('living', $lot->area_from);
            $w->endElement();//area
            $w->writeElement('land_type', $land_type);
            if(mb_strtolower($lot->params['premium_cian']) == 'да'){
            $w->writeElement('premium', '1');
          }
            $w->startElement('price');
              $w->writeAttribute('currency', $lot->currency);
              $w->text(preg_replace('/\D/', '', $lot->price_all_from));
            $w->endElement();//price
            $w->startElement('floor');
            $w->writeAttribute('total', (int)$this->getParam($lot, 'floors'));
            $w->endElement();//floor
            $w->writeElement('phone', $this->_data['phones'][$lot->type]);
            $w->startElement('address');
              $w->writeAttribute('route', $route);
              $w->writeAttribute('mcad', round($lot->params['distance_mkad']));
              $w->writeAttribute('admin_area', 2);
              $w->writeAttribute('locality', $this->translateLocalityOutoftown($lot->params));
             // $w->writeAttribute('street', !empty($lot->address['street']) ? $lot->address['street'] : '');
             // $w->writeAttribute('house_str',  '');
            $w->endElement();//address
        }
        if ($photo = $lot->getImage('pres')) { // main image
          $w->writeElement('photo', 'http://www.kre.ru' . $photo);
        }
        foreach ($lot->Photos as $photo)
        {
          if (!$photo->is_pdf && !$photo->is_xls) {
            $w->writeElement('photo', 'http://www.kre.ru' . $photo->getImage('full'));
          }
        }
        //$w->writeElement('floor_total', $this->getParam($lot, 'floors'));
        $w->startElement('note');
        $w->writeCdata($this->exportNote($lot));
        $w->endElement(); //note
        $w->endElement(); //offer
        //$lot->free(true);
        //$lot = null;
        $w->flush();
      }
      foreach ($lots as &$lot) { $lot->free(true); }
      $lots->free(true);
    }    
    $w->endElement(); //suburbian
    $w->endDocument();
    $w->flush();

    $this->logSection('result', sprintf('%d total, %d added and %d excluded', $i, ($i - $j), $j));
    $this->logSection('done', 'and write to file');
    Tools::rollOutXmlFile('cian', 'outoftown');

    $w = null;
    $ids = null;
  }

  /* protected function makeCottageFile()
  {
    $ids = Doctrine::getTable('Lot')->createQuery()
      ->andWhere('hide_price = ?')
      ->andWhere('price_all_from > ?')
      ->andWhere('exportable = ?')
      ->andWhere('has_children != ?')
      ->active()
      ->type('cottage')
      ->select('id')
      ->execute(array(0, 0, 1, 1), Doctrine::HYDRATE_SINGLE_SCALAR);
    $sum = count($ids);
    $w = Tools::getXmlWriter('cian', 'cottage');
    $w->startDocument('1.0','windows-1251');
    $w->writeComment(sprintf(' Generated at %s, contains approx %s lots ', date('Y-m-d H:i:s'), $sum));
      $w->startElement('suburbian');
      $i = 0;
      $j = 0;
      foreach($ids as $id) {
        $i++;
        $lot = Doctrine::getTable('Lot')->find($id);
        $bad = false;
        if($lot->params['export_to_cian'] != 'да'){
          $bad = 'export_to_cian';
        }
        if(!isset($lot->params['spaceplot'])){
          $bad = 'spaceplot';
        }
        if(!isset($lot->params['objecttype']) || !($realty_type = $this->translateRealtyTypeOutoftown($lot->params['objecttype']))) {
          $bad = 'objecttype';
        }
        if(!isset($lot->params['type_of_land']) || !($land_type = $this->translateLandType($lot->params['type_of_land']))) {
          $bad = 'type_of_land';
        }
        if(!isset($lot->ward) || !($route = $this->translateRouteId($lot->ward))) {
          $bad = 'ward';
        }

        if($bad) {
          $this->logSection($id, sprintf('bad %s. excluded.', $bad), null, 'ERROR');
          $lot->free(true);
          $j++;
          continue;
        }
        $this->logSection($id, sprintf('start. %s of %s (%s%%)', $i, $sum, round($i/$sum*100, 2)));

        $w->startElement('offer');
          $w->writeElement('id', $lot->id);
          $w->writeElement('deal_type', 'R'); // R – аренда
          $w->writeElement('realty_type', $realty_type);
          $w->startElement('area');
            $w->writeAttribute('region', $this->toDouble($lot->params['spaceplot']));
            $w->writeAttribute('living', $lot->area_from);
          $w->endElement();//area
          $w->writeElement('land_type', $land_type);
          $w->startElement('price');
            $w->writeAttribute('currency', $lot->currency);
            $w->text(preg_replace('/\D/', '', $lot->price_all_from));
          $w->endElement();//price
          $w->writeElement('phone', $this->_data['phones'][$lot->type]);
          $w->startElement('address');
            $w->writeAttribute('route', $route);
            $w->writeAttribute('mcad', $lot->params['distance_mkad']);
            $w->writeAttribute('admin_area', 2);
            $w->writeAttribute('locality', $this->translateLocalityOutoftown($lot->params));
            $w->writeAttribute('street', !empty($lot->address['street']) ? $lot->address['street'] : '');
            $w->writeAttribute('house_str',  '');
          $w->endElement();//address
          if($photo = $lot->getImage('pres')){ // main image
            $w->writeElement('photo', 'http://www.kre.ru' . $photo);
          }
          foreach($lot->Photos as $photo) {
            if(!$photo->is_pdf && !$photo->is_xls) {
              $w->writeElement('photo', 'http://www.kre.ru' . $photo->getImage('full'));
            }
          }
          //$w->writeElement('floor_total', $this->getParam($lot, 'floors'));
          $w->startElement('note');
            $w->writeCdata($this->exportNote($lot));
          $w->endElement();//note
        $w->endElement();//offer
        $lot->free(true);
        $lot = null;
        $w->flush();
      }
      $w->endElement();//suburbian
    $w->endDocument();
    $w->flush();

    $this->logSection('result', sprintf('%d total, %d added and %d excluded', $i, ($i-$j), $j));
    $this->logSection('done', 'and write to file');
    Tools::rollOutXmlFile('cian', 'cottage');

    $w = null;
    $ids = null;
  }*/

  protected function makeComsellFile()
  {
    $ids = Doctrine::getTable('Lot')->createQuery()
      ->andWhere('hide_price = ?')
      ->andWhere('price_all_from > ?')
      ->andWhere('exportable = ?')
      ->orderBy('priority desc')
      ->active()
      ->type('comsell')
      ->select('id')
      ->execute(array(0, 0, 1), Doctrine::HYDRATE_SINGLE_SCALAR);
    $sum = count($ids);
    $w = Tools::getXmlWriter('cian', 'comsell');
    $w->startDocument('1.0','windows-1251');
    $w->writeComment(sprintf(' Generated at %s, contains approx %s lots ', date('Y-m-d H:i:s'), $sum));
    $w->startElement('commerce');
      $i = 0;
      $j = 0;
      foreach($ids as $id) {
        $i++;
        $lot = Doctrine::getTable('Lot')->find($id);
        $bad = false;
        if(!($commerce_type = $this->translateCommerceType($lot))){
          $bad = 'objecttype';
        }
        if(((int)$lot->area_from > 0 && (int)$lot->area_to > 0) || ((int)$lot->price_all_from > 0 && (int)$lot->price_all_to > 0)){
          $bad = 'area_to';
        }
        if($bad) {
          $this->logSection($id, sprintf('bad %s. excluded.', $bad), null, 'ERROR');
          $lot->free(true);
          $j++;
          continue;
        }
        $this->logSection($id, sprintf('start. %s of %s (%s%%)', $i, $sum, round($i/$sum*100, 2)));
        $w->startElement('offer');
          $w->writeElement('id', $lot->id);
          $w->writeElement('commerce_type', $commerce_type);
          $w->writeElement('contract_type', 4);
          $w->startElement('area');
            $w->writeAttribute('total', $lot->area_from);
            $w->writeAttribute('rooms_count', 0);
            $w->writeAttribute('rooms', $lot->area_from);
          $w->endElement();//area
          $w->startElement('price');
            $w->writeAttribute('currency', $lot->currency);
            $w->text(preg_replace('/\D/', '', $lot->price_all_from));
          $w->endElement();//price
          $w->startElement('building');
            $w->writeAttribute('floor', (int)$this->getParam($lot, 'about_floor'));
            $w->writeAttribute('floor_total', (int)$this->getParam($lot, 'floors'));
          $w->endElement();//building
          $w->startElement('options');
            $w->writeAttribute('phones', 0);
          $w->endElement();//options
          $w->startElement('note');
            $w->writeCdata($this->exportNote($lot));
          $w->endElement();//note
          $w->writeElement('phone', $this->_data['phones'][$lot->type]);
          if(mb_strtolower($lot->params['premium_cian']) == 'да'){
            $w->writeElement('premium', '1');
          }
          $w->startElement('address');
            $w->writeAttribute('admin_area', ($lot->address['city'] != '' && $lot->address['city'] != 'Москва') ? 2 : 1);
            $w->writeAttribute('locality', $lot->address['city'] != '' ? $lot->address['city'] : 'Москва');
            $w->writeAttribute('street', !empty($lot->address['street']) ? $lot->address['street'] : '');
            $w->writeAttribute('house_str', $this->translateHouseNumber($lot));
          $w->endElement();//address
          if($this->getMetro($lot)){
            $w->startElement('metro');
              $w->writeAttribute('id', $this->getMetro($lot));
            $w->endElement();//metro
          }
          if($photo = $lot->getImage('pres')){ // main image
            $w->writeElement('photo', 'http://www.kre.ru' . $photo);
          }
          foreach($lot->Photos as $photo) {
            if(!$photo->is_pdf && !$photo->is_xls) {
              $w->writeElement('photo', 'http://www.kre.ru' . $photo->getImage('full'));
            }
          }
        $w->endElement();//offer
        $w->flush();
        $lot->free(true);
        $lot = null;
      }
    $w->endElement();//commerce
    $w->endDocument();
    $w->flush();

    $this->logSection('result', sprintf('%d total, %d added and %d excluded', $i, ($i-$j), $j));
    $this->logSection('done', 'and write to file');
    Tools::rollOutXmlFile('cian', 'comsell');

    $w = null;
    $ids = null;
  }

  private function translateRouteId($route)
  {
    $table = array(
      1 => 281,
      2 => 282,
      3 => 284,
      4 => 285,
      5 => 286,
      6 => 256,
      7 => 257,
      8 => 258,
      9 => 259,
      10 => 260,
      11 => 261,
      12 => 262,
      13 => 263,
      14 => 264,
      15 => 265,
      16 => 267,
      17 => 268,
      18 => 270,
      19 => 271,
      20 => 273,
      21 => 274,
      22 => 275,
      23 => 287,
      24 => 276,
      25 => 277,
      26 => 279,
      27 => 280,
    );
    if(!isset($table[$route])) {
      return false;
    }
    return $table[$route];
  }

  private function translateRealtyTypeOutoftown($type)
  {
    $table = array(
      'Участок' => 'A',
      'Таунхаус' => 'T',
      'Коттедж' => 'K',
      'Коттеджный поселок' => 'K',
    );
    if(!isset($table[$type]) || $table[$type] == '') {
      return false;
    }
    return $table[$type];
  }

  private function translateLocalityOutoftown($params)
  {
    $parts = array();
    if(!empty($params['district_of'])) {
      $parts[] = $params['district_of'] . ' район';
    }
    
    if(!empty($params['cottageVillage'])) {
      $parts[] = $params['cottageVillage'];
    } else if (!empty($params['locality'])) {
      $parts[] = $params['locality'];
    }

    if(!count($parts)) {
      $this->logSection($this->_lot_id, 'bad address', null, 'ERROR');
      return '';
    }
    return implode(', ', $parts);
  }

  private function translateLandType($param)
  {
    $table = array(
      'ИЖС' => 2
    );
    if(!preg_match('/^\d$/', $param)) {
      if(!isset($table[$param])) {
        foreach ($table as $key => $value) {
          if (preg_match('/' . $key . '/', $param)) {
            return $value;
          }
        }
        return false;
      }
      return $table[$param];
    }
    return $param;
  }

  private function toDouble($val)
  {
    $val = explode('-', $val);
    $val = array_shift($val);
    $val = (double)$val;
    $val = str_replace(',', '.', $val);
    return $val;
  }

  private function getParam($lot, $param)
  {
    if($param == 'market')
    {
      if($lot->params[$param] == 'Вторичный')
      {
        return 1;
      }
      else{
        return 2;
      }
    }
    return $lot->params[$param];

  }

  private function translateTypeEliteflat($lot)
  {
    $construction = $this->getParam($lot, 'construction');
    if($construction == ''){
      $construction = 0;
    }
    elseif(mb_stristr($construction, 'монолит', null, 'utf-8') && mb_stristr($construction, 'кирпич', null, 'utf-8')){
      $construction = 4;
    }
    elseif(mb_stristr($construction, 'монолит', null, 'utf-8')){
      $construction = 3;
    }
    elseif(mb_stristr($construction, 'кирпич', null, 'utf-8')){
      $construction = 2;
    }
    elseif(mb_stristr($construction, 'панель', null, 'utf-8')){
      $construction = 1;
    }
    elseif(mb_stristr($construction, 'блоч', null, 'utf-8')){
      $construction = 5;
    }
    elseif(mb_stristr($construction, 'дерев', null, 'utf-8')){
      $construction = 6;
    }
    elseif(mb_stristr($construction, 'сталин', null, 'utf-8')){
      $construction = 7;
    }
    else{
      $construction = 0;
    }
    return $construction;
  }

  private function translateHouseNumber($lot)
  {
    $result = '';
    if(!empty($lot->address['house'])) {
      $result .= $lot->address['house'];
    }
    if(!empty($lot->address['building'])) {
      $result .= 'к'.$lot->address['building'];
    }
    if(!empty($lot->address['construction'])) {
      $result .= 'с'.$lot->address['construction'];
    }
    return $result;
  }

  private function getMetro($lot)
  {
    $table = array (0 => 85,  1 => 13,  3 => 97,  4 => 53,
      5 => 105,  6 => 135,  7 => 156,  8 => 50,  9 => 5,  10 => 109,
      11 => 57,  12 => 71,  13 => 47,  14 => 69,  15 => 7,  16 => 93,  17 => 131,
      18 => 30,  20 => 120,  21 => 107,  23 => 145,  25 => 106,  24 => 16,  97 => 24,  26 => 112,  27 => 2,
      28 => 3,  29 => 77,  30 => 142,  31 => 157,  147 => 198,  32 => 81,  33 => 6,  34 => 115,  35 => 132,
      36 => 21,  22 => 164,  153 => 205,  37 => 140,  38 => 43,  40 => 94,  41 => 18,  42 => 17,  43 => 15,
      44 => 52,  45 => 74,  46 => 144,  47 => 14,  48 => 35,  49 => 92,  50 => 22,  51 => 133,  52 => 36,
      53 => 34,  54 => 29,  55 => 62,  56 => 73,  57 => 79,  58 => 60,  59 => 48,  60 => 55,  61 => 98,  62 => 32,
      63 => 143,  152 => 204,  64 => 146,  65 => 8,  66 => 110,  148 => 197,  67 => 117,  68 => 61,  154 => 202,
      69 => 124,  70 => 125,  71 => 126,  72 => 82,  73 => 11,  74 => 134,  19 => 89,  75 => 95,  76 => 100,
      77 => 67,  78 => 20,  79 => 111,  80 => 31,  81 => 12,  82 => 28,  83 => 165,  39 => 44,
      84 => 42,  85 => 83,  86 => 113,  87 => 141,  88 => 59,  89 => 63,  90 => 86,  91 => 49,
      92 => 68,  93 => 121,  94 => 130,  95 => 38,  96 => 76,  98 => 136,  99 => 96,  100 => 72,  155 => 214,  101 => 1,
      102 => 104,  103 => 80,  104 => 116,  105 => 108,  106 => 127,  107 => 45,  108 => 122,  151 => 201,  109 => 51,
      110 => 4,  111 => 37,  112 => 26,  149 => 206,  113 => 54,  114 => 102,  115 => 64,  116 => 75,  117 => 9,  118 => 10,
      119 => 78,  120 => 91,  121 => 114,  122 => 88,  150 => 199,  123 => 123,  124 => 103,  125 => 65,  126 => 70,  127 => 40,
      128 => 25,  129 => 58,  130 => 56,  131 => 27,  132 => 19,  133 => 118,  134 => 39,  135 => 128,  136 => 119,  137 => 33,
      138 => 137,  139 => 99,  140 => 84,  141 => 41,  142 => 66,  143 => 46,  144 => 23,  145 => 129,  2 => 155,  146 => 90, 156 => 196);
    if($lot->metro_id) {
      return $table[$lot->metro_id];
    }
  }

  private function translateCommerceType($lot)
  {
    /*
      *O – офис
      *W – склад
      *T – торговая площадь
      *FP – помещение свободного назначения
      *SB – продажа бизнеса
      *WP – производственное помещение
      *B – отдельно стоящее здание
       F – под общепит
       G – гараж
       AU – автосервис
       UA – юридический адрес
       BU – под бытовые услуги (салон красоты и т.д.)
    */
    $table = array(
      'Торговое помещение'              =>  'T',
      'Офисное помещение'               =>  'O',
      'Отдельно стоящее здание'         =>  'B',
      'Готовый арендный бизнес'         => 'SB',
      'Особняк'                         =>  'B',
      'Помещение свободного назначения' => 'FP',
      'Склад/складской комплекс'        =>  'W',
      'Промышленный комплекс'           => 'WP',
      'Земельный участок'               => '',
      'Прочее'                          => '',
    );
    $type = $this->getParam($lot, 'objecttype');
    if(!$type || !isset($table[$type])){
      return false;
    }
    return $table[$type];
  }

  private function exportNote(Lot $lot)
  {
    return exportBaseTask::getLotDescription($lot);
  }  
  
}
