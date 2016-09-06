<?php
/*
 * Принимаются цены только в RUR и USD
 *
 * Для разных типов недвижимости должен быть отдельный файл: новостройки, вторичный рынок, загородная недвижимость и коммерческая недвижимость.
 * В фиде должны передаваться все актуальные объявления.
 * Цену объявления надо передавать только в той валюте, которую указал владелец объявления.
 * Все числовые значения не должны содержать десятичных знаков и запятых, только целые числа.
 * Все тэги должны быть расположены строго в том же порядке, что и на образце.
 * Тэги нельзя пропускать или оставлять пустыми, кроме случаев, указанных в описании.
 * Все заглавные и строчные буквы должны строго соответствовать образцу. Регистр имеет значение.
 * Для уменьшения объема трафика возможно архивировать xml-файл. На данный момент поддерживается архиватор gzip. Расширение архива должно быть gz или gzip.
 */

class exportRbcTask extends exportBaseTask
{
  const
    PARTNER       = 'rbc',
    ENCODING      = 'utf-8',
    PHONE_FORMAT  = '+7(495)%d-%d-%d';

  protected
    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse','flatrent'),
      'newbuilds' => 'elitenew',
      'commerce'  => array('comrent', 'comsell'),
      'country'   => array('outoftown', 'cottage'),
    );


  protected function configure()
  {
    parent::configure();

    $this->name = 'rbc';
  }


  protected function writeDocumentStart()
  {
    $this->_xml_writer->startDocument('1.0', self::ENCODING);
    $this->_xml_writer->startElement('document');

    switch ($this->_current_type) {
      case 'flats':     $this->_xml_writer->startElement('flats');    break;
      case 'newbuilds': $this->_xml_writer->startElement('newflats'); break;
      case 'commerce':  $this->_xml_writer->startElement('country');  break;
      case 'country':   $this->_xml_writer->startElement('country');  break;
      default:          throw new Exception(sprintf('Estate type is required for %s export', mb_strtoupper($this->name)));
    }
  }

  protected function writeDocumentFinish()
  {
    $this->_xml_writer->endElement();
    $this->_xml_writer->endElement();
    $this->_xml_writer->endDocument();
  }

  protected function getDataArray(Lot $lot)
  {
    //внутренний идентификатор предложения в базе данных партнера, у каждого объекта должен быть свой уникальный id
    $data['id'] = $lot->id;

    if ($this->_current_type != 'newbuilds') {
      //тип сделки (enum: S – продажа; R — аренда)
      $data['deal_type'] = self::getLotOperationType($lot);
    }
    if ($this->_current_type == 'commerce') {
      //тип недвижимости (enum: O – офис; W – склад; T – торговые; F – другое)
      $data['commerce_type'] = self::getLotObjectType($lot);
    }
    elseif ($this->_current_type == 'country') {
      //ип недвижимости (enum: K - коттедж; A - земельный участок)
      $data['realty_type']  = self::getLotObjectType($lot);
    }

    //регион или столица субъекта РФ или город федерального значения
    //M - столица субъекта РФ или город федерального значения
    //R - регион, край или иной Субъект РФ
    $v = self::getLotRegion($lot);
    $data['address']['region']['attributes']['type'] = ($v == 'Москва' ? 'M' : 'R');
    $data['address']['region']['data'] = $v;

    if (self::getLotRegion($lot) == 'Москва') {
      //район города
      if ($v = self::getLotCityArea($lot)) {
        $data['address']['district'] = $v;
      }
      //название ближайшей станции метро
      if (!empty($lot->metro_id)) {
        $data['address']['metro'] = $lot->metro;
      }
      //название улицы
      $data['address']['street'] = $lot->address['street'];
      //номер дома
      $data['address']['houseNo'] = $lot->getPrettyAddress('house', false);
      //расстояние от метро в минутах (enum: T - транспорт; P - пешком)
      if ($v = self::getLotMetroDistanceWalk($lot)) {
        $data['address']['range'] = array(
          'attributes'  => array('type' => 'P'),
          'data'        => $v,
        );
      }
      elseif ($v = self::getLotMetroDistanceTransport($lot)) {
        $data['address']['range'] = array(
          'attributes'  => array('type' => 'T'),
          'data'        => $v,
        );
      }
    }
    else {
      //крупный населенный пункт
      $data['address']['district']  = self::getLotCity($lot);
      //название шоссе
      $data['address']['highway']   = $lot->array_wards[0];
      //расстояние от областного центра в километрах
      $data['address']['range']     = round(self::getLotDistanceMkad($lot));
    }

    $data = array_merge($data, $this->{$this->getDataMethod($lot->type)}($lot));

    //тэг для краткого описания
    $data['description']['short'] = self::getLotDescription($lot, 300);
    //тэг для полного описания
    $data['description']['full'] = self::getLotDescription($lot);

    //фотографии объекта
    $data['photo'] = array();
    if ($photo = $lot->getImage('pres')) {
      $data['photo'][] = self::ORG_SITE . $photo;
    }
    foreach ($lot->Photos as $photo) {
      if (!$photo->is_pdf && !$photo->is_xls) {
        $data['photo'][] = self::ORG_SITE . $photo->getImage('full');
      }
    }

    //тэг для контактов. Можно добавлять этот тег, если у агентства несколько отделений, этим тегом можно привязать к определенному объекту определенное отделение
    //название отделения
    $data['contact']['name']  = self::ORG_NAME;
    //телефон отделения
    $data['contact']['info']  = self::getLotPhone($lot);
    //электронная почта отделения (агента)
    $data['contact']['email'] = self::ORG_EMAIL;

    return array('offer' => $data);
  }

  protected function getDataArrayFlats($lot)
  {
    //цена. Если продажа, то цена всего объекта, если же аренда, то цена аренды за месяц
    $data['price']    = self::getLotPrice($lot);
    //валюта (enum: RUR; USD)
    $data['currency'] = self::getLotCurrency($lot);

    //общая площадь квартиры в квадратных метрах
    if ($v = self::getLotAreaTotal($lot)) {
      $data['area']['total']    = round($v);
    }
    //жилая площадь квартиры в квадратных метрах
    if ($v = self::getLotAreaLiving($lot)) {
      $data['area']['live']     = round($v);
    }
    //площадь кухни в квадратных метрах
    if ($v = self::getLotAreaKitchen($lot)) {
      $data['area']['kitchen']  = round($v);
    }

    //т.к. для выгрузки требуется, чтобы теги были в строго определенном порядке
    //общие теги определим сейчас, а заполним их позже (в общем методе)
    $data['description']['short'] = null;
    $data['description']['full'] = null;
    $data['photo'] = array();

    //количество комнат
    if ($v = self::getLotNbRooms($lot)) {
      $data['rooms'] = $v;
    }
    //количество этажей в доме
    if ($v = self::getLotNbFloors($lot)) {
      $data['floors'] = $v;
    }
    //этаж квартиры
    if ($v = self::getLotFloor($lot)) {
      $data['floors_count'] = $v;
    }

    $data['options'] = array('data' => array());
    //наличие лифта (boolean: 1; 0)
    if (!is_null($v = self::getLotIsElevator($lot))) {
      $data['options']['lift'] = (int) $v;
    }
    //наличие балкона (enum: 0 – нет; 1 - один балкон; 2 – два балкона; 3 - три балкона)
    if (!is_null($v = self::getLotNbBalconies($lot))) {
      $data['options']['balcon'] = $v;
    }
    //наличие лоджии (enum: 0 – нет; 1 – одна лоджия; 2 – две лоджии; 3 – три лоджии)
    if (!is_null($v = self::getLotNbLoggias($lot))) {
      $data['options']['loggia'] = $v;
    }
    //тип санузла (enum: U - смежный; S – раздельный; D – два санузла)
    if ($v = self::getLotBathrooms($lot)) {
      $data['options']['bathroom'] = $v;
    }
    //наличие телефона (boolean: 1; 0)
    if (!is_null($v = self::getLotIsTelephone($lot))) {
      $data['options']['havephone'] = (int) $v;
    }

    return $data;
  }

  protected function getDataArrayCountry($lot)
  {
    //цена. Если продажа, то цена всего объекта, если же аренда, то цена аренды за месяц
    $data['price']    = self::getLotPrice($lot);
    //валюта (enum: RUR; USD)
    $data['currency'] = self::getLotCurrency($lot);

    //площадь участка в сотках
    if ($v = self::getLotAreaLand($lot)) {
      $data['area']['plot']     = round($v);
    }
    //общая площадь квартиры в квадратных метрах
    if ($v = self::getLotAreaTotal($lot)) {
      $data['area']['total']    = round($v);
    }
    //жилая площадь квартиры в квадратных метрах
    if ($v = self::getLotAreaLiving($lot)) {
      $data['area']['live']     = round($v);
    }
    //площадь кухни в квадратных метрах
    if ($v = self::getLotAreaKitchen($lot)) {
      $data['area']['kitchen']  = round($v);
    }

    return $data;
  }

  protected function getDataArrayCommerce($lot)
  {
    //цена продажи или аренды (за год) всего объекта
    $data['price']['total']   = self::getLotPrice($lot);
    //цена продажи или аренды (за год) квадратного метра
    if (!empty($lot->price_from) && round($lot->price_from) > 0) {
      $data['price']['area']  = round($lot->price_from);
    }
    //валюта (enum: RUR; USD)
    $data['currency']   = self::getLotCurrency($lot);

    if ($v = self::getLotAreaTotal($lot)) {
      //общая площадь в квадратных метрах
      $data['area']['total']    = round($v);
      //вакантная площадь в квадратных метрах
      $data['area']['vacant']   = round($v);
    }

    return $data;
  }

  protected function validateLot(Lot $lot)
  {
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

    if (empty($lot->anons) && empty($lot->description)) {
      throw new Exception('anons and description are empty');
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

    if (self::getLotRegion($lot) == 'Москва') {
      if (empty($lot->address['street'])) {
        throw new Exception('street address is empty');
      }
      if (!$lot->getPrettyAddress('house', false)) {
        throw new Exception('house number is empty');
      }
    }
    else {
      if (!self::getLotCity($lot)) {
        throw new Exception('city is empty');
      }
      if (empty($lot->ward) && empty($lot->ward2)) {
        throw new Exception('wards is empty');
      }
      if (empty($lot->params['distance_mkad'])) {
        throw new Exception('distance_mkad parameter is empty');
      }
      if (!self::getLotDistanceMkad($lot)) {
        throw new Exception(sprintf('can\'t parse distance_mkad parameter as a number: "%s"', $lot->params['distance_mkad']));
      }
    }

    if ($this->_current_type == 'newbuilds') {

    }
    elseif ($this->_current_type == 'flats') {
      if (empty($lot->area_from) && empty($lot->area_to)) {
        throw new Exception('area is empty');
      }
      if (($v = self::getLotAreaTotal($lot)) < 1) {
        throw new Exception(sprintf('area is less than one: "%s"', $v));
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
    }
    elseif ($this->_current_type == 'country') {
      if ((!($v = self::getLotAreaTotal($lot)) || $v < 1) && (!($v = self::getLotAreaLandArray($lot)) || $v < 1)) {
        throw new Exception('both area and spaceplot parameter are empty or less than one');
      }
    }
    elseif ($this->_current_type == 'commerce') {
      if (empty($lot->area_from) && empty($lot->area_to)) {
        throw new Exception('area is empty');
      }
      if (($v = self::getLotAreaTotal($lot)) < 1) {
        throw new Exception(sprintf('area is less than one: "%s"', $v));
      }
    }
  }


  protected static function getLotOperationType($lot)
  {
    return $lot->is_rent_type ? 'R' : 'S';
  }

  protected static function getLotCityArea($lot)
  {
    return rtrim(parent::getLotCityArea($lot), ' АО');
  }

  protected static function getLotCurrency($lot)
  {
    return $lot->currency == 'EUR' ? 'RUR' : $lot->currency;
  }

  protected static function getLotPrice($lot)
  {
    return round(Currency::convert(parent::getLotPrice($lot), $lot->currency, self::getLotCurrency($lot)));
  }

  protected static function getLotBathrooms($lot)
  {
    //U - смежный; S – раздельный; D – два санузла
    $s = self::getLotNbBathroomsSeparate($lot);
    $c = self::getLotNbBathroomsCombined($lot);

    if ($s == 1 && !$c)   return 'S';
    if ($c == 1 && !$s)   return 'U';
    if ($s > 0 || $c > 0) return 'D';

    if ($v = self::getLotNbBathrooms($lot) > 1) return 'D';

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
          case 'Таунхаус':            return 'K';
          case 'Квартира':            return null;
          case 'Коттеджный поселок':  return null;
          default:                    return null;
        }
        //K - коттедж; A - земельный участок

      case 'comsell':
      case 'comrent':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Торговое помещение':              return 'T';
          case 'Офисное помещение':               return 'O';
          case 'Отдельно стоящее здание':         return 'F';
          case 'Готовый арендный бизнес':         return 'F';
          case 'Особняк':                         return 'F';
          case 'Помещение свободного назначения': return 'F';
          case 'Склад/складской комплекс':        return 'W';
          case 'Промышленный комплекс':           return 'F';
          case 'Земельный участок':               return 'F';
          case 'Прочее':                          return 'F';
        }
        //O – офис; W – склад; T – торговые; F – другое
    }

    return null;
  }
}
