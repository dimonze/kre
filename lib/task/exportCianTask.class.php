<?php
/*
 * разные файлы для разных типов недвижимости
 */

class exportCianTask extends exportBaseTask
{
  const
    PARTNER       = 'cian',
    ENCODING      = 'windows-1251',
    PHONE_FORMAT  = '495%d%d%d';

  protected
    $_suptypes = array(
      'flatrent'  => array('flatrent'),
      'flatsale'  => array('eliteflat','penthouse','elitenew'),
      'commerce'  => array('comrent', 'comsell'),
      'country'   => array('outoftown', 'cottage'),
    );

  private
    $_dictionaries = array();


  protected function configure()
  {
    parent::configure();

    $this->name = 'cian';
  }


  protected function writeDocumentStart()
  {
    $this->_xml_writer->startDocument('1.0', self::ENCODING);

    switch ($this->_current_type) {
      case 'flatrent':  $this->_xml_writer->startElement('flats_rent');     break;
      case 'flatsale':  $this->_xml_writer->startElement('flats_for_sale'); break;
      case 'commerce':  $this->_xml_writer->startElement('commerce');       break;
      case 'country':   $this->_xml_writer->startElement('suburbian');      break;
      default:          throw new Exception(sprintf('Estate type is required for %s export', mb_strtoupper($this->name)));
    }
  }

  protected function writeDocumentFinish()
  {
    $this->_xml_writer->endElement();
    $this->_xml_writer->endDocument();
  }

  protected function getDataArray(Lot $lot)
  {
    //внутренний уникальный идентификатор объекта в базе данных агентства (integer)
    $data['id'] = $lot->id;

    //числовой идентификатор административной области
    $data['address']['attributes']['admin_area']  = self::getLotRegionId($lot);
    //название населенного пункта
    $data['address']['attributes']['locality']    = self::getLotCity($lot);
    //название улицы
    if (!empty($lot->address['street'])) {
      $data['address']['attributes']['street']    = $lot->address['street'];
    }
    //строковое представление номера дома
    if ($v = $lot->getPrettyAddress('house', false)) {
      $data['address']['attributes']['house_str'] = $v;
    }

    if (self::getLotRegion($lot) == 'Москва') {
      //числовой идентификатор станции метро
      if ($v = self::getLotDictionaryMetroId($lot, $this->getDictionary('metros'))) {
        $data['metro']['attributes']['id']        = $v;
        //расстояние в минутах до метро пешком
        if ($v = self::getLotMetroDistanceWalk($lot)) {
          $data['metro']['attributes']['wtime']   = $v;
        }
        //расстояние в минутах до метро транспортом
        elseif ($v = self::getLotMetroDistanceTransport($lot)) {
          $data['metro']['attributes']['ttime']   = $v;
        }
      }
    }
    else {
      //расстояние от объекта до МКАД в километрах (для Московской области)
      if ($v = self::getLotDistanceMkad($lot)) {
        $data['address']['attributes']['mcad']    = (int) $v;
      }
      //числовой идентификатор шоссе (для Московской области)
      if ($v = self::getLotDictionaryHighwayId($lot, $this->getDictionary('highways'))) {
        $data['address']['attributes']['route']   = $v;
      }
    }

    $data = array_merge($data, $this->{$this->getDataMethod($lot->type)}($lot));

    //текстовое примечание, помещенное внутрь CDATA
    $data['note'] = self::getLotDescription($lot);
    //контактные номера телефонов. максимум 2 номера. разделитель между номерами - «;». длина номера - 10 цифр
    $data['phone'] = self::getLotPhone($lot);

    //премиум-статус объявления
    if (!empty($lot->params['premium_cian']) && mb_strtolower($lot->params['premium_cian']) == 'да') {
      $data['premium'] = 1;
    }

    //фото объекта
    $data['photo'] = array();
    if ($photo = $lot->getImage('pres')) {
      $data['photo'][] = self::ORG_SITE . $photo;
    }
    foreach ($lot->Photos as $photo) {
      if (!$photo->is_pdf && !$photo->is_xls) {
        $data['photo'][] = self::ORG_SITE . $photo->getImage('full');
      }
    }

    return array('offer' => $data);
  }

