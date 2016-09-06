<?php
/*
 * разные файлы на разные типы недвижимости
 * только USD и RUB
 */

class exportSobTask extends exportBaseTask
{
  const
    PARTNER       = 'sob',
    ENCODING      = 'utf-8',
    DATE_FORMAT   = 'd.m.Y',
    PHONE_FORMAT  = '495%d%d%d';

  protected
    $_suptypes = array(
      'flatrent'  => array('flatrent'),
      'flatsale'  => array('eliteflat','penthouse','elitenew'),
      'country'   => array('outoftown', 'cottage'),
    );


  protected function configure()
  {
    parent::configure();

    $this->name = 'sob';
  }


  protected function writeDocumentStart()
  {
    $this->_xml_writer->startDocument('1.0', self::ENCODING);

    switch ($this->_current_type) {
      case 'flatsale':  $this->_xml_writer->startElement('flats');          break;
      case 'flatrent':  $this->_xml_writer->startElement('rent');           break;
      case 'country':   $this->_xml_writer->startElement('country_houses'); break;
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
    $data['id'] = $lot->id;
    $data['date'] = self::getLotCreationDate($lot);
    $data['aptp'] = self::getLotObjectType($lot);

    //ссылка на страницу объекта на клиентском сайте (временно не заполняется)
    $data['object_url'] = self::getLotUrl($lot);

    //цена
    $data['price']['data'] = self::getLotPrice($lot);
    $data['price']['attributes']['currency'] = self::getLotCurrency($lot);

    //регион
    $data['area'] = self::getLotRegion($lot);

    if ($lot->is_country_type) {
      $data = array_merge_recursive($data, $this->getDataArrayCountry($lot));
    }
    else {
      $data = array_merge_recursive($data, $this->getDataArrayFlats($lot));
    }

    //примечание
    $data['remark'] = self::getLotDescription($lot);

    //фотографии - ссылки на фотографии, разделённые ';'
    $photos = array();
    if ($photo = $lot->getImage('pres')) {
      $photos[] = self::ORG_SITE . $photo;
    }
    foreach ($lot->Photos as $photo) {
      if (!$photo->is_pdf && !$photo->is_xls) {
        $photos[] = self::ORG_SITE . $photo->getImage('full');
      }
    }
    if (!empty($photos)) {
      $data['photos'] = implode(';', $photos);
    }

    //контактный номер телефона
    $data['telefon'] = self::getLotPhone($lot);
    //эл.адрес (временно не заполняется)
    $data['email'] = self::ORG_EMAIL;
    //имя компании
    $data['company_name'] = self::ORG_NAME;
    //ссылка на сайт компании/личная страница
    $data['company_www'] = self::ORG_SITE;

    //тип объявления (enum: 0 - обычные объявления; 3 - привлекательные объявления)
    $data['tariff_mask'] = 0;


    return array(
      ($this->_current_type == 'country' ? 'country_house' : 'flat') => $data,
    );
  }

  protected function getDataArrayFlats(Lot $lot)
  {
    //станция метро (если area = Москва) или нас.пункт (Подмосковье)
    //farval="удалённость от станции в минутах (если area = Москва)"
    //fartp="тип удалённости, пешком (п) или транспортом (т) (если area = Москва)"
    if (self::getLotRegion($lot) == 'Москва') {
      $data['metro']['data'] = $lot->metro;
      if ($v = self::getLotMetroDistanceWalk($lot)) {
        $data['metro']['attributes']['farval'] = $v;
        $data['metro']['attributes']['fartp'] = 'п';
      }
      elseif ($v = self::getLotMetroDistanceTransport($lot)) {
        $data['metro']['attributes']['farval'] = $v;
        $data['metro']['attributes']['fartp'] = 'т';
      }
    }
    else {
      $data['metro']['data'] = self::getLotCity($lot);
    }

    //улица, обязательно с указанием типа адреса (ул., проезд и т.д.)
    $data['address'] = $lot->address['street'];
    //номер дома, корпуса
    $data['dom'] = $lot->getPrettyAddress('house', false);

    //тип дома
    if ($v = self::getLotConstructionType($lot)) {
      $data['tip'] = $v;
    }

    //этаж
    if ($v = self::getLotFloor($lot)) {
      $data['floor'] = $v;
    }
    //этажность
    if ($v = self::getLotNbFloors($lot)) {
      $data['fl_ob'] = $v;
    }
    //кол-во комнат в квартире
    if ($v = self::getLotNbRooms($lot)) {
      $data['flats'] = $v;
    }
    //общая площадь
    if ($v = self::getLotAreaTotal($lot)) {
      $data['sq']['attributes']['pl_ob'] = $v;
    }
    //жилая площадь
    if ($v = self::getLotAreaLiving($lot)) {
      $data['sq']['attributes']['pl'] = $v;
    }
    //площадь кухни
    if ($v = self::getLotAreaKitchen($lot)) {
      $data['sq']['attributes']['kitch'] = $v;
    }

    //балкон (enum: Б - балкон; Л - лоджия; БЛ - балкон и лоджия; Эрк - Эркер; ЭркЛ - Эркер и лоджия; -; 2Б; 2Л; 3Б; 3Л; 4Л; Б2Л; 2Б2Л)
    if ($v = self::getLotBalconies($lot)) {
      $data['balkon'] = $v;
    }
    //наличие телефона (enum: -; Т; 2Т)
    if (!is_null($v = self::getLotIsTelephone($lot))) {
      $data['tel'] = ($v ? 'Т' : '-');
    }
		//санузел (enum: -; +; 2; 3; 4; С; Р; 2С; 2Р; 3С; 3Р; 4С; 4Р)
    if ($v = self::getLotBathrooms($lot)) {
      $data['san'] = $v;
    }
    //лифт (enum: без лифта; лифт)
    if (!is_null($v = self::getLotIsElevator($lot))) {
      $data['lift'] = ($v ? 'лифт' : 'без лифта');
    }
    //ремонт (enum: после ремонта; требует ремонта; евроремонт; среднее состояние; хорошее состояние; отличное состояние; без отделки; эксклюзивный евроремонт)
    if ($v = self::getLotDecoration($lot)) {
      $data['remont'] = $v;
    }
		//окна (enum: окна на улицу; окна во двор; окна во двор и на улицу)
    if ($v = self::getLotWindowView($lot)) {
      $data['okna'] = $v;
    }
    //новостройка (boolean: +/-)
    $data['nova'] = $lot->is_newbuild_type ? '+' : '-';

    if (!$lot->is_rent_type) {
      //ипотека (boolean)
      if (!is_null($v = self::getLotIsMortgage($lot))) {
        $data['ipoteka'] = ($v ? '+' : '-');
      }
    }

    return $data;
  }

  protected function getDataArrayCountry(Lot $lot)
  {
    //название населённого пункта (если в МО несколько нас.пунтков с одинаковым названием, указание района обязательно!)
    if ($v = self::getLotCity($lot)) {
      $data['locality'] = $v;
    }
    if ($v = self::getLotRegionDistrict($lot)) {
    	$v = preg_replace('/\s*р(?:-|айо)н\s*/u', '', $v).' р-н';
      if (isset($data['locality'])) {
        $data['locality'] = $data['locality'].' ('.$v.')';
      }
      else {
      	$data['locality'] = $v;
      }
    }
    //название шоссе/направление
    if (!empty($lot->ward) || !empty($lot->ward2)) {
      $data['highway'] = sprintf('%s ш.', $lot->array_wards[0]);
    }
    //удаленность от МКАД в сотнях метров (умноженное на 10)
    if ($v = self::getLotDistanceMkad($lot)) {
      $data['otMKAD'] = $v*10;
    }
    //название улицы (обязательный параметр только для городов)
    if (!empty($lot->address['street'])) {
      $data['address'] = $lot->address['street'];
    }
    //номер дома, корпуса
    if ($v = $lot->getPrettyAddress('house', false)) {
      $data['dom'] = $v;
    }

    //площадь дома (в м2)
    $data['sq']['attributes']['pl'] = self::getLotAreaTotal($lot);
    //площадь участка (в сотках)
    if ($v = self::getLotAreaLand($lot)) {
      $data['sq']['attributes']['pl_s'] = $v;
    }

    //водоснабжение (enum: нет; центральный; скважина; колодец; магистральный; иное; есть; летний)
    if (!is_null($v = self::getLotIsWater($lot))) {
      $data['water'] = ($v ? 'есть' : 'нет');
    }
		//газификация (enum: нет; магистральный; по границе; перспектива; рядом; баллоны; есть; иное; центральный)
    if (!is_null($v = self::getLotIsGas($lot))) {
      $data['gas'] = ($v ? 'есть' : 'нет');
    }
		//канализация (enum: нет; есть; вне дома; септик; центральная; иное)
    if (!is_null($v = self::getLotIsSewerage($lot))) {
      $data['sewer'] = ($v ? 'есть' : 'нет');
    }
		//электроснабжение (enum: нет; есть; 220 В; 380 В; перспектива; по границе; 10 КВт; иное)
    if (!is_null($v = self::getLotIsElectricity($lot))) {
      $data['electro'] = ($v ? 'есть' : 'нет');
    }

    if ($lot->is_rent_type) {
      //тип операции
      $data['optp'] = 'аренда';
      //срок аренды (enum: Любой срок; Длительный срок; Посуточно; От месяца и более; Сезонная сдача)
      $data['rent_term'] = 'Длительный срок';
    }
    else {
      //тип операции
      $data['optp'] = 'продажа';
      //тип цены (enum: За всю площадь; За сотку (для участок); За кв.м. (для всех, КРОМЕ участок));
      $data['prc_type'] = $lot->is_land_type ? 'За сотку' : 'За всю площадь';
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
     || ($lot->is_country_type && self::getLotRegion($lot) != 'Московская обл.')) {
      throw new Exception(sprintf('lot of type "%s" has unexpected region: "%s"', $lot->type, self::getLotRegion($lot)));
    }
    if (empty($lot->area_from) && empty($lot->area_to)) {
        throw new Exception('area is empty');
      }
      if (($v = self::getLotAreaTotal($lot)) < 1) {
        throw new Exception(sprintf('area is less than one: "%s"', $v));
      }

    if (self::getLotRegion($lot) == 'Москва') {
      if (empty($lot->metro_id)) {
        throw new Exception('metro is empty');
      }
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

    if (self::getLotObjectType($lot) == 'Квартира') {
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
    else {

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
      case 'EUR':
      case 'RUR': return 'RUB';
      default:    return 'USD';
    }
  }

  protected static function getLotPrice($lot)
  {
    $currency = self::getLotCurrency($lot);
    $currency = $currency == 'RUB' ? 'RUR' : $currency;
    return round(Currency::convert(parent::getLotPrice($lot), $lot->currency, $currency));
  }

  protected static function getLotDecoration($lot)
  {
    //после ремонта; требует ремонта; евроремонт; среднее состояние; хорошее состояние; отличное состояние; без отделки; эксклюзивный евроремонт
    if (!empty($lot->params['about_decoration']) && $lot->params['about_decoration'] == 'без отделки') return 'без отделки';

    return null;
  }

  protected static function getLotWindowView($lot)
  {
    //окна на улицу; окна во двор; окна во двор и на улицу
    if (!empty($lot->params['where_to_go_out_the_window'])) {
      $v = $lot->params['where_to_go_out_the_window'];
      if (mb_stripos($v, 'двор') !== false && mb_stripos($v, 'улиц') !== false) return 'окна во двор и на улицу';
      if (mb_stripos($v, 'двор') !== false) return 'окна во двор';
      if (mb_stripos($v, 'улиц') !== false) return 'окна на улицу';
    }

    return null;
  }

  protected static function getLotConstructionType($lot)
  {
    //М - монолитный; П - панельный; К - кирпичный; Б - блочный; С - сталинский; Э - элитный; МК - монолитно-кирпичный
    $v = '';
    if (!empty($lot->params['construction'])) {
      $v .= $lot->params['construction'];
    }
    if (!empty($lot->params['buildtype'])) {
      $v .= !empty($v) ? ' ' : '';
      $v .= $lot->params['buildtype'];
    }

    if (!empty($v)) {
      if (mb_stripos($v, 'монолит') !== false && mb_stripos($v, 'кирпич') !== false)  return 'МК';
      if (mb_stripos($v, 'монолит') !== false)                                        return 'М';
      if (mb_stripos($v, 'кирпич') !== false)                                         return 'К';
      if (mb_stripos($v, 'блок') !== false || mb_stripos($v, 'блочн') !== false)      return 'Б';
      if (mb_stripos($v, 'панель') !== false)                                         return 'П';
      if (mb_stripos($v, 'сталин') !== false)                                         return 'С';
      if (mb_stripos($v, 'элит') !== false)                                           return 'Э';
    }

    return null;
  }

  protected static function getLotBathrooms($lot)
  {
    //-; +; 2; 3; 4; С; Р; 2С; 2Р; 3С; 3Р; 4С; 4Р
    $s = self::getLotNbBathroomsSeparate($lot);
    $c = self::getLotNbBathroomsCombined($lot);

    if (is_null($s) && is_null($c)) return null;
    if ($s == 1 && !$c)   return 'Р';
    if ($c == 1 && !$s)   return 'С';
    if ($s > 1 && !$c)    return $s.'Р';
    if ($c > 1 && !$s)    return $c.'С';
    if ($s > 0 && $c > 0) return $s+$c;

    if (self::getLotNbBathrooms($lot) > 0) return '+';

    return null;
  }

  protected static function getLotBalconies($lot)
  {
    //Б - балкон; Л - лоджия; БЛ - балкон и лоджия; Эрк - Эркер; ЭркЛ - Эркер и лоджия; -; 2Б; 2Л; 3Б; 3Л; 4Л; Б2Л; 2Б2Л
    $b = self::getLotNbBalconies($lot);
    $l = self::getLotNbLoggias($lot);

    if (is_null($b) && is_null($l))   return null;
    if ($b <= 0 && $l <= 0)           return '-';
    if ($b === true && $l === true)   return 'БЛ';
    if ($b > 1)                       return $b.'Б';
    if ($l > 1)                       return $l.'Л';
    if (!is_null($b))                 return 'Б';
    if (!is_null($l))                 return 'Л';
  }

  protected static function getLotObjectType($lot)
  {
    switch($lot->type) {
      case 'eliteflat':
      case 'penthouse':
      case 'flatrent':
      case 'elitenew':
        return 'Квартира';

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
          //дом; дача; коттедж; таунхаус; участок; коттеджный поселок;
          //дуплекс; квадрохаус; усадьба; часть дома (только для объектов М.О.)
        }
    }

    return null;
  }
}
