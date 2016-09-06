<?php

/**
 * menu components.
 *
 * @package    kre
 * @subpackage menu
 * @author     Garin Studio
 * @version    SVN: $Id: components.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $el
 */
class menuComponents extends sfComponents
{
  public function executeClaimForm()
  { }

  public function executeClaim()
  { }

  public function executeOrderCatalog()
  { }

  public function executeSearchForm()
  {
    $this->mode = $this->getContext()->getRequest()->getParameter('type') ? 'list' : 'homepage';
  }

  public function executeServices()
  {
    $service = Doctrine::getTable('Page')->findOneById(Page::SERVICE_ID);
    if ($service) {
      $this->services = Doctrine::getTable('Page')->findSubtreeLevel2($service);
    }
  }

  public function executeVisual(sfWebRequest $request)
  {
    $this->route = $this->getContext()->getRouting()->getCurrentRouteName();
    switch ($this->route) {
      case 'homepage':
        $this->class = 'homepage';
        break;
      case 'offers_list':
      case 'offers_list_preset':
      case 'offer':
      case 'offer_shortcut':
        $this->class = $request->getParameter('type', $this->getLotType('deafault'));
        break;

      case 'vacancies':
      case 'vacancy_page':
        $this->class = 'vacancies';
        break;

      default:
        $this->class = 'other';
    }
    sfConfig::set('header_class', $this->class);
  }

  public function executeLot(sfWebRequest $request)
  {
    $this->cur_type = $request->getParameter('type', $this->getLotType());
    $this->cur_pres = $request->getParameter('preset');
    $this->counters = Doctrine::getTable('Lot')->getCounters($this->cur_type);
    $this->types    = Lot::$_types;
    $this->nb_show  = array(
      'eliteflat' => 6,
      'elitenew'  => 6,
      'penthouse' => 6,
      'flatrent'  => 6,
      'outoftown' => 6,
      'cottage'   => 6,
    );
  }

  public function executeMain(sfWebRequest $request)
  {
    $this->menu = array();
    $current_page = sfConfig::get('current_page', $this->getContext()->getRouting()->getCurrentRouteName());
    $pages = Doctrine::getTable('Page')->getMainPages();

    $this->menu[] = array(
      'uri'       => '@homepage',
      'name'      => 'main_page',
      'is_active' => $current_page == 'homepage' ,
    );

    foreach ($pages as $page) {
      $this->menu[] = array(
        'uri'       => '@'.$page->getRoute(),
        'name'      => $page->name,
        'is_active' => $this->isActive($current_page, $page),
      );
    }

    $offers_item = array(
      'uri'       => '@offers',
      'name'      => 'Предложения',
      'is_active' => $this->isActive($current_page, 'offer'),
    );
    $menu_head  = array_slice($this->menu, 0, 2);
    $menu_tail  = array_slice($this->menu, 2);
    $this->menu = array_merge($menu_head, array($offers_item), $menu_tail);
  }

