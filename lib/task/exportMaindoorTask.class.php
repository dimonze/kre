<?php
/*
 * только жилая нежвижимость
 * все числа - integer
 */

class exportMaindoorTask extends exportBaseTask
{
  const
    PARTNER   = 'maindoor',
    ENCODING  = 'utf-8';

  protected
    $_suptypes = array(
      'flats'     => array('eliteflat','penthouse','flatrent','elitenew'),
      'country'   => array('outoftown', 'cottage'),
    );


  protected function configure()
  {
    parent::configure();

    $this->name = 'maindoor';
  }


  protected function writeDocumentStart()
  {
    $this->_xml_writer->startDocument('1.0', self::ENCODING);
    $this->_xml_writer->startElement('maindoor');
    $this->_xml_writer->startElement('objects');
  }

  protected function writeDocumentFinish()
  {
    $this->_xml_writer->endElement();
    $this->_xml_writer->endElement();
    $this->_xml_writer->endDocument();
  }

  protected function getDataArray(Lot $lot)
  {
    $data = array(
      array('param' => array(
        'attributes'  => array('name' => 'Название на русском', 'code' => 'name_ru'),
        'data'        => $lot->name,
      )),
      array('param' => array(
        'attributes'  => array('name' => 'Внутренний id объекта на русском (задает агенство)', 'code' => 'md_ref_id_ru'),
        'data'        => $lot->id,
      )),
      array('param' => array(
        'attributes'  => array('name' => 'Агенство (компания - представитель объекта)', 'code' => 'company'),
        'data'        => self::ORG_NAME,
      )),
      array('param' => array(
        'attributes'  => array('name' => 'Тип недвижимости', 'code' => 'realty_type'),
        'data'        => '?',
      )),
      array('param' => array(
        'attributes'  => array('name' => 'Вид недвижимости', 'code' => 'realty_form'),
        'data'        => ($lot->is_newbuild_type ? 76 : 75),
      )),
      array('param' => array(
        'attributes'  => array('name' => 'Тип предложения', 'code' => 'section_id'),
        'data'        => ($lot->is_rent_type ? 18 : 17),
      )),
      array('param' => array(
        'attributes'  => array('name' => 'Страна', 'code' => 'country'),
        'data'        => 411,
      )),
      array('param' => array(
        'attributes'  => array('name' => 'Регион', 'code' => 'region'),
        'data'        => '?',
      )),
      array('param' => array(
        'attributes'  => array('name' => 'Город', 'code' => 'city'),
        'data'        => '?',
      )),
      array('param' => array(
        'attributes'  => array('name' => 'Расположение', 'code' => 'realty_location'),
        'data'        => '?',
      )),
      array('param' => array(
        'attributes'  => array('name' => 'Адрес на русском', 'code' => 'address_ru'),
        'data'        => sprintf('Россия, %s', $lot->getPrettyAddress('region', false)),
      )),
      array('param' => array(
        'attributes'  => array('name' => 'Подробное описание на русском', 'code' => 'detail_text_ru'),
        'data'        => self::getLotDescription($lot),
      )),
      array('param' => array(
        'attributes'  => array('name' => 'Цена показывается по запросу?', 'code' => 'price_on_request'),
        'data'        => 'нет',
      )),
      array('param' => array(
        'attributes'  => array('name' => 'Рекламируется?', 'code' => 'advertised'),
        'data'        => 'нет',
      )),
    );

    if ($lot->lat && $lot->lng) {
      $data[] = array('param' => array(
        'attributes'  => array('name' => 'Координаты на карте', 'code' => 'map'),
        'data'        => sprintf('%s,%s', $lot->lat, $lot->lng),
      ));
    }

    if ($v = self::getLotNbBedrooms($lot)) {
      $data[] = array('param' => array(
        'attributes'  => array('name' => 'Количество спален', 'code' => 'bedroom_count'),
        'data'        => $v,
     ));
    }

    if ($v = self::getLotAreaLand($lot)) {
      $data[] = array('param' => array(
        'attributes'  => array('name' => 'Площадь участка (м2)', 'code' => 'area_home'),
        'data'        => round($v),
      ));
    }

    if (!$lot->is_land_type) {
      $data[] = array('param' => array(
        'attributes'  => array('name' => 'Общая площадь (м2)', 'code' => 'area'),
        'data'        => round(self::getLotAreaTotal($lot)),
      ));
    }

    if ($lot->is_rent_type) {
      $data[] = array('param' => array(
        'attributes'  => array('name' => 'Цена (аренда)', 'code' => 'price_rent'),
        'data'        => round(self::getLotPrice($lot)),
      ));
      $data[] = array('param' => array(
        'attributes'  => array('name' => 'Валюта (аренда)', 'code' => 'currency_rent'),
        'data'        => self::getLotCurrency($lot),
      ));
    }
    else {
      $data[] = array('param' => array(
        'attributes'  => array('name' => 'Цена (продажа)', 'code' => 'price'),
        'data'        => round(self::getLotPrice($lot)),
      ));
      $data[] = array('param' => array(
        'attributes'  => array('name' => 'Валюта (продажа)', 'code' => 'currency'),
        'data'        => self::getLotCurrency($lot),
      ));
    }

    if ($photo = $lot->getImage('pres')) {
      $data[] = array('param' => array(
        'attributes'  => array('name' => 'Фото для анонса', 'code' => 'preview_photo'),
        'data'        => self::ORG_SITE . $photo,
      ));
    }

    $photos = array();
    foreach ($lot->Photos as $photo) {
      if (!$photo->is_pdf && !$photo->is_xls) {
        $photos[] = self::ORG_SITE . $photo->getImage('full');
      }
    }

    if (!empty($photos)) {
      $data[] = array('param' => array(
        'attributes'  => array('name' => 'Фотографии', 'code' => 'photos'),
        'data'        => implode(';', $photos),
      ));
    }

    return array('object' => $data);
  }

  protected function validateLot(Lot $lot)
  {
    if (!in_array($lot->type, $this->getAllowedTypes())) {
      throw new Exception(sprintf('not allowed lot type: "%s"', $lot->type));
    }
    //if (!self::getLotObjectType($lot)) {
//      throw new Exception(sprintf('unrecognized objecttype for "%s"', $lot->type.(isset($lot->params['objecttype']) ? ' ('.$lot->params['objecttype'].')' : '')));
//    }
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
  }


  protected static function getLotCurrency($lot)
  {
    switch ($lot->currency) {
      case 'RUR': return 'rub';
      case 'USD': return 'dol';
      case 'EUR': return 'eur';
    }
  }
}
