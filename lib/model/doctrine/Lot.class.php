<?php

/**
 * Lot
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    kre
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Lot extends BaseLot
{
  public static
    $_types = array(
      'eliteflat' => 'Элитные квартиры',
      'elitenew'  => 'Элитные новостройки',
      'penthouse' => 'Пентхаусы',
      'flatrent'  => 'Квартиры в аренду',
      'outoftown' => 'Загородные дома, поселки',
      'cottage'   => 'Коттеджи в аренду',
      'comsell'   => 'Коммерческая недвижимость',
      'comrent'   => 'Коммерческая недвижимость в аренду',
    ),
    $_types_genetive = array(
      'eliteflat' => 'Продажа элитных квартир',
      'elitenew'  => 'Продажа элитных новостроек',
      'penthouse' => 'Продажа пентхаусов',
      'flatrent'  => 'Аренда квартир',
      'outoftown' => 'Продажа загородных домов, участков',
      'cottage'   => 'Аренда коттеджей',
      'comsell'   => 'Продажа коммерческой недвижимости',
      'comrent'   => 'Аренда коммерческой недвижимости',
    ),
    $_suptypes = array(
      'city' => array(
        'name' => 'Городская недвижимость',
        'types' => array(
          'eliteflat',
          'elitenew',
          'penthouse',
        ),
      ),
      'country' => array(
        'name' => 'Загородная недвижимость',
        'types' => array(
          'outoftown',
          'cottage',
        ),
      ),
      'commercial' => array(
        'name' => 'Коммерческая недвижимость + Аренда квартир',
        'types' => array(
          'comrent',
          'comsell',
          'flatrent',
        ),
      ),
  ),
  $_subobjectTypeShift = array(
    'elitenew'  => '',
    'eliteflat' => 'elitenew',
    'flatrent'  => 'elitenew',
    'cottage'   => array('cottage', 'outoftown'),
    'outoftown' => array('cottage', 'outoftown'),
    'comsell'   => array('comsell', 'comrent'),
    'comrent'   => array('comsell', 'comrent'),
  ),
  $_statuses_rent = array(
    'flatrent',
    'cottage',
    'comrent',
  ),
  $_status = array(
    'active'   => 'Активно',
    'hidden'   => 'Скрыто',
    'inactive' => 'Деактивировано',
  );

  public
    $_should_be_cloned = false;

  protected
    $_reset_ratings,
    $_pass_status_check = false,
    $_file,
    $_storage,
    $_thumb_options = array(
      'list'  => array('width' => 200, 'height' => 150, 'quality' => 80, 'inflate' => false, 'crop' => true),
      'item'  => array('width' => 300, 'height' => 228, 'quality' => 86, 'inflate' => false, 'crop' => true),
      'pres'  => array('width' => 620, 'height' => 470, 'quality' => 86, 'inflate' => true, 'crop' => true, 'watermark' => true),
      'pres_'  => array('width' => 620, 'height' => 470, 'quality' => 86, 'inflate' => true, 'crop' => true),
  );

  private $_params = null;
  private static $_broker_phone = array();

  private $_old_params = null ;

  public static function getTypesReal($genetive = false)
  {
    $values = $genetive ? self::$_types_genetive : self::$_types;
    unset($values['penthouse']);
    return $values;
  }

  public static function sortByTypes($a, $b) {
    $list = array_keys(self::$_types);
    return array_search($a['type'], $list) < array_search($b['type'], $list) ? -1 : 1;
  }

  public static function isParamsExist($params)
  {
    $isexist = false;
    $notexist = false;
      foreach ((array) $params as $key => $value) {
      switch ($key) {
        case 'pid':
        case 'id':
        case 'currency':
        case 'area_from':
        case 'area_to':
        case 'price_all_from':
        case 'price_all_to':
        case 'price_from':
        case 'price_to':
        case 'district':
        case 'districts':
        case 'districts[]':
        case 'ward':
        case 'wards':
        case 'wards[]':
        case 'cottageVillage':
        case 'estate':
        case 'lat':
        case 'lng':
        case 'objecttype':
        case 'range':
        case 'street':
        case 'no_price_ok':
        case 'rooms_from':
        case 'rooms_to':
        case 'autocomplete_street':
        case 'autocomplete_estate':
        case 'decoration':
        case 'decoration[]':
        case 'balcony':
        case 'parking':
        case 'balcony[]':
        case 'parking[]':
        case 'under_construction':
        case 'only_new':
        case 'only_new_price':
        case 'spaceplot_to':
        case 'spaceplot_from':
        case 'locality':
        case 'autocomplete_locality':
        case 'autocomplete_cottageVillage':
        case 'distance_mkad_to':
        case 'distance_mkad_from':
        case 'page':
        case 'preset':
        case 'type':
        case 'action':
        case 'module':
        case 'per_page':
        case 'shortcut':
          $isexist = true;
         break;
        default:
          $notexist[$key][] = array('bad' => $key);
          continue;
      }

      if ($notexist) return $key;
    }
    return -1;
  }

  public function getCombinedName()
  {
    if ($this->is_commercial_type) {
      $name = '';
      if (isset($this->params['objecttype'])) {
        $name .= $this->params['objecttype'].', ';
      }
      $name .= $this->getName();
      if (isset($this->params['estate'])) {
        $name .= ', '. $this->params['estate'];
      }
      return $name;
    }
    else {
      return $this->getName();
    }
  }

  public function getNameWithId()
  {
    return sprintf('%d: %s', $this->id, mb_substr($this->name, 0, 50));
  }

  public function getTypeReal()
  {
    return $this->_get('type');
  }

  public function setTypeReal($value)
  {
    return $this->_set('type', $value);
  }

  public function getType()
  {
    $type = $this->_get('type');
    return 'eliteflat' == $type && $this->is_penthouse ? 'penthouse' : $type;
  }

  public function getIsCommercialType()
  {
    return in_array($this->_get('type'), array('comsell','comrent'));
  }

  public function getIsCountryType()
  {
    return in_array($this->_get('type'), array('cottage', 'outoftown'));
  }

  public function getIsCityType()
  {
    return in_array($this->_get('type'), array('eliteflat','elitenew','penthouse','flatrent'));
  }

  public function getIsLandType()
  {
    return $this->is_country_type && isset($this->params['objecttype']) && $this->params['objecttype'] == 'Участок';
  }

  public function getIsNewbuildType()
  {
    return $this->is_city_type && isset($this->params['market']) && $this->params['market'] == 'Первичный';
  }

  public function getIsRentType()
  {
    return in_array($this->_get('type'), self::$_statuses_rent);
  }

  public function getSuptype()
  {
    foreach(self::$_suptypes as $name=>$suptype) {
      if(in_array($this->_get('type'), $suptype['types'])) {
        return $name;
      }
    }
    return null;
  }

  public function toArray($deep = true, $prefixKey = false)
  {
    return array_merge(parent::toArray($deep, $prefixKey), array(
      'type_real' => $this->type_real,
    ));
  }


  public function getFile()
  {
    return $this->_file;
  }

  public function setFile($value)
  {
    if (!$value) return;

    if ($value instanceOf sfValidatedFile) {
      $tmpfile = sprintf('%s/%s', sfConfig::get('sf_tmp_dir'), $value->generateFilename());
      $this->_file = $value->save($tmpfile);
    }
    else {
      throw new Exception('Only instance of sfValidatedFile can be set as file');
    }
  }

  public function setFileDelete($value)
  {
    if ($value) $this->deleteImage();
  }

  public function getIsImageStored()
  {
    return $this->getStorage()->isStored($this->id);
  }

  public function getImage($thumb)
  {
    if (!isset($this->_thumb_options[$thumb])) {
      throw new Exception(sprintf('Options for thumb "%s" is not specified', $thumb));
    }

    $path = $this->getStorage()->getThumb($this->id, $thumb, $this->_thumb_options[$thumb]);
    return str_replace(sfConfig::get('sf_web_dir'), '', $path);
  }

  public function getImageSource($full_path = false)
  {
    $path = $this->getStorage()->getFilename($this->id);
    return $full_path ? $path : str_replace(sfConfig::get('sf_web_dir'), '', $path);
  }

  public function getImageAlt()
  {
    return $this->name;
  }

  public function getWebFileName()
  {
    return str_replace(sfConfig::get('sf_web_dir'), '', $this->getStorage()->getFileName($this->id));
  }

  public function getTypeText()
  {
    return self::$_types[$this->type];
  }


  public function getIsNewObject()
  {
    return $this->new_object && time() < strtotime($this->new_object);
  }

  public function getIsNewPrice()
  {
    return $this->new_price && time() < strtotime($this->new_price);
  }

  public function getHasPrice()
  {
    return (!$this->hide_price || Broker::isAuth()) && $this->price_from;
  }

  public function getHasPriceLand()
  {
    return (!$this->hide_price || Broker::isAuth()) && $this->price_from;
  }

  public function getHasPriceAll()
  {
    return (!$this->hide_price || Broker::isAuth()) && $this->price_all_from;
  }

  public function getIsPriceOnRequest()
  {
    return $this->hide_price || !$this->price_all_from;
  }

  public function preSave($event)
  {
    $modified = $this->getModified();

    if ($this->isNew() && !$this->new_object) {
      $this->new_object = date('Y-m-d', strtotime('+1 month'));
    }

    if (!($this->isNew() && $this->new_price)) {
      foreach (array('price_from', 'price_to', 'price_all_from', 'price_all_to') as $field) {
        if (isset($modified[$field])) {
          $this->new_price = date('Y-m-d', strtotime('+1 month'));
          break;
        }
      }
    }

    $this->shortcut = $this->shortcut ? Tools::slugify($this->shortcut) : null;

    if (isset($modified['rating']) && $modified['rating'] > 0) {
      $this->_reset_ratings = true;
    }
  }

  public function postSave($event)
  {
    $modified = $this->getModified(true, true);

    if ($this->_file) {
      $this->cleanupThumbs();
      $this->getStorage()->store($this->_file, $this->id, true);
      $this->_file = null;
    }

    if ($this->_reset_ratings) {
      $this->_reset_ratings = false;
      if ($ids = $this->getTable()->getTop40Ids($this->type)) {
        $stmt = $this->getTable()->getConnection()->prepare('
          update lot set rating = 0 where id not in ('. implode(',', $ids) . ') and type = ?
        ');
        $stmt->execute(array($this->type));
        $stmt->closeCursor();
      }
    }

    if ($this->address) {
      $old = !empty($modified['type']) ? $modified['type'] : null;
      $new = $this->type;

      foreach(array('street', 'cities') as $obj) {
        if (!empty($this->address['street'])) {
          $street = Tools::prepareStreet($this->address['street']);
          UniqueList::getInstance('streets_' . $new)->add($street);
          if($old) {
            UniqueList::getInstance('streets_' . $old)->remove($street);
          }
        }
        if (!empty($this->address['city'])) {
          $city = $this->address['city'];
          UniqueList::getInstance('cities_' . $new)->add($city);
          if($old) {
            UniqueList::getInstance('streets_' . $old)->remove($street);
          }
        }
      }
    }

    $obj = serialize($this);
    $offer = Doctrine::getTable('MainOffer')->findOneBy('lot_id', $this->id);
    if ($offer) {
      $offer->lot_object = unserialize($obj);
      $offer->save();
    }

    if (array_key_exists('pid', $modified) && $this->pid !== null) {
      $level = array_combine(array_keys(self::$_status), range(1, count(self::$_status)));
      $old = $modified['pid'];
      $new = $this->pid;
      if($level[$this->getTable()->find($new)->status] > $level[$this->status]) {
        $this->status = $this->getTable()->find($modified['pid'])->status;
        $this->save();
      }
    }

    if($this->_should_be_cloned) {
      $clone = $this->copy();
      $clone->shortcut = null;
      $clone->new_object = date('Y-m-d', strtotime('+1 month'));
      $clone->new_price = date('Y-m-d', strtotime('+1 month'));
      $clone->save(); //Yes, I know, that's the worst way, but...

      //Main photo
      $this->getStorage()->store($this->getStorage()->getFilename($this->id), $clone->id, false);

      //Photoset
      foreach($this->Photos as $photo) {
        $new_photo = new Photo();
        $new_photo->lot_id        = $clone->id;
        $new_photo->name          = $photo->name;
        $new_photo->photo_type_id = $photo->photo_type_id;
        $new_photo->position      = $photo->position;
        $new_photo->setFile($photo->getImageSource(true), true);
        $new_photo->save();
      }

      //Params
      foreach($this->LotParams as $param){
        $new_param = new LotParam();
        $new_param->lot_id        = $clone->id;
        $new_param->param_id      = $param->param_id;
        $new_param->param_type_id = $param->param_type_id;
        $new_param->value         = $param->value;
        $new_param->position      = $param->position;
        $new_param->save();

      }

      sfContext::getInstance()->getResponse()->setCookie('please_redirect_me_to', $clone->id);
    }
  }

  public function postUpdate($event)
  {
    $this->writeLotLog();

    parent::postUpdate($event);
  }

  public function preDelete($event)
  {
    $this->deleteImage();
    Doctrine::getTable('MainOffer')->clearLot($this->id);
  }

  public function setParams(array $values)
  {
    $q = Doctrine_Query::create()
      ->select('lp.value')
      ->from('LotParam lp')
      ->where('lp.lot_id = ? AND lp.param_id = ?', array($this->id, 2));
    $params_db = $q->fetchArray();
    $current_params = isset($params_db[0]) ? $params_db[0]['value'] : null;

    if ($this->pid) {
      $values['estate'] = $current_params;
      $parent_params = $this->Parent->params;
      foreach ($values as $key => $value) {
        if (isset($parent_params[$key]) && $parent_params[$key] == $value) {
          unset($values[$key]);
        }
      }
    }
    elseif( (empty($_POST['lot']['pid'])) && (!empty($_POST['lot']['pid_hid'])) ){
      $values['estate'] = $current_params;
    }

    $fields = call_user_func_array('array_merge', Param::$_map[$this->type]);
    $fields = array_combine(
      array_map(function($field) { return $field['property_id']; }, $fields),
      $fields
    );

    foreach($this->LotParams as $i => $link) {
      $field_name = isset($fields[$link->param_id]) ? $fields[$link->param_id]['field'] : null;
      if ($field_name && !empty($values[$field_name])) {
        $link->value = is_array($values[$field_name]) ? array_map('trim', $values[$field_name]) : trim($values[$field_name]);
        unset($values[$field_name]);
      }
      else {
        $this->LotParams->remove($i);
        $link->delete();
      }
    }

    foreach (Param::$_map[$this->type] as $type => $_fields) {
      foreach ($_fields as $field) {
        if (!empty($values[$field['field']])) {
          $clean_value = is_array($values[$field['field']]) ? array_map('trim', $values[$field['field']]) : trim($values[$field['field']]);
          $lot_param = new LotParam();
          $lot_param->fromArray(array(
            'lot_id' => $this->id,
            'param_id' => $field['property_id'],
            'param_type_id' => $type,
            'value' => $clean_value,
          ));
          $this->LotParams[] = $lot_param;
        }
      }
    }
  }

  public function getParams()
  {
    if (is_null($this->_params) && $this->id) {
      //$this->LotParams = Doctrine::getTable('LotParam')->findBy('lot_id', $this->id);#19653

      $not_inherited = array();
      $params = array();
      foreach (Param::$_map[$this->type] as $type => $fields) {
        foreach ($fields as $field) {
          foreach ($this->LotParams as $link) {
            if ($field['property_id'] == $link->param_id) {
//              if($link->value == '--') {
//                $not_inherited[] = $field['field'];
//                continue;
//              }
              switch($field['field']) {
                case 'about_decoration':
                case 'decoration':
                case 'infra_parking':
                case 'territory':
                  $replace = array(
                    'с отделкой' => 'С отделкой',
                    'без отделки' => 'Без отделки',
                    'наземный паркинг' => 'Наземный паркинг',
                    'подземный паркинг' => 'Подземный паркинг',
                    'нет'  => 'Нет',
                    'огорожена'  => 'Огорожена',
                  );
                  $params[$field['field']] = str_replace(array_keys($replace),array_values($replace),$link->value);
                break;
                default:
                  $params[$field['field']] = $link->value;
                break;
              }
              if(('infra_parking' == $field['field'] || 'territory' == $field['field'])
                && !isset(Param::$_widget_properties[$this->type][$field['field']])
                && is_array($params[$field['field']]))
              {
                $params[$field['field']] = implode(', ', $params[$field['field']]);
              }
              break;
            }
          }
        }
      }

      if ($this->pid && $this->Parent->is_visible) {
        $params['estate'] = !empty($params['estate']) ? $params['estate'] : '';
        $params['estate'] = !empty($this->Parent->params['estate']) ? $this->Parent->params['estate'] : $params['estate'];
        if($params['estate'] == '') {
          unset($params['estate']);
        }
        $parent_params =  $this->Parent->params;
        if(!empty($not_inherited)) {
          foreach($not_inherited as $key) {
            unset($parent_params[$key]);
          }
        }
        if (isset($parent_params['about_balcony'])) {#9644 -> #11491 Ho-ho-ho! )
          //$params['about_balcony'] = $parent_params['about_balcony'];
        }

        $this->_params = array_merge($parent_params, $params);
      }
      else {
        $this->_params = $params;
      }
    }

    return $this->_params;
  }

  public function getParamVisibilitySettings()
  {
    $settings = array();
    $map = Param::$_map[$this->type];

    foreach($map as $group) {
      foreach($group as $field) {
        $settings[$field['name']] = !empty($field['use_for']) ? $field['use_for'] : null;
      }
    }
    return $settings;
  }

  public function getParamsGrouppedFiltered($not_presentation = true)
  {
    $groups = array();
    $params = $this->params;
    $map = Param::$_map[$this->type];
//    $except = $this->has_children || $forced_parent ? 'object' : 'supobject';

    unset($map['hidden']);
    if ($this->has_children) {
      unset($map['flat']);
    }
    elseif (!$this->is_country_type  && $not_presentation) {
      unset($map['base']);
    }

    foreach ($map as $type => $fields) {
      $group_name = Param::$_types[$type];
      $groups['object'][$group_name] = array();
      $groups['supobject'][$group_name] = array();

      foreach ($fields as $field) {
        $field_id = $field['field'];
        if(!empty($field['authorized_only']) && $field['authorized_only'] == 1 && !Broker::isAuth()) {
          continue;
        }
        if (!empty($params[$field_id])) {
          $value = $params[$field_id];

          if(is_array($value) ? in_array('--', $value) : $value == '--') {
            continue;
          }
          $name = preg_replace('#\s+\(.*?\)\s?#', '', $field['name']);

          if($this->is_country_type && $field['field'] == 'distance_mkad') {
            continue;
          }
          if($this->is_country_type && $field['field'] == 'spaceplot') {
            continue;
          }
          if($this->is_country_type && $field['field'] == 'price_land_from') {
            continue;
          }
          if($this->is_country_type && $field['field'] == 'price_land_to') {
            continue;
          }
          if($this->is_commercial_type && $field['field'] == 'objecttype') {
            continue;
          }
          if($this->is_commercial_type && $field['field'] == 'm_a_p_Currency') {
            continue;
          }
          if($this->is_commercial_type && $field['field'] == 'payback') {
            continue;
          }
          if($this->is_commercial_type && $field['field'] == 'yield') {
            continue;
          }
          if($this->is_commercial_type && $field['field'] == 'm_a_p') {
            continue;
          }

          switch($field['field']) {
            case 'space':
//Only for #9556
//            case 'spaceuseful':
            case 'flatspaces':    $value .= ' м²';   break;
//            case 'roomheight':    $value .= ' м.';   break;
            case 'distance_mkad': $value .= ' км.';  break;
            case 'spaceplot':     $value .= ' сот.'; break;
          }
          if (isset($field['use_for'])) {
            $groups[$field['use_for']][$group_name][$name] = $value;
          }
          else {
            $groups['object'][$group_name][$name] = $value;
          }
        }
      }

      if (empty($groups['object'][$group_name])) {
        unset($groups['object'][$group_name]);
      }
      if (empty($groups['supobject'][$group_name])) {
        unset($groups['supobject'][$group_name]);
      }
    }
    $groups['both'] = array_merge_recursive($groups['object'], $groups['supobject']);
    return $groups;
  }

  public function getDistrict()
  {
    $values = sfConfig::get('app_districts', array());
    return isset($values[$this->district_id]) ? $values[$this->district_id] : null;
  }

  public function getMetro()
  {
    $values = sfConfig::get('app_subways', array());
    return isset($values[$this->metro_id]) ? $values[$this->metro_id] : null;
  }

  public function getArrayWards()
  {
    if (empty($this->ward) && empty($this->ward2)) return null;

    $values = sfConfig::get('app_wards', array());
    $result = array();
    isset($values[$this->ward])  ? $result[] = $values[$this->ward]  : null;
    isset($values[$this->ward2]) ? $result[] = $values[$this->ward2] : null;

    return !empty($result) ? $result : null;
  }

  public function getPrettyWards()
  {
    $result = $this->getArrayWards();
    return $result ? implode(', ', $result) : null;
  }

  public function getPrettyAddress($highest_member = 'street', $or_string = true)
  {
    $result = array();
    $parts  = array('region', 'district', 'city', 'street', 'house', 'building', 'construction');

    if (!in_array($highest_member, $parts)) return null;
    $index = array_search($highest_member, $parts);

    foreach ($parts as $i => $p) {
      if ($i >= $index && !empty($this->address[$p])) {
        switch($p) {
          case 'house':         $result[] = sprintf('д. %s', $this->address[$p]);     break;
          case 'building':      $result[] = sprintf('корп. %s', $this->address[$p]);  break;
          case 'construction':  $result[] = sprintf('стр. %s', $this->address[$p]);   break;
          default:              $result[] = $this->address[$p];
        }
      }
    }

    if (!empty($result))                                return implode(', ', array_unique($result, SORT_STRING));
    if ($or_string && !empty($this->address['string'])) return $this->address['string'];

    return null;
  }

  public function getPhotosGroupped($include_restrcted = true)
  {
    $groups = array();

    foreach ($this->Photos as $photo) {
      if ($photo->is_restricted && !$include_restrcted) {
        continue;
      }

      if (!isset($groups[$photo->photo_type_text])) {
        $groups[$photo->photo_type_text] = array();
      }

      $groups[$photo->photo_type_text][] = $photo;
    }

    return $groups;
  }

  public function getActiveLots()
  {
    return array_filter($this->Lots->getData(), function($lot) { return $lot->status == 'active'; });
  }

  public function getParentName()
  {
    if (!empty($this->params['estate'])) {
      return $this->params['estate'];
    }
    elseif (!empty($this->params['cottageVillage'])) {
      return $this->params['cottageVillage'];
    }
  }

  public function getStatusName()
  {
    return self::$_status[$this->getStatus()];
  }

  public function getIsChild()
  {
    return (bool)($this->pid && !$this->has_children);
  }

  public function getIsOrphan()
  {
    return (bool)(!$this->pid && !$this->has_children);
  }

  public function getIsParent()
  {
    $children = $this->getTable()->getAllChildren($this->id);
    return (bool)(!$this->pid && $this->has_children && count($children));
  }

  public function getIsSterile()
  {
    $children = $this->getTable()->getAllChildren($this->id);
    return (bool)(!$this->pid && $this->has_children && count($children) === 0);
  }

  public function getOfficePhone()
  {
    $result = sfConfig::get('app_phones_office_' . $this->type);
    if(in_array($this->show_phone, array('both', 'office'))){
      return $result;
    }
    elseif($this->show_phone == 'broker') {
      if(!$this->broker_id) {
        return $result;
      }
      elseif (!$this->hasReference('Broker')) {
        $this->Broker = Doctrine::getTable('Broker')->find($this->broker_id);
      }
      if($this->Broker && $this->Broker->hidden == true) {
        return $result;
      }
    }
    return false;
  }

  public function getBrokerPhone()
  {

    if(in_array($this->show_phone, array('both', 'broker')) && $this->broker_id){
      if(!isset(self::$_broker_phone[$this->broker_id])) {
        if (!$this->hasReference('Broker')) {
          $this->Broker = Doctrine::getTable('Broker')->find($this->broker_id);
        }
        if($this->Broker->hidden == true && !Broker::isAuth()){
          return false;
        }
        self::$_broker_phone[$this->broker_id] = $this->Broker->phone;
      }
      return self::$_broker_phone[$this->broker_id];
    }
    return false;
  }

  public function getRoute()
  {
    return !empty($this->shortcut) ? 'offer_shortcut' : 'offer';
  }

  public function getIsVisible()
  {
    return Broker::isAuth()
      ? $this->status != 'inactive'
      : $this->status == 'active';
  }


  private function writeLotLog()
  {
    $sf_context = sfContext::getInstance();
    $files = $sf_context->getRequest()->getFiles('lot');
    $params = $sf_context->getRequest()->getParameter('lot');

    $modifications = array();
    $modified = $this->getModified(true, true);
    unset($modified['created_at'], $modified['updated_at']);

    foreach ($modified as $k => $v) {
      switch ($k) {
        case 'metro_id':
          $values = sfConfig::get('app_subways', array());
          $old = isset($values[$v]) ? $values[$v] : null;
          $new = isset($values[$this->_get($k)]) ? $values[$this->_get($k)] : null;
          break;
        case 'district_id':
          $values = sfConfig::get('app_districts', array());
          $old = isset($values[$v]) ? $values[$v] : null;
          $new = isset($values[$this->_get($k)]) ? $values[$this->_get($k)] : null;
          break;
        case 'ward':
        case 'ward2':
          $values = sfConfig::get('app_wards', array());
          $old = isset($values[$v]) ? $values[$v] : null;
          $new = isset($values[$this->_get($k)]) ? $values[$this->_get($k)] : null;
          break;
        case 'broker_id':
          $old = Doctrine::getTable('Broker')->createQuery()->select('name')->where('id = ?')->limit(1)->execute(array($v), Doctrine::HYDRATE_SINGLE_SCALAR);
          $new = Doctrine::getTable('Broker')->createQuery()->select('name')->where('id = ?')->limit(1)->execute(array($this->_get($k)), Doctrine::HYDRATE_SINGLE_SCALAR);
          break;
        case 'status':
          $values = array('active' => 'Активно', 'hidden' => 'Скрыто', 'inactive' => 'Деактивировано');
          $old = $values[$v];
          $new = $values[$this->_get($k)];
          break;
        case 'show_phone':
          $values = array('both' => 'офисный + мобильный', 'broker' => 'мобильный', 'office' => 'офисный');
          $old = $values[$v];
          $new = $values[$this->_get($k)];
          break;
        case 'type':
          $old = self::$_types[$v];
          $new = self::$_types[$this->_get($k)];
          break;
        default:
          $old = is_array($v) ? implode(', ', $v) : $v;
          $new = is_array($this->_get($k)) ? implode(', ', $this->_get($k)) : $this->_get($k);
      }

      if ($old != $new) {
        $modifications[$k] = array($old, $new);
      }
    }

    foreach ($this->LotParams as $r) {
      if ($r->isModified()) {
        $old = is_array($r->_oldValues['value']) ? implode(', ', $r->_oldValues['value']) : $r->_oldValues['value'];
        $new = is_array($r->value) ? implode(', ', $r->value) : $r->value;
        if ($old != $new) {
          $modifications[$r->param_id] = array($old, $new);
        }
      }
    }

    if ($files['file']['error'] === 0) {
      $modifications['images']['title']['new'] = 1;
    }
    elseif (isset($params['file_delete'])) {
      $modifications['images']['title']['delete'] = 1;
    }
    if (isset($params['Photos'])) {
      foreach ($params['Photos'] as $f) {
        if (!empty($f['file_delete'])) {
          if (!isset($modifications['images']['photos']['delete'])) $modifications['images']['photos']['delete'] = 0;
          $modifications['images']['photos']['delete'] += 1;
        }
      }
    }
    if (isset($files['Photos'])) {
      foreach ($files['Photos'] as $f) {
        if ($f['file']['error'] === 0) {
          if (!isset($modifications['images']['photos']['update'])) $modifications['images']['photos']['update'] = 0;
          $modifications['images']['photos']['update'] += 1;
        }
      }
    }
    if (isset($files['PhotosNew'])) {
      foreach ($files['PhotosNew']['file'] as $f) {
        if ($f['error'] === 0) {
          if (!isset($modifications['images']['photos']['new'])) $modifications['images']['photos']['new'] = 0;
          $modifications['images']['photos']['new'] += 1;
        }
      }
    }

    if (!empty($modifications)) {
      $lot_log = new LotLog();
      $lot_log->lot_id = $this->id;
      $lot_log->created_by = $sf_context->getUser()->getAttribute('username');
      $lot_log->modifications = $modifications;
      $lot_log->save();
      $lot_log->free();
    }
  }

  private function deleteImage()
  {
    $this->cleanupThumbs();
    $this->getStorage()->delete($this->id);
  }

  private function cleanupThumbs()
  {
    foreach (array_keys($this->_thumb_options) as $thumb) {
      $this->getStorage()->delete($this->id, $thumb);
    }
  }

  private function getStorage()
  {
    if (!$this->_storage) {
      $storage_options = array(
        'root'        => sfConfig::get('sf_upload_dir').'/lot',
        'target_mime' => 'image/jpeg',
      );

      $this->_storage = new FileStorage($storage_options);
    }

    return $this->_storage;
  }


  public static function fillLotsWithParams(Doctrine_Collection &$lots)
  {
    $params = Doctrine::getTable('LotParam')->getLotParamsByLotIds($lots->getPrimaryKeys());
    if ($params) {
      foreach ($lots as &$lot) {
        if (!$lot->hasReference('LotParams')) $lot->setRelated('LotParams', new Doctrine_Collection('LotParam'));
        foreach ($params as $k => $p) {
          if ($p->lot_id == $lot->id) {
            $lot->LotParams->add($p);
            $params->remove($k);
          }
        }
      }
    }
  }

  public static function fillLotsWithPhotos(Doctrine_Collection &$lots)
  {
    $photos = Doctrine::getTable('Photo')->getPhotosByLotIds($lots->getPrimaryKeys());
    if ($photos) {
      foreach ($lots as &$lot) {
        foreach ($photos as $k => $p) {
          if (!$lot->hasReference('Photos')) $lot->setRelated('Photos', new Doctrine_Collection('Photo'));
          if ($p->lot_id == $lot->id) {
            $lot->Photos->add($p);
            $photos->remove($k);
          }
        }
      }
    }
  }

  public static function fillLotsWithParents(Doctrine_Collection &$lots, $outoftown = false)
  {
    if($outoftown) self::fillLotsWithParents($lots); 
    $parent_ids = $outoftown ? $lots->toKeyValueArray('id', 'id') : $lots->toKeyValueArray('id', 'pid');
    $parent_ids = array_diff(array_unique($parent_ids, SORT_NUMERIC), array(null));
    $parents    = Doctrine::getTable('Lot')->getParentsByIds($parent_ids);
    if ($parents) {
      foreach ($lots as &$lot) {
        if ($lot->pid && $parents->contains($lot->pid)) {
          $lot->Parent = clone $parents->get($lot->pid);
        }
      }
    }
  }

  public static function fillLotsWithBrokers(Doctrine_Collection &$lots)
  {
    $broker_ids = $lots->toKeyValueArray('id', 'broker_id');
    $broker_ids = array_diff(array_unique($broker_ids, SORT_NUMERIC), array(null));
    $brokers    = Doctrine::getTable('Broker')->getBrokersByIds($broker_ids);
    if ($brokers) {
      foreach ($lots as &$lot) {
        if ($lot->broker_id && $brokers->contains($lot->broker_id)) {
          $lot->Broker = clone $brokers->get($lot->broker_id);
        }
      }
    }
  }

//  public function getPid()
//  {
//    return $this->pid && $this->Parent->is_visible;
//  }
}