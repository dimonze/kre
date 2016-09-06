<?php


/**
 * stat actions.
 *
 * @package    kre
 * @subpackage stat
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class statActions extends sfActions
{
  public function executeIndex()
  {
    $this->index = 'index';
    $this->totalLots = Doctrine::getTable('Lot')
    ->createQuery()
    ->andWhere('status = "active"')
    ->count();

    $this->goodLots = Doctrine::getTable('Lot')
      ->createQuery()
      ->andWhere('hide_price = 0')
      ->andWhere('price_all_from > 0')
      ->andWhere('exportable = 1')
      ->andWhere('status = "active"')
      ->andWhere('NOT ((type = "cottage" OR type = "outoftown") AND (has_children > 0))')
      ->andWhere('NOT ((type = "comsell" OR type = "comrent") AND (price_all_to > 0 OR area_to > 0))')
      ->count();

    $this->badLotsCount = Doctrine::getTable('Lot')
      ->createQuery()
      ->andWhere('status = "active" AND (hide_price <> 0 OR price_all_from <= 0 '
              . 'OR price_all_from IS NULL OR exportable <> 1 OR exportable IS NULL '
              . 'OR ((type = "comsell" OR type = "comrent") AND ((price_all_to > 0) '
              . 'OR (area_to > 0))) OR ((type = "cottage" OR type = "outoftown") AND (has_children > 0)))')
      ->count();
  }

  public function executeLot(sfWebRequest $request)
  {
    switch ($request->getParameter('id')) {
      case 'bad':
      case 'tba':
      case 'cian':        
        $this->index = $request->getParameter('id');
        $action = sprintf('execute%s', sfInflector::camelize($request->getParameter('id')));
        $this->$action($request);
        $this->types = $this->getTypes();
        break;

      default:
        break;
    }
  }


  public function executeBad(sfWebRequest $request)
  {
    if ($request->getParameter('sort')) {
      $this->sort = array($request->getParameter('sort'), $request->getParameter('sort_type'));
      $this->getUser()->setAttribute('stat.sort', $this->sort, 'admin_module');
    } else {
      $this->sort = array('l.id', 'asc');
      $this->getUser()->setAttribute('stat.sort', $this->sort, 'admin_module');
    }
    $this->badLots = Doctrine_Core::getTable('Lot')
            ->createQuery('l')
            ->orderBy($this->sort[0].' '.$this->sort[1]) 
      ->andWhere('
              l.status = "active" 
              AND (
                  l.hide_price <> 0 
                  OR l.price_all_from <= 0 
                  OR l.price_all_from IS NULL 
                  OR l.exportable <> 1 
                  OR l.exportable IS NULL
                  OR ((l.type = "comsell" OR l.type = "comrent") AND ((l.price_all_to > 0) OR (l.area_to > 0))) 
                  OR ((l.type = "cottage" OR l.type = "outoftown") AND (l.has_children > 0))
                  )')
      ->execute();
    
    $this->setTemplate('lot');
  }

  public function executeCian(sfWebRequest $request)
  { 
    if ($request->getParameter('sort')) {
      $this->sort = array($request->getParameter('sort'), $request->getParameter('sort_type'));
      $this->getUser()->setAttribute('stat.sort', $this->sort, 'admin_module');
    } else {
      $this->sort = array('l.id', 'asc');
      $this->getUser()->setAttribute('stat.sort', $this->sort, 'admin_module');
    }
    $this->totalLots = Doctrine::getTable('Lot')
            ->createQuery()
            ->andWhere('status = ?', 'active')
            ->count();

    $this->goodLots = Doctrine::getTable('Lot')
            ->createQuery('l')
            ->joinParams()
            ->andWhere('
        l.status = "active"
        AND l.type != "flatrent" 
        AND l.type != "comrent" 
        AND NOT (l.type = "cottage" AND (SQL: SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 81) != "да")
        AND NOT ('.self::getQueryCian('query_').')')
      ->groupBy('id')
      ->count();
    
    $this->badType = Doctrine::getTable('Lot')
      ->createQuery('l')
      ->joinParams()
      ->andWhere('
        l.status = "active"
        AND( 
        l.type = "flatrent" 
        OR l.type = "comrent" 
        OR (l.type = "cottage" AND (SQL: SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 81) != "да")
        )')
      ->groupBy('id')
      ->count();

    $this->badLots = Doctrine::getTable('Lot')
      ->createQuery('l')
      ->joinParams()
      ->orderBy($this->sort[0].' '.$this->sort[1]) 
      ->andWhere('
        l.status = "active"
        AND l.type != "flatrent" 
        AND l.type != "comrent" 
        AND NOT (l.type = "cottage" AND (SQL: SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 81) != "да")
        AND '.self::getQueryCian('query_'));

    $this->per_page = $request->getParameter('per_page', 50);

    $this->pager = new sfDoctrinePager('Lot', $this->per_page);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->setQuery($this->badLots);
    $this->pager->init();

    $this->badLots = $this->pager->getResults();

    Lot::fillLotsWithParents($this->badLots);
    
    $this->setTemplate('cian');
  }
  
  public function executeTbaList()
  {      
    //$cache  = KreCache::getInstance();
    $this->_settlements = array();
    $url = 'http://topba.ru/assets/directory.php?type=settlements';
    $_content = curl_init($url);
    curl_setopt($_content, CURLOPT_RETURNTRANSFER, 1);
    $geo = curl_exec($_content);
    curl_close($_content);
    $result = (array) simplexml_load_string($geo);
    foreach ($result['item'] as $value)
    { 
      $this->_settlements[] = (string)$value['name'];     
    }
    
    return preg_replace('/\'/', '', implode(",", $this->_settlements));
  }
  
  public function executeTba(sfWebRequest $request)
  {
    if ($request->getParameter('sort')) {
      $this->sort = array($request->getParameter('sort'), $request->getParameter('sort_type'));
      $this->getUser()->setAttribute('stat.sort', $this->sort, 'admin_module');
    } else {
      $this->sort = array('l.id', 'asc');
      $this->getUser()->setAttribute('stat.sort', $this->sort, 'admin_module');
    }
    $this->totalLots = Doctrine::getTable('Lot')
            ->createQuery()
            ->andWhere('status = ?', 'active')
            ->count();

    $query = 'l.hide_price <> 0
          OR l.price_all_from <= 0
          OR l.price_all_from IS NULL
          OR l.exportable <> 1
          OR l.exportable IS NULL
          OR (
          CASE
           WHEN (l.type = "outoftown" OR l.type = "cottage")                  
                    THEN (
                      l.has_children > 0
                      OR l.ward IS NULL
                      OR ((l.lat IS NULL OR l.lng IS NULL) AND ((SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 88) IS NULL)) 
                    )
           END
          )
         ';

    $this->goodLots = Doctrine::getTable('Lot')
      ->createQuery('l')
      ->joinParams()
      ->andWhere('
        l.status = "active"
        AND l.type != "comrent" 
        AND l.type != "comsell" 
        AND NOT ('.$query.')')
      ->groupBy('id')
      ->count();

    $this->badLots = Doctrine::getTable('Lot')
      ->createQuery('l')
      ->joinParams()
      ->orderBy($this->sort[0].' '.$this->sort[1]) 
      ->andWhere('
        l.status = "active"
        AND l.type != "comrent" 
        AND l.type != "comsell" 
        AND ('.$query.')');

    $this->per_page = $request->getParameter('per_page', 50);

    $this->pager = new sfDoctrinePager('Lot', $this->per_page);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->setQuery($this->badLots);
    $this->pager->init();

    $this->badLots = $this->pager->getResults();

    Lot::fillLotsWithParents($this->badLots);

    $this->setTemplate('tba');
  }
  
  private function getQueryCian($param)
  {
    switch ($param)
    {
      case 'query_':
        $query = ' 
              CASE                  
                  WHEN (l.type = "eliteflat" OR l.type = "elitenew")
                    THEN (
                      l.hide_price <> 0
                      OR l.price_all_from <= 0
                      OR l.price_all_from IS NULL
                      OR l.exportable <> 1
                      OR l.exportable IS NULL
                      OR
                      CASE WHEN l.pid IS NULL
                      THEN (                        
                          LOWER((SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 34)) NOT REGEXP "монолит|кирпич|панель|блоч|дерев|сталин"
                          OR (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 34) IS NULL
                          ) 
                      ELSE
                      IF(
                        ((SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 34) IS NULL 
                        OR LOWER((SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 34)) NOT REGEXP "монолит|кирпич|панель|блоч|дерев|сталин"),
                        (LOWER((SELECT value FROM lot_param WHERE lot_param.lot_id = l.pid AND lot_param.param_id = 34)) NOT REGEXP "монолит|кирпич|панель|блоч|дерев|сталин"
                        OR (SELECT value FROM lot_param WHERE lot_param.lot_id = l.pid AND lot_param.param_id = 34) IS NULL),
                        false
                      )
                      END
                    )   
                  WHEN l.type = "comsell"
                    THEN (
                      l.hide_price <> 0
                      OR l.price_all_from <= 0
                      OR l.price_all_from IS NULL
                      OR l.exportable <> 1
                      OR l.exportable IS NULL
                      OR
                      CASE WHEN l.pid IS NULL
                      THEN (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 1) IS NULL
                      OR (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 1) = "--"
                      ELSE
                      IF(
                        (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 1) IS NULL,
                        (SELECT value FROM lot_param WHERE lot_param.lot_id = l.pid AND lot_param.param_id = 1) IS NULL,
                        false
                      )
                      END
                      OR (l.price_all_to > 0 OR l.area_to > 0)
                    )
                  WHEN l.type = "outoftown"
                    THEN (
                      l.has_children > 0
                      OR l.hide_price <> 0
                      OR l.price_all_from <= 0
                      OR l.price_all_from IS NULL
                      OR l.exportable <> 1
                      OR l.exportable IS NULL
                      OR l.ward IS NULL
                      OR
                      CASE WHEN l.pid IS NULL
                        THEN (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 1) IS NULL
                        OR (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 1) = "--"
                      ELSE
                        IF(
                        (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 1) IS NULL,
                        (SELECT value FROM lot_param WHERE lot_param.lot_id = l.pid AND lot_param.param_id = 1) IS NULL,
                        false
                        )
                      END
                      OR
                      CASE WHEN l.pid IS NULL
                        THEN (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 22) IS NULL
                        OR (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 22) = "--"
                      ELSE
                        IF(
                        (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 22) IS NULL,
                        (SELECT value FROM lot_param WHERE lot_param.lot_id = l.pid AND lot_param.param_id = 22) IS NULL,
                        false
                        )
                      END
                      OR
                      CASE WHEN l.pid IS NULL
                        THEN (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 45) IS NULL
                        OR (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 45) = "--"
                      ELSE
                        IF(
                        (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 45) IS NULL,
                        (SELECT value FROM lot_param WHERE lot_param.lot_id = l.pid AND lot_param.param_id = 45) IS NULL,
                        false
                        )
                      END		 
                    )
                  WHEN (l.type = "cottage" AND (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 81) = "да")
                    THEN (
                      l.hide_price <> 0
                      OR l.price_all_from <= 0
                      OR l.price_all_from IS NULL
                      OR l.exportable <> 1
                      OR l.exportable IS NULL
                      OR l.has_children > 0
                      OR l.ward IS NULL
                      OR
                      CASE WHEN l.pid IS NULL
                        THEN (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 1) IS NULL
                        OR (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 1) = "--"
                      ELSE
                        IF(
                        (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 1) IS NULL,
                        (SELECT value FROM lot_param WHERE lot_param.lot_id = l.pid AND lot_param.param_id = 1) IS NULL,
                        false
                        )
                      END
                      OR
                      CASE WHEN l.pid IS NULL
                        THEN (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 22) IS NULL
                        OR (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 22) = "--"
                      ELSE
                        IF(
                        (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 22) IS NULL,
                        (SELECT value FROM lot_param WHERE lot_param.lot_id = l.pid AND lot_param.param_id = 22) IS NULL,
                        false
                        )
                      END
                      OR
                      CASE WHEN l.pid IS NULL
                        THEN (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 45) IS NULL
                        OR (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 45) = "--"
                      ELSE
                        IF(
                        (SELECT value FROM lot_param WHERE lot_param.lot_id = l.id AND lot_param.param_id = 45) IS NULL,
                        (SELECT value FROM lot_param WHERE lot_param.lot_id = l.pid AND lot_param.param_id = 45) IS NULL,
                        false
                        )
                      END	
                    )                    
                END
              ';
        break;     
    }
    return $query;
  }
  
  private function getTypes()
  {
    return array(
        'eliteflat' => 'Продажа квартир',
        'penthouse' => 'Продажа квартир',
        'flatrent' => 'Аренда квартир',
        'elitenew' => 'Продажа новостроек',
        'outoftown' => 'Продажа загород',
        'cottage' => 'Аренда загород',
        'comsell' => 'Продажа коммерция',
        'comrent' => 'Аренда коммерция',
    );
  }
}