  public function executePage(sfWebRequest $request)
  {
    $this->menu = array();
    $current_page = sfConfig::get('current_page');
    $page = Doctrine::getTable('Page')->getFirstLevelPage($current_page);

    $subpage = null;
    if ($page->id == Page::NEWS_PARENT_ID || $page->id == Page::REVIEW_PARENT_ID) {
      $subpage_id = $page->id == Page::NEWS_PARENT_ID ? Page::NEWS_ID : Page::REVIEW_ID;
      $subpage = Doctrine::getTable('Page')->findOneBy('id', $subpage_id);
    }

    if ($route = $page->getRoute()) {
      $route = '@'.$route;
    }
    else {
      $route = $request->hasParameter('elem') ? '@'.$request->getParameter('elem') : null;
    }    
    if ($tree = Doctrine::getTable('Page')->findSubtree($page, $subpage, true)) {
      foreach ($tree as $child) {
        
        $this->menu[] = array(
          'uri'       => $route.'?id='.$child->id,
          'name'      => $child->name,
          'is_current'=> $this->isCurrent($current_page, $child),
          'is_active' => $this->isActive($current_page, $child),
          'level'     => $child->level-2,
        );
        
        if ($child->isNews() || $child->isReview()) {
          $subroute = $child->isNews() ? '@news_archive' : '@review_archive';
          $jahre = $request->getParameter('year', date('Y'));
          $this->menu[count($this->menu)-1]['uri'] = $child->isReview() ? $route : $subroute;
         
          $have_years = false;
          if (in_array($current_page->id, array(Page::NEWS_ID, Page::REVIEW_ID)) || 
              in_array($current_page->getNode()->getParent()->id, array(Page::NEWS_ID, Page::REVIEW_ID)))
          { 
            $have_years = true;
          }
          if ($have_years){
            foreach (Doctrine::getTable('Page')->getChildrenYears($subpage) as $year) {
              $this->menu[] = array(
                'uri'       => $year == date('Y') && $child->isReview() ? $route : $subroute.'?year='.$year,
                'name'      => $year,
                'is_current'=> $jahre == $year && $this->isCurrent($current_page, $child),
                'is_active' => $this->isActive($current_page, $child, $year),
                'level'     => 1,
              );
            }
          }
        }
      }
    }
    foreach ($this->menu as $key => &$value)
    {
      if($value['uri'] =='@services?id=2135'){
        
        $value['uri'] = '@services';
      }
      if($value['uri'] =='@advices?id=17'){
        
        $value['uri'] = '@advices';
      }
      
    }
  }

  public function executeVacancy()
  {
    $this->menu = array();
    $this->submenu = array();
    $current_page = sfConfig::get('current_page');
    $page = Doctrine::getTable('Page')->getFirstLevelPage($current_page);

    if ($tree = Doctrine::getTable('Page')->findSubtree($page)) {
      foreach ($tree as $child) {
        if ($child->isVacancyType()) {
          $this->submenu[] = array(
            'uri'       => '@vacancies?type='.$child->vacancy_type,
            'name'      => $child->name,
            'is_current'=> $this->isCurrent($current_page, $child),
            'is_active' => $this->isActive($current_page, $child),
          );
        }
        else {
          $this->menu[] = array(
            'uri'       => '@vacancy_page?id='.$child->id,
            'name'      => $child->name,
            'is_current'=> $this->isCurrent($current_page, $child),
            'is_active' => $this->isActive($current_page, $child),
            'level'     => $child->level-2,
          );
        }
      }
    }
  }

  public function executeBreadcrumbs()
  {
    $this->crumbs = array();
    $this->current_page = sfConfig::get('current_page');

    if ($this->current_page instanceof Page) {
      $this->crumbs = $this->getPageBreadcrumbs();
    }
    elseif ($this->current_page instanceof Lot) {
      $this->crumbs = $this->getLotBreadcrumbs();
    }
    else {
      $this->crumbs = $this->getRouteBreadcrumbs();
    }
  }

  public function executeCsstat()
  {
    $credentials = $this->getUser()->getCredentials();
    $this->menu = sfConfig::get('app_csstat_menu_' . $credentials[0]);
    $this->current = $this->getContext()->getRequest()->getParameter('action');
  }

  private function getPageBreadcrumbs()
  {
    $crumbs = array();

    if ($tree = Doctrine::getTable('Page')->findAscendants($this->current_page)) {
      foreach ($tree as $child) {
        if ($child->getRoute()) {
          $route_name = $child->getRoute();
        }
        var_dump($route_name);
        $crumbs[] = array(
          'uri'   => $this->getUri($child, $route_name == 'news' ? 'news_archive' : $route_name),
          'name'  => $child->name,
        );        
        if ($child->isNews() || $child->isReview()) {
          $year = date('Y', strtotime($this->current_page->created_at));
          if ($child->isReview() && date('Y') == $year){
           $uri = 'analytics';
          }else{
            $uri = false;
          }
          $crumbs[] = array(
            'uri'   => $uri ? $uri : ($child->isNews() ? '@news_archive' : '@review_archive').'?year='.$year,
            'name'  => $year,
          );
        }
      }
    }

    if ($this->current_page->isNews() || $this->current_page->isReview()) {
      $crumbs[] = array(
        'uri'   => $this->current_page->isNews() ? '@news_archive' : $this->current_page->getRoute(),
        'name'  => $this->current_page->name,
      );
      $this->current_page = array('name' => $this->getRequest()->getParameter('year', date('Y')));
    }

    return $crumbs;
  }

