<?php

/**
 * csstat actions.
 *
 * @package    kre
 * @subpackage csstat
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class csstatActions extends sfActions
{
  public $_months = array(1 => 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public $_fields = array(
    'id'         => 'Id',
    'rating'     => 'Рейтинг',
    'name'       => 'Название',
    'status'     => 'Статус',
    'area_from'  => 'Площадь',
    'price_all_from' => 'Цена',
  );

  public function preExecute()
  {
    $user_credential = (array)$this->getUser()->getCredentials();
    $user_credential = array_shift($user_credential);
    $action_credentials = (array)$this->getCredential();

    if(!empty($action_credentials) && !in_array($user_credential, $action_credentials)) {
      $this->getUser()->clearCredentials();
      $this->getUser()->setAuthenticated(false);
      $this->getUser()->getAttributeHolder()->clear();
      $this->redirect('csstat');
    }
  }

  public function executeLogin(sfWebRequest $request)
  {
    if ($request->isMethod('post') && $request->hasParameter('cs_auth')) {
      $values = $request->getParameter('cs_auth');
      $user = Doctrine::getTable('Broker')->createQuery()
            ->andWhere('login = ?', $values['login'])
            ->andWhere('password = ?', $values['pass'])
            ->execute(array(), Doctrine::HYDRATE_ARRAY);
      if(!empty($user)) {
        $user = array_pop($user);
        $this->getUser()->setAuthenticated(true);
        $this->getUser()->addCredentials($user['role']);
        foreach($user as $name => $value) {
          if($name == 'password')
            continue;

          $this->getUser()->setAttribute($name, $value);
        }
        if(!empty($values['ref']) && $values['ref'] != $this->getController()->genUrl('csstat', true)) {
          $this->redirect($values['ref']);
        }
        else {
          $this->redirect('csstat_acts', array('action' => 'object'));
        }
      }
      else {
        $this->getUser()->setFlash('error', 'Неверный логин или пароль');
      }
    }

    $ref = null === $this->getUser()->getFlash('error')
      ? $request->getReferer()
      : $values['ref'];

    $this->form = new sfForm();
    $this->form->setWidgets(array(
      'login'     => new sfWidgetFormInput(),
      'pass'      => new sfWidgetFormInputPassword(),
      'ref'       => new sfWidgetFormInputHidden(array(
        'default' => $ref,
      )),
    ));
    $this->form->getWidgetSchema()->setNameFormat('cs_auth[%s]');

  }

  public function executeObject(sfWebRequest $request)
  {
    $this->fields = $this->_fields;
    $this->_types = Lot::$_types;
    $this->markets = array('Первичный' , 'Вторичный');
    $this->objectType = array();
    $this->objectType['Коммерция'] = array(
          'Торговое помещение' => 'Торговое помещение',
          'Офисное помещение' => 'Офисное помещение',
          'Отдельно стоящее здание' => 'Отдельно стоящее здание',
          'Готовый арендный бизнес' => 'Готовый арендный бизнес',
          'Особняк' => 'Особняк',
          'Помещение свободного назначения' => 'Помещение свободного назначения',
          'Склад/складской комплекс' => 'Склад/складской комплекс',
          'Промышленный комплекс'  => 'Промышленный комплекс',   
          'Земельный участок' => 'Земельный участок',
          'Прочее'  => 'Прочее',
        ); 
    $this->objectType['Загород'] = array(          
          'Участок' => 'Участок',
          'Таунхаус'  => 'Таунхаус',
          'Квартира'  => 'Квартира',
          'Коттедж'  => 'Коттедж',
          'Коттеджный поселок'  => 'Коттеджный поселок',); 
   
    if($request->hasParameter('status') && $request->hasParameter('type')) {
      $query = Doctrine::getTable('Lot')->createQuery('l')              
              ->select(implode(', ', array_merge(array_keys($this->fields), array('status', 'type', 'currency'))));      
      $query->addSelect('if(l.is_penthouse = 1, "penthouse", l.type) as pseudotype');

      if($request->hasParameter('market') && $request->getParameter('market') != 'all'){        
        $query->joinOnce($query->getRootAlias() . '.LotParams lp2 ON lp2.lot_id = l.id')
              ->joinOnce($query->getRootAlias() . '.LotParams lp3 ON lp3.lot_id = l.pid')
              ->andWhere('
                      CASE 
                        WHEN l.pid IS NULL
                        THEN (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 79) = ?
                        WHEN (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 79) = "--"
                        THEN false
                        ELSE
                          IF(
                            (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 79) = ?,
                            (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 79) = ?,
                            (SELECT value FROM lot_param WHERE lot_param.lot_id = l.pid AND lot_param.param_id = 79) = ?
                          )
                        END
                      ', 
                      array (($request->getParameter('market') == 0 ? 'Первичный' : 'Вторичный'), 
                             ($request->getParameter('market') == 0 ? 'Первичный' : 'Вторичный'),
                             ($request->getParameter('market') == 0 ? 'Первичный' : 'Вторичный'),
                             ($request->getParameter('market') == 0 ? 'Первичный' : 'Вторичный')))
              ->groupBy('l.id');
      } 
      if($request->hasParameter('objectType') && $request->getParameter('objectType') != 'all'){
        $query->joinOnce($query->getRootAlias() . '.LotParams lp ON lp.lot_id = l.id')
                ->andWhere('lp.param_id = 1 and lp.value = ?', 
                      array ($request->getParameter('objectType'),))
                ->groupBy('l.id');
      }
      if($request->getParameter('status') != 'all'){
        $query->andWhere('status = ?', $request->getParameter('status'));
      }
      if($request->getParameter('type') != 'all'){
        $query->having('pseudotype = ?', $request->getParameter('type'));
      }
      if($request->hasParameter('by') && $request->hasParameter('dir')){
        if(in_array($request->getParameter('by'), array_keys($this->fields))){
          $dir = 'desc';
          if($request->getParameter('dir') != 'desc') {
            $dir = 'asc';
          }
          $query->orderBy($request->getParameter('by', 'type') . ' ' . $dir);
        }
      }

      if($this->getUser()->getAttribute('role') == 'broker'){
        $query->andWhere('broker_id = ?', $this->getUser()->getAttribute('id'));
      }

      if($request->getParameter('page', 1) == 'all') {
        $this->result = $this->analyzeObjects($query->execute(array(), Doctrine::HYDRATE_ARRAY),true);
      }

      $query2 = Doctrine::getTable('Lot')
        ->createQuery('l')
        ->select(implode(', ', array_merge(array_keys($this->fields), array('status', 'type', 'currency'))))
        ->addSelect('if(l.is_penthouse = 1, "penthouse", l.type) as pseudotype');
      
      if($request->hasParameter('market') && $request->getParameter('market') != 'all'){        
        $query2->joinOnce($query->getRootAlias() . '.LotParams lp2 ON lp2.lot_id = l.id')
              ->joinOnce($query->getRootAlias() . '.LotParams lp3 ON lp3.lot_id = l.pid')
              ->andWhere('
                      CASE 
                        WHEN l.pid IS NULL
                        THEN (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 79) = ?
                        WHEN (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 79) = "--"
                        THEN false
                        ELSE
                          IF(
                            (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 79) = ?,
                            (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 79) = ?,
                            (SELECT value FROM lot_param WHERE lot_param.lot_id = l.pid AND lot_param.param_id = 79) = ?
                          )
                        END
                      ', 
                      array (($request->getParameter('market') == 0 ? 'Первичный' : 'Вторичный'), 
                             ($request->getParameter('market') == 0 ? 'Первичный' : 'Вторичный'),
                             ($request->getParameter('market') == 0 ? 'Первичный' : 'Вторичный'),
                             ($request->getParameter('market') == 0 ? 'Первичный' : 'Вторичный')))
              ->groupBy('l.id');
      } 
      if($request->hasParameter('objectType') && $request->getParameter('objectType') != 'all'){
        $query2->joinOnce($query->getRootAlias() . '.LotParams lp ON lp.lot_id = l.id')
                ->andWhere('lp.param_id = 1 and lp.value = ?', 
                      array ($request->getParameter('objectType'),))
                ->groupBy('l.id');
      }
      if($request->getParameter('type') != 'all'){
        $query2->having('pseudotype = ?', $request->getParameter('type'));
      }
      if($this->getUser()->getAttribute('role') == 'broker'){
        $query2->andWhere('broker_id = ?', $this->getUser()->getAttribute('id'));
      }
      $this->result2 = $this->analyzeObjects($query2->execute(array(), Doctrine::HYDRATE_ARRAY),false);

      $this->pager = new sfDoctrinePager('Lot', 50);
      $this->pager->setPage($request->getParameter('page', 1));
      $this->pager->setQuery($query);
      $this->pager->init();

      if($request->getParameter('page', 1) != 'all') {
        $this->result = $this->analyzeObjects($this->pager->getResults(), true);
      }

      if($this->getUser()->getAttribute('role') == 'broker'){
        $query3 = Doctrine::getTable('Lot')
          ->createQuery('l')
          ->addSelect('if(l.is_penthouse = 1, "penthouse", l.type) as pseudotype')
          ->addSelect('COUNT(l.id) as cnt')
          ->groupBy('pseudotype')
          ->andWhere('broker_id = ?', $this->getUser()->getAttribute('id'));

        $types = $query3->execute(array(), Doctrine::HYDRATE_SINGLE_SCALAR);
        foreach($this->_types as $key => $value) {
          if(!in_array($key, $types)) {
            unset($this->_types[$key]);
          }
        }
      }
    }
    $this->form = new sfForm();
    $this->form->setWidgets(array(
      'status'   => new sfWidgetFormChoice(array(
        'choices' => array('all' => 'Все') + Lot::$_status,
        'default' => $request->getParameter('status', 'all'),
      )),
      'type'   => new sfWidgetFormChoice(array(
        'choices' => array('all' => 'Все разделы') + $this->_types,
        'default' => $request->getParameter('type', 'all'),
      )),
      'market'   => new sfWidgetFormChoice(array(
        'choices' => array('all' => 'Все') + $this->markets,
        'default' => $request->getParameter('market', 'all'),
      )),
      'objectType'   => new sfWidgetFormChoice(array(
        'choices' => array('all' => 'Все') + $this->objectType,
        'default' => $request->getParameter('objectType', 'all'),
      )),
    ));
  }

  public function executeBroker(sfWebRequest $request)
  {
    $this->fields = $this->_fields;
    $this->markets = array('Первичный' , 'Вторичный');
    $this->objectType = array();
    $this->objectType['Коммерция'] = array(
          'Торговое помещение' => 'Торговое помещение',
          'Офисное помещение' => 'Офисное помещение',
          'Отдельно стоящее здание' => 'Отдельно стоящее здание',
          'Готовый арендный бизнес' => 'Готовый арендный бизнес',
          'Особняк' => 'Особняк',
          'Помещение свободного назначения' => 'Помещение свободного назначения',
          'Склад/складской комплекс' => 'Склад/складской комплекс',
          'Промышленный комплекс'  => 'Промышленный комплекс',   
          'Земельный участок' => 'Земельный участок',
          'Прочее'  => 'Прочее',
        ); 
    $this->objectType['Загород'] = array(          
          'Участок' => 'Участок',
          'Таунхаус'  => 'Таунхаус',
          'Квартира'  => 'Квартира',
          'Коттедж'  => 'Коттедж',
          'Коттеджный поселок'  => 'Коттеджный поселок',); 
    unset($this->fields['status']);
    $brokers_list = $this->getBrokersList();
    $broker_ids = array_keys($brokers_list);
    $this->form = new sfForm();
    $this->form->setWidgets(array(
      'user'   => new sfWidgetFormChoice(array(
        'choices' => $brokers_list,
        'default' => $request->getParameter('user',array_shift($broker_ids)),
      )),
      'type'   => new sfWidgetFormChoice(array(
        'choices' => array('all' => 'Все разделы') + Lot::$_types,
        'default' => $request->getParameter('type', 'all'),
      )),
      'market'  => new sfWidgetFormChoice(array(
        'choices' => array('all' => 'Все') + $this->markets,
        'default' => $request->getParameter('market', 'all'),
      )),
      'objectType'   => new sfWidgetFormChoice(array(
        'choices' => array('all' => 'Все') + $this->objectType,
        'default' => $request->getParameter('objectType', 'all'),
      )),
    ));
    $broker_id = $request->getParameter('user');
    if(!empty($broker_id)){
      $query = Doctrine::getTable('Lot')->createQuery('l')
        ->select(implode(', ', array_merge(array_keys($this->fields), array('status', 'type', 'currency'))));
      if($broker_id != 'NULL') {
        $query->andWhere('broker_id = ?', $broker_id);
      }
      else {
        $query->andWhere('broker_id IS NULL');
      }
      $query->addSelect('if(is_penthouse = 1, "penthouse", type) as pseudotype');
      if($request->getParameter('type') != 'all'){
        $query->having('pseudotype = ?', $request->getParameter('type'));
      }
      if($request->hasParameter('market') && $request->getParameter('market') != 'all'){        
        $query->joinOnce($query->getRootAlias() . '.LotParams lp2 ON lp2.lot_id = l.id')
              ->joinOnce($query->getRootAlias() . '.LotParams lp3 ON lp3.lot_id = l.pid')
              ->andWhere('
                     CASE 
                        WHEN l.pid IS NULL
                        THEN (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 79) = ?
                        WHEN (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 79) = "--"
                        THEN false
                        ELSE
                          IF(
                            (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 79) = ?,
                            (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 79) = ?,
                            (SELECT value FROM lot_param WHERE lot_param.lot_id = l.pid AND lot_param.param_id = 79) = ?
                          )
                        END
                      ', 
                      array (($request->getParameter('market') == 0 ? 'Первичный' : 'Вторичный'), 
                             ($request->getParameter('market') == 0 ? 'Первичный' : 'Вторичный'),
                             ($request->getParameter('market') == 0 ? 'Первичный' : 'Вторичный'),
                             ($request->getParameter('market') == 0 ? 'Первичный' : 'Вторичный')))
              ->groupBy('l.id');
      } 
      if($request->hasParameter('objectType') && $request->getParameter('objectType') != 'all'){
        $query->joinOnce($query->getRootAlias() . '.LotParams lp ON lp.lot_id = l.id')
                ->andWhere('lp.param_id = 1 and lp.value = ?', 
                      array ($request->getParameter('objectType'),))
                ->groupBy('l.id');
      }
      if($request->hasParameter('by') && $request->hasParameter('dir')){
        if(in_array($request->getParameter('by'), array_keys($this->fields))){
          $dir = 'desc';
          if($request->getParameter('dir') != 'desc') {
            $dir = 'asc';
          }
          $query->orderBy($request->getParameter('by') . ' ' . $dir);
        }
      }
      $this->result = $this->analyzeObjects($query->execute(array(), Doctrine::HYDRATE_ARRAY), false);
    }
  }

  public function executeQuery(sfWebRequest$request)
  {
    $this->months = $this->_months;
    $first = Doctrine::getTable('Query')->createQuery()
      ->select('DATE(MIN(day)) as min_day')
      ->andWhere('type = ?', $request->getParameter('type', 'eliteflat'))
      ->execute(array(), Doctrine::HYDRATE_ARRAY);
    $this->processDates(!empty($first[0]['min_day']) ? $first[0]['min_day'] : date('Y-m-d'));
    $query = Doctrine::getTable('Query')->createQuery()
      ->select('params, DATE_FORMAT(day, "%H:00") as hour, DATE_FORMAT(day, "%Y-%m-%d") as real_day')
      ->andWhere('type = ?', $request->getParameter('type', 'eliteflat'))
      ->having('real_day = ?', implode('-', $this->act))
      ->orderBy('day asc')
      ->execute(array(), Doctrine::HYDRATE_ARRAY);
    $this->result = array();
    foreach($query as $row) {
      $this->result[$row['hour']][] = $this->humanizeData($row['params']);
    }
  }

  public function executeRecommendation(sfWebRequest $request)
  {
    $this->months = $this->_months;
    $first = Doctrine::getTable('Click')->createQuery()
      ->select('MIN(day) as min_day')->execute(array(), Doctrine::HYDRATE_ARRAY);
    $this->processDates(!empty($first[0]['min_day']) ? $first[0]['min_day'] : date('Y-m-d'));
    $this->result = Doctrine::getTable('Click')->createQuery()
      ->andWhere('day = ?', implode('-', $this->act))
      ->orderBy('amount desc')
      ->execute(array(), Doctrine::HYDRATE_ARRAY);
  }

  public function executeLogout(sfWebRequest $request)
  {
    $this->getUser()->clearCredentials();
    $this->getUser()->setAuthenticated(false);
    $this->getUser()->getAttributeHolder()->clear();
    $this->getController()->redirect($request->getReferer());
  }



  private function getBrokersList()
  {
    $choices = array();
    foreach(Doctrine::getTable('Broker')->findAll(Doctrine::HYDRATE_ARRAY) as $broker) {
      list($last, $first, $patr) = explode(' ', $broker['name']) + array('', '', '');
      $choices[$broker['id']] = $broker['phone'] . ' / ' . $last . ' ' . mb_substr($first, 0, 1, 'utf-8') . '.';
      if($this->getRequest()->getParameter('user') == $broker['id']){
        $this->broker = $broker;
      }
    }
    $choices['NULL'] = '- - Без брокера - -';
    if($this->getRequest()->getParameter('user') == 'NULL') {
      $this->broker = array('name' => '- - Без брокера - ', 'phone' => null);
    }
    return $choices;
  }

  private function analyzeObjects($objects, $without_statuses = false)
  {

    if(!$without_statuses) {
      $types    = array_fill_keys(array_keys(Lot::$_types), array(
        'active'   => array(),
        'hidden'   => array(),
        'inactive' => array(),
      ));
      $counters = array_fill_keys(array_keys(Lot::$_types), array(
        'sum'      => 0,
        'active'   => 0,
        'hidden'   => 0,
        'inactive' => 0,
      ));
      foreach($objects as $object) {
        $types[$object['pseudotype']][$object['status']][] = $object;

        $counters[$object['pseudotype']]['sum']++;
        $counters[$object['pseudotype']][$object['status']]++;
      }
    }
    else {
      $types    = array_fill_keys(array_keys(Lot::$_types), array());
      $counters = array_fill_keys(array_keys(Lot::$_types), 0);
      foreach($objects as $object) {
        $types[$object['pseudotype']][] = $object;
        $counters[$object['pseudotype']]++;
      }
    }
    return array(
      'counters' => $counters,
      'types'    => $types,
    );
  }

  private function processDates($start)
  {
    $this->first = array_combine(array('y','m','d'), explode('-',$start));
    $this->last  = array_combine(array('y','m','d'), explode('-',date('Y-m-d')));
    $this->act = array(
      'y' => $this->getRequest()->getParameter('y', date('Y')),
      'm' => $this->getRequest()->getParameter('m', date('m')),
      'd' => $this->getRequest()->getParameter('d', date('d')),
    );

    if(!checkdate($this->act['m'],$this->act['d'],$this->act['y'])){
      $this->act = $this->last;
      return;
    }
    $stamps = array(
      'first' => mktime(0,0,0,$this->first['m'],$this->first['d'],$this->first['y']),
      'act'   => mktime(0,0,0,$this->act['m'],$this->act['d'],$this->act['y']),
      'last' => mktime(0,0,0,$this->last['m'],$this->last['d'],$this->last['y']),
    );
    if($stamps['first'] > $stamps['act']){
      $this->act = $this->first;
    }else if ($stamps['last'] < $stamps['act']) {
      $this->act = $this->last;
    }
    return true;
  }

  private function humanizeData($data)
  {
    $result = array();
    $sep =   ': ';
    $a_sep = ', ';

    $option_names = array(
      'id'                 => 'Лот',
      'area_from'          => 'Площадь%s м',
      'space_from'         => 'Площадь дома%s м',
      'spaceplot_from'     => 'Площадь участка%s сот.',
      'rooms_from'         => 'Кол-во комнат%s',
      'price_from'         => 'Цена%s ' . (!empty($data['currency']) ? $data['currency'] : ''),
      'price_all_from'     => 'Цена общая%s ' . (!empty($data['currencyAll']) ? $data['currencyAll'] : ''),
      'distance_mkad_from' => 'От МКАД%s км.',
      'no_price_ok'        => 'Без цены',
      'districts'          => 'Район(ы)',
      'wards'              => 'Направления',
      'objecttype'         => 'Тип объекта',
      'street'             => 'Адрес',
      'estate'             => 'ЖК',
      'city'               => 'Населенный пункт',
      'cottageVillage'     => 'Коттеджный поселок',
      'decoration'         => '',
      'balcony'            => '',
      'parking'            => '',
      'under_construction' => 'Только строящиеся',
      'only_new'           => 'Только новые объекты',
      'only_new_price'     => 'Только объекты с новой ценой',
    );

    foreach($data as $key=>$value) {
      switch($key) {

        //range (key: value_from - value_to dimen.)
        case 'area_from':
        case 'space_from':
        case 'spaceplot_from':
        case 'price_from':
        case 'price_all_from':        
        case 'rooms_from':
        case 'distance_mkad_from':
          $prefix = str_replace('_from', '', $key);
          $result[] = sprintf($option_names[$prefix . '_from'], $sep . $value
                    . (!empty($data[$prefix . '_to']) ? ' - ' . $data[$prefix . '_to'] : ''));
          $key2 = $prefix . '_to';
        break;

        //literal (value)
        case 'no_price_ok':
        case 'under_construction':
        case 'only_new':
        case 'only_new_price':
          $result[] = $option_names[$key];
        break;

        //array of ids from config (key: app_lala[1], app_lala[2])
        case 'districts':
        case 'wards':
          $elements = array();
          $config = sfConfig::get('app_' . $key);
          foreach($value as $id) {
            if(!empty($config[$id]))
              $elements[] = $config[$id];
          }
          $result[] = $option_names[$key] . $sep . implode($a_sep, $elements);
        break;

        //array of values (key: value1, value2)
        case 'objecttype' :
          $result[] = $option_names[$key] . $sep . implode($a_sep, $value);
        break;

        // arrays of literals (value1, value2)
        case 'decoration':
        case 'balcony':
        case 'parking':
          $result[] = implode($a_sep, $value);
        break;

        // key: value
        case 'id':
        case 'street':
        case 'estate':
        case 'city':
        case 'cottageVillage':
          $result[] = $option_names[$key] . $sep . $value;
        break;

        //dark side of ranges
        case 'area_to':
        case 'space_to':
        case 'spaceplot_to':        
        case 'price_to':    
        case 'price_all_to':
        case 'rooms_to':
        case 'distance_mkad_to':
        case 'currencyAll':
        case 'currency':
          //It's all right, gentlemen!
        break;

        default: $result[] = "{$key} {$value}";
      }
    }
    return implode('; ', $result);
  }
}
