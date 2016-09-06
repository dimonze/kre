<?php

/*
 * Допускается публикация XML-файла в архиве (расширение .gz, алгоритм сжатия GNU ZIP). Архивирование рекомендуется выполнять при размере XML-файла от 20 МБ.
 */

class exportIrrTask extends exportBaseTask
{
  const
    PARTNER       = 'irr',
    ENCODING      = 'utf-8',
    DATE_FORMAT   = 'Y-m-d\TH:i:s',
    PHONE_FORMAT  = '+7 (495) %d-%d-%d',
    CLIENT_ID     = 6324889;

  protected
    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse','flatrent','elitenew'),
      'commerce'  => array('comrent', 'comsell'),
      'country'   => array('outoftown', 'cottage'),
    );


  protected function configure()
  {
    parent::configure();

    $this->name = 'irr';
  }


  protected function writeDocumentStart()
  {
    $this->_xml_writer->startDocument('1.0', self::ENCODING);
    $this->_xml_writer->startElement('users');
    $this->_xml_writer->startElement('user');
    $this->_xml_writer->writeAttribute('deactivate-untouched', 'false');
    $this->_xml_writer->startElement('match');
    $this->_xml_writer->writeElement('user-id', self::CLIENT_ID);
    $this->_xml_writer->endElement();
  }

  protected function writeDocumentFinish()
  {
    $this->_xml_writer->endElement();
    $this->_xml_writer->endDocument();
  }

  protected function getDataArray(Lot $lot)
  {
    //Числовой идентификатор объявления в базе Интернет-партнера. Должен быть уникальным в пределах файла.
    $root_attributes['source-id'] = $lot->id;
    //URI рубрики сайта IRR.RU в которой предполагается размещение объявления.
    $root_attributes['category']  = self::getLotCategory($lot);
    //Дата и время окончания срока действия объявления на сайте IRR.RU.
    $root_attributes['validtill'] = date(self::DATE_FORMAT, strtotime('+7 days'));
    //Признак принадлежности объявления Интернет-партнеру. Всегда должен принимать значение «1».
    $root_attributes['power-ad']  = 1;

    //Заголовок объявления
    $data['title'] = $lot->name;
    //Текст объявления. Не более 1000 символов
    $data['description'] = self::getLotDescription($lot, 1000);
    //Цена объекта (float)
    $data['price']['attributes']['value'] = self::getLotPrice($lot);
    //Валюта, в которой измеряется цена (enum: RUR; USD; EUR)
    $data['price']['attributes']['currency'] = $lot->currency;

    $fields = array();
    //Контактные данные
    $fields['mail']   = self::ORG_EMAIL;
    $fields['phone']  = self::getLotPhone($lot);
    $fields['web']    = self::getLotUrl($lot);

    //Название субъекта РФ без уточнения типа (область, республика и т.д.)
    $fields['region'] = self::getLotRegion($lot);
    //Район субъекта РФ. Указывается название без уточнения типа (район, р-н и т.д.)
    if ($fields['region'] != 'Москва' && ($v = self::getLotRegionDistrict($lot))) {
      $fields['address_area'] = $v;
    }
    //Населенный пункт или город
    if ($v = self::getLotCity($lot)) {
      $fields['address_city'] = $v;
    }
    //Административный округ города. Указывается название без уточнения типа (АО, округ и т.д.)
    if ($v = self::getLotCityArea($lot)) {
      $fields['address_ao'] = $v;
    }
    //Район города. Указывается название без уточнения типа (район, р-н и т.д.)
    if ($v = self::getLotCityDistrict($lot)) {
      $fields['address_district'] = $v;
    }
    //Улица населенного пункта или города
    if (!empty($lot->address['street'])) {
      $fields['address_street'] = $lot->address['street'];
    }
    //Номер дома. Указывается без уточнения типа д. (integer)
    if (!empty($lot->address['house']) && is_numeric($lot->address['house'])) {
      $fields['address_house'] = $lot->address['house'];
    }
    //Станция метро. Указывается название без уточнения типа м.
    if (!empty($lot->metro_id)) {
      $fields['metro'] = $lot->metro;
    }
    //Расстояние до указанной станции метро в минутах (пешком) (integer)
    if ($v = self::getLotMetroDistanceWalk($lot)) {
      $fields['distance'] = $v;
    }
    //Название шоссе (только для Московской области) с указанием типа объекта
    if ($fields['region'] != 'Москва') {
      if (!empty($lot->ward) || !empty($lot->ward2)) {
        $fields['shosse'] = sprintf('%s ш.', $lot->array_wards[0]);
      }
      //Удаленность от МКАД по указанному шоссе (в километрах) (float)
      if ($v = self::getLotDistanceMkad($lot)) {
        $fields['distance_mkad'] = $v;
      }
    }
    //Координаты
    if ($lot->lat && $lot->lng) {
      $fields['geo_lat'] = $lot->lat;
      $fields['geo_lng'] = $lot->lng;
    }

    $fields = array_merge($fields, $this->{$this->getDataMethod($lot->type)}($lot));

    $data['custom-fields'] = array();
    foreach ($fields as $name => $value) {
      $data['custom-fields'][] = array('field' => array(
        'attributes'  => array('name' => $name),
        'data'        => $value,
      ));
    }
    unset($fields);


    $c = 0;
    $data['fotos'] = array();
    if ($photo = $lot->getImage('pres')) {
      $data['fotos'][] = array('foto-remote' => array('attributes' => array(
        'url' => self::ORG_SITE . $photo,
        'md5' => md5_file(sfConfig::get('sf_web_dir').$photo),
      )));
      $c++;
    }
    foreach ($lot->Photos as $photo) {
      if (!$photo->is_pdf && !$photo->is_xls) {
        $data['fotos'][] = array('foto-remote' => array('attributes' => array(
          'url' => self::ORG_SITE . $photo->getImage('full'),
          'md5' => md5_file(sfConfig::get('sf_web_dir').$photo->getImage('full')),
        )));
        if (++$c >= 10) break;//Максимальное количество фотографий, допустимых для загрузки в объявление – 10 фотографий.
      }
    }

    return array('store-ad' => array(
      'attributes'  => $root_attributes,
      'data'        => $data,
    ));
  }

  protected function getDataArrayFlats($lot)
  {
    $fields = array();
    //Комнат в квартире (integer)
    $fields['rooms'] = self::getLotNbRooms($lot);
    //Общая площадь (float)
    if ($v = self::getLotAreaTotal($lot)) {
      $fields['meters-total'] = $v;
    }
    //Этаж (integer)
    if ($v = self::getLotFloor($lot)) {
      $fields['etage'] = $v;
    }
    //Этажей в здании
    if ($v = self::getLotNbFloors($lot)) {
      if (!isset($fields['etage']) || $fields['etage'] <= $v) {
        $fields['etage-all'] = $v;
      }
    }
    //Жилая площадь (float)
    if ($v = self::getLotAreaLiving($lot)) {
      $fields['meters-living'] = $v;
    }
    //Площадь кухни (float)
    if ($v = self::getLotAreaKitchen($lot)) {
      $fields['kitchen'] = $v;
    }
    //Отделка (enum)
    if ($v = self::getLotDecoration($lot)) {
      $fields['state'] = $v;
    }
    //Телефон (boolean: есть; нету; да; нет)
    if (!is_null($v = self::getLotIsTelephone($lot))) {
      $fields['telephone'] = ($v ? 'да' : 'нет');
    }
    //Интернет (boolean)
    if (!is_null($v = self::getLotIsInternet($lot))) {
      $fields['internet'] = ($v ? 'да' : 'нет');
    }
    //Балкон/Лоджия (bool)
    if (self::getLotNbBalconies($lot) || self::getLotNbLoggias($lot)) {
      $fields['balcony'] = 'да';
    }
    //Год постройки
    if ($v = self::getLotBuiltYear($lot)) {
      $fields['house-year'] = $v;
    }
    //Материал стен (enum)
    if ($v = self::getLotConstructionType($lot)) {
      $fields['walltype'] = $v;
    }
    //Высота потолков (float)
    if ($v = self::getLotCeilingHeight($lot)) {
      $fields['house-ceiling-height'] = $v;
    }
    //Система водоснабжения (enum)
    if ($v = self::getLotWater($lot)) {
      $fields['water'] = $v;
    }
    //Газ в доме (bolean)
    if (!is_null($v = self::getLotIsGas($lot))) {
      $fields['gas'] = ($v ? 'да' : 'нет');
    }
    //Лифты в здании (boolean)
    if (!is_null($v = self::getLotIsElevator($lot))) {
      $fields['house-lift'] = ($v ? 'да' : 'нет');
    }
    //Санузел (enum)
    if ($v = self::getLotBathrooms($lot)) {
      $fields['toilet'] = $v;
    }

    //Краткосрочная аренда (bool)
    if ($lot->is_rent_type) {
      $fields['rentLong'] = 'нет';
    }

    return $fields;
  }

  protected function getDataArrayCountry($lot)
  {
    $fields = array();

    if (!$lot->is_land_type) {
      //Площадь строения (float)
      if ($v = self::getLotAreaTotal($lot)) {
        $fields['meters-total'] = $v;
      }
      //Строение (enum)
      if ($v = self::getLotObjectType($lot)) {
        $fields['object'] = $v;
      }
      //Количество этажей (integer)
      if ($v = self::getLotNbFloors($lot)) {
        $fields['etage-all'] = $v;
      }
      //Количество комнат (integer)
      if ($v = self::getLotNbRooms($lot)) {
        $fields['rooms'] = $v;
      }
    }

    //Площадь участка (float)
    if ($v = self::getLotAreaLand($lot)) {
      $fields['land'] = $v;
    }

    return $fields;
  }

  protected function getDataArrayCommerce($lot)
  {
    $fields = array();
    //Общая площадь (float)
    $fields['square-min'] = self::getLotAreaTotal($lot);
    //Общая площадь (float). В настоящий момент необходимо указывать два тега с общей площадью, т.к. через некоторое время произойдут изменения
    if (!$lot->params['objecttype'] == 'Особняк') {
      $fields['meters-total'] = self::getLotAreaTotal($lot);
    }

    //Этажей в здании (integer)
    if ($v = self::getLotNbFloors($lot)) {
      $fields['etage-all'] = $v;
    }
    //Год постройки
    if ($v = self::getLotBuiltYear($lot)) {
      $fields['house-year'] = $v;
    }
    //Материал стен (enum)
    if ($v = self::getLotConstructionType($lot)) {
      $fields['walltype'] = $v;
    }
    //Высота потолков (float)
    if ($v = self::getLotCeilingHeight($lot)) {
      $fields['house-ceiling-height'] = $v;
    }
    //Парковка (boolean)
    if (!is_null($v = self::getLotIsParking($lot))) {
      $fields['parking'] = ($v ? 'да' : 'нет');
    }

    if ($lot->params['objecttype'] == 'Офисное помещение') {
      //Тип здания (enum)
      if ($v = self::getLotBuildType($lot)) {
        $fields['buildingtype'] = $v;
      }
      //Класс (enum)
      if ($v = self::getLotBuildClass($lot)) {
        $fields['class'] = $v;
      }
      //Общее количество машиномест (integer)
      if (!empty($lot->params['parking']) && ctype_digit($lot->params['parking']) && $lot->params['parking'] > 0) {
        $fields['cars'] = $lot->params['parking'];
      }
      //Лифты в здании (boolean)
      if (!is_null($v = self::getLotIsElevator($lot))) {
        $fields['house-lift'] = ($v ? 'да' : 'нет');
      }
      //Центральное кондиционирование (boolean)
      if ($v = self::getLotIsAircondCentral($lot)) {
        $fields['conditioning'] = 'да';
      }
    }
    elseif ($lot->params['objecttype'] == 'Склад/складской комплекс') {
      //Тип здания (enum)
      if ($v = self::getLotBuildType($lot)) {
        $fields['warehouse_type'] = $v;
      }
      //Назначение помещения (enum)
      if ($v = self::getLotPurpose($lot)) {
        $fields['warehouse_type_object'] = $v;
      }
      //Подключенная мощность, кВт (integer)
      if ($v = self::getLotElectricityKvt($lot)) {
        $fields['electro'] = $v;
      }
    }
    elseif ($lot->params['objecttype'] == 'Отдельно стоящее здание') {
      //Тип здания (enum)
      if ($v = self::getLotBuildType($lot)) {
        $fields['trading_type'] = $v;
      }
    }
    elseif ($lot->params['objecttype'] == 'Торговое помещение') {
      //Тип здания (enum)
      if ($v = self::getLotBuildType($lot)) {
        $fields['trading_type'] = $v;
      }
      //Назначение помещения (enum)
      if ($v = self::getLotPurpose($lot)) {
        $fields['trading_purpose'] = $v;
      }
    }
    elseif ($lot->params['objecttype'] == 'Прочее') {
      //Тип здания (enum)
      if ($v = self::getLotBuildType($lot)) {
        $fields['trading_type'] = $v;
      }
    }

    //Период аренды
    if ($lot->is_rent_type) {
      $fields['rent_period'] = 'год';
    }
    //Единица площади (enum: за кв.м; за все)
    $fields['unit'] = 'за все';
    //Тип цены (enum: за все; за кв. м.; за сотку)
    $fields['price_type'] = 'за все';

    return $fields;
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
     || ($lot->is_commercial_type && self::getLotRegion($lot) != 'Москва')
     || ($lot->is_country_type && self::getLotRegion($lot) != 'Московская')) {
      throw new Exception(sprintf('lot of type "%s" has unexpected region: "%s"', $lot->type, self::getLotRegion($lot)));
    }
    if (!self::getLotCity($lot)) {
      throw new Exception('city is empty');
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

    if (self::getLotCategory($lot) == '/real-estate/apartments-sale/new') {//Новостройки
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
      if (self::getLotFloor($lot) && self::getLotNbFloors($lot) && self::getLotFloor($lot) > self::getLotNbFloors($lot)) {
        throw new Exception(sprintf('floor number is greather than total floors: %s > %s', self::getLotFloor($lot), self::getLotNbFloors($lot)));
      }

      if (empty($lot->area_from) && empty($lot->area_to)) {
        throw new Exception('area is empty');
      }
      if (($v = self::getLotAreaTotal($lot)) < 1) {
        throw new Exception(sprintf('area is less than one: "%s"', $v));
      }
    }
    elseif (self::getLotCategory($lot) == '/real-estate/apartments-sale/secondary') {//Вторичный рынок
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
      if (self::getLotFloor($lot) && self::getLotNbFloors($lot) && self::getLotFloor($lot) > self::getLotNbFloors($lot)) {
        throw new Exception(sprintf('floor number is greather than total floors: %s > %s', self::getLotFloor($lot), self::getLotNbFloors($lot)));
      }

      if (empty($lot->area_from) && empty($lot->area_to)) {
        throw new Exception('area is empty');
      }
      if (($v = self::getLotAreaTotal($lot)) < 1) {
        throw new Exception(sprintf('area is less than one: "%s"', $v));
      }
    }
    elseif (self::getLotCategory($lot) == '/real-estate/rent') {//Квартиры. Аренда
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
      if (self::getLotFloor($lot) && self::getLotNbFloors($lot) && self::getLotFloor($lot) > self::getLotNbFloors($lot)) {
        throw new Exception(sprintf('floor number is greather than total floors: %s > %s', self::getLotFloor($lot), self::getLotNbFloors($lot)));
      }
    }
    elseif (self::getLotCategory($lot) == '/real-estate/out-of-town/houses') {//Дома, дачи
      if (empty($lot->area_from) && empty($lot->area_to)) {
        throw new Exception('area is empty');
      }
      if (($v = self::getLotAreaTotal($lot)) < 1) {
        throw new Exception(sprintf('area is less than one: "%s"', $v));
      }
    }
    elseif (self::getLotCategory($lot) == '/real-estate/out-of-town-rent') {//Дома, дачи. Аренда
      //nothing
    }
    elseif (self::getLotCategory($lot) == '/real-estate/out-of-town/lands') {//Участки
      if (empty($lot->params['spaceplot'])) {
        throw new Exception('spaceplot parameter is empty');
      }
      if (!self::getLotAreaLandArray($lot)) {
        throw new Exception(sprintf('can\'t parse spaceplot parameter: "%s"', $lot->params['spaceplot']));
      }
    }
    else {//Коммерческая недвижимость
      if (empty($lot->area_from) && empty($lot->area_to)) {
        throw new Exception('area is empty');
      }
      if (($v = self::getLotAreaTotal($lot)) < 1) {
        throw new Exception(sprintf('area is less than one: "%s"', $v));
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

  protected static function getLotRegionDistrict($lot)
  {
    return preg_replace('/(?:^|[^\wа-я])р(?:айо|-)н(?:[^\wа-я]|$)/iu', '', self::getLotRegionDistrict($lot));
  }

  protected static function getLotCityArea($lot)
  {
    return rtrim(parent::getLotCityArea($lot), ' АО');
  }

  protected static function getLotDecoration($lot)
  {
    if (!empty($lot->params['about_decoration'])) {
      if ($lot->is_newbuild_type) {
        switch ($lot->params['about_decoration']) {
          case 'без отделки':   return 'без отделки';
          case 'с отделкой':    return 'с отделкой';
        }
      }
    }

    return null;
  }

  protected static function getLotWater($lot)
  {
    if (!empty($lot->params['service_water'])) {
      $v = $lot->params['service_water'];
      if (self::isValueMeansNo($v))                     return null;
      if (preg_match('/(?:^|\s)центр|магистр/iu', $v))  return 'Центральная';
      if (mb_stripos($v, 'скважин') !== false)          return 'Местная';
    }

    return null;
  }

  protected static function getLotElectricityKvt($lot)
  {
    if (!empty($lot->params['electricity_power'])) {
      $v = $lot->params['electricity_power'];
      if (ctype_digit($v) && $v > 0) {
        return (int) $v;
      }
      elseif (preg_match('/(?:^|\s)(\d+)\s*кВт/iu', $v, $matches) && !empty($matches[1])) {
        return (int) $matches[1];
      }
    }

    return null;
  }

  protected static function getLotIsAircondCentral($lot)
  {
    $v = '';
    if (!empty($lot->params['about_conditioning'])) {
      $v .= $lot->params['about_conditioning'];
    }
    if (!empty($lot->params['conditioning'])) {
      $v .= !empty($v) ? ' ' : '';
      $v .= $lot->params['conditioning'];
    }

    if (!empty($v) && mb_stripos($v, 'центральн') !== false) return true;

    return null;
  }

  protected static function getLotBathrooms($lot)
  {
    $s = self::getLotNbBathroomsSeparate($lot);
    $c = self::getLotNbBathroomsCombined($lot);

    if ($s == 1 && !$c)   return 'раздельный';
    if ($c == 1 && !$s)   return 'совмещенный';
    if ($s > 0 || $c > 0) return '2 и более';

    if (($v = self::getLotNbBathrooms($lot)) > 1) return '2 и более';

    return null;
  }

  protected static function getLotBuildClass($lot)
  {
    if (!empty($lot->params['buildclass']) && in_array($lot->params['buildclass'], array('A','A+','B','B+','C','C+','D','D+','А','А+','В','В+','С','С+'))) {
      return str_replace(array('А','В','С'), array('A','B','C'), $lot->params['buildclass']);
    }

    return null;
  }

  protected static function getLotPurpose($lot)
  {
    if (!empty($lot->params['goaluse'])) {
      $v = $lot->params['goaluse'];
      if (mb_stripos($v, 'кроме') !== false) return null;
      if ($lot->params['objecttype'] == 'Склад/складской комплекс') {
        if (mb_stripos($v, 'склад') !== false && mb_stripos($v, 'производств') !== false) return 'Производство и склад';
        if (mb_stripos($v, 'склад') !== false) return 'Склад';
        if (mb_stripos($v, 'производств') !== false) return 'Производство';
      }
      elseif ($lot->params['objecttype'] == 'Торговое помещение') {
        if (preg_match('/(?:^|\s)банк(?:овское)*(?:$|[^\wа-я])/iu', $v)) return 'Банковское';
        if (mb_stripos($v, 'магазин') !== false || mb_stripos($v, 'торгов') !== false) return 'Торговое';
        if (mb_stripos($v, 'фитнес') !== false || mb_stripos($v, 'спорт') !== false) return 'Спортивное';
        if (preg_match('/(?:^|\s)мед(?:ицинский|[-.\s]*центр|ицина)(?:$|[^\wа-я])/iu', $v)) return 'Медицинское';
        if (preg_match('/(?:^|\s)авто(?:[-]*[а-я]+)(?:$|[^\wа-я])/iu', $v)) return 'Автомобильное';
      }
    }

    return null;
  }

  protected static function getLotBuildType($lot)
  {
    if (!empty($lot->params['buildtype'])) {
      $v = $lot->params['buildtype'];

      if ($lot->params['objecttype'] == 'Офисное помещение') {
        if (preg_match('/(?:^|\s)б(?:изнес[\s-]+)*ц(?:ентр)*(?:[.,\s]|$)/iu', $v))
                return 'Бизнес-центр';
        if (preg_match('/(?:^|[^\wа-я])жило(?:й (?:дом|комплекс)|е здание)|(?:^|[^\wа-я])ЖК(?:[^\wа-я]|$)/iu', $v))
                return 'Жилой дом';
        if (preg_match('/(?:^|\s)административное(?:\s+(?:здание|строение)|$)/iu', $v))
                return 'Административное здание';
        if (preg_match('/(?:^|\s)м(?:(ного|ульти))*ф(?:ункциональный )*к(?:омплекс)*(?:[.,\s]|$)/iu', $v))
                return 'Многофункциональный комплекс';
        if (mb_stripos($v, 'особняк') !== false || preg_match('/(?:^|\s)о(?:тдельно[\s-]+)*с(?:тоящее\s)*([а-я\s])*з(?:дание)*(?:[.,\s]|$)/iu', $v))
                return 'Особняк';
        if (preg_match('/(?:^|[^\wа-я])Банк(?:[^\wа-я]|$)/iu', $v))
                return 'Банк';
        if (preg_match('/(?:^|[^\wа-я])т(?:оргово[ -])*р(?:азвлекательный )*(?:ц(?:ентр)*|к(?:омплекс)*)(?:$|[^\wа-я])/iu', $v))
                return 'Торгово-развлекательный центр';
        if (preg_match('/(?:^|[^\wа-я])торгово[ -]офисный (?:центр|комплекс)(?:$|[^\wа-я])/iu', $v))
                return 'Торгово-офисный комплекс';
        if (preg_match('/(?:^|[^\wа-я])офисно[ -]складской (?:центр|комплекс)(?:$|[^\wа-я])/iu', $v))
                return 'Офисно-складской комплекс';
        if (preg_match('/(?:^|\s)офис(?:ное здание|ный особняк)*(?:$|[^\wа-я])/iu', $v))
                return 'Офисное здание';
        if (preg_match('/(?:^|\s)производственн(?:ое |ый )*(?:$|[^\wа-я])/iu', $v))
                return 'Производственное здание';
      }
      elseif ($lot->params['objecttype'] == 'Склад/складской комплекс') {
        if (preg_match('/(?:^|[^\wа-я])жило(?:й (?:дом|комплекс)|е здание)|(?:^|[^\wа-я])ЖК(?:[^\wа-я]|$)/iu', $v))
                return 'Жилой дом';
        if (preg_match('/(?:^|\s)административное(?:\s+(?:здание|строение)|$)/iu', $v))
                return 'Административное здание';
        if (preg_match('/(?:^|[^\wа-я])Бокс(?:[^\wа-я]|$)/iu', $v))
                return 'Бокс';
        if (preg_match('/(?:^|[^\wа-я])Ангар(?:[^\wа-я]|$)/iu', $v))
                return 'Ангар';
      }
      else {
        if (preg_match('/(?:^|[^\wа-я])жило(?:й (?:дом|комплекс)|е здание)|(?:^|[^\wа-я])ЖК(?:[^\wа-я]|$)/iu', $v))
                return 'Жилое здание';
        if (preg_match('/(?:^|\s)административное(?:\s+(?:здание|строение)|$)/iu', $v))
                return 'Административное здание';
        if (mb_stripos($v, 'торговый центр') !== false || preg_match('/торгов(о|ый)[-\s](?:офис|развлекатель|обществен)ный центр/iu', $v))
                return 'Торговый центр';
        if (mb_stripos($v, 'особняк') !== false || preg_match('/(?:^|\s)о(?:тдельно[\s-]+)*с(?:тоящее\s)*([а-я\s])*з(?:дание)*(?:[.,\s]|$)/iu', $v))
                return 'Отдельное строение';
        if (mb_stripos($v, 'рынок') !== false)
                return 'Рынок';
      }
    }

    return null;
  }

  protected static function getLotConstructionType($lot)
  {
    if (!empty($lot->params['construction'])) {
      $v = $lot->params['construction'];
      if (mb_stripos($v, 'кирпич') !== false && mb_stripos($v, 'монолит') !== false)  return 'Кирпично-Монолитный';
      if (mb_stripos($v, 'кирпич') !== false)   return 'Кирпичный';
      if (mb_stripos($v, 'панель') !== false)   return 'Панельный';
      if (mb_stripos($v, 'монолит') !== false)  return 'Монолит';
      if (mb_stripos($v, 'блочн') !== false)    return 'Блочный';
      if (mb_stripos($v, 'блок') !== false)     return 'Блочный';
      if (mb_stripos($v, 'дерев') !== false)    return 'Деревянный';
      if (mb_stripos($v, 'брус') !== false)     return 'Деревянный';
      if (mb_stripos($v, 'ж/б') !== false)      return 'Железобетон';
      if (mb_stripos($v, 'железобет') !== false) return 'Железобетон';
      if (mb_stripos($v, 'железо-бет') !== false) return 'Железобетон';
      if (mb_stripos($v, 'металоконст') !== false) return 'Металоконструкции';
    }

    return null;
  }

  protected static function getLotObjectType($lot)
  {
    if (!empty($lot->params['objecttype'])) {
      switch ($lot->params['objecttype']) {
        case 'Коттедж':             return 'Коттедж';
        case 'Таунхаус':            return 'Таун-хаус';
        case 'Квартира':            return null;
        case 'Коттеджный поселок':  return null;
        default:                    return null;
      }
    }

    return null;
  }

  protected static function getLotCategory($lot)
  {
    switch($lot->type) {
      case 'eliteflat':
      case 'penthouse':
      case 'elitenew':
        if ($lot->is_newbuild_type) return '/real-estate/apartments-sale/new';//Новостройки
        else                        return '/real-estate/apartments-sale/secondary';//Вторичный рынок
      case 'flatrent':
        return '/real-estate/rent';//Квартиры. Аренда
      case 'cottage':
        return '/real-estate/out-of-town-rent';//Дома, дачи. Аренда
      case 'outoftown':
        if ($lot->is_land_type) return '/real-estate/out-of-town/lands';//Участки
        else                    return '/real-estate/out-of-town/houses';//Дома, дачи
      case 'comsell':
        if (!isset($lot->params['objecttype']))   return null;
        switch ($lot->params['objecttype']) {
          case 'Торговое помещение':              return '/real-estate/commercial-sale/retail';//Торговля и сервис (продажа)
          case 'Офисное помещение':               return '/real-estate/commercial-sale/offices';//Офисы (продажа)
          case 'Отдельно стоящее здание':         return '/real-estate/commercial-sale/houses';//Здания и особняки (продажа)
          case 'Готовый арендный бизнес':         return null;
          case 'Особняк':                         return '/real-estate/commercial-sale/houses';//Здания и особняки (продажа)
          case 'Помещение свободного назначения': return '/real-estate/commercial-sale/misc';//Другого и свободного назначения (продажа)
          case 'Склад/складской комплекс':        return '/real-estate/commercial-sale/production-warehouses';//Производство и склады (продажа)
          case 'Промышленный комплекс':           return '/real-estate/commercial-sale/production-warehouses';//Производство и склады (продажа)
          case 'Земельный участок':               return null;
          case 'Прочее':                          return '/real-estate/commercial-sale/misc';//Другого и свободного назначения (продажа)
        }
      case 'comrent':
        if (!isset($lot->params['objecttype']))   return null;
        switch ($lot->params['objecttype']) {
          case 'Торговое помещение':              return '/real-estate/commercial/retail';//Торговля и сервис (аренда)
          case 'Офисное помещение':               return '/real-estate/commercial/offices';//Офисы (аренда)
          case 'Отдельно стоящее здание':         return '/real-estate/commercial/houses';//Здания и особняки (аренда)
          case 'Готовый арендный бизнес':         return null;
          case 'Особняк':                         return '/real-estate/commercial/houses';//Здания и особняки (аренда)
          case 'Помещение свободного назначения': return '/real-estate/commercial/misc';//Другого и свободного назначения (аренда)
          case 'Склад/складской комплекс':        return '/real-estate/commercial/production-warehouses';//Производство и склады (аренда)
          case 'Промышленный комплекс':           return '/real-estate/commercial/production-warehouses';//Производство и склады (аренда)
          case 'Земельный участок':               return null;
          case 'Прочее':                          return '/real-estate/commercial/misc';//Другого и свободного назначения (аренда)
        }
    }

    return null;
  }
}
