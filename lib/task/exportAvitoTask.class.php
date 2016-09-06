<?php
/*
 * Цена только в рублях
 */


class exportAvitoTask extends exportBaseTask
{
  const
    PARTNER       = 'avito',
    ENCODING      = 'utf-8',
    DATE_FORMAT   = 'Y-m-d',
    PHONE_FORMAT  = '+7 (495) %d-%d-%d';

  protected
    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse','elitenew'),
      'commerce'  => array('comsell'),
      'country'   => array('outoftown'),
    );

  private
    $_dictionaries = array();


  protected function configure()
  {
    parent::configure();

    $this->name = 'avito';
  }


  protected function writeDocumentStart()
  {
    $this->_xml_writer->startDocument('1.0', self::ENCODING);
    $this->_xml_writer->startElement('Ads');
    $this->_xml_writer->writeAttribute('target', 'Avito.ru');
    $this->_xml_writer->writeAttribute('formatVersion', 2);
  }

  protected function writeDocumentFinish()
  {
    $this->_xml_writer->endElement();
    $this->_xml_writer->endDocument();
  }

  protected function getDataArray(Lot &$lot)
  {
    $data['Id']               = $lot->id;
    //Категория объявления (значение из справочника)
    $data['Category']         = self::getLotCategory($lot);
    //Сдам или Продам
    $data['OperationType']    = self::getLotOperationType($lot);

    //Если дата начала экспозиции не указана, началом считается день приема объявления.
    $data['DateBegin']        = self::getLotCreationDate($lot);
    //Если дата окончания экспозиции не указана, концом считается конец 30-го дня, начиная с даты начала экспозиции, т.е. объявление получается на 30 дней.
    $data['DateEnd']          = date(self::DATE_FORMAT, strtotime('+30 days'));

    //Регион объекта объявления из справочника
    $data['Region']           = self::getLotRegion($lot);
    //City не указывается для регионов Москва и Санкт-Петербург.
    if ($data['Region'] != 'Москва') {
      //В элементе City указывается населенный пункт из справочника городов в соответствии с указанным ранее регионом.
      //Если требуемый населенный пункт в справочнике отсутствует, то указывается ближайший к вашему объекту...
      $data['City']           = self::getLotNearestCity($lot, $this->getDictionaryCities());
      //...а само название населенного пункта, где находится объект, указывается в элементе Locality
      if ($v = self::getLotCity($lot)) {
        $data['Locality']     = $v;
      }
    }
    //Указывается улица и, опционально, номер дома
    if ($v = $lot->getPrettyAddress('street', false)) {
      $data['Street']         = $v;
    }
    //Значение из Cправочника метро (в соответствии с выбранным городом)
    if ($data['Region'] == 'Москва' && $lot->metro_id) {
      $data['Subway']         = self::getLotMetro($lot);
    }

    $data = array_merge($data, $this->{$this->getDataMethod($lot->type)}($lot));

    //Цена в рублях (decimal)
    $data['Price']            = Currency::convert(self::getLotPrice($lot), $lot->currency, 'RUR');

    //CompanyName и EMail можно указывать при наличии соответствующих разрешений: иметь несколько названий и несколько адресов электронной почты.
    //Если необходимое разрешение отсутствует, указанное значение игнорируется.
    $data['CompanyName']      = self::ORG_NAME;
    $data['EMail']            = self::ORG_EMAIL;
    $data['ContactPhone']     = self::getLotPhone($lot);

    $data['Description']      = self::getLotDescription($lot, 2700);

    $data['AdStatus']         = 'Free';

    $data['Images']           = array();
    if ($photo = $lot->getImage('pres_')) {
      $data['Images'][] = array('Image' => array(
        'attributes' => array('url' => self::ORG_SITE . $photo),
      ));
    }
    foreach ($lot->Photos as $photo) {
      if (!$photo->is_pdf && !$photo->is_xls) {
        $data['Images'][] = array('Image' => array(
          'attributes' => array('url' => self::ORG_SITE . $photo->getImage('full_')),
        ));

        if (count($data['Images']) >= 20) break;
      }
    }

    return array('Ad' => $data);
  }

  protected function getDataArrayFlats(&$lot)
  {
    //Общая площадь квартиры в м.кв. (decimal, >= 10)
    $data['Square']           = self::getLotAreaTotal($lot);
    //Количество комнат в квартире (текст «Студия» или целое число)
    $data['Rooms']            = self::getLotNbRooms($lot);
    //Этаж, на котором находится объект
    if ($v = self::getLotFloor($lot)) {
      $data['Floor']          = $v;
    }
    //Количество этажей в доме
    if ($v = self::getLotNbFloors($lot)) {
      $data['Floors']         = $v;
    }

    //Тип дома (значение из справочника)
    if ($v = self::getLotConstructionType($lot)) {
      $data['HouseType']      = $v;
    }

    //Для аренды указывается тип аренды (LeaseType),
    //а для продажи - принадлежность к рынку (MarketType)
    if ($lot->is_rent_type) {
      $data['LeaseType']      = 'Долгосрочная';
    }
    elseif ($lot->is_newbuild_type) {
      $data['MarketType']     = 'Новостройка';
    }
    elseif ($v = self::getLotMarketType($lot)) {
      $data['MarketType']     = $v;
    }
    else {
      $data['MarketType']     = 'Вторичка';
    }

    return $data;
  }

  protected function getDataArrayCountry(&$lot)
  {
    //Категория объекта недвижимости (Дома, дачи, коттеджи)
    //или (если участок)
    //Вид земельного участка (Поселений (ИЖС); Сельхозназначения (СНТ, ДНП); Промназначения)
    $data['ObjectType']       = self::getLotObjectType($lot);
    //Расстояние до города в км (от 0 до 160)
    //значение 0 означает, что объект находится в черте города
    $data['DistanceToCity']   = round(self::getLotDistanceMkad($lot));
    //Направление, можно указывать если объект не в черте города
    if ($data['DistanceToCity'] > 0 && ($v = self::getLotDirection($lot))) {
      //Значение из Справочника направлений (шоссе) (в соответствии с выбранным городом)
      $data['DirectionRoad']  = $v;
    }
    //Площадь земли в сотках (decimal, >= 1)
    if ($v = self::getLotAreaLand($lot)) {
      $data['LandArea']       = $v;
    }

    if (!$lot->is_land_type) {
      //Площадь дома в м. кв. (decimal, >= 20)
      $data['Square']         = self::getLotAreaTotal($lot);
      //Материал стен (значение из справочника)
      if ($v = self::getLotConstructionType($lot)) {
        $data['WallsType']    = $v;
      }
      //Количество этажей в доме (от 1 до 10)
      if ($v = self::getLotNbFloors($lot)) {
        $data['Floors']       = $v;
      }
      //Тип аренды (только для типа "Сдам")
      if ($lot->is_rent_type) {
        $data['LeaseType']    = 'Долгосрочная';
      }
    }

    return $data;
  }

  protected function getDataArrayCommerce(&$lot)
  {
    //Вид объекта (значение из справочника)
    $data['ObjectType']       = self::getLotObjectType($lot);
    //Площадь помещения в м.кв. (decimal, >= 5)
    $data['Square']           = self::getLotAreaTotal($lot);
    //Класс здания для офисных и складских помещений (A,B,C,D)
    if ($v = self::getLotBuildClass($lot)) {
      $data['BuildingClass']  = $v;
    }
    //Для типа "Продам" - объект является готовым бизнесом (Да, Нет)
    if (!$lot->is_rent_type) {
      $data['BusinessForSale'] = 'Нет';
    }

    return $data;
  }

  protected function validateLot(Lot &$lot)
  {
    if (!in_array($lot->type, $this->getAllowedTypes())) {
      throw new Exception(sprintf('not allowed lot type: "%s"', $lot->type));
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

    if (self::getLotFloor($lot) && self::getLotNbFloors($lot) && self::getLotFloor($lot) > self::getLotNbFloors($lot)) {
      throw new Exception(sprintf('floor number is greather than total floors: %s > %s', self::getLotFloor($lot), self::getLotNbFloors($lot)));
    }

    if ($lot->is_city_type) {
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
    elseif ($lot->is_commercial_type) {
      if (empty($lot->params['objecttype'])) {
        throw new Exception('objecttype parameter is empty');
      }
      if (!self::getLotObjectType($lot)) {
        throw new Exception(sprintf('unrecognized objecttype parameter: "%s"', $lot->params['objecttype']));
      }
    }
    elseif ($lot->is_country_type) {
      if (empty($lot->params['objecttype'])) {
        throw new Exception('objecttype parameter is empty');
      }
      if (!self::getLotObjectType($lot)) {
        throw new Exception(sprintf('unrecognized objecttype parameter: "%s"', $lot->params['objecttype']));
      }

      if (!isset($lot->params['distance_mkad'])) {
        throw new Exception('distance_mkad parameter is empty');
      }
      if (!($v = self::getLotDistanceMkad($lot))) {
        throw new Exception(sprintf('can\'t parse distance_mkad parameter as a number: "%s"', $lot->params['distance_mkad']));
      }
      if ($v < 0 || $v > 160) {
        throw new Exception(sprintf('distance_mkad parameter is bigger than 160 or lesser than 0: "%s"', $v));
      }
    }

    if ($lot->is_land_type) {
      if (empty($lot->params['spaceplot'])) {
        throw new Exception('spaceplot parameter is empty');
      }
      if (!self::getLotAreaLand($lot)) {
        throw new Exception(sprintf('can\'t parse spaceplot parameter as a number: "%s"', $lot->params['spaceplot']));
      }
    }
    else {
      if (empty($lot->area_from) && empty($lot->area_to)) {
        throw new Exception('area is empty');
      }
      if (($v = self::getLotAreaTotal($lot)) < 1) {
        throw new Exception(sprintf('area is less than one: "%s"', $v));
      }
      if ($lot->is_city_type && $v < 10
       || $lot->is_country_type && $v < 20
       || $lot->is_commercial_type && $v < 5) {
        throw new Exception(sprintf('area is too low: "%s"', $v));
      }
    }
  }


  private function getDictionaryCities()
  {
    if (!isset($this->_dictionaries['cities'])) {
      $this->logSection('fetch', 'cities dictionary');

      $file_path = sprintf('%s%sexport%sAvitoCity.xml', sfConfig::get('sf_web_dir'), DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
      if (!$xml = simplexml_load_file($file_path)) {
        throw new Exception(sprintf('Can\'t read file: %s', $file_path));
      }

      foreach ($xml as $e) {
        $this->_dictionaries['cities'][(string) $e['name']] = array('lat' => (string) $e['lat'], 'lng' => (string) $e['lng']);
      }
    }

    return $this->_dictionaries['cities'];
  }


  protected static function getLotOperationType($lot)
  {
    return $lot->is_rent_type ? 'Сдам' : 'Продам';
  }

  protected static function getLotMarketType($lot)
  {
    if (!empty($lot->params['market'])) {
      switch ($lot->params['market']) {
        case 'Первичный': return 'Новостройка';
        case 'Вторичный': return 'Вторичка';
        default:          return null;
      }
    }

    return null;
  }

  protected static function getLotBuildClass($lot)
  {
    if (!empty($lot->params['buildclass']) && in_array($lot->params['buildclass'], array('A','B','C','D','А','В','С'))) {
      return str_replace(array('А','В','С'), array('A','B','C'), $lot->params['buildclass']);
    }

    return null;
  }

  protected static function getLotConstructionType($lot)
  {
    if (!empty($lot->params['construction'])) {
      $v = $lot->params['construction'];
      if ($lot->is_city_type) {
        if (mb_stripos($v, 'кирпич') !== false)   return 'Кирпичный';
        if (mb_stripos($v, 'панель') !== false)   return 'Панельный';
        if (mb_stripos($v, 'блочн') !== false)    return 'Блочный';
        if (mb_stripos($v, 'блок') !== false)     return 'Блочный';
        if (mb_stripos($v, 'монолит') !== false)  return 'Монолитный';
        if (mb_stripos($v, 'дерев') !== false)    return 'Деревянный';
        if (mb_stripos($v, 'брус') !== false)     return 'Деревянный';
        if (mb_stripos($v, 'кедр') !== false)     return 'Деревянный';
      }
      elseif ($lot->is_country_type) {
        if (mb_stripos($v, 'кирпич') !== false)   return 'Кирпич';
        if (mb_stripos($v, 'брус') !== false)     return 'Брус';
        if (mb_stripos($v, 'дерев') !== false)    return 'Бревно';
        if (mb_stripos($v, 'брев') !== false)     return 'Бревно';
        if (mb_stripos($v, 'металл') !== false)   return 'Металл';
        if (mb_stripos($v, 'пено') !== false)     return 'Пеноблоки';
        if (mb_stripos($v, 'сендвич') !== false)  return 'Сэндвич-панели';
        if (mb_stripos($v, 'ж/б') !== false)      return 'Ж/б панели';
        if (mb_stripos($v, 'ж/б пане') !== false) return 'Ж/б панели';
        if (mb_stripos($v, 'железобет') !== false) return 'Ж/б панели';
        if (mb_stripos($v, 'железо-бет') !== false) return 'Ж/б панели';
        if (mb_stripos($v, 'эксперим') !== false) return 'Экспериментальные материалы';
      }
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
        return 'Квартиры';

      case 'comrent':
      case 'comsell':
        return 'Коммерческая недвижимость';

      case 'outoftown':
      case 'cottage':
        return $lot->is_land_type ? 'Земельные участки' : 'Дома, дачи, коттеджи';
    }

    return null;
  }

  protected static function getLotObjectType($lot)
  {
    switch($lot->type) {
      case 'comrent':
      case 'comsell':
        switch ($lot->params['objecttype']) {
          case 'Торговое помещение':              return 'Торговое помещение';
          case 'Офисное помещение':               return 'Офисное помещение';
          case 'Отдельно стоящее здание':         return null;
          case 'Готовый арендный бизнес':         return null;
          case 'Особняк':                         return null;
          case 'Помещение свободного назначения': return 'Помещение свободного назначения';
          case 'Склад/складской комплекс':        return 'Складское помещение';
          case 'Промышленный комплекс':           return 'Производственное помещение';
          case 'Земельный участок':               return null;
          case 'Прочее':                          return null;
          default:                                return null;
          //'Гостиница','Ресторан, кафе','Салон красоты'
        }

      case 'outoftown':
      case 'cottage':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Участок':             return 'Поселений (ИЖС)';
          case 'Таунхаус':            return 'Таунхаус';
          case 'Квартира':            return null;
          case 'Коттедж':             return 'Коттедж';
          case 'Коттеджный поселок':  return null;
          default:                    return null;
          //'Дом','Дача'
        }
    }

    return null;
  }

  protected static function getLotDirection($lot)
  {
    $wards = $lot->array_wards;
    if (empty($wards)) return null;

    switch ($wards[0]) {
      case 1:  return 'Алтуфьевское шоссе';
      case 2:  return 'Боровское шоссе';
      case 3:  return 'Варшавское шоссе';
      case 4:  return 'Волоколамское шоссе';
      case 5:  return 'Горьковское шоссе';
      case 6:  return 'Дмитровское шоссе';
      case 7:  return 'Егорьевское шоссе';
      case 8:  return 'Ильинское шоссе';
      case 9:  return 'Калужское шоссе';
      case 10: return 'Каширское шоссе';
      case 11: return 'Киевское шоссе';
      case 12: return 'Куркинское шоссе';
      case 13: return 'Ленинградское шоссе';
      case 14: return 'Минское шоссе';
      case 15: return 'Можайское шоссе';
      case 16: return 'Новорижское шоссе';
      case 17: return 'Новорязанское шоссе';
      case 18: return 'Носовихинское шоссе';
      case 19: return 'Осташковское шоссе';
      case 20: return 'Пятницкое шоссе';
      case 21: return 'Рогачёвское шоссе';
      case 22: return 'Рублёво-Успенское шоссе';
      case 23: return 'Рязанское шоссе';
      case 24: return 'Симферопольское шоссе';
      case 25: return 'Сколковское шоссе';
      case 26: return 'Щёлковское шоссе';
      case 27: return 'Ярославское шоссе';
      default: return null;
    }
  }

  protected function getLotMetro($lot)
  {
    switch ($lot->metro) {
      case 'Улица Академика Янгеля':    return 'Улица академика Янгеля';
      case 'Воробьевы горы':            return 'Воробьевы Горы';
      case 'Китай-Город':               return 'Китай-город';
      case 'Кузнецкий Мост':            return 'Кузнецкий мост';
      case 'Марьина роща':              return 'Марьина Роща';
      case 'Парк Культуры':             return 'Парк культуры';
      case 'Парк победы':               return 'Парк Победы';
      case 'Преображенская пл.':        return 'Преображенская площадь';
      case 'Юго-западная':              return 'Юго-Западная';
      default:                          return $lot->metro;
    }
  }
}