  protected function getDataArrayFlatrent(Lot $lot)
  {
    //цена
    $data['price']['data'] = self::getLotPrice($lot);
    //валюта (enum: USD, RUB, EUR)
    $data['price']['attributes']['currency'] = self::getLotCurrency($lot);
    //срок сдачи (enum: 0 – длительный срок (от года); 1 – посуточно; 2 – несколько месяцев (до года))
    $data['price']['attributes']['for_day']  = 0;
    //кол-во месяцев предоплаты при заключении договора
    //$data['price']['attributes']['prepay'] = 1;//???
    //наличие страхового депозита (boolean: 1; 0)
    //$data['price']['attributes']['deposit'] = 0;//???

    //размер комиссии для агентов
    //$data['com']['attributes']['agent'] = ?;
    //размер комиссии для клиентов
    //$data['com']['attributes']['client'] = ?;

    //этаж
    if ($v = self::getLotFloor($lot)) {
      $data['floor']['data'] = $v;
    }
    //кол-во этажей в доме
    if ($v = self::getLotNbFloors($lot)) {
      $data['floor']['attributes']['total'] = $v;
    }
    //количество комнат
    if ($v = self::getLotNbRooms($lot)) {
      $data['rooms_num'] = $v;
    }

    //общая площадь в кв. метрах
    if ($v = self::getLotAreaTotal($lot)) {
      $data['area']['attributes']['total']    = $v;
    }
    //площадь кухни в кв. метрах
    if ($v = self::getLotAreaKitchen($lot)) {
      $data['area']['attributes']['kitchen']  = $v;
    }
    //жилая площадь в кв. метрах
    if ($v = self::getLotAreaLiving($lot)) {
      $data['area']['attributes']['living']   = $v;
    }
    //площадь по комнатам (текстовое поле)
    if (!empty($lot->params['area_of_each_room'])) {
      $data['area']['attributes']['rooms'] = $lot->params['area_of_each_room'];
    }

    //наличие телефона (boolean: yes; no)
    if (!is_null($v = self::getLotIsTelephone($lot))) {
      $data['options']['attributes']['phone'] = ($v ? 'yes' : 'no');
    }
    //наличие балкона или лоджии (boolean: yes; no)
    if (!is_null($v = self::getLotIsTV($lot))) {
      $data['options']['attributes']['tv']    = ($v ? 'yes' : 'no');
    }
    //наличие телевизора (boolean: yes; no)
    if (!is_null($v = self::getLotIsBalconies($lot))) {
      $data['options']['attributes']['balcon'] = ($v ? 'yes' : 'no');
    }
    //наличие мебели в жилых комнатах (boolean)
    //$data['options']['attributes']['mebel'] = ?;
    //наличие мебели на кухне
    //$data['options']['attributes']['mebel_kitchen'] = ?;
    //наличие стиральной машины
    //$data['options']['attributes']['wm'] = ?;
    //наличие холодильника
    //$data['options']['attributes']['rfgr'] = ?;
    //возьмут с детьми
    //$data['options']['attributes']['kids'] = ?;
    //возьмут с животными
    //$data['options']['attributes']['pets'] = ?;

    return $data;
  }

