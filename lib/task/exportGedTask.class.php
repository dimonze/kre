<?php
/*
 * Максимальный размер одного файла не должен привышать 8 Мб.
 * Размер значений текстовых атрибутов не должен превышать 4000 символов, если не указано иное ограничение.
 * Можно не создавать тэг для необязательного атрибута.
 */

class exportGedTask extends exportBaseTask
{
  const
    PARTNER       = 'gdeetotdom',
    ENCODING      = 'windows-1251',
    DATE_FORMAT   = 'Y-m-d H',
    PHONE_FORMAT  = '+7(495)%d-%d-%d',
    CLIENT_ID     = 1586344638;

  protected
    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse','flatrent','elitenew'),
      'commerce'  => array('comrent', 'comsell'),
      'country'   => array('outoftown', 'cottage'),
    );


  protected function configure()
  {
    parent::configure();

    $this->name = 'ged';
  }


  protected function writeDocumentStart()
  {
    $this->_xml_writer->startDocument('1.0', self::ENCODING);
    $this->_xml_writer->startElement('LISTINGS');

    $this->_xml_writer->startElement('LISTING');
    $this->_xml_writer->writeAttribute('ADVFILENAME', sprintf('%s_%s', self::CLIENT_ID, date('dmYHis')));
    $this->_xml_writer->writeAttribute('DT_FILE', date('Y-m-d\TH:i:s.0Z'));
    $this->_xml_writer->writeAttribute('GED_VERSION', 2);
    $this->_xml_writer->endElement();

    $this->_xml_writer->startElement('PARTNER');
    $this->_xml_writer->writeElement('PARTNER_NAME', self::ORG_NAME);
    $this->_xml_writer->writeElement('PARTNER_ID', self::CLIENT_ID);
    $this->_xml_writer->endElement();

    $this->_xml_writer->startElement('ADS');
  }

  protected function writeDocumentFinish()
  {
    $this->_xml_writer->endElement();
    $this->_xml_writer->endElement();
    $this->_xml_writer->endDocument();
  }

  protected function getDataArray(Lot $lot)
  {
    $data['TYPE_REALTY']  = self::getLotCategory($lot);
    $data['TYPE_OBJECT']  = self::getLotObjectType($lot);
    $data['TYPE_OPER']    = self::getLotOperationType($lot);
    $data['WEB_OBJECT']   = self::getLotUrl($lot);

    $data['AD']           = array('attributes' => array(
      'ADVNUM'              => $lot->id,//Уникальный идентификатор объявления в вашей учетной системе
    ));
    $data['AGENCY']       = array('attributes' => array(
      'CONTACT_PHONE'       => self::getLotPhone($lot),//Контактный телефон
      'EMAIL'               => self::ORG_EMAIL,//Контактный e-mail, на который вам придет заявка, оформленная посетителем портала
    ));
    $data['DT']           = array('attributes' => array(
      'BEGIN'               => self::getLotCreationDate($lot),//Дата начала показа объявления на портале
      'END'                 => date(self::DATE_FORMAT, strtotime('+7 days')),//Дата окончания показа объявления
    ));

    $data['ADRINF']               = array();
    $data['ADRINF']['COUNTRY']    = 'Россия';
    $data['ADRINF']['REGION']     = self::getLotRegion($lot);
    //Наименование города (или населенного пункта)
    if ($v = self::getLotCity($lot)) {
      $data['ADRINF']['CITY']     = $v;
    }
    //Округ (в случае г. Москва) или район (во всех остальных случаях)
    if ($data['ADRINF']['REGION'] == 'Москва') {
      if ($v = self::getLotCityArea($lot)) {
        $data['ADRINF']['ADM1']   = $v;
      }
      //Район города Москвы
      if ($v = self::getLotCityDistrict($lot)) {
        $data['ADRINF']['ADM2']   = $v;
      }
    }
    else {
      if ($v = self::getLotRegionDistrict($lot)) {
        $data['ADRINF']['ADM1']   = $v;
      }
    }
    //Наименование улицы
    if (!empty($lot->address['street'])) {
      $data['ADRINF']['STREET']   = $lot->address['street'];
    }
    //Номер дома, вместе со строениями и корпусами
    if ($v = $lot->getPrettyAddress('house', false)) {
      $data['ADRINF']['NUM']      = $v;
    }

    //Информация о метро. Могут быть указаны данные о трех ближайших к объекту станциях метро. Заполняется отдельными тегами
    if (!empty($lot->metro_id)) {
      $data['ADRINF']['SUBWAY']['METRO'] = $lot->metro;
    }
    //Время до метро. Одно из возможных справочных значений
    if ($v = self::getLotMetroDistanceWalk($lot)) {
      $data['ADRINF']['SUBWAY']['METRO_TIME'] = $v;
      $data['ADRINF']['SUBWAY']['TYPETRANSP'] = 'пешком';
    }
    elseif ($v = self::getLotMetroDistanceTransport($lot)) {
      $data['ADRINF']['SUBWAY']['METRO_TIME'] = $v;
      $data['ADRINF']['SUBWAY']['TYPETRANSP'] = 'транспортом';
    }

    //Информация о шоссе. Могут быть указаны данные о двух ближайших к объекту шоссе
    if (!empty($lot->ward) || !empty($lot->ward2)) {
      $data['ADRINF']['HIGHWAY']['HIGHWAY']   = sprintf('%s шоссе', $lot->array_wards[0]);
    }
    //Удаленность от МКАД для Москвы и МО (км)
    if ($v = self::getLotDistanceMkad($lot)) {
      $data['ADRINF']['DISTANCE'] = $v;
    }

    //Географические координаты
    if ($lot->lat && $lot->lng) {
      $data['ADRINF']['COORD']['X'] = $lot->lat;
      $data['ADRINF']['COORD']['Y'] = $lot->lng;
    }

    //Краткое наименование (ISO — код) валюты объявления
    $data['CURRENCY']             = $lot->currency;
    //Полная стоимость объекта. Значения цен можно указывать с точностью до сотых
    $data['COST']                 = self::getLotPrice($lot);
    //Максимальная стоимость объекта
    if (!empty($lot->price_all_to) && is_numeric($lot->price_all_to) && $lot->price_all_to > $data['COST']) {
      $data['COST_MAX']           = (float) $lot->price_all_to;
    }
    //Цена за 1 кв.м
    if (!empty($lot->price_from) && is_numeric($lot->price_from) && $lot->price_from > 0) {
      $data['PRICE']              = (float) $lot->price_from;
    }
    //Максимальная цена 1 (одного) кв. метра объекта
    if (!empty($lot->price_to) && is_numeric($lot->price_to) && $lot->price_to > $lot->price_from) {
      $data['PRICE_MAX']          = (float) $lot->price_to;
    }
    //Если объект продается по ипотеке, то указывайте в этом теге значение «TRUE». По умолчанию: «FALSE».
    if (!is_null($v = self::getLotIsMortgage($lot))) {
      $data['IS_HYPOTHEC']        = ($v ? 'TRUE' : 'FALSE');
    }

    //Информация по зданию
    //Если объект новостройка — «TRUE». По умолчанию «FALSE»
    if ($lot->is_newbuild_type) {
      $data['BUILDINGINF']['NEW_BUILDING']['attributes']['IS_NEW'] = 'TRUE';
      //Срок (год) сдачи новостройки. Формат: «ГГГГ»
      if ($v = self::getLotBuiltYear($lot)) {
        $data['BUILDINGINF']['NEW_BUILDING']['attributes']['DL_ENDING_CONSTR'] = $v;
      }
    }
    //Год постройки здания. Формат: «ГГГГ»
    if ($v = self::getLotBuiltYear($lot)) {
      $data['BUILDINGINF']['BLDYEAR'] = $v;
    }
    //Количество этажей здания
    if ($v = self::getLotNbFloors($lot)) {
      $data['BUILDINGINF']['FLOOR_QTY'] = $v;
    }
    //Тип здания
    if ($v = self::getLotConstructionType($lot)) {
      $data['BUILDINGINF']['BLDKIND'] = $v;
    }

    //Информация по объекту
    //Этаж расположения объекта
    if ($v = self::getLotFloor($lot)) {
      $data['FLATINF']['FLOOR'] = $v;
    }
    //Общая площадь в кв. метрах
    if ($v = self::getLotAreaTotalArray($lot)) {
      $data['FLATINF']['FULLSQUARE'] = $v[0];
    }
    //Максимальное значение общей площади в кв. метрах
    if (isset($v[1])) {
      $data['FLATINF']['FULLSQUARE_MAX'] = $v[1];
    }
    //Тип и количество санузлов. Значение из справочника
    if ($v = self::getLotBathrooms($lot)) {
      $data['FLATINF']['BATHROOM'] = $v;
    }
    //Высота потолков в метрах
    if ($v = self::getLotCeilingHeight($lot)) {
      $data['FLATINF']['HEADROOM'] = $v;
    }
    //Состояние объекта и ремонт. Значение из справочника
    if (!empty($lot->params['about_decoration']) && $lot->params['about_decoration'] == 'без отделки') {
      $data['FLATINF']['REPAIR'] = 'без отделки';
    }
    //Материал перекрытий. Значение из справочника
    if ($v = self::getLotOverlap($lot)) {
      $data['FLATINF']['OVERLAP_MAT'] = $v;
    }
    //Тип окон. Значение из справочника
    if ($v = self::getLotWindows($lot)) {
      $data['FLATINF']['WINDOW'] = $v;
    }
    //Вид из окон на объекте. Значение из справочника
    if ($v = self::getLotWindowView($lot)) {
      $data['FLATINF']['VIEW'] = $v;
    }
    //Количество пассажирских лифтов
    if (!empty($lot->params['passenger_elevators']) && ctype_digit($lot->params['passenger_elevators'])) {
      $data['FLATINF']['AUXINFO']['attributes']['ELEVATOR'] = (int) $lot->params['passenger_elevators'];
    }
    //Количество грузовых лифтов
    if (!empty($lot->params['cargo_elevators']) && ctype_digit($lot->params['cargo_elevators'])) {
      $data['FLATINF']['AUXINFO']['attributes']['FR_ELEVATOR'] = (int) $lot->params['cargo_elevators'];
    }
    //Тип парковки. Значение из справочника
    if ($v = self::getLotParking($lot)) {
      $data['FLATINF']['AUXINFO']['attributes']['PARKING'] = $v;
    }
    //Дополнительная информация об объекте в произвольной форме
    $data['FLATINF']['ADDITION'] = self::getLotDescription($lot, 4000);

    //Инженерная инфраструктуру объекта
    //Тип канализации. Значение из справочника
    if ($v = self::getLotSewerage($lot)) {
      $data['INFRASTRUCTURE']['attributes']['SEWERADAGE'] = $v;
    }
    //Тип водоснабжения. Значение из справочника
    if ($v = self::getLotWater($lot)) {
      $data['INFRASTRUCTURE']['attributes']['WATER'] = $v;
    }
    //Тип электроснабжения. Значение из справочника
    if (!is_null($v = self::getLotIsElectricity($lot))) {
      $data['INFRASTRUCTURE']['attributes']['ELECTRICITY'] = ($v ? 'есть' : 'нет');
    }
    //Тип газоснабжения. Значение из справочника
    if ($v = self::getLotGas($lot)) {
      $data['INFRASTRUCTURE']['attributes']['GAS'] = $v;
    }

    if ($lot->is_commercial_type) {
      $data = array_merge_recursive($data, $this->getDataArrayCommerce($lot));
    }
    else {
      $data = array_merge_recursive($data, $this->getDataArrayElite($lot));
    }

    $c = 0;
    $data['FILES'] = array();
    if ($photo = $lot->getImage('pres')) {
      $data['FILES'][] = array('FILE' => array('attributes' => array(
        'FILEPATH' => self::ORG_SITE . $photo,
      )));
      $c++;
    }
    foreach ($lot->Photos as $photo) {
      if (!$photo->is_pdf && !$photo->is_xls) {
        $data['FILES'][] = array('FILE' => array('attributes' => array(
          'FILEPATH' => self::ORG_SITE . $photo->getImage('full'),
        )));
        if (++$c >= 12) break;//не более 12 файлов фотографий
      }
    }

    return array('OBJECT' => array('data' => $data));
  }

  protected function getDataArrayElite($lot)
  {
    $data = array();
    //Укажите «TRUE», если объект — пентхаус. По умолчанию — «FALSE»
    if ($lot->type == 'penthouse' || $lot->is_penthouse) {
      $data['BUILDINGINF']['ELITE']['attributes']['IS_PENTHOUSE'] = 'TRUE';
    }
    //Класс здания / поселения. Значение из справочника
    if ($v = self::getLotBuildClass($lot)) {
      $data['BUILDINGINF']['ELITE']['attributes']['HOUSE_CLASS'] = $v;
    }
    //Собственное наименование дома в произвольном виде
    if ($v = self::getLotEstate($lot)) {
      $data['BUILDINGINF']['ELITE']['attributes']['HOUSE_NAME'] = $v;
    }
    //Собственное наименование поселения в произвольном виде
    if (!empty($lot->params['cottageVillage'])) {
      $data['BUILDINGINF']['ELITE']['attributes']['SETTL_NAME'] = $lot->params['cottageVillage'];
    }
    //Количество жилых комнат
    if ($v = self::getLotNbRooms($lot)) {
      $data['FLATINF']['ROOM_QTY'] = $v;
      $data['FLATINF']['OPER_ROOM_QTY'] = $v;//Количество комнат для аренды/ продажи
    }
    //Жилая площадь в кв. метрах
    if ($v = self::getLotAreaLiving($lot)) {
      $data['FLATINF']['LIVESQUARE'] = $v;
    }
    //Площадь кухни в кв. метрах
    if ($v = self::getLotAreaKitchen($lot)) {
      $data['FLATINF']['KITCHENSQUARE'] = $v;
    }
    //Количество спален
    if ($v = self::getLotNbBedrooms($lot)) {
      $data['FLATINF']['ELITE']['BADROOMS']['attributes']['BADROOM_QTY'] = $v;
    }
    //Количество санузлов (Дополнительная информация об элитной недвижимости)
    if ($v = self::getLotNbBathrooms($lot)) {
      $data['FLATINF']['ELITE']['BATHROOMS']['attributes']['BATHROOM_QTY'] = $v;
    }
    //Информация о типе кондиционера. Значение из справочника
    if ($v = self::getLotAircond($lot)) {
      $data['FLATINF']['AUXINFO']['attributes']['AIRCOND'] = $v;
    }
    //Тип балкона или лоджии. Значение из справочника
    if ($v = self::getLotBalconies($lot)) {
      $data['FLATINF']['AUXINFO']['attributes']['BALCONY'] = $v;
    }
    //Есть ли балкон
    if (isset($data['FLATINF']['AUXINFO']['attributes']['BALCONY']) && $data['FLATINF']['AUXINFO']['attributes']['BALCONY'] != 'нет') {
      $data['FLATINF']['AUXINFO']['attributes']['IS_BALCONY'] = 'TRUE';
    }
    //Информация о сети интернет. Значение из справочника
    if (self::getLotIsInternet($lot)) {
      $data['FLATINF']['AUXINFO']['attributes']['INET'] = 'есть';
    }
    //Информация о ТВ-подключении. Значение из справочника
    if ($v = self::getLotTV($lot)) {
      $data['FLATINF']['AUXINFO']['attributes']['TV'] = $v;
    }
    //Информация о дополнительных удобствах. Можно указать несколько из возможных справочных значений. Разделитель — точка с запятой («;»)
    if ($v = self::getLotFacilities($lot)) {
      $data['FLATINF']['AUXINFO']['attributes']['ADD_IMPROV'] = $v;
    }

    //Информация об участке
    //Общая площадь участка, единица измерения — сотки
    if ($v = self::getLotAreaLand($lot)) {
      $data['GROUNDINF']['GROUNDSQUARE'] = $v;
    }
    //Текущее назначение участка. Значение из справочника
    if ($v = self::getLotPlotType($lot)) {
      $data['DOCS']['GROUND']['attributes']['GROUND_GOAL'] = $v;
    }
    //Права текущих владельцев участка. Значение из справочника
    if ($v = self::getLotPlotLaw($lot)) {
      $data['DOCS']['GROUND']['attributes']['LEGAL_STATUS'] = $v;
    }

    return $data;
  }

  protected function getDataArrayCommerce($lot)
  {
    $data = array();
    //Класс объекта. Значение из справочника
    if ($v = self::getLotBuildClass($lot)) {
      $data['COMMERCIAL']['attributes']['OBJ_CLASS'] = $v;
    }
    //Назначение объекта. Значение из справочника
    if ($v = self::getLotPurpose($lot)) {
      $data['COMMERCIAL']['attributes']['OBJ_PURPOSE'] = $v;
    }
    //Перечень инфраструктуры на объекте (до 100 символов)
    if (!empty($lot->params['infra_objects'])) {
      $data['COMMERCIAL']['attributes']['INFRA_OBJECTS'] = mb_strcut($lot->params['infra_objects'], 0, 100);
    }
    //Количество машиномест, входящее в стоимость объекта
    if (!empty($lot->params['parking']) && ctype_digit($lot->params['parking'])) {
      $data['INFRASTRUCTURE']['PARKING']['attributes']['PARKING_QTY'] = (int) $lot->params['parking'];
    }


    return $data;
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

    if ($lot->is_land_type) {
      if (empty($lot->params['spaceplot'])) {
        throw new Exception('spaceplot parameter is empty');
      }
      if (!self::getLotAreaLand($lot)) {
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

    if (self::getLotObjectType($lot) == 'квартира' && self::getLotRegion($lot) == 'Москва') {
      if (empty($lot->address['street'])) {
        throw new Exception('street address is empty');
      }
      if (empty($lot->metro_id)) {
        throw new Exception('metro is empty');
      }
    }
  }


  protected static function getLotMetroDistanceWalk($lot)
  {
    $v = parent::getLotMetroDistanceWalk($lot);
    if ($v <= 5)  return 'до 5 минут';
    if ($v <= 10) return 'до 10 минут';
    if ($v <= 15) return 'до 15 минут';
    if ($v <= 20) return 'до 20 минут';
    if ($v <= 30) return 'до 30 минут';
    if ($v > 30 && $v <= 60) return '30 минут - 1 час';
    if ($v > 60)  return 'более 1 часа';

    return null;
  }

  protected static function getLotMetroDistanceTransport($lot)
  {
    $v = parent::getLotMetroDistanceTransport($lot);
    if ($v <= 5)  return 'до 5 минут';
    if ($v <= 10) return 'до 10 минут';
    if ($v <= 15) return 'до 15 минут';
    if ($v <= 20) return 'до 20 минут';
    if ($v <= 30) return 'до 30 минут';
    if ($v > 30 && $v <= 60) return '30 минут - 1 час';
    if ($v > 60)  return 'более 1 часа';

    return null;
  }

  protected static function getLotOverlap($lot)
  {
    if (!empty($lot->params['constrfloors'])) {
      $v = $lot->params['constrfloors'];
      if (preg_match('/(?:^|[^\wа-я])ж(?:елезо)*[-\/]*б(?:етон)*(?:н((?:о|ы)е|ый))*(?:[.,\s]|$)/iu', $v)) return 'ЖБ';
      if (mb_stripos($v, 'дерев') !== false)  return 'дерево';
      if (mb_stripos($v, 'смешан') !== false) return 'смешанные';
    }

    return null;
  }

  protected static function getLotWindows($lot)
  {
    if (!empty($lot->params['windows'])) {
      $v = $lot->params['windows'];
      if (mb_stripos($v, 'стеклопакет') !== false) return 'стеклопакет';
      if (mb_stripos($v, 'дерев') !== false)       return 'деревянные';
    }

    return null;
  }

  protected static function getLotWindowView($lot)
  {
    if (!empty($lot->params['where_to_go_out_the_window'])) {
      $v = $lot->params['where_to_go_out_the_window'];
      if (mb_stripos($v, 'двор') !== false && mb_stripos($v, 'улиц') !== false) return 'двор, улица';
      if (mb_stripos($v, 'двор') !== false) return 'двор';
      if (mb_stripos($v, 'улиц') !== false) return 'улица';
      if (mb_stripos($v, 'парк') !== false) return 'парк, лес';
      if (mb_stripos($v, 'лес') !== false)  return 'парк, лес';
    }

    return null;
  }

  protected static function getLotParking($lot)
  {
    if (!empty($lot->params['infra_parking'])) {
      $v = $lot->params['infra_parking'];
      if (is_array($v)) $v = implode(' ', $v);

      if (self::isValueMeansNo($v))                 return 'отсутствует';
      if (mb_stripos($v, 'гараж') !== false)        return 'гараж';
      if (mb_stripos($v, 'стихийн') !== false)      return 'стихийная парковка';
      if (mb_stripos($v, 'подземн') !== false)      return 'подземная парковка';
      if (mb_stripos($v, 'неохраняемая') !== false) return 'неохраняемая парковка';
      if (mb_stripos($v, 'охраняемая') !== false)   return 'охраняемая парковка';
    }

    return null;
  }

  protected static function getLotGas($lot)
  {
    if (!empty($lot->params['service_gas'])) {
      $v = $lot->params['service_gas'];
      if (self::isValueMeansNo($v))     return 'нет';
      if (mb_stripos($v, 'автономн') !== false)   return 'автономный';
      if (preg_match('/(?:^|\s)центр|магистр/iu', $v))  return 'магистральный';
    }

    return null;
  }

  protected static function getLotWater($lot)
  {
    if (!empty($lot->params['service_water'])) {
      $v = $lot->params['service_water'];
      if (self::isValueMeansNo($v)) return 'нет';
      if (preg_match('/(?:^|\s)центр|магистр/iu', $v)) return 'центральное водоснабжение';
      if (mb_stripos($v, 'скважин') !== false && !preg_match('/посел[ок]/iu', $v)) return 'скважина на участке';
    }

    return null;
  }

  protected static function getLotSewerage($lot)
  {
    if (!empty($lot->params['service_drainage'])) {
      $v = $lot->params['service_drainage'];
      if (self::isValueMeansNo($v)) return 'нет';
      if (preg_match('/(?:^|\s)септик/iu', $v)) return 'септик';
      if (preg_match('/(?:^|\s)центр|магистр/iu', $v)) return 'центральная';
    }

    return null;
  }

  protected static function getLotAircond($lot)
  {
    $v = '';
    if (!empty($lot->params['about_conditioning'])) {
      $v .= $lot->params['about_conditioning'];
    }
    if (!empty($lot->params['conditioning'])) {
      $v .= !empty($v) ? ' ' : '';
      $v .= $lot->params['conditioning'];
    }

    if (empty($v))                              return null;
    if (self::isValueMeansNo($v))               return 'нет';
    if (mb_stripos($v, 'центральн') !== false)  return 'центральное кондиционирование';
    if (mb_stripos($v, 'сплит') !== false)      return 'сплит-система';

    return null;
  }

  protected static function getLotTV($lot)
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

    if (empty($v)) return null;
    if (preg_match('/(?:[^\wа-я]|^)спутник(овое)*\s(?:TV|т(?:еле)*в(?:идение)*)(?:[^\wа-я]|$)/iu', $v)) return 'спутниковое';
    if (preg_match('/(?:[^\wа-я]|^)(?:сателлит|космос)\s(?:TV|ТВ)(?:[^\wа-я]|$)/iu', $v))               return 'спутниковое';
    if (preg_match('/(?:[^\wа-я]|^)кабель(ное)*\s(?:TV|т(?:еле)*в(?:идение)*)(?:[^\wа-я]|$)/iu', $v))   return 'кабельное';

    return null;
  }

  protected static function getLotFacilities($lot)
  {
    $v = '';
    if (!empty($lot->params['infra_objects'])) {
      $v .= $lot->params['infra_objects'];
    }
    if (!empty($lot->params['infra_additional'])) {
      $v .= !empty($v) ? ' ' : '';
      $v .= $lot->params['infra_additional'];
    }
    if (!empty($lot->params['other'])) {
      $v .= !empty($v) ? ' ' : '';
      $v .= $lot->params['other'];
    }

    if (empty($v)) return null;

    $data = array();
    if (mb_stripos($v, 'сауна') !== false || mb_stripos($v, 'баня') !== false) $data[] = 'сауна / баня';
    if (mb_stripos($v, 'тренажер') !== false || mb_stripos($v, 'фитнес') !== false) $data[] = 'тренажерный зал';
    if (mb_stripos($v, 'бассейн') !== false) $data[] = 'бассейн';
    if (mb_stripos($v, 'бильярд') !== false) $data[] = 'бильярдная';

    return !empty($data) ? implode(';', $data) : null;
  }

  protected static function getLotBathrooms($lot)
  {
    $s = self::getLotNbBathroomsSeparate($lot);
    $c = self::getLotNbBathroomsCombined($lot);

    if ($s == 1 && !$c)   return 'раздельный';
    if ($c == 1 && !$s)   return 'совмещенный';
    if ($s > 0 || $c > 0) return (int) $s + (int) $c;

    if ($v = self::getLotNbBathrooms($lot)) return $v;

    return null;
  }

  protected static function getLotBalconies($lot)
  {
    $b = self::getLotNbBalconies($lot);
    $l = self::getLotNbLoggias($lot);

    if (is_null($b) && is_null($l))   return null;//если null - нет данных
    if ($b <= 0 && $l <= 0)           return 'нет';//если false или 0 - точно нету
    if ($b > 0 && $l > 0)             return 'балкон и лоджия';//если true или число - то есть
    if ($b === true)                  return 'балкон';
    if ($l === true)                  return 'лоджия';
    return 'есть';
  }

  protected static function getLotPlotType($lot)
  {
    if (!empty($lot->params['type_of_land'])) {
      $v = $lot->params['type_of_land'];
      if (mb_stripos($v, 'дачное строительство') !== false ) return 'ИЖС';
      if (preg_match('/(?:^|[^\wа-я])и(?:ндивидуальное )*ж(?:илое )*с(?:троительство)*(?:$|[^\wа-я])/iu', $v)) return 'ИЖС';
      if (preg_match('/(?:^|[^\wа-я])с(?:адоводческое )*н(?:екоммерческое )*т(?:оварищество)*(?:$|[^\wа-я])/iu', $v)) return 'Садоводство';
    }

    return null;
  }

  protected static function getLotPlotLaw($lot)
  {
    if (!empty($lot->params['plotlaw'])) {
      $v = $lot->params['plotlaw'];
      if (mb_stripos($v, 'собственност') !== false) return 'собственность';
    }

    return null;
  }

  protected static function getLotPurpose($lot)
  {
    if (!empty($lot->params['goaluse'])) {
      $v = $lot->params['goaluse'];
      if (mb_stripos($v, 'кроме') !== false)    return null;
      if (mb_stripos($v, 'аптека') !== false)   return 'аптека';
      if (mb_stripos($v, 'ресторан') !== false) return 'ресторан';
      if (mb_stripos($v, 'мойка') !== false)    return 'мойка';
      if (preg_match('/(?:,\s|^)салон(?:\sкрасоты)*(?:\s,|.|$)/iu', $v))    return 'салон красоты';
      if (preg_match('/(?:,\s|^)(?:ночной\s)*клуб(?:[^\wа-я]|$)/iu', $v))   return 'ночной клуб';
      if (preg_match('/(?:[^\wа-я]\s|,\s|^)бар(?:,|\s[^\wа-я]|$)/iu', $v))  return 'бар';
    }

    return null;
  }

  protected static function getLotBuildClass($lot)
  {
    if (!$lot->is_commercial_type && !empty($lot->params['buildtype'])) {
      $v = $lot->params['buildtype'];
      if (in_array(mb_strtolower($v), array('а','a'))
       || preg_match('/de[\s-]luxe|де[\s-]люкс/iu', $v)
       || preg_match('/класса*\s[АA](?:[^\а-я]|$)/iu', $v)
       || preg_match('/(?:^|[^\wа-я])[АA][\s-]класс/iu', $v))   return 'De Luxe (А)';
      if (preg_match('/luxe|люкс/iu', $v))                      return 'Luxe (А-)';
      if (in_array(mb_strtolower($v), array('b','б','в'))
       || preg_match('/premium|премиум/iu', $v)
       || preg_match('/класса*\s[BВБ](?:[^\а-я]|$)/iu', $v)
       || preg_match('/(?:^|[^\wа-я])[BВБ][\s-]класс/iu', $v))  return 'Premium (В)';
      if (in_array(mb_strtolower($v), array('c','с'))
       || preg_match('/(класса*[\s-])бизнес|бизнес([\s-]класса*)/ui', $v)
       || preg_match('/класса*\s[CС](?:[^\а-я]|$)/iu', $v)
       || preg_match('/(?:^|[^\wа-я])[CС][\s-]класс/iu', $v))   return 'Business (С)';
    }
    elseif (!empty($lot->params['buildclass']) && in_array($lot->params['buildclass'], array('A','A+','A-','B','B+','C','А','А+','А-','В','В+','С'))) {
      return str_replace(array('А','В','С'), array('A','B','C'), $lot->params['buildclass']);
    }

    return null;
  }

  protected static function getLotConstructionType($lot)
  {
    if ($lot->is_commercial_type && !empty($lot->params['buildtype'])) {
      $v = $lot->params['buildtype'];
      if (preg_match('/(?:^|\s)бизнес[\s-]+парк(?:[.,\s]|$)/iu', $v))             return 'Бизнес-парк';
      if (preg_match('/(?:^|\s)б(?:изнес[\s-]+)*ц(?:ентр)*(?:[.,\s]|$)/iu', $v))  return 'Бизнес центр';
      if (preg_match('/(?:^|\s)административное(?:\s+здание|$)/iu', $v))          return 'Административное здание';
      if (preg_match('/(?:^|\s)офисное(?:\s+здание|$)/iu', $v))                   return 'Офисное здание';
      if (preg_match('/офисно[\s-]жило(е|й)/iu', $v))                             return 'Офисно-жилой комплекс';
      if (preg_match('/офисно[\s-]складско(е|й)/iu', $v))                         return 'Офисно-складской комплекс';
      if (mb_stripos($v, 'офис') !== false
       && mb_stripos($v, 'склад') !== false
       && mb_stripos($v, 'производ') !== false)                                   return 'Офисно-производственно-складской комплекс';
      if (mb_stripos($v, 'офис') !== false
       && mb_stripos($v, 'производ') !== false)                                   return 'Офисно-производственный комплекс';
      if (mb_stripos($v, 'производ') !== false
       && mb_stripos($v, 'склад') !== false)                                      return 'Производственно-складской комплекс';
      if (mb_stripos($v, 'производственный комплекс') !== false)                  return 'Производственный комплекс';
      if (mb_stripos($v, 'складской комплекс') !== false)                         return 'Складской комплекс';
      if (preg_match('/торгов(о|ый)[-\s]офисный комплекс/iu', $v))                return 'Торгово-офисный комплекс';
      if (preg_match('/торгов(о|ый)[-\s]офисный центр/iu', $v))                   return 'Торгово-офисный центр';
      if (preg_match('/торгов(о|ый)[-\s]развлекатель/iu', $v))                    return 'Торгово-развлекательный центр';
      if (preg_match('/торгов(о|ый)[-\s]обществ/iu', $v))                         return 'Торгово-общественный центр';
      if (mb_stripos($v, 'развлекательный центр') !== false)                      return 'Развлекательный центр';
      if (mb_stripos($v, 'торговый центр') !== false)                             return 'Торговый центр';
      if (preg_match('/(?:^|\s)м(?:(ного|ульти))*ф(?:ункциональный )*к(?:омплекс)*(?:[.,\s]|$)/iu', $v))    return 'Многофункциональный комплекс';
      if (preg_match('/(?:^|\s)о(?:тдельно[\s-]+)*с(?:тоящее\s)*([а-я\s])*з(?:дание)*(?:[.,\s]|$)/iu', $v)) return 'Особняк';
      if (mb_stripos($v, 'особняк') !== false)                                  return 'Особняк';
      if (mb_stripos($v, 'ресторан') !== false)                                 return 'Ресторан';
      if (mb_stripos($v, 'гостиниц') !== false)                                 return 'Гостиница';
      if (mb_stripos($v, 'имущественный комплекс') !== false)                   return 'Имущественный комплекс';

    }
    elseif (!empty($lot->params['construction'])) {
      $v = $lot->params['construction'];
      if (mb_stripos($v, 'кирпич') !== false && mb_stripos($v, 'монолит') !== false)  return 'кирпично-монолитный';
      if (mb_stripos($v, 'панель') !== false && mb_stripos($v, 'монолит') !== false)  return 'монолитно-панельный';
      if (mb_stripos($v, 'сендвич') !== false)  return 'сендвич-панели';
      if (mb_stripos($v, 'кирпич') !== false)   return 'кирпичный';
      if (mb_stripos($v, 'панель') !== false)   return 'панельный';
      if (mb_stripos($v, 'монолит') !== false)  return 'монолитный';
      if (mb_stripos($v, 'пенобл') !== false)   return 'пеноблочный';
      if (mb_stripos($v, 'блочн') !== false)    return 'блочный';
      if (mb_stripos($v, 'блок') !== false)     return 'блочный';
      if (mb_stripos($v, 'дерев') !== false)    return 'деревянный (брусовой)';
      if (mb_stripos($v, 'брус') !== false)     return 'деревянный (брусовой)';
      if (mb_stripos($v, 'смешанн') !== false)  return 'смешанного типа';
      if (mb_stripos($v, 'сталин') !== false)   return 'сталинский';
      if (mb_stripos($v, 'хрущев') !== false)   return 'хрущевка';
      if (mb_stripos($v, 'брежнев') !== false)  return 'брежневка';
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
        return 'элитная';//или жилая ?

      case 'outoftown':
      case 'cottage':
        return 'элитная';//или загородная ?

      case 'comrent':
      case 'comsell':
        return 'коммерческая';
    }

    return null;
  }

  protected static function getLotObjectType($lot)
  {
    switch($lot->type) {
      case 'eliteflat':
      case 'penthouse':
      case 'flatrent':
      case 'elitenew':
        return 'квартира';

      case 'cottage':
      case 'outoftown':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Участок':             return 'земельный участок';
          case 'Коттедж':             return 'дом (коттедж)';
          case 'Таунхаус':            return 'таунхаус';
          case 'Квартира':            return null;
          case 'Коттеджный поселок':  return null;
          default:                    return null;
        }

      case 'comsell':
      case 'comrent':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Торговое помещение':              return 'торговое помещение';
          case 'Офисное помещение':               return 'офис';
          case 'Отдельно стоящее здание':         return null;
          case 'Готовый арендный бизнес':         return null;
          case 'Особняк':                         return null;
          case 'Помещение свободного назначения': return 'ПСН';
          case 'Склад/складской комплекс':        return 'склад';
          case 'Промышленный комплекс':           return 'производственное помещение';
          case 'Земельный участок':               return null;
          case 'Прочее':                          return null;
        }
        //помещение под услуги, помещение под банк, готовый бизнес, помещение под клуб, помещения под кафе, гараж
    }

    return null;
  }
}
