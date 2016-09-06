<?php

abstract class MetaParse
{
  public static function setMetas(sfActions $action)
  {    
    self::setDesctiption();   
    self::getMetasFromLand($action);
    if ('page' == $action->getModuleName() && 'homepage' != $action->getActionName()) {      
      return self::setMetasPage($action);
    }
    if ('vacancy' == $action->getModuleName()) {
      return self::setMetasVacancy($action);
    }
    if ('lot' == $action->getModuleName()) {
      return self::setMetasLot($action);
    }
  }

  protected static function setMetasPage(sfActions $action)
  {    
    $response = $action->getResponse();
    if($action->getRequest()->getParameter('elem') == 'services' && $action->getRequest()->getParameter('id') == 2135)
    {
      $response->addMeta('robots', 'noindex, nofollow');
    } 
    if($action->getRequest()->getParameter('elem') == 'advices' && $action->getRequest()->getParameter('id') == 17)
    {
      $response->addMeta('robots', 'noindex, nofollow');
    } 
    if($action->getRequest()->getParameter('elem') == 'about' && $action->getRequest()->getParameter('id') == 5)
    {
      $response->addMeta('robots', 'noindex, nofollow');
    }
    if($action->getRequest()->getParameter('action') == 'reviewArchive' && $action->getRequest()->getParameter('year') == date("Y"))
    {
      $response->addMeta('robots', 'noindex, nofollow');
    } 
    if($action->getRequest()->getParameter('action') == 'newsArchive' && $action->getRequest()->getParameter('year') == date("Y"))
    {
      $response->addMeta('robots', 'noindex, nofollow');
    }    
    $_metas = self::getMetasFromLand($action);
    
    if (($page = $action->page) && !$_metas) { 
      if($_title = sfConfig::get(sprintf('app_title_hc_%s', preg_replace('/\//', '', $action->getRequest()->getPathInfo())))) {
        $response->addMeta('title', $_title);
      }
      else if($action->getRequest()->getParameter('action') == 'newsArchive'){
        $response->addMeta('title', 'Новости | Архив '.$action->getRequest()->getParameter('year'));
      }      
      else if(stristr(preg_replace('/\//', '', $action->getRequest()->getPathInfo()), 'news')){
        $response->addMeta('title', 'Новости');
      }
      else if ($page->seo_title != '') {
        $response->addMeta('title', $page->seo_title);
      }
      else {
        $response->addMeta('title', $page->name);
      }
      
      if ($page->seo_description != '') {
        $response->addMeta('description', $page->seo_description);
      }
      if ($page->seo_keywords != '') {
        $response->addMeta('keywords', $page->seo_keywords);
      }
    }
  }

  protected static function setMetasVacancy(sfActions $action)
  {
    $response = $action->getResponse();
    $route = $action->getRequest()->getPathInfo();
    $_metas = self::getMetasFromLand($action);
    if (($vacancy = $action->page) && !$_metas) {
      $seo = Doctrine::getTable('SeoText')->findOneBy('url', $route);
      if ($seo) {
        if ('' != $seo->name) {
          $response->addMeta('title', $seo->name);
        }
        if ('' != $seo->description) {
          $response->addMeta('description', $seo->description);
        }
        if ('' != $seo->keywords) {
          $response->addMeta('keywords', $seo->keywords);
        }
      }
      else {
        $response->addMeta('title', $vacancy->name);
      }
    }
  }

  protected static function setMetasLot(sfActions $action)
  {
    $response = $action->getResponse();
    $route = $action->getRequest()->getUri();
    $noindex = (int)$action->getRequest()->getParameter('page') >= 1;
    if ($lot = $action->lot) {      
      $seo = self::generateLotMetas($lot);
      $response->addMeta('title', !empty($lot->seo_title)
        ? $lot->seo_title
        : $seo['title']
      );
      $response->addMeta('description', !empty($lot->seo_description)
        ? $lot->seo_description
        : $seo['description']
      );
      $response->addMeta('keywords', !empty($lot->seo_keywords)
        ? $lot->seo_keywords
        : $seo['keywords']
      );
    }
    else if($noindex) { 
      if ($action->getRequest()->hasParameter('preset')){
        $seo = SeoText::getAllSeo(SeoText::getUrlByHrurl($action->getRequest()->getPathInfo()));
      }else {      
        $seo = SeoText::getAllSeo($route);   
      }
      if ($seo) {
        if ('' != $seo->title) {
          $response->addMeta('title', $seo->title);
        }
        else {
          $_title = sfConfig::get(sprintf('app_presets_h1_%s', $action->getRequest()->getParameter('type')));
          $response->addMeta('title', $_title['_main']);  
        }
      }
      else{
        $_title = sfConfig::get(sprintf('app_presets_h1_%s', $action->getRequest()->getParameter('type')));
        $response->addMeta('title', $_title['_main']); 
      }                        
       $response->addMeta('h1', ' ');
       $response->addMeta('description', ' ');
       $response->addMeta('keywords', ' ');
       $response->addMeta('robots', 'noindex, nofollow');
    }   
    else{
      if ($action->getRequest()->hasParameter('preset')){
        if($action->getRequest()->getGetParameters()){
          $response->addMeta('robots', 'noindex, nofollow');
        }
        $seo = SeoText::getAllSeo(SeoText::getUrlByHrurl($action->getRequest()->getPathInfo()));
      }else {      
        $seo = SeoText::getAllSeo($route);   
      }
      
      if ($seo) {
        if ('' != $seo->name) {
          $response->addMeta('h1', $seo->name);
        }
        if ('' != $seo->title) {
          $response->addMeta('title', $seo->title);
        }
        if ('' != $seo->description) {
          $response->addMeta('description', $seo->description);
        }
        if ('' != $seo->keywords) {
          $response->addMeta('keywords', $seo->keywords);
        }
      }
      else{
        $_title = sfConfig::get(sprintf('app_presets_h1_%s', $action->getRequest()->getParameter('type')));
        $response->addMeta('title', $_title['_main']); 
        $response->addMeta('description', $_title['_main']); 
        
      }
    }
  }