  protected function getDataArrayFlatsale(Lot $lot)
  {
    //цена
    $data['price']['data'] = self::getLotPrice($lot);
    //валюта (enum: USD, RUB, EUR)
    $data['price']['attributes']['currency'] = self::getLotCurrency($lot);

    //количество комнат (enum: 0 – комната; от 1 до 5 – сколькикомнатная квартира; 6 – многокомнатная квартира (более 5 комнат); 7 – свободная планировка; 8 – доля в квартире)
    if ($v = self::getLotNbRooms($lot)) {
      $data['rooms_num'] = ($v > 6 ? 6 : $v);
    }

    //этаж
    if ($v = self::getLotFloor($lot)) {
      $data['floor']['data'] = $v;
    }
    //кол-во этажей в доме
    if ($v = self::getLotNbFloors($lot)) {
      $data['floor']['attributes']['total'] = $v;
    }
    //тип дома (enum: 1 – панельный; 2 – кирпичный; 3 – монолитный; 4 – кирпично-монолитный; 5 – блочный; 6 – деревянный; 7 – «сталинский»)
    if ($v = self::getLotConstructionType($lot)) {
      $data['floor']['attributes']['type'] = $v;
    }

    //общая площадь в кв. метрах
    if ($v = self::getLotAreaTotal($lot)) {
      $data['area']['attributes']['total']    = $v;
    }
    //площадь кухни в кв. метрах
    if ($v = self::getLotAreaKitchen($lot)) {
      $data['area']['attributes']['kitchen']  = $v;
    }
    //жилая площадь в кв. метрах
    if ($v = self::getLotAreaLiving($lot)) {
      $data['area']['attributes']['living']   = $v;
    }
    //площадь по комнатам (текстовое поле)
    if (!empty($lot->params['area_of_each_room'])) {
      $data['area']['attributes']['rooms'] = $lot->params['area_of_each_room'];
    }

    //тип жилья (enum: 1 – вторичное жилье; 2 – новостройка)
    $data['options']['attributes']['object_type'] = $lot->is_newbuild_type ? 2 : 1;
    //тип продажи (F – свободная продажа; A – альтернатива)
    $data['options']['attributes']['sale_type'] = 'F';
    //наличие телефона (boolean: yes; no)
    if (!is_null($v = self::getLotIsTelephone($lot))) {
      $data['options']['attributes']['phone'] = $v;
    }
    //количество пассажирских лифтов
    if ($v = self::getLotNbElevatorsPassenger($lot)) {
      $data['options']['attributes']['lift_p'] = $v;
    }
    //количество грузовых лифтов
    if ($v = self::getLotNbElevatorsCargo($lot)) {
      $data['options']['attributes']['lift_g'] = $v;
    }
    //количество балконов
    if ($v = self::getLotNbBalconies($lot)) {
      $data['options']['attributes']['balcon'] = (int) $v;
    }
    //количество лоджий
    if ($v = self::getLotNbLoggias($lot)) {
      $data['options']['attributes']['lodgia'] = (int) $v;
    }
    //количество совмещенных санузлов
    if ($v = self::getLotNbBathroomsCombined($lot)) {
      $data['options']['attributes']['su_s'] = $v;
    }
    //количество раздельных санузлов
    if ($v = self::getLotNbBathroomsSeparate($lot)) {
      $data['options']['attributes']['su_r'] = $v;
    }
    //куда выходят окна (enum: 1 – двор; 2 – улица; 3 – двор и улица)
    if ($v = self::getLotWindowView($lot)) {
      $data['options']['attributes']['windows'] = $v;
    }
    //возможность ипотеки (boolean: 1; 0)
    if (!is_null($v = self::getLotIsMortgage($lot))) {
      $data['options']['attributes']['ipoteka'] = (int) $v;
    }

    return $data;
  }

  protected function getDataArrayCommerce(Lot $lot)
  {
    //тип помещения (enum)
    $data['commerce_type'] = self::getLotObjectType($lot);
    //тип договора (enum: 1 – прямая аренда; 2 – субаренда; 3 – продажа права аренды (ППА); 4 – продажа объекта; 5 – договор совместной деятельности)
    $data['contract_type'] = $lot->is_rent_type ? 1 : 4;

    //цена
    $data['price']['data'] = self::getLotPriceMeter($lot);
    //цена аренды помещения (enum: month – в месяц; year – за м2 в год)
    $data['price']['attributes']['period'] = 'year';
    //валюта (enum: USD, RUB, EUR)
    $data['price']['attributes']['currency'] = self::getLotCurrency($lot);

    //общая площадь
    $data['area']['attributes']['total'] = self::getLotAreaTotal($lot);
    //количество комнат
    //$data['area']['attributes']['rooms_count'] = '?';
    //площадь по комнатам
    //$data['area']['attributes']['rooms'] = '?';

    //этаж (если подвал, то «-2», полуподвал - «-1»)
    if ($v = self::getLotFloor($lot)) {
      $data['bulding']['attributes']['floor'] = $v;
    }
    //количество этажей в здании
    if ($v = self::getLotNbFloors($lot)) {
      $data['bulding']['attributes']['floor_total'] = $v;
    }

    //количество телефонных линий
    if ($v = self::getLotNbTelephoneLines($lot)) {
      $data['options']['attributes']['phones'] = $v;
    }

    if ($lot->is_rent_type) {
      //размер комиссии для агентов
      //$data['com']['attributes']['agent'] = ?;
      //размер комиссии для клиентов
      //$data['com']['attributes']['client'] = ?;
    }

    return $data;
  }

