<?php

class LotQuery extends Doctrine_Query
{
  public static $_filter_params = array(
    'parking'            => array(26, 'exact'),
    'balcony'            => array(63, 'exact'),
    'decoration'         => array(8,  'exact'),
    'estate'             => array(2,  'equals_with_parent'),
    'under_construction' => array(78, 'exact'),
    'rooms'              => array(47, 'range'),
    'objecttype'         => array(1,  'exact'),
    'spaceplot'          => array(22, 'range'),
    'space'              => array(33, 'range'),
    'distance_mkad'      => array(31, 'range'),
    'cottageVillage'     => array(44, 'equals_with_parent'),
    'locality'           => array(43, 'equals_with_parent'),
    'market'             => array(79, 'exact'),
  );
  protected
    $_joins = array(),
    $_parent_joined = false;

  public function joinOnce($join)
  {
    if (in_array($join, $this->_joins)) {
      return $this;
    }
    else {
      $this->_joins[] = $join;
      return $this->leftJoin($join);
    }
  }

  public function joinParams()
  {
   return $this->joinOnce($this->getRootAlias() . '.LotParams p');
  }

  public function joinParent()
  {
    if (!$this->_parent_joined) {
      $this->_parent_joined = true;
      $this
        ->leftJoin($this->getRootAlias() . '.Parent pl')
        ->joinOnce('pl.LotParams pp');
    }

    return $this;
  }


