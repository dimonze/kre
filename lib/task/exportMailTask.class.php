<?php

class exportMailTask extends exportBaseTask
{
  const
    PARTNER       = 'mail',
    ENCODING      = 'utf-8',
    DATE_FORMAT   = 'Y-m-d',
    PHONE_FORMAT  = '8 (495) %d-%d%d',
    CLIENT_ID     = 'p8583';

  protected
    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse','flatrent','elitenew'),
      'commerce'  => array('comrent', 'comsell'),
      'country'   => array('outoftown', 'cottage'),
    );


  protected function configure()
  {
    parent::configure();

    $this->name = 'mail';
  }


  protected function writeDocumentStart()
  {
    $this->_xml_writer->startDocument('1.0', self::ENCODING);
    $this->_xml_writer->startElement('root');
  }

  protected function writeDocumentFinish()
  {
    $this->_xml_writer->endElement();
    $this->_xml_writer->endDocument();
  }

  protected function getDataArray(Lot $lot)
  {
    //Идентификатор объекта в вашей базе данных, должно соответствовать аналогичному объекту в вашей базе данных (integer)
    $data['direction']    = 'предложение';
    $data['provider']     = self::CLIENT_ID;
    $data['id']           = $lot->id;
    $data['part']         = self::getLotCategory($lot);
    $data['object']       = self::getLotObjectType($lot);
    $data['bargain']      = self::getLotOperationType($lot);
    $data['lastupdated']  = self::getLotUpdationDate($lot);
    $data['url']          = self::getLotUrl($lot);

    $data['country']      = 'Россия';
    //Область, в которой находится объект недвижимости (string)
    //Если квартира находится в, например, Москве, то она одновременно находится и в Московской области
    $data['state']        = 'Московская';
    //Район области/крае, в которой находится объект недвижимости (string)
    //Тег обязателен для заполнения в случае отсутствия информации в теге <town>
    if ($v = self::getLotRegionDistrict($lot)) {
      $data['state_region'] = $v;
    }
    //Любой населенный пункт (деревня, село, город и так далее; в том числе и основной город, например, Москва) (string)
    //Тег обязателен для заполнения в случае отсутствия информации в теге <state_region>
    if ($v = self::getLotCity($lot)) {
      $data['town'] = $v;
    }

    if (self::getLotRegion($lot) == 'Москва') {
      //Для городской недвижимости Москвы - административный округ (enum)
      if ($v = self::getLotCityArea($lot)) {
        $data['district'] = $v;
      }
      //Район в населенном пункте указанном в <town>, районы могут быть не только, например, в Москве, но в любом другом городе, селе, поселке, деревне (string)
      if ($v = self::getLotCityDistrict($lot)) {
        $data['region'] = $v;
      }
      //Ближайшая станция метро. Поддерживаются все станции метро в городах с метрополитеном (string)
      if (!empty($lot->metro_id)) {
        $data['subway'] = $lot->metro;
      }
      //Удаленность от метро в минутах (integer)
      if ($v = self::getLotMetroDistanceWalk($lot)) {
        $data['distance'] = $v;
        //Тип и единица измерения удаленности (enum)
        $data['distancetype'] = 'минут пешком';
      }
      elseif ($v = self::getLotMetroDistanceTransport($lot)) {
        $data['distance'] = $v;
        //Тип и единица измерения удаленности (enum)
        $data['distancetype'] = 'минут транспортом';
      }
    }
    else {
      //Для загородной недвижимости - шоссе (enum)
      $data['district'] = $lot->array_wards[0];
      //Удаленность от МКАД в километрах (integer).
      if ($v = self::getLotDistanceMkad($lot)) {
        $data['distance'] = round($v);
        //Тип и единица измерения удаленности (enum)
        $data['distancetype'] = 'км от МКАД';
      }
    }
    //Улица, на которой находится объект (string)
    if (!empty($lot->address['street'])) {
      $data['street'] = $lot->address['street'];
    }
    //Номер дома, корпус, литера, строение в формате: д. 2, к. 1, л. А, стр. 2 (string)
    if ($v = $lot->getPrettyAddress('house', false)) {
      $data['house'] = str_replace(' корп.', ' к.', $v);
    }
    //Полный адрес объекта, включая улицу и номер дома (string)
    $data['address'] = $lot->getPrettyAddress('region', false);

    //ln – долгота; lt – широта
    if ($lot->lat && $lot->lng) {
      $data['maplnlt'] = sprintf('%s,%s', $lot->lng, $lot->lat);
    }


    //Этаж, на котором расположен объект (integer)
    if ($v = self::getLotFloor($lot)) {
      $data['floor'] = $v;
    }
    //Количество этажей в здании (integer)
    if ($v = self::getLotNbFloors($lot)) {
      $data['nfloor'] = $v;
    }
    //Площадь объекта и/или земельного участка (float)
    if (self::getLotCategory($lot) == 'загородная') {
      if ($v = self::getLotAreaTotalArray($lot)) {//Жилая площадь
        foreach ($v as $vv) $data['areas'][] = array('area' => array(
          'attributes'  => array('type' => 'live'),
          'data'        => $vv,
        ));
      }
      if ($v = self::getLotAreaLandArray($lot)) {//Площадь участка (сотки)
        foreach ($v as $vv) $data['areas'][] = array('area' => array(
          'attributes'  => array('type' => 'plot'),
          'data'        => $vv,
        ));
      }
    }
    else {
      if ($v = self::getLotAreaTotalArray($lot)) {//Общая площадь
        foreach ($v as $vv) $data['areas'][] = array('area' => array(
          'attributes'  => array('type' => 'total'),
          'data'        => $vv,
        ));
      }
    }

    //Количество комнат для жилой и загородной недвижимости (integer)
    //В случае указания количества комнат От-До количеством тегов room не может быть больше 2
    if (in_array(self::getLotCategory($lot), array('жилая', 'загородная'))) {
      if ($v = self::getLotNbRoomsArray($lot)) {
        foreach ($v as $vv) $data['rooms'][] = array('room' => $vv);
      }
    }
    //Тип здания для продажи квартир в Москве и МО (enum)
    if (in_array(self::getLotObjectType($lot), array('квартира', 'новостройка'))) {
      if ($v = self::getLotConstructionType($lot)) {
        $data['housetype'] = $v;
      }
    }
    //Количество реализуемых квартир для новостроек (integer)
    if (self::getLotObjectType($lot) == 'новостройка') {
      if (!empty($lot->params['flats']) && ctype_digit($lot->params['flats']) && $lot->params['flats'] > 0) {
        $data['flatcount'] = (int) $lot->params['flats'];
      }
    }

    //Признак новостройки (boolean)
    if ($lot->is_newbuild_type) {
      $data['newbuilding'] = 1;
      //Дата госкомиссии для новостройки (string). Формат даты: IVкв 2007г. или IVкв2007г. или 4кв 2007г. или 4кв2007. Иные форматы игнорируются
      if ($v = self::getLotBuiltQuarter($lot) && $vv = self::getLotBuiltYear($lot)) {
        $data['newbuilding_date'] = sprintf('%dкв %dг.', $v, $vv);
      }
    }
    else {
      $data['newbuilding'] = 0;
    }

    //Цены на объект недвижимости (integer)
    //В случае, если продается несколько квартир (или площадь реализуемого помещения может быть "от-до"), то и цена должна включать минимальное и максимальное значение
    //Вложенных тегов price не может быть больше двух
    if ($v = self::getLotPriceArray($lot)) {
      foreach ($v as $vv) $data['prices'][] = array('price' => round($vv));
    }
    //Валюта, в которой рассчитывалась Стоимость объекта недвижимости (enum: RUR; USD; EUR)
    $data['currency'] = $lot->currency;
    //Единица измерения стоимости (enum:  total; m2)
    $data['priceunit'] = 'total';
    //Указание периода, за который указана стоимость (enum)
    $data['priceperiod'] = $lot->is_rent_type ? ($lot->is_commercial_type ? 'year' : 'month') : 'total';
    //Признак продажи с помощью ипотечного кредита (boolean)
    if (!is_null($v = self::getLotIsMortgage($lot))) {
      $data['mortgage'] = (int) $v;
    }

    //Описание объекта (string)
    $data['description']  = self::getLotDescription($lot);
    $data['company']      = self::ORG_NAME;
    $data['phone']        = self::getLotPhone($lot);
    $data['email']        = self::ORG_EMAIL;

    //Множество ссылок на изображения объекта недвижимости, каждая из которых заключена в тег image
    //В случае, если у изображения установлен атрибут primary="1" - оно считается основным
    //Все изображения по размеру не должны быть меньше 250px по ширине и меньше 180px по высоте
    $data['images'] = array();
    if ($photo = $lot->getImage('pres')) {
      $data['images'][] = array('image' => array(
        'attributes'  => array('primary' => 1),
        'data'        => self::ORG_SITE . $photo,
      ));
    }
    foreach ($lot->Photos as $photo) {
      if (!$photo->is_pdf && !$photo->is_xls) {
        $data['images'][] = array('image' => self::ORG_SITE . $photo->getImage('full'));
      }
    }

    return array('offer' => $data);
  }

  protected function validateLot(Lot $lot)
  {
    if (!in_array($lot->type, $this->getAllowedTypes())) {
      throw new Exception(sprintf('not allowed lot type: "%s"', $lot->type));
    }
    if (!self::getLotObjectType($lot)) {
      throw new Exception(sprintf('unrecognized objecttype for "%s"', $lot->type.(isset($lot->params['objecttype']) ? ' ('.$lot->params['objecttype'].')' : '')));
    }
    if (($lot->is_city_type && self::getLotRegion($lot) != 'Москва')
     || ($lot->is_commercial_type && self::getLotRegion($lot) != 'Москва')
     || ($lot->is_country_type && self::getLotRegion($lot) != 'Московская')) {
      throw new Exception(sprintf('lot of type "%s" has unexpected region: "%s"', $lot->type, self::getLotRegion($lot)));
    }
    if (!self::getLotCity($lot) && !self::getLotRegionDistrict($lot)) {
      throw new Exception('both city and region district are empty');
    }
    if (!$lot->getPrettyAddress('street', false)) {
      throw new Exception('lot address is empty');
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
    if ($v <= 0) {
      throw new Exception(sprintf('price is less than or equal to zero: "%s"', $v));
    }

    if (self::getLotFloor($lot) && self::getLotNbFloors($lot) && self::getLotFloor($lot) > self::getLotNbFloors($lot)) {
      throw new Exception(sprintf('floor number is greather than total floors: %s > %s', self::getLotFloor($lot), self::getLotNbFloors($lot)));
    }

    if (self::getLotRegion($lot) != 'Москва') {
      if (empty($lot->ward) && empty($lot->ward2)) {
        throw new Exception('wards is empty');
      }
    }
  }


  protected static function getLotRegion($lot)
  {
    switch (parent::getLotRegion($lot)) {
      case 'Московская область':  return 'Московская';
      default:                    return 'Москва';
    }
  }

  protected static function getLotNbRoomsArray($lot)
  {
    $data = array();
    if (!empty($lot->params['roomsfrom']) && ctype_digit($lot->params['roomsfrom']) && $lot->params['roomsfrom'] > 0) {
      $data[] = (int) $lot->params['roomsfrom'];
    }
    if (!empty($lot->params['roomsto']) && ctype_digit($lot->params['roomsto']) && $lot->params['roomsto'] > 0) {
      $data[] = (int) $lot->params['roomsto'];
    }
    if (count($data) > 1) return array_unique($data, SORT_NUMERIC);

    if (!empty($lot->params['rooms'])) {
      if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $lot->params['rooms'], $matches) && !empty($matches[1]) && !empty($matches[2])) {
        return array_unique(array($matches[1], $matches[2]), SORT_NUMERIC);
      }
      elseif (ctype_digit($lot->params['rooms']) && $lot->params['rooms'] > 0) {
        return array((int) $lot->params['rooms']);
      }
    }
    elseif (!empty($data)) {
      return $data;
    }

    return null;
  }

  protected static function getLotConstructionType($lot)
  {
    if (!empty($lot->params['construction'])) {
      $v = $lot->params['construction'];
      if (mb_stripos($v, 'кирпич') !== false && mb_stripos($v, 'монолит') !== false)  return 'кирпич-монолит';
      if (mb_stripos($v, 'кирпич') !== false)   return 'кирпич';
      if (mb_stripos($v, 'панель') !== false)   return 'панель';
      if (mb_stripos($v, 'монолит') !== false)  return 'монолит';
      if (mb_stripos($v, 'сталин') !== false)   return 'сталинский';
    }

    return null;
  }

  protected static function getLotCategory($lot)
  {
    switch($lot->type) {
      case 'eliteflat':
      case 'penthouse':
      case 'flatrent':
      case 'elitenew':
        return 'жилая';

      case 'outoftown':
      case 'cottage':
        return 'загородная';

      case 'comrent':
      case 'comsell':
        return 'коммерческая';
    }
  }

  protected static function getLotObjectType($lot)
  {
    switch($lot->type) {
      case 'eliteflat':
      case 'penthouse':
      case 'flatrent':
      case 'elitenew':
        return $lot->is_newbuild_type ? 'новостройка' : 'квартира';
        //квартира; комната; новостройка

      case 'cottage':
      case 'outoftown':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Участок':             return 'участок';
          case 'Коттедж':             return 'коттедж';
          case 'Таунхаус':            return 'таунхаус';
          case 'Квартира':            return null;
          case 'Коттеджный поселок':  return 'коттеджный поселок';
          default:                    return null;
        }
        //дом; участок; коттедж; дача; особняк; таунхаус; коттеджный поселок

      case 'comsell':
      case 'comrent':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Торговое помещение':              return 'ТП';
          case 'Офисное помещение':               return 'офис';
          case 'Отдельно стоящее здание':         return 'ОСЗ';
          case 'Готовый арендный бизнес':         return 'готовый бизнес';
          case 'Особняк':                         return 'здание';
          case 'Помещение свободного назначения': return 'ПСН';
          case 'Склад/складской комплекс':        return 'склад';
          case 'Промышленный комплекс':           return 'производство';
          case 'Земельный участок':               return 'земля';
          case 'Прочее':                          return null;
        }
        //офис; склад; ТП; ПСН; ПСУ; ОСЗ; нежилое помещение; здание; земля; магазин; готовый бизнес; инвестпроект; гостиница; производство;
    }

    return null;
  }
}