  protected function getDataArrayCountry(Lot $lot)
  {
    //тип сделки (enum: R – аренда; S – продажа)
    $data['deal_type'] = $lot->is_rent_type ? 'R': 'S';
    //тип объекта (enum);
    $data['realty_type'] = self::getLotObjectType($lot);

    //цена
    $data['price']['data'] = self::getLotPrice($lot);
    //валюта (enum: USD, RUB, EUR)
    $data['price']['attributes']['currency'] = self::getLotCurrency($lot);
    //срок сдачи (для типа сделки «Аренда») (enum: 0 – длительный срок (от года); 1 – посуточно; 2 – несколько месяцев (до года))
    if ($lot->is_rent_type) {
      $data['price']['attributes']['for_day'] = 0;
    }

    //площадь участка в сотках
    if ($v = self::getLotAreaLand($lot)) {
      $data['area']['attributes']['region'] = $v;
    }
    //площадь дома в м2
    if (($v = self::getLotAreaTotal($lot)) || ($v = self::getLotAreaLiving($lot))) {
      $data['area']['attributes']['living'] = $v;
    }

    //количество этажей в доме
    if ($v = self::getLotNbFloors($lot)) {
      $data['floor_total'] = $v;
    }

    if ($lot->is_rent_type) {
      //размер комиссии для агентов
      //$data['com']['attributes']['agent'] = ?;
      //размер комиссии для клиентов
      //$data['com']['attributes']['client'] = ?;
    }

    return $data;
  }

