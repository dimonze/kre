<?php
/*
 * Только продажа и только жилая недвижимость
 */

class exportTbaTask extends exportBaseTask
{
  const
    PARTNER       = 'tba',
    ENCODING      = 'utf-8',
    DATE_FORMAT   = 'Y-m-d\TH:i:s+03:00',
    PHONE_FORMAT  = '+7 (495) %d-%d-%d';

  protected
    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse','elitenew'),
      'country'   => array('outoftown'),
    );

  private
    $_dictionaries = array();


  protected function configure()
  {
    parent::configure();

    $this->name = 'tba';
  }


  protected function writeDocumentStart()
  {
    $this->_xml_writer->startDocument('1.0', self::ENCODING);
    $this->_xml_writer->startElement('offers');
    $this->_xml_writer->writeAttribute('date', date(self::DATE_FORMAT));
  }

  protected function writeDocumentFinish()
  {
    $this->_xml_writer->endElement();
    $this->_xml_writer->endDocument();
  }

  protected function getDataArray(Lot $lot)
  {
    $data['type']                   = 'продажа';
    $data['category']               = self::getLotCategory($lot);
    $data['url']                    = self::getLotUrl($lot);
    $data['creation-date']          = self::getLotCreationDate($lot);
    $data['last-update-date']       = self::getLotUpdationDate($lot);
    $data['price_hidden']           = '0';
    $data['price']['attributes']    = array(
      'value'     => self::getLotPrice($lot),
      'currency'  => self::getLotCurrency($lot),
    );

    $data['location']['region'] = self::getLotRegion($lot);
    if ($data['location']['region'] != 'Москва') {
      //название поселка (для загородной недвижимости). (см. справочник Поселки)
      if ($v = self::getLotDictionaryVillage($lot, $this->getDictionary('villages'))) {
        $data['location']['locality-name'] = $v;
      }
      //оригинальное название поселка хранящееся в базе источника
      if (!empty($lot->params['settlements_tba'])) {
        $data['location']['locality-original-name'] = $lot->params['settlements_tba'];
      }
      elseif (!empty($lot->params['cottageVillage'])) {
        $data['location']['locality-original-name'] = $lot->params['cottageVillage'];
      }
      //ближайший населенный пункт (для загородной недвижимости)
      if ($v = self::getLotCity($lot)) {
        $data['location']['nearest_town'] = $v;
      }
      //привязка к шоссе, допустимо указывать несклько привязок но не более трех
      //(возможно указание и для городской недвижимости, в этом случае расстояние до МКАД = 0)
      $data['location']['highway']['attributes']  = array(
        'distance'  => round(self::getLotDistanceMkad($lot)),
        'name'      => self::getLotDictionaryHighway($lot, $this->getDictionary('highways')),
      );
      //улица (обязательно для города)
      if (!empty($lot->address['street'])) {
        $data['location']['address']          = $lot->address['street'];
      }
      //номер дома (обязательно для города)
      if (!empty($lot->address['house'])) {
        $data['location']['house_humber']     = $lot->address['house'];
      }
      //корпус дома
      if (!empty($lot->address['building'])) {
        $data['location']['house_building']   = $lot->address['building'];
      }
      //строение дома
      if (!empty($lot->address['construction'])) {
        $data['location']['house_building2']  = $lot->address['construction'];
      }

      if (!is_null($v = self::getLotIsWater($lot))) {
        $data['water-supply'] = ($v ? 'да' : 'нет');
      }
      if (!is_null($v = self::getLotIsSewerage($lot))) {
        $data['sewerage-supply'] = ($v ? 'да' : 'нет');
      }
      if (!is_null($v = self::getLotIsElectricity($lot))) {
        $data['electricity-supply'] = ($v ? 'да' : 'нет');
      }
      if (!is_null($v = self::getLotIsGas($lot))) {
        $data['gas-supply'] = ($v ? 'да' : 'нет');
      }
    }
    else {
      //район города (см. справочник Районы Москвы)
      if ($v = self::getLotDictionaryDistrict($lot, $this->getDictionary('districts'))) {
        $data['location']['sub-locality-name'] = $v;
      }
      //район города в том виде, в котором он хранится в базе источника объявления
      if ($v = self::getLotDistrict($lot)) {
        $data['location']['sub-locality-original-name'] = $v;
      }
      //улица (обязательно для города)
      $data['location']['address']            = $lot->address['street'];
      //номер дома (обязательно для города)
      $data['location']['house_humber']       = $lot->address['house'];
      //корпус дома
      if (!empty($lot->address['building'])) {
        $data['location']['house_building']   = $lot->address['building'];
      }
      //строение дома
      if (!empty($lot->address['construction'])) {
        $data['location']['house_building2']  = $lot->address['construction'];
      }
      //привязка к станции метро, допустимо указывать несколько привязок, но не более трех (см. справочник Метро)
      $data['location']['metro']['attributes']['name'] = self::getLotDictionaryMetro($lot, $this->getDictionary('metros'));
      if ($v = self::getLotMetroDistanceWalk($lot)) {
        $data['location']['metro']['attributes']['distance_walk'] = self::getLotMetroDistanceWalk($lot);
      }
      elseif ($v = self::getLotMetroDistanceTransport($lot)) {
        $data['location']['metro']['attributes']['distance_auto'] = self::getLotMetroDistanceTransport($lot);
      }
      //название жилого комплекса (см. справочник Жилые комплексы)
      if ($v = self::getLotDictionaryEstate($lot, $this->getDictionary('estates'))) {
        $data['building-name'] = $v;
      }
      //оригинальное название ЖК хранящееся в базе источника
      if ($v = self::getLotEstate($lot)) {
        $data['building-original-name'] = $v;
      }

      //количество комнат (обязательно для городской недвижимости) (integer)
      $data['rooms'] = self::getLotNbRooms($lot);

      //количество спален (integer)
      if ($v = self::getLotNbBedrooms($lot)) {
        $data['bedrooms'] = $v;
      }
      //этаж (integer)
      if ($v = self::getLotFloor($lot)) {
        $data['floor'] = $v;
      }
      //общее количество этажей в доме (integer)
      if ($v = self::getLotNbFloors($lot)) {
        $data['floors-total'] = $v;
      }

      //наличие парковки (boolean: да; нет)
      if (!is_null($v = self::getLotIsParking($lot))) {
        $data['Parking'] = ($v ? 'да' : 'нет');
      }
    }

    //общая площадь (в кв.м.)
    if ($v = self::getLotAreaTotal($lot)) {
      $data['area'] = $v;
    }
    //площадь участка (в сотках)
    if ($v = self::getLotAreaLand($lot)) {
      $data['lot-area'] = $v;
    }
    //наличие отделки (enum: да; нет)
    if ($v = self::getLotDecoration($lot)) {
      $data['finish'] = $v;
    }

    if ($lot->lat && $lot->lng) {
      $data['location']['latitude']   = $lot->lat;
      $data['location']['longitude']  = $lot->lng;
    }

    $data['description']            = self::getLotDescription($lot);
    $data['contact_phone']          = self::getLotPhone($lot);

    //убрать логотипы с фоток лотов newbuilds и country
    $postfix = in_array($lot->type, array('elitenew', 'outoftown')) ? '_' : '';
    $data['Image'] = array();
    if ($photo = $lot->getImage('pres'.$postfix)) {
      $data['Image'][] = self::ORG_SITE . $photo;
    }
    foreach ($lot->Photos as $photo) {
      if (!$photo->is_pdf && !$photo->is_xls) {
        $data['Image'][] = self::ORG_SITE . $photo->getImage('full'.$postfix);
      }
    }

    return array('offer' => array('attributes' => array('internal-id' => $lot->id), 'data' => $data));
  }

  protected function validateLot(Lot $lot)
  {
    if (!in_array($lot->type, $this->getAllowedTypes())) {
      throw new Exception(sprintf('not allowed lot type: "%s"', $lot->type));
    }
    if (!self::getLotCategory($lot)) {
      throw new Exception(sprintf('unrecognized lot category for type "%s"', $lot->type.(isset($lot->params['objecttype']) ? ' ('.$lot->params['objecttype'].')' : '')));
    }
    if (($lot->is_city_type && self::getLotRegion($lot) != 'Москва')
     || ($lot->is_country_type && self::getLotRegion($lot) != 'Московская область')) {
      throw new Exception(sprintf('lot of type "%s" has unexpected region: "%s"', $lot->type, self::getLotRegion($lot)));
    }

    if (($v = self::getLotPrice($lot)) && empty($v)) {
      throw new Exception('price is empty');
    }
    if (!is_numeric($v)) {
      throw new Exception(sprintf('price is not a valid number: "%s"', $v));
    }
    if ($v <= 0) {
      throw new Exception(sprintf('price is less than or equal to zero: "%s"', $v));
    }

    if (self::getLotFloor($lot) && self::getLotNbFloors($lot) && self::getLotFloor($lot) > self::getLotNbFloors($lot)) {
      throw new Exception(sprintf('floor number is greather than total floors: %s > %s', self::getLotFloor($lot), self::getLotNbFloors($lot)));
    }

    if ($lot->is_city_type) {
      if (empty($lot->address['street'])) {
        throw new Exception('lot street address is empty');
      }
      if (empty($lot->address['house'])) {
        throw new Exception('lot address house number is empty');
      }

      if (empty($lot->metro_id)) {
        throw new Exception('lot metro is empty');
      }
      if (!self::getLotDictionaryMetro($lot, $this->getDictionary('metros'))) {
        throw new Exception(sprintf('can\'t find matching metro: "%s"', $lot->metro));
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
    }
    elseif ($lot->is_country_type) {
      if (empty($lot->ward) && empty($lot->ward2)) {
        throw new Exception('lot wards is empty');
      }
      if (!self::getLotDictionaryHighway($lot, $this->getDictionary('highways'))) {
        throw new Exception(sprintf('can\'t find matching highway: "%s"', $lot->pretty_wards));
      }

      if (empty($lot->params['distance_mkad'])) {
        throw new Exception('distance_mkad parameter is empty');
      }
      if (!self::getLotDistanceMkad($lot)) {
        throw new Exception(sprintf('can\'t parse distance_mkad parameter as a number: "%s"', $lot->params['distance_mkad']));
      }
    }
  }


  private function getDictionary($dictionary)
  {
    $dictionaries = array(
      'districts' => 'locations',
      'highways'  => 'highways',
      'metros'    => 'metros',
      'villages'  => 'settlements',
      'estates'   => 'estates',
    );

    if (!in_array($dictionary, array_keys($dictionaries))) {
      throw new Exception(sprintf('unknown dictionary "%s"', $dictionary));
    }

    if (!isset($this->_dictionaries[$dictionary])) {
      $this->logSection('fetch', sprintf('%s dictionary', $dictionary));

      $url  = sprintf('http://topba.ru/assets/directory.php?type=%s', $dictionaries[$dictionary]);
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      if (!$response = curl_exec($curl)) {
        throw new Exception(sprintf('curl response fail: "%s"', $url));
      }

      curl_close($curl);

      $xml = simplexml_load_string($response);
      foreach ($xml as $e) {
        $this->_dictionaries[$dictionary][(string) $e['id']] = $e['name']->__toString();
        if (isset($e->alt)) {
          $this->_dictionaries[$dictionary][(string) $e->alt] = $e['name']->__toString();
        }
      }

      unset($curl, $xml);
    }

    return $this->_dictionaries[$dictionary];
  }


  protected static function getLotCurrency($lot)
  {
    return $lot->currency == 'RUR' ? 'RUB' : $lot->currency;
  }

  protected static function getLotDecoration($lot)
  {
    if (!empty($lot->params['about_decoration'])) {
      switch ($lot->params['about_decoration']) {
        case 'без отделки':   return 'нет';
        case 'с отделкой':    return 'да';
      }
    }

    return null;
  }

  protected static function getLotCategory($lot)
  {
    switch($lot->type) {
      case 'eliteflat':
      case 'elitenew':
      case 'penthouse':
      case 'flatrent':
        return 'квартира';

      case 'outoftown':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Участок':             return 'участок';
          case 'Таунхаус':            return 'таунхаус';
          case 'Коттедж':             return 'дом';
          case 'Коттеджный поселок':  return null;
          case 'Квартира':            return null;
          default:                    return null;
        }
    }

    return null;
  }

  protected static function getLotDictionaryHighway($lot, array $dictionary)
  {
    $wards = array_map(function($v) { return $v.' шоссе'; }, $lot->array_wards);

    foreach ($wards as $w) {
      if (isset($dictionary[mb_strtolower($w)])) return $dictionary[mb_strtolower($w)];
    }

    return null;
  }

  protected static function getLotDictionaryMetro($lot, array $dictionary)
  {
    $metro = $lot->metro;

    if ($metro == 'Преображенская пл.') return 'Преображенская площадь';
    if (isset($dictionary[mb_strtolower($metro)])) return $dictionary[mb_strtolower($metro)];

    return null;
  }

  protected static function getLotDictionaryDistrict($lot, array $dictionary)
  {
    if (empty($lot->district_id))         return null;
    if (!$v = self::getLotDistrict($lot)) return null;

    if (isset($dictionary[mb_strtolower($v)]))  return $dictionary[mb_strtolower($v)];
    if (in_array($lot->district_id, array_merge(range(1, 19), array(31)))) {
      return 'Центральный АО';
    }
    elseif ($lot->district_id == 30) {
      return 'Западный АО';
    }

    return null;
  }

  protected static function getLotDictionaryVillage($lot, array $dictionary)
  {
    if (!empty($lot->params['settlements_tba'])) {
      $v = $lot->params['settlements_tba'];
    }
    elseif (!empty($lot->params['cottageVillage'])) {
      $v = $lot->params['cottageVillage'];
    }
    else {
      return null;
    }

    if (isset($dictionary[mb_strtolower($v)])) return $dictionary[mb_strtolower($v)];

    return self::findSimilarString($v, $dictionary);
  }

  protected static function getLotDictionaryEstate($lot, array $dictionary)
  {
    if (!$v = self::getLotEstate($lot)) return null;

    if (isset($dictionary[mb_strtolower($v)])) return $dictionary[mb_strtolower($v)];

    return self::findSimilarString($v, $dictionary);
  }
}