  protected static function generateLotMetas(Lot $lot)
  {
    $params = $lot->params;

    $price     = !empty($lot->price_from) && (!$lot->hide_price || Broker::isAuth()) ? number_format(round(Currency::convert($lot->price_from,     $lot->currency, 'RUR')), 0, '', ' ') : '-';
    $price_all = !empty($lot->price_all_from) && (!$lot->hide_price || Broker::isAuth()) ? number_format(round(Currency::convert($lot->price_all_from, $lot->currency, 'RUR')), 0, '', ' ') : '-';
    $rooms = !empty($params['rooms']) ? sprintf(' комнат %s,', $params['rooms']) : '';
    $area = $lot->area_from > 0 ? number_format($lot->area_from, 1, '.', '') : null;
    if($area > 1 && round($area) == $area) {
      $area = round($area);
    }

    $areas = round($lot->area_from) . (!empty($lot->area_to) ? '-' . round($lot->area_to) : '');

    $wards = sfConfig::get('app_wards');
    $ward = $lot->pretty_wards;
    $subways = sfConfig::get('app_subways');
    $subway = empty($lot->metro_id) ? '' : $subways[$lot->metro_id];


    switch ($lot->type) {
      case 'eliteflat':
        $title = sprintf('Квартира, район %s,%s площадь %s м2,  цена за м2: %s рублей',
          $lot->district, $rooms, $area, $price
        );
        $keywords = sprintf('квартира, район %s, метро %s,%s площадь %s м2',
          $lot->district, $subway, $rooms, $area
        );
        $description = sprintf('Квартира, район %s, метро %s, тип дома %s,%s площадь %s м2',
          $lot->district,
          $subway,
          isset($params['buildtype']) ? $params['buildtype'] : '',
          $rooms,
          $area
        );
        break;
      case "elitenew":
        $title = sprintf('Квартира, район %s, тип дома %s, площадь %s м2, количество этажей %s',
          $lot->district,
          isset($params['buildtype']) ? $params['buildtype'] : '',
          $area,
          isset($params['floors']) ? $params['floors'] : ''
        );
        $keywords = sprintf('квартира, район %s, метро %s,%s площадь %s м2',
          $lot->district, $subway, $rooms, $areas
        );
        $description = sprintf('Квартира, район %s, метро %s, тип дома %s,%s высота потолков %s м, площадь %s м2',
          $lot->district,
          $subway,
          isset($params['buildtype']) ? $params['buildtype'] : '',
          $rooms,
          isset($params['roomheight']) ? $params['roomheight'] : '',
          $areas
        );
        break;
      case "flatrent":
        $title = sprintf('Квартира в аренду, район %s,%s площадь %s м2,  цена в месяц: %s рублей',
          $lot->district, $rooms, $area, $price_all
        );
        $keywords = sprintf('квартира в аренду, район %s, метро %s,%s площадь %s м2',
          $lot->district, $subway, $rooms, $area
        );
        $description = sprintf('Квартира, район %s, метро %s, тип дома %s,%s площадь %s м2',
          $lot->district,
          $subway,
          isset($params['buildtype']) ? $params['buildtype'] : '',
          $rooms,
          $area
        );
        break;
      case "penthouse":
        $title = sprintf('Пентхаус, район %s,%s площадь %s м2, цена за м2: %s рублей',
          $lot->district, $rooms, $area, $price
        );
        $keywords = sprintf('пентхаус, район %s, метро %s,%s площадь %s м2',
          $lot->district, $subway, $rooms, $area
        );
        $description = sprintf('Пентхаус, район %s, метро %s, тип дома %s,%s площадь %s м2',
          $lot->district,
          $subway,
          isset($params['buildtype']) ? $params['buildtype'] : '',
          $rooms,
          $area
        );
        break;

      case "comsell":
        $title = sprintf('%s, район %s, общая площадь %s м2, цена за м2: %s рублей',
          isset($params['objecttype']) ? $params['objecttype'] : '',
          $lot->district,
          $area,
          $price
        );
        $keywords = sprintf('%s, район %s, метро %s, площадь %s м2',
          isset($params['objecttype']) ? $params['objecttype'] : '',
          $lot->district,
          $subway,
          $area
        );
        $description = sprintf('%s, район %s, метро %s, %s, %s, площадь %s м2',
          isset($params['objecttype']) ? $params['objecttype'] : '',
          $lot->district,
          $subway,
          isset($params['buildtype']) ? $params['buildtype'] : '',
          !empty($params['decoration']) ? $params['decoration'] : 'отделка',
          $area
        );
        break;
      case "comrent":
        $title = sprintf('%s, район %s, метро %s, площадь в аренду %s м2, цена за м2: %s рублей',
          isset($params['objecttype']) ? $params['objecttype'] : '',
          $lot->district,
          $subway,
          $area,
          $price
        );
        $keywords = sprintf('%s, район %s, метро %s, площадь %s м2',
          isset($params['objecttype']) ? $params['objecttype'] : '',
          $lot->district,
          $subway,
          $area
        );
        $description = sprintf('%s, район %s, метро %s, %s, %s, площадь в аренду %s м2',
          isset($params['objecttype']) ? $params['objecttype'] : '',
          $lot->district,
          $subway,
          isset($params['buildtype']) ? $params['buildtype'] : '',
          !empty($params['decoration']) ? $params['decoration'] : 'отделка',
          $area
        );
        break;


      case "cottage":
        $title = sprintf('Коттедж, направление %s, площадь %s м2,  цена за месяц: %s рублей',
          $ward, $area, $price
        );
        $description = sprintf('Коттедж, направление %s, удаленность от МКАД %s, конструкция дома %s, площадь %s м2',
          $ward,
          isset($params['distance_mkad']) ? $params['distance_mkad'] : '',
          isset($params['construction']) ? $params['construction'] : '',
          $area
        );
        $keywords = sprintf('коттедж, направление %s, площадь %s м2,  цена за месяц: %s рублей',
          $ward, $area, $price
        );
        break;
      case "outoftown":
        $title = sprintf('Коттедж, направление %s, площадь %s м2,  цена: %s рублей',
          $ward, $area, $price_all
        );
        $description = sprintf('Коттедж, направление %s, удаленность от МКАД %s, конструкция дома %s, площадь %s м2',
          $ward,
          isset($params['distance_mkad']) ? $params['distance_mkad'] : '',
          isset($params['construction']) ? $params['construction'] : '',
          $area
        );
        $keywords = sprintf('коттедж, направление %s, площадь %s м2',
          $ward, $area
        );
        break;
      default:
        $title = $lot->name;
        $keywords = null;
        $description = null;
    }
    return array(
      'title'       => $title,
      'keywords'    => $keywords,
      'description' => $description
    );
  }
  