  protected function validateLot(Lot $lot)
  {
    if ($lot->is_country_type && (empty($lot->params['export_to_cian']) || mb_strtolower($lot->params['export_to_cian']) != 'да')) {
      throw new Exception('export_to_cian != да');
    }

    if (!in_array($lot->type, $this->getAllowedTypes())) {
      throw new Exception(sprintf('not allowed lot type: "%s"', $lot->type));
    }
    if (($lot->is_commercial_type || $lot->is_country_type) && !self::getLotObjectType($lot)) {
      throw new Exception(sprintf('unrecognized objecttype for "%s"', $lot->type.(isset($lot->params['objecttype']) ? ' ('.$lot->params['objecttype'].')' : '')));
    }
    if (($lot->is_city_type && self::getLotRegion($lot) != 'Москва')
     || ($lot->is_commercial_type && self::getLotRegion($lot) != 'Москва')
     || ($lot->is_country_type && self::getLotRegion($lot) != 'Московская область')) {
      throw new Exception(sprintf('lot of type "%s" has unexpected region: "%s"', $lot->type, self::getLotRegion($lot)));
    }


    if (($v = self::getLotPrice($lot)) && empty($v)) {
      throw new Exception('price is empty');
    }
    if (!is_numeric($v)) {
      throw new Exception(sprintf('price is not a valid number: "%s"', $v));
    }
    if ($v < 1) {
      throw new Exception(sprintf('price is less than one: "%s"', $v));
    }

    if (empty($lot->anons) && empty($lot->description)) {
      throw new Exception('anons and description are empty');
    }
    if (!self::getLotCity($lot)) {
      throw new Exception('street address is empty');
    }
    if (self::getLotRegion($lot) == 'Москва') {
      if (empty($lot->metro_id)) {
        throw new Exception('metro is empty');
      }
      if (!self::getLotDictionaryMetroId($lot, $this->getDictionary('metros'))) {
        throw new Exception(sprintf('can\'t find matching metro: "%s"', $lot->metro));
      }
    }

    if (self::getLotObjectType($lot) == 'A') {//земельный участок
      if (empty($lot->params['spaceplot'])) {
        throw new Exception('spaceplot parameter is empty');
      }
      if (!self::getLotAreaLandArray($lot)) {
        throw new Exception(sprintf('can\'t parse spaceplot parameter: "%s"', $lot->params['spaceplot']));
      }
    }
    else {
      if (empty($lot->area_from) && empty($lot->area_to)) {
        throw new Exception('area is empty');
      }
      if (($v = self::getLotAreaTotal($lot)) < 1) {
        throw new Exception(sprintf('area is less than one: "%s"', $v));
      }
    }

    if (in_array($this->_current_type, array('flatrent', 'flatsale'))) {
      if (empty($lot->address['street'])) {
        throw new Exception('street address is empty');
      }

      if (empty($lot->params['rooms'])) {
        throw new Exception('rooms parameter is empty');
      }
      if (!ctype_digit($lot->params['rooms'])) {
        throw new Exception(sprintf('rooms parameter is not a valid number: "%s"', $lot->params['rooms']));
      }
      if ($lot->params['rooms'] < 1) {
        throw new Exception(sprintf('rooms number is less than one: "%s"', $lot->params['rooms']));
      }

      if (empty($lot->params['about_floor'])) {
        throw new Exception('floor parameter is empty');
      }
      if (!ctype_digit($lot->params['about_floor'])) {
        throw new Exception(sprintf('floor parameter is not a valid number: "%s"', $lot->params['about_floor']));
      }
      if ($lot->params['about_floor'] < 1) {
        throw new Exception(sprintf('floor number is less than one: "%s"', $lot->params['about_floor']));
      }

      if (empty($lot->params['floors'])) {
        throw new Exception('floors parameter is empty');
      }
      if (!ctype_digit($lot->params['floors'])) {
        throw new Exception(sprintf('floors parameter is not a valid number: "%s"', $lot->params['floors']));
      }
      if ($lot->params['floors'] < 1) {
        throw new Exception(sprintf('floors number is less than one: "%s"', $lot->params['floors']));
      }
      if ($lot->params['about_floor'] > $lot->params['floors']) {
        throw new Exception(sprintf('floor number is greather than total floors: %s > %s', $lot->params['about_floor'], $lot->params['floors']));
      }

      if (empty($lot->params['about_floorspace'])) {
        throw new Exception('living area parameter is empty');
      }
      if (!self::getLotAreaLiving($lot)) {
        throw new Exception(sprintf('can\'t parse living area perameter as a number: "%s"', $lot->params['about_floorspace']));
      }

      if (empty($lot->params['kitchen_area'])) {
        throw new Exception('kitchen area parameter is empty');
      }
      if (!self::getLotAreaKitchen($lot)) {
        throw new Exception(sprintf('can\'t parse kitchen area perameter as a number: "%s"', $lot->params['kitchen_area']));
      }
    }
    elseif ($this->_current_type == 'commerce') {
      if (empty($lot->params['about_floor'])) {
        throw new Exception('floor parameter is empty');
      }
      if (!self::getLotFloor($lot)) {
        throw new Exception(sprintf('can\'t parse floor perameter as a number: "%s"', $lot->params['about_floor']));
      }

      if (empty($lot->params['floors'])) {
        throw new Exception('floors parameter is empty');
      }
      if (!self::getLotFloor($lot)) {
        throw new Exception(sprintf('can\'t parse floors perameter as a number: "%s"', $lot->params['floors']));
      }
      if ($lot->params['about_floor'] > $lot->params['floors']) {
        throw new Exception(sprintf('floor number is greather than total floors: %s > %s', $lot->params['about_floor'], $lot->params['floors']));
      }

      if (!self::getLotNbTelephoneLines($lot)) {
        throw new Exception('unknown phone lines number');
      }
    }
    elseif ($this->_current_type == 'country') {
      if (empty($lot->params['spaceplot'])) {
        throw new Exception('spaceplot parameter is empty');
      }
      if (!self::getLotAreaLandArray($lot)) {
        throw new Exception(sprintf('can\'t parse spaceplot parameter: "%s"', $lot->params['spaceplot']));
      }

      if (empty($lot->params['type_of_land'])) {
        throw new Exception('type_of_land parameter is empty');
      }
      if (!self::getLotPlotType($lot)) {
        throw new Exception(sprintf('can\'t find matching land type: "%s"', $lot->params['type_of_land']));
      }
    }
  }


