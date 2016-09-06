<?php

class exportDmirTask extends exportBaseTask
{
  const
    PARTNER       = 'dmir',
    ENCODING      = 'windows-1251',
    PHONE_FORMAT  = '8-495-%d-%d%d';

  protected
    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse','elitenew'),
      'commerce'  => array('comrent', 'comsell'),
      'country'   => array('outoftown', 'cottage'),
    );


  protected function configure()
  {
    parent::configure();

    $this->name = 'dmir';
  }


  protected function writeDocumentStart()
  {
    $this->_xml_writer->startDocument('1.0', self::ENCODING, 'yes');
    $this->_xml_writer->startElement('VFPData');
  }

  protected function writeDocumentFinish()
  {
    $this->_xml_writer->endElement();
    $this->_xml_writer->endDocument();
  }

  protected function getDataArray(Lot $lot)
  {
    $data['object_id']    = $lot->id;
    $data['rubr_id']      = self::getLotCategory($lot);
    $data['apartm']       = self::getLotObjectType($lot);
    $data['type_bargain'] = self::getLotOperationType($lot);
    $data['url_offer']    = self::getLotUrl($lot);
    $data['region']       = self::getLotRegion($lot);

    if ($data['region'] == 'Москва') {
      //Метро или район, где нет метро
      if (!empty($lot->metro_id)) {
        $data['metro']      = $lot->metro;
      }
      elseif (!empty($lot->district_id)) {
        $data['metro']      = $lot->district;
      }
      //Время до метро
      if ($v = self::getLotMetroDistanceWalk($lot)) {
        //п – пешком
        $data['time']       = $v;
        $data['timerea']    = 'п';
      }
      elseif ($v = self::getLotMetroDistanceTransport($lot)) {
        //т – транспортом
        $data['time']       = $v;
        $data['timerea']    = 'т';
      }
    }
    else {
      //Район в регионе
      if ($v = self::getLotRegionDistrict($lot)) {
        $data['district'] = $v;
      }
      //Город
      if ($v = self::getLotCity($lot)) {
        $data['town']     = $v;
      }
      //Шоссе или направление
      if (!empty($lot->ward) || !empty($lot->ward2)) {
        $data['highway']  = sprintf('%s шоссе', $lot->array_wards[0]);
      }
      //Расстояние от МКАД
      if ($v = self::getLotDistanceMkad($lot)) {
        $data['distance_from_mkad'] = round($v);
      }
    }

    //Улица
    if (!empty($lot->address['street'])) {
      $data['houseadr']   = $lot->address['street'];
    }
    //Дом
    if (!empty($lot->address['house'])) {
      $data['house']      = $lot->address['house'];
    }
    //Корпус
    if (!empty($lot->address['building'])) {
      $data['block']      = $lot->address['building'];
    }
    //Строение
    if (!empty($lot->address['construction'])) {
       $data['building']  = $lot->address['construction'];
    }

    $data = array_merge($data, $this->{$this->getDataMethod($lot->type)}($lot));

    if ($lot->lat && $lot->lng) {
      $data['coordinateslat'] = $lot->lat;
      $data['coordinateslng'] = $lot->lng;
    }

    $data['addinf']       = self::getLotDescription($lot, 500);

    $data['firm']         = self::ORG_NAME;
    $data['email']        = self::ORG_EMAIL;
    $data['web']          = self::ORG_SITE;
    $data['phone']        = self::getLotPhone($lot);

    $data['price_int']    = round(self::getLotPrice($lot));
    $data['price_valuta'] = self::getLotCurrency($lot);

    $data['pro']          = 0;

    $data['photoweb']     = '';
    if ($photo = $lot->getImage('pres')) {
      $data['photoweb'] .= self::ORG_SITE . $photo . chr(13);
    }
    foreach ($lot->Photos as $photo) {
      if (!$photo->is_pdf && !$photo->is_xls) {
        $data['photoweb'] .= self::ORG_SITE . $photo->getImage('full') . chr(13);
      }
    }

    return array('crstemp' => array('data' => $data));
  }

  protected function getDataArrayFlats($lot)
  {
    //Общая площадь
    $data['totalarea']    = self::getLotAreaTotal($lot);
    //Жилая площадь
    if ($v = self::getLotAreaLiving($lot)) {
      $data['livarea']    = v;
    }
    //Площадь кухни
    if ($v = self::getLotAreaKitchen($lot)) {
      $data['kitarea']    = $v;
    }

    //Количество комнат всего
    $data['room_count_all_int'] = self::getLotNbRooms($lot);
    //Этаж
    $data['floor_int']      = self::getLotFloor($lot);
    //Этажность
    $data['floor_all_int']  = self::getLotNbFloors($lot);

    //Новостройка (bool: 1; 0)
    if ($lot->is_newbuild_type) {
      $data['newbuild']   = 1;
    }
    elseif (!empty($lot->params['market']) && $lot->params['market'] == 'Вторичный') {
      $data['newbuild']   = 0;
    }
    //Ипотека (bool: 1; 0)
    if (!is_null($v = self::getLotIsMortgage($lot))) {
      $data['mortgage']   = (int) $v;
    }
    //Тип дома
    if ($v = self::getLotConstructionType($lot)) {
      $data['housetype']  = $v;
    }
    //Балкон (enum: Н,Б,Л,БЛ,2Б,2Л,3Б,3Л)
    if ($v = self::getLotBalconies($lot)) {
      $data['balcony']    = $v;
    }
    //Санузел (enum: разд.; совм.; 2 с/у; 3 с/у; 4 с/у)
    if ($v = self::getLotBathrooms($lot)) {
      $data['bathroom']   = $v;
    }

    return $data;
  }

  protected function getDataArrayCountry($lot)
  {
    if (!$lot->is_land_type) {
      //Общая площадь дома
      $data['totalarea']  = self::getLotAreaTotal($lot);
    }
    //Общая площадь участка
    if ($v = self::getLotAreaLand($lot)) {
      $data['area_size']  = $v;
    }

    //Электричество (220В, 380В, есть)
    if ($v = self::getLotIsElectricity($lot)) {
      $data['electric']   = 'есть';
    }
    //Газ (центр., балон.)
    if ($v = self::getLotGas($lot)) {
      $data['gas']        = $v;
    }
    //Водопровод (центр., летний)
    if ($v = self::getLotWater($lot)) {
      $data['water']      = $v;
    }
    //Канализация (центр., септик)
    if ($v = self::getLotSewerage($lot)) {
      $data['sewage']     = $v;
    }
    //Интернет – 1, иначе 0
    if (!is_null($v = self::getLotIsInternet($lot))) {
      $data['internet']   = (int) $v;
    }

    return $data;
  }

  protected function getDataArrayCommerce($lot)
  {
    //Общая площадь
    $data['totalarea']    = self::getLotAreaTotal($lot);

    //Парковка (есть, кол-во м/м)
    if (self::getLotIsParking($lot)) {
      $data['parking']   = 'есть';
    }
    //Интернет – 1, иначе 0
    if (!is_null($v = self::getLotIsInternet($lot))) {
      $data['internet']   = (int) $v;
    }

    return $data;
  }


  protected function validateLot(Lot $lot)
  {
    if (!in_array($lot->type, $this->getAllowedTypes())) {
      throw new Exception(sprintf('not allowed lot type: "%s"', $lot->type));
    }
    if (!self::getLotCategory($lot)) {
      throw new Exception(sprintf('unrecognized lot category for type "%s"', $lot->type.(isset($lot->params['objecttype']) ? ' ('.$lot->params['objecttype'].')' : '')));
    }
    if (!self::getLotObjectType($lot)) {
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
    if ($v <= 0) {
      throw new Exception(sprintf('price is less than or equal to zero: "%s"', $v));
    }

    if (!$lot->is_land_type) {
      if (empty($lot->area_from) && empty($lot->area_to)) {
        throw new Exception('area is empty');
      }
      if (($v = self::getLotAreaTotal($lot)) < 1) {
        throw new Exception(sprintf('area is less than one: "%s"', $v));
      }
    }


    if ($lot->is_city_type) {
      if (empty($lot->address['street'])) {
        throw new Exception('street address is empty');
      }
      if (empty($lot->address['house']) && empty($lot->address['building']) && empty($lot->address['construction'])) {
        throw new Exception('lot address house, building and construction are empty');
      }
      if (empty($lot->metro_id) && empty($lot->district_id)) {
        throw new Exception('both metro and district are empty');
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
    elseif ($lot->is_country_type) {
      if (empty($lot->ward) && empty($lot->ward2)) {
        throw new Exception('wards is empty');
      }
      if (empty($lot->params['distance_mkad'])) {
        throw new Exception('distance_mkad parameter is empty');
      }
      if (!self::getLotDistanceMkad($lot)) {
        throw new Exception(sprintf('can\'t parse distance_mkad parameter as a number: "%s"', $lot->params['distance_mkad']));
      }

      if (empty($lot->params['spaceplot'])) {
        throw new Exception('spaceplot parameter is empty');
      }
      if (!self::getLotAreaLand($lot)) {
        throw new Exception(sprintf('can\'t parse spaceplot parameter: "%s"', $lot->params['spaceplot']));
      }
    }
    elseif ($lot->is_commercial_type) {
      if (self::getLotRegion($lot) == 'Москва') {
        if (empty($lot->address['street'])) {
          throw new Exception('street address is empty');
        }
        if (empty($lot->address['house']) && empty($lot->address['building']) && empty($lot->address['construction'])) {
          throw new Exception('lot address house, building and construction are empty');
        }
        if (empty($lot->metro_id) && empty($lot->district_id)) {
          throw new Exception('both metro and district are empty');
        }
      }
    }
  }


  protected static function getLotRegion($lot)
  {
    switch (parent::getLotRegion($lot)) {
      case 'Московская область':  return 'Московская обл.';
      default:                    return 'Москва';
    }
  }

  protected static function getLotCurrency($lot)
  {
    switch ($lot->currency) {
      case 'RUR': return 0;
      case 'USD': return 1;
      case 'EUR': return 2;
    }
  }

  protected static function getLotGas($lot)
  {
    if (!empty($lot->params['service_gas'])) {
      $v = $lot->params['service_gas'];
      if (self::isValueMeansNo($v)) return null;
      if (preg_match('/(?:^|\s)центр|магистр/iu', $v)) return 'центр.';
    }

    return null;
  }

  protected static function getLotWater($lot)
  {
    if (!empty($lot->params['service_water'])) {
      $v = $lot->params['service_water'];
      if (self::isValueMeansNo($v)) return null;
      if (preg_match('/(?:^|\s)центр|магистр/iu', $v)) return 'центр.';
    }

    return null;
  }

  protected static function getLotSewerage($lot)
  {
    if (!empty($lot->params['service_drainage'])) {
      $v = $lot->params['service_drainage'];
      if (self::isValueMeansNo($v)) return null;
      if (preg_match('/(?:^|\s)септик/iu', $v)) return 'септик';
      if (preg_match('/(?:^|\s)центр|магистр/iu', $v)) return 'центр.';
    }

    return null;
  }

  protected static function getLotBalconies($lot)
  {
    //Н;Б;Л;БЛ;2Б;2Л;3Б;3Л
    $b = self::getLotNbBalconies($lot);
    $l = self::getLotNbLoggias($lot);

    if (is_null($b) && is_null($l))   return null;
    if ($b <= 0 && $l <= 0)           return 'Н';
    if ($b === true && $l === true)   return 'БЛ';
    if ($b > 1)                       return $b.'Б';
    if ($l > 1)                       return $l.'Л';
    if (!is_null($b))                 return $b;
    if (!is_null($l))                 return $l;
  }

  protected static function getLotBathrooms($lot)
  {
    //разд.; совм.; 2 с/у; 3 с/у; 4 с/у
    $s = self::getLotNbBathroomsSeparate($lot);
    $c = self::getLotNbBathroomsCombined($lot);

    if ($s == 1 && !$c)   return 'разд.';
    if ($c == 1 && !$s)   return 'совм.';
    if ($s > 0 || $c > 0) return sprintf('%d с/у', (int) $s + (int) $c);

    if ($v = self::getLotNbBathrooms($lot) > 1) return sprintf('%d с/у', $v);

    return null;
  }

  protected static function getLotConstructionType($lot)
  {
    if (!empty($lot->params['construction'])) {
      $v = $lot->params['construction'];
      if (mb_stripos($v, 'кирпич') !== false
       && mb_stripos($v, 'монолит') !== false)  return 'мон.-кирп.';
      if (mb_stripos($v, 'кирпич') !== false)   return 'кирп.';
      if (mb_stripos($v, 'панель') !== false)   return 'пан.';
      if (mb_stripos($v, 'монолит') !== false)  return 'мон.';
      if (mb_stripos($v, 'блочн') !== false)    return 'блочн.';
      if (mb_stripos($v, 'блок') !== false)     return 'блочн.';
      if (mb_stripos($v, 'дерев') !== false)    return 'дер.';
      if (mb_stripos($v, 'щит') !== false)      return 'каркасно-щитов.';
      if (mb_stripos($v, 'сталь') !== false)    return 'стал.';
    }

    return null;
  }

  protected static function getLotCategory($lot)
  {
    switch ($lot->type) {
      case 'eliteflat': return '1';//Жилая недвижимость в Москве (квартиры, комнаты, таунхаусы)
      case 'elitenew':  return '1';//Жилая недвижимость в Москве (квартиры, комнаты, таунхаусы)
      case 'penthouse': return '1';//Жилая недвижимость в Москве (квартиры, комнаты, таунхаусы)
      case 'flatrent':  return '4.1';//Аренда квартиры и комнаты в Москве
      case 'comrent':   return '7.1';//Коммерческая недвижимость в Москве
      case 'comsell':   return '7.1';//Коммерческая недвижимость в Москве
      case 'cottage':   return '4.3';//Аренда загородной недвижимости
      case 'outoftown':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Участок':   return '3.2';//Загородная недвижимость в МО. Земельные участки
          case 'Квартира':  return null;//may be 2 (Квартиры и комнаты в МО), but it is difficult to work with it
          default:          return '3.1';//Таунхаус; Коттедж; Коттеджный поселок -> Загородная недвижимость в МО. Коттеджи и дачи
        }
    }

    return null;
  }

  protected static function getLotObjectType($lot)
  {
    switch($lot->type) {
      case 'eliteflat':
      case 'elitenew':
      case 'flatrent':    return 'Квартира';
      case 'penthouse':   return 'Пентхаус';
      //Квартира,  Комната, Таунхаус, Пентхаус, Дом, Коттедж, Полдома, Часть дома, Апартаменты, Часть коттеджа

      case 'cottage':
      case 'outoftown':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Участок':             return 'Участок';
          case 'Коттедж':             return 'Коттедж';
          case 'Таунхаус':            return null;
          case 'Квартира':            return null;
          case 'Коттеджный поселок':  return null;
          default:                    return null;
          //Дача , Дом, Коттедж, Полдома, Сруб, Часть дома, Участок, Два участка
        }

      case 'comsell':
      case 'comrent':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Торговое помещение':              return 'Торговая площадь';
          case 'Офисное помещение':               return 'Офис';
          case 'Отдельно стоящее здание':         return 'Отдельно стоящее здание';
          case 'Готовый арендный бизнес':         return null;
          case 'Особняк':                         return 'Особняк';
          case 'Помещение свободного назначения': return 'Помещение свободного назначения';
          case 'Склад/складской комплекс':        return 'Складское помещение';
          case 'Промышленный комплекс':           return 'Производство';
          case 'Земельный участок':               return 'Участок';
          case 'Прочее':                          return null;
        }
        //Агрокомплекс, Ангар, Аптека, Ателье, Автотехцентр, База, Бар, Бистро, Бутик, Бизнес-центр,
        //Гастроном, Завод, Здание, Кафе, Киоск, Клуб, Комплекс, Крестьянско-фермерское хозяйство,
        //Магазин, Магазин-склад, Мастерская, Медцентр, Обменный пункт, Оздоровительный комплекс,
        //Отдельно стоящее здание, Особняк, Отдел, Офис, Парикмахерская, Павильон, Помещение,
        //Производство, Помещение свободного назначения, Ресторан, Спортклуб, Салон, Сауна,
        //Сеть магазинов, Складской комплекс, Склад, Складское помещение, Торговый комплекс,
        //Торговая площадь, Универсам, Участки, Участок, Фитнес-центр, Холодильная камера,
        //Цех, Торгово-офисный центр, Торгово-развлекательный центр.
    }

    return null;
  }
}