  private function getLotBreadcrumbs()
  {
    $type = $this->getRequest()->getParameter('type', $this->getLotType());

    return $crumbs = array(
      array(
        'uri'   => '@offers',
        'name'  => 'Предложения',
      ),
      array(
        'uri'   => '@offers_list?type='.$type,
        'name'  => Lot::$_types[$type],
      ),
    );
  }

  private function getRouteBreadcrumbs()
  {
    $crumbs = array();
    $route  = $this->getContext()->getRouting()->getCurrentRouteName();
    $type   = $this->getRequest()->getParameter('type', $this->getLotType());

    if ($route == 'offers_list' || $route == 'offers_list_preset') {
      $crumbs[] = array(
        'uri'   => '@offers',
        'name'  => 'Предложения',
      );
      if ($preset = $this->getRequest()->getParameter('preset')) {
        $crumbs[] = array(
          'uri'   => '@offers_list?type=' . $type,
          'name'  => Lot::$_types[$type],
        );
        $name = Tools::getValueOfPreset($preset);
        $this->current_page = array('name' => !empty($name) ? $name : null);
      }
      else {
        $this->current_page = array('name' => Lot::$_types[$type]);
      }
    }
    elseif ($route == 'claim') {
      $this->current_page = array('name' => 'Оставить заявку');
    }
    elseif ($route == 'offer_search') {
      $this->current_page = array('name' => 'Поиск');
    }
    elseif ($route == 'ordercatalog') {
      $this->current_page = array('name' => 'Заказать каталог элитной недвижимости');
    }
    elseif ($route == 'sitemap') {
      $this->current_page = array('name' => 'Карта сайта');
    }
    else {
      $this->current_page = array('name' => 'I am lost');
    }

    return $crumbs;
  }

  private function isCurrent($current, $page, $year = null)
  {
    if (!empty($year)) {
      return ($current->isEqualTo($page) && date('Y', strtotime($current->created_at)) == $year);
    }

    if ($current->isEqualTo($page)) return true;
  }

  private function isActive($current, $page, $year = null)
  {
    if (!is_object($current) && is_string($page)) {
      return mb_stripos($current, $page) !== false;
    }
    elseif ($current instanceof Page && $page instanceof Page) {
      if (!empty($year) && $current->level > 2) {
        return ($current->isDescendantOfOrEqualTo($page) && date('Y', strtotime($current->created_at)) == $year);
      }
      elseif ($current->level == 1) {
        return $current->isDescendantOfOrEqualTo($page);
      }
      else {
        return $current->isDescendantOf($page);
      }
    }
    elseif ($current instanceof Lot) {
      if ($page == 'offer') return false;
    }

    return false;
  }

  private function getUri(Page $page, $route_name = null)
  {
    if (!$route_name) {
      if (!$route_name = $page->getRoute()) return null;
    }

    if ($page->level == 1 || $page->isNews() || $page->isReview()) {
      return '@'.$route_name;
    }

    $routes = $this->getContext()->getRouting()->getRoutes();
    $params = array();
    foreach (array_keys($routes[$route_name]->getVariables()) as $key) {
      $params[$key] = $page->$key;
    }

    return '@'.$route_name.(!empty($params) ? '?'.http_build_query($params) : '');
  }

  protected function getLotType($default = false)
  {
    return sfConfig::get('current_page') instanceof Lot
      ? sfConfig::get('current_page')->type
      : ('offer_shortcut' == $this->getContext()->getRouting()->getCurrentRouteName()
        ? Doctrine::getTable('Lot')
          ->findOneBy('shortcut', $this->getRequest()->getParameter('shortcut'))
          ->type
        : $default
      );
  }
}