  public function addSelectActualType()
  {
    return $this->addSelect(sprintf('
      IF("eliteflat" = %1$s.type AND %1$s.is_penthouse, "penthouse", %1$s.type) actual_type
    ', $this->getRootAlias()));
  }

  public function groupByActualType()
  {
    return $this->groupBy(sprintf('
      IF("eliteflat" = %1$s.type AND %1$s.is_penthouse, "penthouse", %1$s.type)
    ', $this->getRootAlias()));
  }

  public function active($action = null)
  {
    return !Broker::isAuth()
      ? $this->andWhere($this->getRootAlias() . '.status = ?', 'active')        //Not broker
      : $action == 'show_lot' && strpos(sfContext::getInstance()->getRequest()->getReferer(), 'csstat') !== false
        ? $this                                                                 //Broker from csstat
        : $this->andWhere($this->getRootAlias() . '.status != ?', 'inactive');  //Broker
  }

  public function exclude($id)
  {
    return $this->andWhere($this->getRootAlias() . '.id <> ?', $id);
  }

  public function type($type, $action = 'show', $value = null)
  {
    if (is_array($type)) {
      if (in_array('penthouse', $type) && in_array('eliteflat', $type)) {
        return $this->andWhereIn($this->getRootAlias() . '.type', $type);
      }
      elseif (in_array('penthouse', $type)) {
        return $this->andWhere(sprintf(
          '(%s.type IN (%s) OR %1$s.is_penthouse = 1)',
          $this->getRootAlias(),
          implode(',', array_map(function($i) { return "'$i'"; }, $type))
        ));
      }
      else {
        return $this
          ->andWhereIn($this->getRootAlias() . '.type', $type)
          ->andWhere($this->getRootAlias() . '.is_penthouse = ?', false);
      }
    }

    elseif ('penthouse' == $type) {
      return $this
        ->andWhere($this->getRootAlias() . '.type = ?', 'eliteflat')
        ->andWhere($this->getRootAlias() . '.is_penthouse = ?', true);
    }
    elseif (in_array($type, array('comsell', 'comrent')) && $action == 'list' && !is_null($value)) {
      return $this
        ->andWhere($this->getRootAlias() . '.type = ?', $type)
        ->joinOnce($this->getRootAlias() . '.LotParams p')
        ->andWhere('p.param_id = ?', 1)
        ->andWhereIn('p.value', $value);
    }
    else {
      return $this->andWhere($this->getRootAlias() . '.type = ?', $type);
    }
  }

  public function ratedSort($by = 'rating', $dir = null)
  {
    $dir = $dir == 'desc' ? 'desc' : 'asc';
    switch ($by) {
      case 'price':
        return $this->orderBy(sprintf('%s.price_all_from %s', $this->getRootAlias(), $dir));
      break;

      case 'area':
        return $this->orderBy(sprintf('%s.area_from %s', $this->getRootAlias(), $dir));
      break;

      case 'rating':
      default:
        return $this->orderBy(sprintf('
          %1$s.rating desc,
          if(%1$s.new_object > "%2$s", %1$s.new_object, "%2$s") desc,
          if(%1$s.new_price > "%2$s",  %1$s.new_price, "%2$s")  desc,
          new_object desc,
          id desc
        ', $this->getRootAlias(), date('Y-m-d')));
        break;
    }
  }

  public function street($value)
  {
    $query  = array();
    $params = array();
    $signs_del = array(".", ",");
    $abbr = Tools::$_address_replace_table['types'];

    // Two or more streets separated by |
    foreach (explode('|', $value) as $street) {
      //Strange bug @prod
      if(!preg_match('/\.$/', $street)) {
        $street .= '.';
      }
      $and_params = array();
      $or_params  = array();
      $and_query  = array();
      $or_query   = array();
      // Parts of name separated by space
      foreach (explode(' ', $street) as $part) {
        if(in_array($part, $abbr)) {
          // Add abbreviation itself
          $or_params[] = '%' . str_replace($signs_del, '', trim($part)). '%';
          $or_query[] = $this->getRootAlias() . '.address LIKE ?';
          // Add similar abbreviations
          foreach(array_keys($abbr, $part) as $or_part) {
            $or_params[] = '%' . str_replace($signs_del, '', trim($or_part)). '%';
            $or_query[] = $this->getRootAlias() . '.address LIKE ?';
          }
        }
        // "1-ый" == "1-й" == "1й"
        elseif (preg_match('#(\d){1,2}-?[ыао]?([йяе]{1})#u',$part, $matches)) {
          $and_query[] = $this->getRootAlias() . '.address REGEXP ?';
          $and_params[] = sprintf('%s-*[ыао]*%s', $matches[1], $matches[2]);
        }
        else {
          $and_query[] = $this->getRootAlias() . '.address LIKE ?';
          $and_params[] = '%' . str_replace($signs_del, '', trim($part)). '%';
        }
      }
      $query[]  = count($or_query)
        ? sprintf('( %s AND (%s) )', implode(' AND ', $and_query), implode(' OR ', $or_query))
        : sprintf('( %s )', implode(' AND ', $and_query));
      $params = array_merge($params, array_merge($and_params, $or_params));
    }
    $this->andWhere(sprintf('( %s )', implode(' OR ', $query)), $params);
  }

  public function city($value)
  {
    return $this->andWhere(
      $this->getRootAlias() . '.name LIKE ? OR ' . $this->getRootAlias() . '.description LIKE ?',
      array("%$value%", "%$value%")
    );
  }

  public function districts($value)
  {
    if ($value) {
      $this->andWhereIn($this->getRootAlias() . '.district_id', $value);
    }
    return $this;
  }

  public function wards($value)
  {
    if ($value) {
      $this->andWhere(sprintf(
        '(%s.ward IN (%s) OR %s.ward2 IN (%s))',
        $this->getRootAlias(),
        implode(',', $value),
        $this->getRootAlias(),
        implode(',', $value)
      ));
    }
    return $this;
  }

  public function radius($lat, $lng, $radius)
  {
    return $this->andWhere('(
      6371 * ACOS(
        COS(RADIANS(?))
        * COS(RADIANS(lat))
        * COS(RADIANS(lng) - RADIANS(?))
        + SIN(RADIANS(?))
        * SIN(RADIANS(lat))
      )
    ) <= ?', array($lat, $lng, $lat, str_replace(',', '.', $radius)));
  }

  public function filter(array $params, $skip_empty = true)
  {
    
    if (isset($params['price_from']) || isset($params['price_to'])) {
      $this->price($params['price_from'], $params['price_to'], $params['currency'], $params['no_price_ok']);      
    }
    if ((isset($params['price_all_from']) || isset($params['price_all_to'])) && sfContext::getInstance()->getRequest()->getParameter('type') == 'comsell') {
      $this->price($params['price_all_from'], $params['price_all_to'], $params['currencyAll'], $params['no_price_ok'], true);      
    }
    if (isset($params['area_from']) || isset($params['area_to'])) {
      $this->area($params['area_from'], $params['area_to']);
    }
    if (!empty($params['lat']) && !empty($params['lng']) && !empty($params['radius'])) {
      $this->radius($params['lat'], $params['lng'], $params['radius']);
    }
 
    foreach (self::$_filter_params as $param => $options) {
      if ('range' == $options[1]) {
        if (isset($params[$param . '_from']) || isset($params[$param . '_to'])) {
          $this->hasParam($param, array($params[$param . '_from'], $params[$param . '_to']));
        }
        unset($params[$param . '_from'], $params[$param . '_to']);
      }
    }
    unset(
      $params['price_all_from'], $params['price_all_to'],
      $params['price_from'], $params['price_to'], $params['currency'], $params['currencyAll'], $params['no_price_ok'],
      $params['area_from'], $params['area_to'],
      $params['lat'], $params['lng'], $params['radius']
    );    
    foreach ($params as $key => $value) {
      if ($skip_empty && !$value) {
        continue;
      }
      $method = sfInflector::camelize($key);
      if (is_callable(array($this, $method))) {
        $this->$method($value);
        
      }
      elseif (isset(self::$_filter_params[$key])) {
        $this->hasParam($key, $value);       
      }
      else {        
        $this->andWhere($this->getRootAlias() . '.' . $key . ' = ?', $value);
      }
    }

    return $this;
  }

  public function onlyNew()
  {
    return $this->andWhere($this->getRootAlias() . '.new_object > ?', date('Y-m-d'));
  }

  public function onlyNewPrice()
  {
    return $this->andWhere($this->getRootAlias() . '.new_price > ?', date('Y-m-d'));
  }

  public function price($from, $to, $currency = 'RUR', $no_price_ok = false, $all = false)
  {
    $rates = Currency::getRates();
    $alias = $this->getRootAlias();
    $request = sfContext::getInstance()->getRequest();
    $fields = array();
    // currency convert
    $currency = $currency ?: 'RUR';
    list($need_fields, $suffix) = strpos($request->getParameter('type'), 'com') === 0 && $request->getParameter('action') == 'list'
      ? array(array('price_from', 'price_to'), '')
      : array(array('price_all_from', 'price_all_to'), '_all');
    if($all){
      $need_fields = array('price_all_from', 'price_all_to');      
    }    
    foreach ($need_fields as $field) {
      $select = sprintf('case %s.currency ', $alias);
      foreach (array('RUR', 'USD', 'EUR') as $c) {
        if ($c != $currency) {
          $select .= sprintf(
            'WHEN "%s" THEN %s.%s * %.8F ',
            $c, $alias, $field, $rates[$c][$currency]
          );
        }
      }      
      $select .= sprintf('ELSE %s.%s END', $alias, $field);
      $fields[] = $select;
    }
    // or without price
    if ($no_price_ok) {
      if (!$all) {
        $no_price_condition = sprintf(
                ' OR (%1$s.price%2$s_from = 0 AND %1$s.price%2$s_to = 0) OR %1$s.hide_price', $alias, $suffix
        );
      }else{
        $no_price_condition = sprintf(
                ' OR (%1$s.price%2$s_all_from = 0 AND %1$s.price%2$s_all_to = 0) OR %1$s.hide_price', $alias, $suffix
        );
      }
    }
    else {
      if (!$all) {
        $no_price_condition = sprintf(
                ' AND %1$s.price%2$s_from > 0 AND NOT %1$s.hide_price', $alias, $suffix
        );
      }else{
        $no_price_condition = sprintf(
                ' AND %1$s.price%2$s_all_from > 0 AND NOT %1$s.hide_price', $alias, $suffix
        );
      }
    }

    return $this->rangeFilter($fields, $from, $to, $no_price_condition);
  }

  public function area($from, $to)
  {
    $fields = array($this->getRootAlias() . '.area_from', $this->getRootAlias() . '.area_to');
    return $this->rangeFilter($fields, $from, $to);
  }

  public function address($value)
  {
    $value = preg_replace('/[^\w\d\s-\\\(\)]/iu', ' ', $value);
    $value = preg_replace('/\d+/', '"$0"', $value);
    $value = preg_replace('/\s+/', '%', $value);
    return $this->andWhere(
      sprintf('%s.address LIKE ? OR %1$s.description LIKE ?', $this->getRootAlias()),
      array("%$value%", "%$value%")
    );
  }

  public function name($value)
  {
    $value = preg_replace('/[^\w\d\s-\\\(\)]/iu', ' ', $value);
    $value = preg_replace('/\s+/', '%', $value);
    return $this->andWhere(sprintf('%s.name LIKE ?', $this->getRootAlias()), '%' . $value . '%');
  }



  protected function hasParam($key, $value)
  {
    $this->joinParams()->groupBy($this->getRootAlias() . '.id');
    list($id, $match_type) = self::$_filter_params[$key];

    switch ($match_type) {
      case 'exact':
        if (is_array($value)) {
          $this->addHaving(
            'SUM(p.param_id = ? AND p.value IN (' . implode(',', array_fill(0, count($value), '?')) . '))',
            array_merge(array($id), $value)
          );
        }
        else {
          $this->joinOnce($this->getRootAlias() . '.LotParams lp2 ON lp2.lot_id = '.$this->getRootAlias() .'.pid');
          $this->addHaving('IF(SUM(p.param_id = ? AND p.value = ?), SUM(p.param_id = ? AND p.value = ?), SUM(lp2.param_id = ? AND lp2.value = ?))', array($id, $value, $id, $value, $id, $value));
        }
        break;

      case 'range':
        list($from, $to) = $value;
        if ($from && $to) {
          $this->joinOnce($this->getRootAlias() . '.LotParams lp2 ON lp2.lot_id = '.$this->getRootAlias() .'.pid');          
          $this->addHaving('IF(SUM(p.param_id = ? AND p.value + 0 BETWEEN  ? AND ?), SUM(p.param_id = ? AND p.value + 0 BETWEEN  ? AND ?), SUM(lp2.param_id = ? AND lp2.value + 0 BETWEEN ? AND ?))', array($id, $from, $to, $id, $from, $to, $id, $from, $to));
        }
        elseif ($from) {
          $this->joinOnce($this->getRootAlias() . '.LotParams lp2 ON lp2.lot_id = l.pid');         
           $this->addHaving('IF(SUM(p.param_id = ? AND p.value + 0 >= ?), SUM(p.param_id = ? AND p.value + 0 >= ?), SUM(lp2.param_id = ? AND lp2.value + 0 >= ?))', array($id, $from, $id, $from, $id, $from));
        }
        else {
          $this->joinOnce($this->getRootAlias() . '.LotParams lp2 ON lp2.lot_id = l.pid');
          $this->addHaving('IF(SUM(p.param_id = ? AND p.value + 0 <= ?), SUM(p.param_id = ? AND p.value + 0 <= ?), SUM(lp2.param_id = ? AND lp2.value + 0 <= ?))', array($id, $to, $id, $to, $id, $to));
        }
        break;

      case 'like':
        $this->addHaving('SUM(p.param_id = ? AND p.value LIKE ?)', array($id, '%' . $value . '%'));
        break;

      case 'like_with_parent':
        $this->joinParent()->addHaving(
          'SUM(p.param_id = ? AND p.value LIKE ?) OR SUM(pp.param_id = ? AND pp.value LIKE ?)',
          array($id, '%' . $value . '%', $id, '%' . $value . '%')
        );
        break;

      case 'equals_with_parent':
        $values = explode(',', $value);
        $having = "SUM(p.param_id = ? AND p.value = ? AND ".$this->getRootAlias() .".pid IS NULL) OR SUM(pp.param_id = ? AND pp.value = ?)";
        $havings = array();
        $variables = array();
        foreach($values as $value){
          $havings[] = $having;
          $variables = array_merge($variables, array($id, $value, $id, $value));
        }
        $this->joinParent()->addHaving(implode(" OR ", $havings), $variables);
        break;
    }

    return $this;
  }

  protected function rangeFilter(array $fields, $from, $to, $append = null)
  {
    if ($from && $to) {
      $to = $from == $to ? $to+1 : $to;
      $condition = sprintf('not if(%1$s < ?, %2$s < ?, ? < %1$s)', $fields[0], $fields[1]);
      $params = array($from, $from, $to);
    }
    elseif ($from) {
      $condition = sprintf('%s >= ?', $fields[0]);
      $params = array($from);
    }
    else {
      $condition = sprintf('if(%1$s, %1$s <= ?, %2$s <= ?)', $fields[1], $fields[0]);
      $params = array($to, $to);
    }

    return $this->andWhere($condition . $append, $params);
  }
}