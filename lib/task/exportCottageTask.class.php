<?php
/*
 * Принимаются объявления только о продаже и аренде жилой загородной недвижимости и объектов из загородных малоэтажных жилых комплексов.
 * Должны передаваться все актуальные объявления.
 * URL объявления должен быть постоянным. Объявления, поступающие от партнера через фид, должны обновляться, а не удаляться и создаваться заново.
 * Цену объявления надо передавать только в той валюте, которую указал владелец объявления.
 * Если для объявления нет какого-то параметра, то не надо передавать соответствующий тег.
 *
 * При возникновении ошибки "значение locality-name для ID неправильное или его нет в нашей базе данных" смотреть список соответствий на странице http://www.cottage.ru/files/xml/
 */

class exportCottageTask extends exportBaseTask
{
  const
    PARTNER       = 'cottage',
    ENCODING      = 'utf-8',
    DATE_FORMAT   = 'Y-m-d\TH:i:s+03:00',
    PHONE_FORMAT  = '+7 (495) %d-%d-%d';

  protected
    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse','flatrent'),
      'country'   => array('outoftown', 'cottage'),
    );


  protected function configure()
  {
    parent::configure();

    $this->name = 'cottage';
  }


  protected function writeDocumentStart()
  {
    $this->_xml_writer->startDocument('1.0', self::ENCODING);
    $this->_xml_writer->startElementNS(null, 'realty-feed', 'http://webmaster.yandex.ru/schemas/feed/realty/2010-06');
    $this->_xml_writer->writeElement('generation-date', date(self::DATE_FORMAT));
  }

  protected function writeDocumentFinish()
  {
    $this->_xml_writer->endElement();
    $this->_xml_writer->endDocument();
  }

  protected function getDataArray(Lot $lot)
  {
    $data['type']                   = self::getLotOperationType($lot);;
    $data['property-type']          = 'жилая';
    $data['category']               = self::getLotCategory($lot);
    $data['url']                    = self::getLotUrl($lot);
    $data['creation-date']          = self::getLotCreationDate($lot);
    $data['last-update-date']       = self::getLotUpdationDate($lot);

    $data['location']['country']    = 'Россия';
    $data['location']['region']     = self::getLotRegion($lot);

    if ($lot->is_country_type) {
      //название района субъекта РФ
      if ($v = self::getLotRegionDistrict($lot)) {
        $data['location']['district'] = $v;
      }
      //название города, деревни, поселка и т.д.
      if ($v = self::getLotCity($lot)) {
        $data['location']['locality-name'] = $v;
      }
      //название поселка/жилого комплекса, в котором находится данный объект
      if (!empty($lot->params['cottageVillage'])) {
        $data['location']['parent-object-name'] = $lot->params['cottageVillage'];
      }
      //улица, дом
      if ($v = $lot->getPrettyAddress('street', false)) {
        $data['location']['address'] = $v;
      }
      //шоссе (только для Москвы)
      if (!empty($lot->ward) || !empty($lot->ward2)) {
        $data['location']['direction'] = sprintf('%s шоссе', $lot->array_wards[0]);
      }
      //расстояние по шоссе от МКАД (указывается в км)
      if ($v = self::getLotDistanceMkad($lot)) {
        $data['location']['distance'] = $v;
      }
    }
    else {
      //название города, деревни, поселка и т.д.
      $data['location']['locality-name'] = 'Москва';
      //район города
      if ($v = self::getLotDistrict($lot)) {
        $data['location']['sub-locality-name'] = $v;
      }
      //название поселка/жилого комплекса, в котором находится данный объект
      if ($v = self::getLotEstate($lot)) {
        $data['location']['parent-object-name'] = $v;
      }
      //улица, дом
      if ($v = $lot->getPrettyAddress('street', false)) {
        $data['location']['address'] = $v;
      }
    }

    if ($lot->lat && $lot->lng) {
      $data['location']['latitude']   = $lot->lat;
      $data['location']['longitude']  = $lot->lng;
    }

    //Информация о сделке
    $data['price']['value']     = self::getLotPrice($lot);
    $data['price']['currency']  = $lot->currency;
    //в случае сдачи недвижимости в аренду — промежуток времени («день», «месяц», «day», «month»)
    if ($lot->is_rent_type) {
      $data['price']['period']  = 'месяц';
    }
    //ипотека (строго ограниченные значения — «да»/«нет», «true»/«false», «1»/«0»)
    if (!is_null($v = self::getLotIsMortgage($lot))) {
      $data['mortgage'] = ($v ? 'да' : 'нет');
    }

    //Информация об объекте
    //общая площадь
    if ($v = self::getLotAreaTotal($lot)) {
      $data['area']['value']  = $v;
      $data['area']['unit']   = 'кв. м';
    }
    //площадь кухни
    if ($v = self::getLotAreaKitchen($lot)) {
      $data['kitchen-space']['value'] = $v;
      $data['kitchen-space']['unit']  = 'кв. м';
    }
    //площадь участка
    if ($v = self::getLotAreaLand($lot)) {
      $data['lot-area']['value']  = $v;
      $data['lot-area']['unit']   = 'сот';
    }

    //Описание жилого помещения
    //элитность («да»/«нет», « true»/«false», «1»/«0», «+»/«˗»)
    $data['is-elite'] = 'да';
    //устанавливается, если квартира продается в новостройке («да», «true», «1», «+»)
    if ($lot->is_newbuild_type) {
      $data['new-flat'] = 'да';
    }
    //общее количество комнат в квартире
    if ($v = self::getLotNbRooms($lot)) {
      $data['rooms'] = $v;
    }
    //этаж
    if ($v = self::getLotFloor($lot)) {
      $data['floor'] = $v;
    }
    //общее количество этажей в доме
    if ($v = self::getLotNbFloors($lot)) {
      $data['floors-total'] = $v;
    }
    //тип балкона (рекомендуемые значения — «балкон», «лоджия», «2 балкона», «2 лоджии»)
    if ($v = self::getLotBalconies($lot)) {
      $data['balcony'] = $v;
    }
    //вид из окон (рекомендуемые значения — «во двор», «на улицу»)
    if ($v = self::getLotWindowView($lot)) {
      $data['window-view'] = $v;
    }
    //название жилого комплекса (для новостроек)
    if (isset($data['location']['parent-object-name'])) {
      $data['building-name'] = $data['location']['parent-object-name'];
    }
    //тип дома (рекомендуемые значения — «кирпичный», «монолит», «панельный»)
    if ($v = self::getLotConstructionType($lot)) {
      $data['building-type'] = $v;
    }
    //год постройки. Для новостроек — год сдачи (год необходимо указывать полностью, например, 1996, а не 96)
    if ($v = self::getLotBuiltYear($lot)) {
      $data['built-year'] = $v;
    }
    //для новостроек — квартал сдачи дома («1», «2», «3», «4»)
    if ($lot->is_newbuild_type && ($v = self::getLotBuiltQuarter($lot))) {
      $data['ready-quarter'] = $v;
    }
    //наличие лифта («да»/«нет», «true»/«false», «1»/«0», «+»/«˗»)
    if (!is_null($v = self::getLotIsElevator($lot))) {
      $data['lift'] = (int) $v;
    }
    //наличие парковки («да»/«нет», «true»/«false», «1»/«0», «+»/«˗»)
    if (!is_null($v = self::getLotIsParking($lot))) {
      $data['parking'] = (int) $v;
    }
    //наличие телефона («да»/ «нет», «true»/ «false», «1»/ «0», «+»/ «˗»)
    if (!is_null($v = self::getLotIsTelephone($lot))) {
      $data['phone'] = (int) $v;
    }
    //наличие интернета («да»/«нет», «true»/«false», «1»/«0», «+»/«˗»)
    if (!is_null($v = self::getLotIsInternet($lot))) {
      $data['internet'] = (int) $v;
    }
    //высота потолков
    if ($v = self::getLotCeilingHeight($lot)) {
      $data['ceiling-height'] = $v;
    }

    //Для загородной недвижимости
    //наличие водопровода («да»/«нет», « true»/«false», «1»/«0», «+»/«˗»)
    if (!is_null($v = self::getLotIsWater($lot))) {
      $data['water-supply'] = (int) $v;
    }
    //канализация («да»/«нет», «true»/«false», «1»/«0», «+»/«˗»)
    if (!is_null($v = self::getLotIsSewerage($lot))) {
      $data['sewerage-supply'] = (int) $v;
    }
    //электроснабжение («да»/«нет», «true»/«false», «1»/«0», «+»/«˗»)
    if (!is_null($v = self::getLotIsElectricity($lot))) {
      $data['electricity-supply'] = (int) $v;
    }
    //подключение к газовым сетям («да»/«нет», «true»/«false», «1»/«0», «+»/«˗»)
    if (!is_null($v = self::getLotIsGas($lot))) {
      $data['gas-supply'] = (int) $v;
    }

    //Информация о продавце
    $data['sales-agent']['category']      = 'агентство';
    $data['sales-agent']['organization']  = self::ORG_NAME;
    $data['sales-agent']['url']           = self::ORG_SITE;
    $data['sales-agent']['email']         = self::ORG_EMAIL;
    $data['sales-agent']['phone']         = self::getLotPhone($lot);


    $data['description'] = self::getLotDescription($lot);

    $data['image'] = array();
    if ($photo = $lot->getImage('pres')) {
      $data['image'][] = self::ORG_SITE . $photo;
    }
    foreach ($lot->Photos as $photo) {
      if (!$photo->is_pdf && !$photo->is_xls) {
        $data['image'][] = self::ORG_SITE . $photo->getImage('full');
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
      //для регионов обязательны следующие поля:
      //district (район субъекта РФ) или locality-name (если он является административным центром)
      //или district (район субъекта РФ) + locality-name (который НЕ является административным центром и тогда будет записан в поле "Ближайший населенный пункт")
      if (empty($lot->params['district_of']) && empty($lot->address['district'])
       && empty($lot->params['locality']) && empty($lot->address['city'])) {
        throw new Exception('both district and locality name are empty');
      }
    }
  }


  protected static function getLotWindowView($lot)
  {
    if (!empty($lot->params['where_to_go_out_the_window'])) {
      $v = $lot->params['where_to_go_out_the_window'];
      if (mb_stripos($v, 'двор') !== false) return 'во двор';
      if (mb_stripos($v, 'улиц') !== false) return 'на улицу';
    }

    return null;
  }

  protected static function getLotBalconies($lot)
  {
    $data = array();
    if (($v = self::getLotNbBalconies($lot))) {//если === 0 - значит точно нету, если === null - то нет данных
      $data[] = ($v > 1) ? $v.' балкона' : 'балкон'; //если true - то есть, но не понятно сколько
    }
    if (($v = self::getLotNbLoggias($lot)) !== 0) {
      $data[] = ($v > 1) ? $v.' лоджии' : 'лоджия';
    }

    return !empty($data) ? implode(' и ', $data) : null;
  }

  protected static function getLotConstructionType($lot)
  {
    if (!empty($lot->params['construction'])) {
      $v = $lot->params['construction'];
      if (mb_stripos($v, 'кирпич') !== false)   return 'кирпичный';
      if (mb_stripos($v, 'панель') !== false)   return 'панельный';
      if (mb_stripos($v, 'монолит') !== false)  return 'монолит';
      if (mb_stripos($v, 'блочн') !== false)    return 'блочный';
      if (mb_stripos($v, 'блок') !== false)     return 'блочный';
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
          case 'Квартира':            return 'квартира';
          case 'Коттедж':             return 'коттедж';
          case 'Коттеджный поселок':  return null;
          default:                    return null;
        }
    }

    return null;
  }
}