  private function getDictionary($dictionary)
  {
    $dictionaries = array(
      'highways'  => 'routes',
      'metros'    => 'metros',
    );

    if (!in_array($dictionary, array_keys($dictionaries))) {
      throw new Exception(sprintf('unknown dictionary "%s"', $dictionary));
    }

    if (!isset($this->_dictionaries[$dictionary])) {
      $this->logSection('fetch', sprintf('%s dictionary', $dictionary));

      $url  = sprintf('http://www.cian.ru/%s.php', $dictionaries[$dictionary]);
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      if (!$response = curl_exec($curl)) {
        throw new Exception(sprintf('curl response fail: "%s"', $url));
      }

      curl_close($curl);

      $xml = simplexml_load_string($response);
      foreach ($xml as $e) {
        $this->_dictionaries[$dictionary][mb_strtolower($e->__toString())] = (integer) $e['id'];
      }

      unset($curl, $xml);
    }

    return $this->_dictionaries[$dictionary];
  }


  protected static function getLotCurrency($lot)
  {
    return $lot->currency == 'RUR' ? 'RUB' : $lot->currency;
  }

  protected static function getLotRegionId($lot)
  {
    switch (self::getLotRegion($lot)) {
      case 'Москва':  return 1;
      default:        return 2;
    }
  }

  protected static function getLotNbElevatorsPassenger($lot)
  {
    if (!empty($lot->params['passenger_elevators']) && ctype_digit($lot->params['passenger_elevators'])) {
      return (int) $lot->params['passenger_elevators'];
    }

    return null;
  }

  protected static function getLotNbElevatorsCargo($lot)
  {
    if (!empty($lot->params['cargo_elevators']) && ctype_digit($lot->params['cargo_elevators'])) {
      return (int) $lot->params['cargo_elevators'];
    }

    return null;
  }

  protected static function getLotNbTelephoneLines($lot)
  {
    if (!empty($lot->params['service_inner'])) {
      preg_match('/(\d+) (?:телефонн*[аяыех]+ )*(?:лини[йия]|мгтс)/iu', $lot->params['service_inner'], $matches);
      if (!empty($matches[1])) return (int) $matches[1];
    }

    return null;
  }

  protected static function getLotIsBalconies($lot)
  {
    $b = self::getLotNbBalconies($lot);
    $l = self::getLotNbLoggias($lot);

    if (is_null($b) && is_null($l)) return null;
    if (!$b && !$l)                 return false;
    if ($b || $l)                   return true;
  }

  protected static function getLotIsTV($lot)
  {
    $v = '';
    if (!empty($lot->params['service_tele'])) {
      $v .= $lot->params['service_tele'];
    }
    if (!empty($lot->params['infra_additional'])) {
      $v .= !empty($v) ? ' ' : '';
      $v .= $lot->params['infra_additional'];
    }
    if (!empty($lot->params['other'])) {
      $v .= !empty($v) ? ' ' : '';
      $v .= $lot->params['other'];
    }
    if (!empty($lot->params['service_inner'])) {
      $v .= !empty($v) ? ' ' : '';
      $v .= $lot->params['service_inner'];
    }

    if (!empty($v) && preg_match('/(?:[^\wа-я]|^)(?:TV|т(?:еле)*в(?:идение)*)(?:[^\wа-я]|$)/iu', $v)) return true;

    return null;
  }

  protected static function getLotWindowView($lot)
  {
    //1 – двор; 2 – улица; 3 – двор и улица
    if (!empty($lot->params['where_to_go_out_the_window'])) {
      $v = $lot->params['where_to_go_out_the_window'];
      if (mb_stripos($v, 'двор') !== false && mb_stripos($v, 'улиц') !== false) return 3;
      if (mb_stripos($v, 'двор') !== false) return 1;
      if (mb_stripos($v, 'улиц') !== false) return 2;
    }

    return null;
  }

