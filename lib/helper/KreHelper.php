<?php

function single_or_range($lot, $param)
{
  list($p1, $p2) = array($param . '_from', $param . '_to' );
  if (!empty($lot->$p1) && $lot->$p1 > 0 && !empty($lot->$p2) && $lot->$p2 > 0) {
    return implode('<br />', array_filter(array('от ' . $lot->$p1, 'до ' . $lot->$p2)));
  }
  else {
    return implode('<br />', array_filter(array($lot->$p1, $lot->$p2), function($v) { return $v >0;}));
  }
}

function to_normal_alt($alt) {
  $alt = intval($alt);
  if (strlen($alt) == '1') {
    $vt = $alt;
  } else {
    $vt = substr($alt, -1);
    $vt2 = substr($alt, -2);
  }
  if ($vt == '0' || ($vt2 > '10' && $vt2 < '20'))
    $p = 'лет';
  elseif ($vt == '1')
    $p = 'год';
  elseif ($vt <= '4')
    $p = 'года';
  else
    $p = 'лет';
  return $p;
}

function single_or_range_price_converted($lot, $param, $currency, $separator = '<br />') {
  $values = array();

  if ($param == 'm_a_p') {
    foreach (array($param) as $field) {
      if ($lot->params[$field]) {
        $values[] = Currency::formatPrice($lot->params[$field], $lot->params['m_a_p_Currency'], $currency);
      }
    }
}
else if($param == 'price_land'){
  foreach (array($param . '_from', $param . '_to' ) as $field) {
    if ($lot->params[$field] && $lot->params[$field] > 0) {
      $values[] = Currency::formatPrice($lot->params[$field], $lot->currency, $currency);
    }
  }
}else{
  foreach (array($param . '_from', $param . '_to' ) as $field) {
    if ($lot->$field && $lot->$field > 0) {
      $values[] = Currency::formatPrice($lot->$field, $lot->currency, $currency);
    }
  }
}
  if (count($values) == 2) {
    $values[0] = 'от ' . $values[0];
    $values[1] = 'до ' . $values[1];
  }
  return implode($separator, $values);
}
function single_or_range_price($lot, $param, $currency, $separator = '<br />')
{
  $values = array();
  $currencies = array('RUR' => 'руб.', 'EUR' => '€', 'USD' => '$');

  foreach (array($param . '_from', $param . '_to' ) as $field) {
    if ($lot->$field && $lot->$field > 0) {
      $values[] = $lot->$field . ' ' . $currencies[$lot->currency];
    }
  }
  if (count($values) == 2) {
    $values[0] = 'от ' . $values[0];
    $values[1] = 'до ' . $values[1];
  }

  return implode($separator, $values);
}

function prepare_phone_number($sf_params)
{
  if ($sf_params->get('module') == 'lot' && ($type = $sf_params->get('type'))) {
    $number = sfConfig::get('app_phones_office_' . $type);
  }
  else {
    $number = sfConfig::get('app_phones_office');
  }
  return preg_replace('#([+\d\s()]+)\s+([\d\-]+)#', '$1</span> <span class="number">$2', $number);
}

function get_title_class($request)
{
  switch ($request->getParameter('module')) {
    case 'vacancy':
      return 'dark';
    case 'lot':
      if ($request->hasParameter('type') && !in_array($request->getParameter('type'), array('comsell','comrent'))) {
        return 'dark';
      }
    default:
      return 'bright';
  }
}
function url_for_params2($lot, $params)
{ 
  foreach($params as $key => $value){  
    if($key == 'price_all_from')$key = 'price_from';
    if($key == 'price_all_to')$key = 'price_to';
    switch($key){
      case 'area_from':        
      case 'area_to':        
      case 'price_all_from':        
      case 'price_all_to':
      case 'price_from':        
      case 'price_to':
        $value = round($value);
        break;
    }
    $data[$key] = $value;
  }  
  return url_for2('offers_list', $data).'#content';
}