  protected static function getMetasFromLand(sfActions $action)
  {
    $response = $action->getResponse();
    $seo = SeoText::getAllSeo($action->getRequest()->getUri());
    if ($seo) {
        if ('' != $seo->name) {
          $response->addMeta('h1', $seo->name);
        }
        if ('' != $seo->title) {
          $response->addMeta('title', $seo->title);
        }
        if ('' != $seo->description) {
          $response->addMeta('description', $seo->description);
        }
        if ('' != $seo->keywords) {
          $response->addMeta('keywords', $seo->keywords);
        }
        return true;
    }
    return false;
  }
  
  public static function setDesctiption()
  {
    $route = sfContext::getInstance()->getRequest()->getPathInfo();
    $seo_text = Doctrine::getTable('SeoText')->findOneBy('url', $route);
    if(!$seo_text)
    {
      $seo_text = Doctrine::getTable('SeoText')->findOneBy('hrurl', $route);
    }
    $noindex = (int)sfContext::getInstance()->getRequest()->getParameter('page', 1) > 1;
    $html = null;
    if ($noindex) $html .= '<noindex>';
    if (!empty($seo_text)) $html .= '<div class="text_left_block">'.$seo_text->text.'</div>';
    if ($noindex) $html .= '</noindex>';   
    sfContext::getInstance()->getResponse()->setSlot('SeoText', $html);
  }
}