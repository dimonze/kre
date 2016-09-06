<?php
/*
 * на весь файл указывается одна валюта (USD, EUR или RUR)
 */

class exportRealestateTask extends exportBaseTask
{
  const
    PARTNER     = 'realestate',
    ENCODING    = 'utf-8',
    CLIENT_ID   = 'Contact Real Estate';//5843 ?

  protected
    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse','flatrent','elitenew'),
      'commerce'  => array('comrent', 'comsell'),
      'country'   => array('outoftown', 'cottage'),
    );


  protected function configure()
  {
    parent::configure();

    $this->name = 'realestate';
  }


  protected function writeDocumentStart()
  {
    $this->_xml_writer->startDocument('1.0', self::ENCODING);
    $this->_xml_writer->startElement('ForRealEstate');
    $this->_xml_writer->writeAttribute('FromUserId', 'Contact Real Estate');
    $this->_xml_writer->writeAttribute('currency', 'RUR');
  }

  protected function writeDocumentFinish()
  {
    $this->_xml_writer->endElement();
    $this->_xml_writer->endDocument();
  }

  protected function getDataArray(Lot $lot)
  {
    //Идентификатор объекта во внешнем источнике (integer)
    $data['ExternalId'] = $lot->id;
    //Специальное название объекта
    $data['Name'] = $lot->name;
    //Расширенное описание объекта
    $data['Description'] = self::getLotDescription($lot);

    $data = array_merge($data, $this->{$this->getDataMethod($lot->type)}($lot));

    $data['Images'] = array();
    if ($photo = $lot->getImage('pres')) {
      $data['Images'][] = array('Image' => array(
        'attributes'  => array('isMain' => 'True'),//isMain=True если фотография является главной
        'data'        => self::ORG_SITE . $photo,
      ));
    }
    foreach ($lot->Photos as $photo) {
      if (!$photo->is_pdf && !$photo->is_xls) {
        $data['Images'][] = array('Image' => self::ORG_SITE . $photo->getImage('full'));
      }
    }

    return array('eatate' => array(
      'attributes'  => array('type' => self::getLotObjectType($lot)),
      'data'        => $data,
    ));
  }

  protected function getDataArrayFlats(Lot $lot)
  {
    //Адрес объекта
    $data['Address'] = $lot->getPrettyAddress('street', false);

    if (self::getLotRegion($lot) == 'Москва') {
      //Административный округ (enum)
      $data['Region'] = self::getLotCityArea($lot);
      //Район административного округа (enum)
      $data['SubRegion'] = self::getLotCityDistrict($lot);
      //Ближайшая станция метро (enum)
      $data['Metro'] = self::getLotMetro($lot);
      //Время от/до метро
      if ($v = self::getLotMetroDistanceWalk($lot)) {
        //пешком
        $data['TimeFromMetro']['attributes']['byTransport'] = 'False';
        $data['TimeFromMetro']['data'] = $v;
      }
      elseif ($v = self::getLotMetroDistanceTransport($lot)) {
        //на транспорте
        $data['TimeFromMetro']['attributes']['byTransport'] = 'True';
        $data['TimeFromMetro']['data'] = $v;
      }
    }
    else {
      //Административный округ (enum)
      $data['Region'] = 'Подмосковье';
      //Район административного округа (enum)
      $data['SubRegion'] = self::getLotCity($lot);
    }

    //Тип здания (enum)
    if ($v = self::getLotConstructionType($lot)) {
      $data['BuildingType'] = $v;
    }
    //Этаж, на котором расположен объект (integer)
    if ($v = self::getLotFloor($lot)) {
      $data['Floor'] = $v;
    }
    //Этажность здания (integer)
    if ($v = self::getLotNbFloors($lot)) {
      $data['Floors'] = $v;
    }
    //Количество комнат
    if ($v = self::getLotNbRooms($lot)) {
      $data['RoomsCount'] = ($v > 5 ? '&gt;5' : $v);
    }
    //Количество спален (integer)
    if ($v = self::getLotNbBedrooms($lot)) {
      $data['BedroomsCount'] = $v;
    }
    //Общая площадь объекта (float)
    if ($v = self::getLotAreaTotal($lot)) {
      $data['TotalArea'] = $v;
    }
    //Площадь комнат (float)
    if ($v = self::getLotAreaLiving($lot)) {
      $data['RoomsArea'] = $v;
    }
    //Площадь кухни (float)
    if ($v = self::getLotAreaKitchen($lot)) {
      $data['KitchenArea'] = $v;
    }
    //Высота потолков (float)
    if ($v = self::getLotCeilingHeight($lot)) {
      $data['CeilingHeight'] = $v;
    }
    //Тип санузла (enum)
    if ($v = self::getLotBathrooms($lot)) {
      $data['BathroomType'] = $v;
    }
    //Имеется балкон (boolean)
    if (self::getLotNbBalconies($lot)) {
      $data['HasBalcony'] = 'True';
    }
    //Имеется лоджия (boolean)
    if (self::getLotNbLoggias($lot)) {
      $data['HasLoggia'] = 'True';
    }
    //Вид из окна (enum)
    if ($v = self::getLotWindowView($lot)) {
      $data['WindowView'] = $v;
    }

    if ($lot->type == 'elitenew') {
      //Стадия строительства
      if ($v = self::getLotBuiltQuarter($lot)) {
        $data['ConstructionStage']['attributes']['quartal'] = $v;
      }
      if ($v = self::getLotBuiltYear($lot)) {
        $data['ConstructionStage']['attributes']['year'] = $v;
      }
    }

    if ($lot->is_rent_type) {
      //Арендная ставка (float)
      $data['PriceRent'] = round(Currency::convert(self::getLotPrice($lot), $lot->currency, 'RUR'));
      //Период аренды (enum: не важно; краткий; длительный)
      $data['RentPeriod'] = 'длительный';
    }
    else {
      //Общая стоимость (float)
      $data['PriceTotal']   = round(Currency::convert(self::getLotPrice($lot), $lot->currency, 'RUR'));
      //Цена за квадратный метр (float)
      if (!empty($lot->price_from) && $lot->price_from > 0) {
        $data['PricePerM2'] = round(Currency::convert(self::getLotPriceMeter($lot), $lot->currency, 'RUR'));
      }
    }

    return $data;
  }

  protected function getDataArrayCountry(Lot $lot)
  {
    //Ближайший населённый пункт
    $data['Address'] = parent::getLotCity($lot);//PARENT METHOD CALL
    //Район МО (enum)
    $data['StateRegion'] = self::getLotRegionDistrict($lot);
    //Направление (enum)
    $data['StateDirection'] = self::getLotHighwayDirection($lot);
    //Шоссе (enum)
    if ($v = $lot->array_wards) {
      $data['Highway'] = sprintf('%s шоссе', $v[0]);
    }
    //Расстояние от МКАД
    if ($v = self::getLotDistanceMkad($lot)) {
      $data['DistanceFromMKAD'] = $v;
    }

    if (in_array(self::getLotObjectType($lot), array('CottageRent','CottageSale'))) {
      //Обязательный тег. Тип объекта (enum: Загородный дом; Таунхаус)
      //$data['CottageType'];
      //Обязательный тег. Статус объекта (enum: Коттеджный поселок; Свободная застройка)
      //$data['CottageStatusType'];
    }
    elseif (self::getLotObjectType($lot) == 'Land') {
      //Обязательный тег. Статус участка (enum: Коттеджный поселок; Свободная застройка; Отдельный участок земли)
      //$data['LandType'];
    }

    //Есть интернет (boolean)
    if (!is_null(self::getLotIsInternet($lot))) {
      $data['HasInternet'] = 'True';
    }
    //Есть телефон (boolean)
    if (!is_null(self::getLotIsTelephone($lot))) {
      $data['HasPhone'] = 'True';
    }
    //Есть электрификация (boolean)
    if (!is_null(self::getLotIsElectricity($lot))) {
      $data['Electrified'] = 'True';
    }
    //Водоснабжение (enum: Общая скважина; Артезианская скважина; Центральный водопровод; Нет)
    if ($v = self::getLotWater($lot)) {
      $data['WaterSupply'] = $v;
    }
    //Газ (enum: Магистральный; Газовые баллоны; Нет)
    if ($v = self::getLotGas($lot)) {
      $data['GasType'] = $v;
    }
    //Канализация (enum: Центральная; Биоочистные сооружения; Вне дома; Септик
    if ($v = self::getLotSewerage($lot)) {
      $data['SewerType'] = $v;
    }

    if (self::getLotObjectType($lot) == 'CottageVillage') {
      //Площадь объекта От-До (float)
      foreach (self::getLotAreaTotalArray($lot) as $i => $v) {
        $data['CottageArea'][($i ? 'To' : 'From')] = $v;
      }
      //Площадь участка (в сотках) От-До (float)
      foreach (self::getLotAreaLandArray($lot) as $i => $v) {
        $data['LandArea'][($i ? 'To' : 'From')] = $v;
      }

      if ($lot->is_rent_type) {
        //Стоимость аренды От-До (float)
        foreach (self::getLotPriceArray($lot) as $i => $v) {
          $data['RentPrice'][($i ? 'To' : 'From')] = round(Currency::convert($v, $lot->currency, 'RUR'));
        }
      }
      else {
        //Общая стоимость От (float)
        if (!empty($lot->price_all_from) && $lot->price_all_from > 0) {
          $data['TotalPrice']['From'] = round(Currency::convert($lot->price_all_from, $lot->currency, 'RUR'));
        }
        //Общая стоимость До (float)
        if (!empty($lot->price_all_to) && $lot->price_all_to > 0 && $lot->price_all_to > $lot->price_all_from) {
          $data['TotalPrice']['To']   = round(Currency::convert($lot->price_all_to, $lot->currency, 'RUR'));
        }
        //Цена продажи за квадратный метр От (float)
        if (!empty($lot->price_from) && $lot->price_from > 0) {
          $data['PricePerM2']['From'] = round(Currency::convert($lot->price_from, $lot->currency, 'RUR'));
        }
        //Цена продажи за квадратный метр До (float)
        if (!empty($lot->price_to) && $lot->price_to > 0 && $lot->price_to > $lot->price_from) {
          $data['PricePerM2']['To']   = round(Currency::convert($lot->price_to, $lot->currency, 'RUR'));
        }
      }
    }
    elseif (self::getLotObjectType($lot) == 'Land') {
      //Площадь участка в сотках (float)
      if ($v = self::getLotAreaLand($lot)) {
        $data['LandArea'] = $v;
      }
      //Общая стоимость (float)
      $data['PriceTotal']    = round(Currency::convert(self::getLotPrice($lot), $lot->currency, 'RUR'));
      //Цена продажи за сотку (float)
      $data['PricePer100M2'] = round(Currency::convert(self::getLotPriceMeter($lot), $lot->currency, 'RUR'));
    }
    else {
      //Площадь объекта (float)
      if ($v = self::getLotAreaTotal($lot)) {
        $data['CottageArea'] = $v;
      }
      //Площадь участка в сотках (float)
      if ($v = self::getLotAreaLand($lot)) {
        $data['LandArea'] = $v;
      }

      if ($lot->is_rent_type) {
        //Арендная ставка (float)
        $data['PriceRent']    = round(Currency::convert(self::getLotPrice($lot), $lot->currency, 'RUR'));
      }
      else {
        //Общая стоимость (float)
        $data['PriceTotal']   = round(Currency::convert(self::getLotPrice($lot), $lot->currency, 'RUR'));
        //Цена за квадратный метр (float)
        if (!empty($lot->price_from) && $lot->price_form > 0) {
          $data['PricePerM2'] = round(Currency::convert(self::getLotPriceMeter($lot), $lot->currency, 'RUR'));
        }
      }
    }

    return $data;
  }

  protected function getDataArrayCommerce(Lot $lot)
  {
    //Ближайший населённый пункт
    $data['Address'] = parent::getLotCity($lot);//PARENT METHOD CALL

    if (self::getLotRegion($lot) == 'Москва') {
      //Административный округ (enum)
      $data['Region'] = self::getLotCityArea($lot);
      //Ближайшая станция метро (enum)
      if ($v = self::getLotMetro($lot)) {
        $data['Metro'] = $v;
      }
      //Время от/до метро
      if ($v = self::getLotMetroDistanceWalk($lot)) {
        //пешком
        $data['TimeFromMetro']['attributes']['byTransport'] = 'False';
        $data['TimeFromMetro']['data'] = $v;
      }
      elseif ($v = self::getLotMetroDistanceTransport($lot)) {
        //на транспорте
        $data['TimeFromMetro']['attributes']['byTransport'] = 'True';
        $data['TimeFromMetro']['data'] = $v;
      }
    }
    else {
      //Административный округ (enum)
      $data['Region'] = 'Подмосковье';
      //Шоссе (enum)
      if ($v = $lot->array_wards) {
        $data['Highway'] = sprintf('%s шоссе', $v[0]);
      }
      //Расстояние от МКАД
      if ($v = self::getLotDistanceMkad($lot)) {
        $data['DistanceFromMKAD'] = $v;
      }
    }

    if (in_array(self::getLotObjectType($lot), array(array('OfficeSale','OfficeRent')))) {
      //Тип объекта (enum)
      if ($v = self::getLotBuildType($lot)) {
        $data['OfficeType'] = $v;
      }
      //Класс здания (enum)
      if ($v = self::getLotBuildClass($lot)) {
        $data['BuildingClass'] = $v;
      }
      //Высота потолков (float)
      if ($v = self::getLotCeilingHeight($lot)) {
        $data['CeilingHeight'] = $v;
      }
      //Год постройки (YYYY)
      if ($v = self::getLotBuiltYear($lot)) {
        $data['BuildingYear'];
      }
      //Этажность здания (integer)
      if ($v = self::getLotNbFloors($lot)) {
        $data['Floors'] = $v;
      }
      //Тип парковки (enum: Наземная охраняемая; Неохраняемая; Подземная)
      if ($v = self::getLotParking($lot)) {
        $data['ParkingType'] = $v;
      }
    }
    elseif (in_array(self::getLotObjectType($lot), array('StoreSale','StoreRent'))) {
      //Тип объекта (enum)
      if ($v = self::getLotBuildType($lot)) {
        $data['StoreType'] = $v;
      }
      //Класс здания (enum)
      if ($v = self::getLotBuildClass($lot)) {
        $data['BuildingClass'] = $v;
      }
      //Высота потолков (float)
      if ($v = self::getLotCeilingHeight($lot)) {
        $data['CeilingHeight'] = $v;
      }
      //Год постройки (YYYY)
      if ($v = self::getLotBuiltYear($lot)) {
        $data['BuildingYear'] = $v;
      }
      //Тип парковки (enum: Наземная охраняемая; Неохраняемая; Подземная)
      if ($v = self::getLotParking($lot)) {
        $data['ParkingType'] = $v;
      }
      //Площадь участка в гектарах (float)
      if ($v = self::getLotAreaLand($lot)) {
        $data['TerritoryArea'] = round($v/100, 2);
      }
    }
    elseif (in_array(self::getLotObjectType($lot), array('RetailSale','RetailRent'))) {
      //Тип объекта (enum)
      if ($v = self::getLotBuildType($lot)) {
        $data['RetailType'] = $v;
      }
      //Год постройки (YYYY)
      if ($v = self::getLotBuiltYear($lot)) {
        $data['BuildingYear'] = $v;
      }
    }


    if ($lot->is_rent_type) {
      //Стоимость аренды От-До (float)
      foreach (self::getLotPriceArray($lot) as $i => $v) {
        $data['RentPrice'][($i ? 'To' : 'From')] = round(Currency::convert($v, $lot->currency, 'RUR'));
      }
      //Арендная ставка в год за квадратный метр От-До (float)
      foreach (self::getLotPriceMeterArray($lot) as $i => $v) {
        $data['RentYearPricePerM2'][($i ? 'To' : 'From')] = round(Currency::convert($v, $lot->currency, 'RUR'));
      }
    }
    else {
      //Цена за квадратный метр От-До (float)
      foreach (self::getLotPriceMeterArray($lot) as $i => $v) {
        $data['PricePerM2'][($i ? 'To' : 'From')]   = round(Currency::convert($v, $lot->currency, 'RUR'));
      }

      if (in_array(self::getLotObjectType($lot), array('StoreSale','StoreRent'))) {
        //Общая стоимость От-До (float)
        foreach (self::getLotPriceArray($lot) as $i => $v) {
          $data['TotalPrice'][($i ? 'To' : 'From')] = round(Currency::convert($v, $lot->currency, 'RUR'));
        }
      }
      else {
        //Общая стоимость От-До (float)
        foreach (self::getLotPriceArray($lot) as $i => $v) {
          $data['PriceTotal'][($i ? 'To' : 'From')] = round(Currency::convert($v, $lot->currency, 'RUR'));
        }
      }
    }

    //Площадь помещения От-До (float)
    foreach (self::getLotAreaTotalArray($lot) as $i => $v) {
      $data['RoomArea'][($i ? 'To' : 'From')] = $v;
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
    if ($v < 1) {
      throw new Exception(sprintf('price is less than one: "%s"', $v));
    }

    if (in_array(self::getLotObjectType($lot), array('FlatSale', 'FlatRent'))) {
      if (empty($lot->area_from) && empty($lot->area_to)) {
        throw new Exception('area is empty');
      }
      if (($v = self::getLotAreaTotal($lot)) < 1) {
        throw new Exception(sprintf('area is less than one: "%s"', $v));
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

      if (self::getLotFloor($lot) && self::getLotNbFloors($lot) && self::getLotFloor($lot) > self::getLotNbFloors($lot)) {
        throw new Exception(sprintf('floor number is greather than total floors: %s > %s', self::getLotFloor($lot), self::getLotNbFloors($lot)));
      }

      if (empty($lot->address['street'])) {
        throw new Exception('street address is empty');
      }
      if (empty($lot->address['house']) && empty($lot->address['building']) && empty($lot->address['construction'])) {
        throw new Exception('lot address house, building and construction are empty');
      }

      if (self::getLotRegion($lot) == 'Москва') {
        if (empty($lot->metro_id)) {
          throw new Exception('metro is empty');
        }
        if (empty($lot->district_id)) {
          throw new Exception('district is empty');
        }
        if (!self::getLotCityDistrict($lot)) {
          throw new Exception(sprintf('can\'t find matching district for "%s"', parent::getLotCityDistrict($lot)));
        }
      }
      else {
        if (!parent::getLotCity($lot)) {
          throw new Exception('city is empty');
        }
        if (!self::getLotCity($lot)) {
          throw new Exception(sprintf('can\'t find matching city for "%s"', parent::getLotCity($lot)));
        }
      }
    }
    elseif (in_array(self::getLotObjectType($lot), array('Land','CottageSale','CottageRent','CottageVillage'))) {
      if (!parent::getLotCity($lot)) {//PARENT METHOD CALL
        throw new Exception('city is empty');
      }
      if (!parent::getLotRegionDistrict($lot)) {
        throw new Exception('region district is empty');
      }
      if (!self::getLotRegionDistrict($lot)) {
        throw new Exception(sprintf('can\'t find matching region district for "%s"', parent::getLotRegionDistrict($lot)));
      }
      if (empty($lot->ward) && empty($lot->ward2)) {
        throw new Exception('wards is empty');
      }
      if (!self::getLotHighwayDirection($lot)) {
        throw new Exception(sprintf('can\'t find matching highway direction for "%s"', $lot->pretty_wards));
      }

      if (empty($lot->params['spaceplot'])) {
        throw new Exception('spaceplot parameter is empty');
      }
      if (!self::getLotAreaLandArray($lot)) {
        throw new Exception(sprintf('can\'t parse spaceplot parameter: "%s"', $lot->params['spaceplot']));
      }

      if (self::getLotObjectType($lot) != 'Land') {
        if (empty($lot->area_from) && empty($lot->area_to)) {
          throw new Exception('area is empty');
        }
        if (($v = self::getLotAreaTotal($lot)) < 1) {
          throw new Exception(sprintf('area is less than one: "%s"', $v));
        }
      }
    }
    else {
      if (empty($lot->params['buildclass'])) {
        throw new Exception('build class parameter is empty');
      }
      if (!self::getLotBuildClass($lot)) {
        throw new Exception(sprintf('can\'t find matching build class for "%s"', $lot->params['buildclass']));
      }
      if (empty($lot->params['buildtype'])) {
        throw new Exception('build type parameter is empty');
      }
      if (!self::getLotBuildType($lot)) {
        throw new Exception(sprintf('can\'t find matching build type for "%s"', $lot->params['buildtype']));
      }

      if (!parent::getLotCity($lot)) {//PARENT METHOD CALL
        throw new Exception('city is empty');
      }

      if (self::getLotRegion($lot) == 'Москва') {
        if (empty($lot->district_id)) {
          throw new Exception('district is empty');
        }
        if (!self::getLotCityDistrict($lot)) {
          throw new Exception(sprintf('can\'t find matching district for "%s"', parent::getLotCityDistrict($lot)));
        }
      }
    }
  }


  protected static function getLotCityArea($lot)
  {
    return rtrim(parent::getLotCityArea($lot), ' АО');
  }

  protected static function getLotCityDistrict($lot)
  {
    $v = parent::getLotCityDistrict($lot);
    switch ($v) {
      case 'Патриаршие пруды':
      case 'Сретенка':
      case 'Чистые пруды':
      case 'Остоженка':
      case 'Китай город':
      case 'Плющиха':
      case 'Москва Сити':
      case 'Новослободский':
      case 'Воробьевы горы':
        return null;//таких районов нет у них в справочнике
    }

    return $v;
  }

  protected static function getLotMetro($lot)
  {
    $metro = $lot->metro;

    switch ($metro) {
      case 'Улица Академика Янгеля':  return 'Ул. Академика Янгеля';
      case 'Воробьевы горы':          return 'Воробьёвы горы';
      case 'Китай-Город':             return 'Китай город';
      case 'Кузнецкий Мост':          return 'Кузнецкий мост';
      case 'Парк победы':             return 'Парк Победы';
      case 'Преображенская пл.':      return 'Преображенская площадь';
      case 'Улица 1905 года':         return 'Ул. 1905 года';
      case 'Улица Подбельского':      return 'ул. Подбельского';
      case 'Марьина роща':            return 'Марьина Роща';
      //case 'Битцевский парк':         return '';//в справочнике нету, но он старый
      //case 'Пятницкое шоссе':         return '';//наверно уже добавили эти станции
    }

    return $metro;
  }

  protected static function getLotCity($lot)
  {
    $dictionary = array(
      'Апрелевка',      'Балашиха',         'Бронницы',
      'Видное',         'Волоколамск',      'Воскресенск',
      'Голицино',       'Дедовск',          'Дмитров',
      'Долгопрудный',   'Домодедово',       'Железнодорожный',
      'Звенигород',     'Зеленоград',       'Ивантеевка',
      'Истра',          'Клин',             'Королёв',
      'Котельники',     'Красноармейск',    'Красногорск',
      'Лобня',          'Лыткарино',        'Люберцы',
      'Московский',     'Мытищи',           'Наро-Фоминск',
      'Ногинск',        'Одинцово',         'Орехово-Зуево',
      'Подольск',       'Пушкино',          'Пятигорск',
      'Раменское',      'Реутов',           'Сергиев Посад',
      'Серпухов',       'Солнечногорск',    'Сходня',
      'Талдом',         'Троицк',           'Фрязино',
      'Химки',          'Черноголовка',     'Чехов',
      'Щёлково',        'Электрогорск',     'Электросталь',
    );

    $v = parent::getLotCity($lot);
    if (!empty($v) && in_array($v, $dictionary)) return $v;

    return null;
  }

  protected static function getLotRegionDistrict($lot)
  {
    $dictionary = array(
      'Балашихинский район',    'Волоколамский район',        'Воскресенский район',
      'Дмитровский район',      'Домодедовский район',        'Егорьевский район',
      'Зарайский район',        'Истринский район',           'Каширский район',
      'Клинский район',         'Коломенский район',          'Красногорский район',
      'Ленинский район',        'Лотошинский район',          'Луховицкий район',
      'Люберецкий район',       'Можайский район',            'Мытищинский район',
      'Наро-Фоминский район',   'Ногинский район',            'Одинцовский район',
      'Озерский район',         'Орехово-Зуевский район',     'Павлово-Посадский район',
      'Подольский район',       'Пушкинский район',           'Раменский район',
      'Рузский район',          'Сергиево-Посадский район',   'Серебряно-Прудский район',
      'Серпуховский район',     'Солнечногорский район',      'Ступинский район',
      'Талдомский район',       'Химкинский район',           'Чеховский район',
      'Шатурский район',        'Шаховской район',            'Щелковский район',
    );

    $v = parent::getLotRegionDistrict($lot);
    if (!empty($v)) {
      $v = sprintf('%s район', preg_replace('/(?:\s+|^)(?:район|р-н)(?:\s+|$)/iu', '', $v));
      if (in_array($v, $dictionary)) return $v;
    }

    return null;
  }

  protected static function getLotHighwayDirection($lot)
  {
    $wards = array();
    if (!empty($lot->ward))   $wards[] = $lot->ward;
    if (!empty($lot->ward2))  $wards[] = $lot->ward2;

    foreach ($wards as $w) {
      switch ($w) {
        case in_array($w, array(1,6,19,21)):      return 'Север';
        case in_array($w, array(26,27)):          return 'Северо-Восток';
        case in_array($w, array(5,7,18)):         return 'Восток';
        case in_array($w, array(17,23)):          return 'Юго-Восток';
        case in_array($w, array(3,10,24)):        return 'Юг';
        case in_array($w, array(2,9,11,25)):      return 'Юго-Запад';
        case in_array($w, array(8,14,15,22)):     return 'Запад';
        case in_array($w, array(4,12,13,16,20)):  return 'Северо-Запад';
      }
    }

    return null;
  }

  protected static function getLotBathrooms($lot)
  {
    //1; 2; 3; 4; 5; 1.5; 2.5; Совмещенный
    $s = self::getLotNbBathroomsSeparate($lot);
    $c = self::getLotNbBathroomsCombined($lot);

    if ($c == 1 && !$s) return 'Совмещенный';
    if ($s || $c)       return (int) $s + (int) $c;

    return null;
  }

  protected static function getLotWater($lot)
  {
    //Общая скважина; Артезианская скважина; Центральный водопровод; Нет
    if (!empty($lot->params['service_water'])) {
      $v = $lot->params['service_water'];
      if (self::isValueMeansNo($v))                     return 'Нет';
      if (preg_match('/(?:^|\s)центр|магистр/iu', $v))  return 'Центральный водопровод';
      if (mb_stripos($v, 'артезианск') !== false)       return 'Артезианская скважина';
      if (mb_stripos($v, 'скважин') !== false && preg_match('/посел[ок]|общая/iu', $v)) return 'Общая скважина';
    }

    return null;
  }

  protected static function getLotGas($lot)
  {
    //Магистральный; Газовые баллоны; Нет
    if (!empty($lot->params['service_gas'])) {
      $v = $lot->params['service_gas'];
      if (self::isValueMeansNo($v))                     return 'Нет';
      if (preg_match('/(?:^|\s)центр|магистр/iu', $v))  return 'Магистральный';
    }

    return null;
  }

  protected static function getLotSewerage($lot)
  {
    //Центральная, Биоочистные сооружения, Вне дома, Септик
    if (!empty($lot->params['service_drainage'])) {
      $v = $lot->params['service_drainage'];
      if (self::isValueMeansNo($v))                     return null;
      if (preg_match('/(?:^|\s)септик/iu', $v))         return 'Септик';
      if (preg_match('/(?:^|\s)центр|магистр/iu', $v))  return 'Центральная';
    }

    return null;
  }

  protected static function getLotParking($lot)
  {
    if (!empty($lot->params['infra_parking'])) {
      $v = $lot->params['infra_parking'];
      if (is_array($v)) $v = implode(' ', $v);

      if (self::isValueMeansNo($v))                 return null;
      if (mb_stripos($v, 'подземн') !== false)      return 'Подземная';
      if (mb_stripos($v, 'неохраняемая') !== false) return 'Неохраняемая';
      if (mb_stripos($v, 'охраняем') !== false
       && mb_stripos($v, 'наземн') !== false)       return 'Наземная охраняемая';
    }

    return null;
  }

  protected static function getLotWindowView($lot)
  {
    //На улицу; Во двор; На две стороны; На три стороны; На четыре стороны; Панорамный
    if (!empty($lot->params['where_to_go_out_the_window'])) {
      $v = $lot->params['where_to_go_out_the_window'];
      if (mb_stripos($v, 'двор') !== false && mb_stripos($v, 'улиц') !== false) return 'На две стороны';
      if (mb_stripos($v, 'панорам') !== false)  return 'Панорамный';
      if (mb_stripos($v, 'двор') !== false)     return 'Во двор';
      if (mb_stripos($v, 'улиц') !== false)     return 'На улицу';
    }

    return null;
  }

  protected static function getLotConstructionType($lot)
  {
    //(не указан); Премиум; De Luxe; Бизнес-класс; Реконструкция; Старинный; Ведомственный(40х-90х); Панельный; Эконом; Монолитный; Блочный
    $v = '';
    if (!empty($lot->params['construction'])) {
      $v .= $lot->params['construction'];
    }
    if (!empty($lot->params['buildtype'])) {
      $v .= !empty($v) ? ' ' : '';
      $v .= $lot->params['buildtype'];
    }

    if (!empty($v)) {
      if (preg_match('/de[\s-]luxe|де[\s-]люкс/iu', $v))                          return 'De Luxe';
      if (preg_match('/(класса*[\s-])бизнес|бизнес([\s-]класса*)/ui', $v))        return 'Бизнес-класс';
      if (mb_stripos($v, 'блок') !== false || mb_stripos($v, 'блочн') !== false)  return 'Блочный';
      if (mb_stripos($v, 'реконструкция') !== false)                              return 'Реконструкция';
      if (mb_stripos($v, 'панель') !== false)                                     return 'Панельный';
      if (mb_stripos($v, 'монолит') !== false)                                    return 'Монолитный';
    }

    return '(не указан)';
  }

  protected static function getLotBuildClass($lot)
  {
    if (!empty($lot->params['buildclass']) && in_array($lot->params['buildclass'], array('А','A+','A-','B','B+','B-','C','C-','D','А','А+','А-','В','В+','В-','С','С-'))) {
      return str_replace(array('А','В','С'), array('A','B','C'), $lot->params['buildclass']);
    }

    return null;
  }

  protected static function getLotBuildType($lot)
  {
    if (!empty($lot->params['buildtype'])) {
      $v = $lot->params['buildtype'];

      if (in_array(self::getLotObjectType($lot), array('OfficeSale','OfficeRent'))) {
        if (preg_match('/(?:^|[^\wа-я])жило(?:й (?:дом|комплекс)|е здание)|(?:^|[^\wа-я])ЖК(?:[^\wа-я]|$)/iu', $v))
                return 'Жилой дом';
        if (preg_match('/(?:^|\s)административное(?:\s+(?:здание|строение)|$)/iu', $v))
                return 'Административное здание';
        if (preg_match('/(?:^|\s)бизнес[\s-]+парк(?:[.,\s]|$)/iu', $v))
                return 'Бизнес-парк';
        if (preg_match('/(?:^|\s)б(?:изнес[\s-]+)*ц(?:ентр)*(?:[.,\s]|$)/iu', $v))
                return 'Бизнес центр';
        if (preg_match('/(?:^|\s)офисное(?:\s+здание|$)/iu', $v))
                return 'Офисное здание';
        if (preg_match('/офисно[\s-]жило(е|й)/iu', $v))
                return 'Офисно-жилой комплекс';
        if (preg_match('/офисно[\s-]складско(е|й)/iu', $v))
                return 'Офисно-складской комплекс';
        if (preg_match('/торгов(о|ый)[-\s]офисный комплекс/iu', $v))
                return 'Торгово-офисный комплекс';
        if (preg_match('/торгов(о|ый)[-\s]развлекатель/iu', $v))
                return 'Торгово-развлекательный центр';
        if (preg_match('/(?:^|\s)м(?:(ного|ульти))*ф(?:ункциональный )*к(?:омплекс)*(?:[.,\s]|$)/iu', $v))
                return 'Многофункциональный комплекс';
        if (preg_match('/(?:^|\s)о(?:тдельно[\s-]+)*с(?:тоящее\s)*([а-я\s])*з(?:дание)*(?:[.,\s]|$)/iu', $v))
                return 'Отдельно стоящее строение';
        if (mb_stripos($v, 'банковское') !== false && mb_stripos($v, 'здание') !== false)
                return 'Банковское здание';
        if (mb_stripos($v, 'банковское') !== false && mb_stripos($v, 'помещение') !== false)
                return 'Банковское помещение';
      }
      elseif (in_array(self::getLotObjectType($lot), array('StoreSale','StoreRent'))) {
        if (preg_match('/встроенн[оейы]+ склад/iu', $v))
                return 'Встроенное складское помещение';
        if (preg_match('/перепрофилированн[оейы]+ склад/iu', $v))
                return 'Перепрофилированное складское помещение';
        if (preg_match('/офисно[\s-]складско(е|й)/iu', $v))
                return 'Современный офисно-складской комплекс';
        if (mb_stripos($v, 'складской комплекс') !== false)
                return 'Современный складской комплекс';
      }
      elseif (in_array(self::getLotObjectType($lot), array('RetailSale','RetailRent'))) {
        if (preg_match('/(?:^|[^\wа-я])жило(?:й (?:дом|комплекс)|е здание)|(?:^|[^\wа-я])ЖК(?:[^\wа-я]|$)/iu', $v))
                return 'Жилой дом';
        if (preg_match('/(?:^|\s)административное(?:\s+(?:здание|строение)|$)/iu', $v))
                return 'Административное здание';
        if (preg_match('/торгов(о|ый)[-\s]офисный комплекс/iu', $v))
                return 'Торгово-офисный комплекс';
        if (preg_match('/торгов(о|ый)[-\s]развлекатель/iu', $v))
                return 'Торгово-развлекательный центр';
        if (mb_stripos($v, 'торговый центр') !== false)
                return 'Торговый центр';
        if (mb_stripos($v, 'особняк') !== false || preg_match('/(?:^|\s)о(?:тдельно[\s-]+)*с(?:тоящее\s)*([а-я\s])*з(?:дание)*(?:[.,\s]|$)/iu', $v))
                return 'Отдельно стоящее строение';
        if (mb_stripos($v, 'рынок') !== false || mb_stripos($v, 'ярмарка') !== false)
                return 'Рынок/Ярмарка';
      }
    }

    return null;
  }

  protected static function getLotObjectType($lot)
  {
    switch($lot->type) {
      case 'eliteflat':
      case 'penthouse':
      case 'elitenew':
        return 'FlatSale';//Квартира. Продажа

      case 'flatrent':
        return 'FlatRent';//Квартира. Аренда

      case 'cottage':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Участок':             return null;
          case 'Коттедж':             return 'CottageRent';//Коттедж. Аренда
          case 'Таунхаус':            return null;
          case 'Квартира':            return null;
          case 'Коттеджный поселок':  return 'CottageVillage';//Коттеджный поселок
          default:                    return null;
        }

      case 'outoftown':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Участок':             return 'Land';//Земельный участок
          case 'Коттедж':             return 'CottageSale';//Коттедж. Продажа
          case 'Таунхаус':            return null;
          case 'Квартира':            return null;
          case 'Коттеджный поселок':  return 'CottageVillage';//Коттеджный поселок
          default:                    return null;
        }

      case 'comsell':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Торговое помещение':              return 'RetailSale';//Торговое помещение. Продажа
          case 'Офисное помещение':               return 'OfficeSale';//Офис. Продажа
          case 'Отдельно стоящее здание':         return null;
          case 'Готовый арендный бизнес':         return null;
          case 'Особняк':                         return null;
          case 'Помещение свободного назначения': return null;
          case 'Склад/складской комплекс':        return 'StoreSale';//Склад. Продажа
          case 'Промышленный комплекс':           return null;
          case 'Земельный участок':               return null;
          case 'Прочее':                          return null;
        }

      case 'comrent':
        if (!isset($lot->params['objecttype'])) return null;
        switch ($lot->params['objecttype']) {
          case 'Торговое помещение':              return 'RetailRent';//Торговое помещение. Аренда
          case 'Офисное помещение':               return 'OfficeRent';//Офис. Аренда
          case 'Отдельно стоящее здание':         return null;
          case 'Готовый арендный бизнес':         return null;
          case 'Особняк':                         return null;
          case 'Помещение свободного назначения': return null;
          case 'Склад/складской комплекс':        return 'StoreRent';//Склад. Аренда
          case 'Промышленный комплекс':           return null;
          case 'Земельный участок':               return null;
          case 'Прочее':                          return null;
        }
    }

    return null;
  }
}