function url_for_params($lot, $params)
{
  $data = array();
  foreach ((array) $params as $p) {
    switch ($p) {
      case 'pid':
      case 'currency':
        $val = $lot->get($p);
        break;
      case 'area_from':
        $val = round($lot->area_from * 0.8);
        break;
      case 'area_to':
        $val = round(($lot->area_to > 0 ? $lot->area_to : $lot->area_from) * 1.2);
        break;
      case 'price_from':
        if ($lot->is_commercial_type) {
          $val = round($lot->price_from * 0.8); 
        }
        else {
          $val = $lot->has_price_all ? round($lot->price_all_from * 0.8) : null;
        }
        break;
      case 'price_to':
        if ($lot->is_commercial_type) {
          $val = round($lot->price_to * 1.2);
        }
        else {
          $val = $lot->has_price_all ? round(($lot->price_all_to > 0 ? $lot->price_all_to : $lot->price_all_from) * 1.2) : null;
        }
        break;
      case 'district':
      case 'districts':
      case 'districts[]':
        $p = 'districts[]';
        $val = $lot->district_id;
        break;
      case 'ward':
      case 'wards':
      case 'wards[]':
        $p = 'wards[]';
        $val = $lot->ward;
        break;
      case 'cottageVillage':
      case 'estate':
        $val = $lot->params[$p];
        break;
      default:
        continue;
    }
    if ($val) $data[$p] = $val;
  }

  $data['type'] = $lot->type;

  return url_for2('offers_list', $data).'#content';
}

function first_paragraph($string)
{
  preg_match_all('#<p.*?>(.*?)</p>#im', $string."<p></p>", $matches);
  foreach($matches[1] as $key=>$p) {
    if(trim(strip_tags($p)) != '') {
      return $matches[0][$key];
    }
  }
}

function print_friendly_include_stylesheets()
{
  echo sfConfig::get('print_version') ? stylesheet_tag('print', array('media' => 'all')) : get_stylesheets();
}

function print_friendly_include_javascripts()
{
  echo sfConfig::get('print_version') ? '' : get_javascripts();
}

function h1($default, $strictly_use_default = false) {
  $h1 = false;
  $request = sfContext::getInstance()->getRequest();
  $type = $request->getParameter('type');

  if('lot' == $request->getParameter('module') && 'list' == $request->getParameter('action')) {    
    
    if($request->hasParameter('preset')){
      $data = SeoText::getH1(sfContext::getInstance()->getRequest()->getPathInfo());
    }else{
      $data = false;
    }   
    if($data){
      $h1 =  $data;
    }
  }
  return $h1 != false ? $h1 : $default;
}

function price($type, $default = 'Цена за м²') {
  switch ($type) {
    case 'comrent':  return 'Цена за м² в год';
    case 'cottage':  return 'Цена за м² в месяц';
    case 'flatrent': return 'Цена за м² в месяц';
    default:         return $default;
  }
}

function price_land($type, $default = 'Стоимость за сотку') {    
  return $default;  
}

function m_a_p($type, $default = 'Месячный арендный поток') {    
  return $default;  
}

function price_all($type, $default = 'Цена') {
  switch ($type) {
    case 'comrent':  return 'Стоимость за год';
    case 'cottage':  return 'Цена за месяц';
    case 'flatrent': return 'Цена за месяц';
    default:         return $default;
  }
}

function route_for_list($params) {
  return isset($params['preset']) ? 'offers_list_preset' : 'offers_list';
}

function prepare_params_for_url($params) {
  if(isset($params['preset'])) {
    switch ($params['type']) {
      case 'comsell':
      case 'comrent':
        if(1 === count($params['objecttype'])) {
          unset($params['objecttype']);
        }
      break;

      case 'cottage':
      case 'outoftown':
        if(1 === count($params['wards'])) {
          unset($params['wards']);
        }
      break;

      default:
        if(1 === count($params['districts'])) {
          unset($params['districts']);
        }
      break;
    }
  }
  return $params;
}

function clean_desc($lot)
{
  return preg_replace(
    array(
      '#<a.*?href="/offers/.*?details/\d+">(.*?)</a>#i',
      '#<span.*?class=blue>(.*?)</span>#i'
    ),
    array(
      '\\1',
      '\\1'
    ),
    $lot->getRaw('description'));
}