  protected static function getLotPlotType($lot)
  {
    //1 – садовое некоммерческое товарищество (СНТ); 2 – индивидуальное жилищное строительство (ИЖС); 3 – земля промназначения
    if (!empty($lot->params['type_of_land'])) {
      $v = $lot->params['type_of_land'];
      if (mb_stripos($v, 'дачное строительство') !== false ) return 2;
      if (preg_match('/(?:^|[^\wа-я])и(?:ндивидуальное )*ж(?:илое )*с(?:троительство)*(?:$|[^\wа-я])/iu', $v)) return 2;
      if (preg_match('/(?:^|[^\wа-я])с(?:адоводческое )*н(?:екоммерческое )*т(?:оварищество)*(?:$|[^\wа-я])/iu', $v)) return 1;
    }

    return null;
  }

  protected static function getLotConstructionType($lot)
  {
    //1 – панельный; 2 – кирпичный; 3 – монолитный; 4 – кирпично-монолитный; 5 – блочный; 6 – деревянный; 7 – «сталинский»
    if (!empty($lot->params['construction'])) {
      $v = $lot->params['construction'];
      if (mb_stripos($v, 'кирпич') !== false && mb_stripos($v, 'монолит') !== false)  return 4;
      if (mb_stripos($v, 'блок') !== false || mb_stripos($v, 'блочн') !== false)      return 5;
      if (mb_stripos($v, 'дерев') !== false || mb_stripos($v, 'брус') !== false)      return 6;
      if (mb_stripos($v, 'панель') !== false)   return 1;
      if (mb_stripos($v, 'кирпич') !== false)   return 2;
      if (mb_stripos($v, 'монолит') !== false)  return 3;
      if (mb_stripos($v, 'сталин') !== false)   return 7;
    }

    return null;
  }

  protected static function getLotObjectType($lot)
  {
    switch($lot->type) {
      case 'cottage':
      case 'outoftown':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Участок':             return 'A';
          case 'Коттедж':             return 'K';
          case 'Таунхаус':            return 'T';
          case 'Квартира':            return null;
          case 'Коттеджный поселок':  return null;
          default:                    return null;
        }
        //K – дом (коттедж); P – часть дома; A – земельный участок; T – таунхаус

      case 'comsell':
      case 'comrent':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Торговое помещение':              return 'T';
          case 'Офисное помещение':               return 'O';
          case 'Отдельно стоящее здание':         return 'B';
          case 'Готовый арендный бизнес':         return 'SB';
          case 'Особняк':                         return 'B';
          case 'Помещение свободного назначения': return 'FP';
          case 'Склад/складской комплекс':        return 'W';
          case 'Промышленный комплекс':           return 'WP';
          case 'Земельный участок':               return null;
          case 'Прочее':                          return null;
        }
        //O – офис; W – склад; T – торговая площадь; F – под общепит; FP – помещение свободного назначения;
        //G – гараж; AU – автосервис; WP – производственное помещение; B – отдельно стоящее здание;
        //UA – юридический адрес; SB – продажа бизнеса; BU – под бытовые услуги (салон красоты и т.д.)
    }

    return null;
  }

  protected static function getLotDictionaryHighwayId($lot, array $dictionary)
  {
    $wards = array_map(function($v) { return $v.' шоссе'; }, $lot->array_wards);

    foreach ($wards as $w) {
      if (isset($dictionary[mb_strtolower($w)])) return $dictionary[mb_strtolower($w)];
    }

    return null;
  }

  protected static function getLotDictionaryMetroId($lot, array $dictionary)
  {
    $metro = $lot->metro;

    switch ($metro) {
      case 'Улица Академика Янгеля':    return 155;//Янгеля Академика
      case 'Библиотека им. Ленина':     return 30;//Библиотека Ленина
      case 'Бульвар Дмитрия Донского':  return 164;//Донского Дмитрия бульвар
      case 'Преображенская пл.':        return 38;//Преображенская площадь
      case 'Проспект Вернадского':      return 24;//Вернадского проспект
      case 'Улица Подбельского':        return 40;//Бульвар Рокоссовского
    }

    if (isset($dictionary[mb_strtolower($metro)])) return $dictionary[mb_strtolower($metro)];

    return null;
  }
}