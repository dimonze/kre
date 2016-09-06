<?php

/**
 * lot actions.
 *
 * @package    kre
 * @subpackage lot
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class lotActions extends sfActions
{
  public function postExecute()
  {
    sfConfig::set('print_version', sfContext::getInstance()->getRequest()->hasParameter('print'));
    MetaParse::setMetas($this);
  }

  public function executeMain(sfWebRequest $request)
  {
    sfConfig::set('body_class', 'textpage');
    $this->counters = Doctrine::getTable('Lot')->getCounters();
    $objects = Doctrine::getTable('MainOffer')->findAll();
    $offers = array();
    foreach($objects as $obj) {
      $offers[] = $obj;
    }
    usort($offers, 'Lot::sortByTypes');
    $this->offers = $offers;
  }

  public function executeList(sfWebRequest $request)
  {
    $type = $this->type = $request->getParameter('type');

    $this->forward404Unless(in_array($type, array_keys(Lot::$_types)));
    //$this->forward404Unless(Lot::isParamsExist($request->getGetParameters()) == -1);

    if ($this->needRedirect = SeoText::checkLandRedirect($request)) {
      $this->redirect($this->needRedirect, 301);
    }
    if ($request->hasParameter('preset')) {
      $this->arrayOfParams = SeoText::getAllParams(sfContext::getInstance()->getRequest()->getPathInfo());
      if ($this->arrayOfParams){
        foreach($this->arrayOfParams as $key => $value)
        {
          $request->setParameter($key, $value);
        }
      }
      $this->forward404Unless($this->arrayOfParams);
    }

    $params = $request->getParameterHolder()->getAll();

    if(!empty($params['slink'])) {
      $params += Tools::$slink[$params['slink']];
      unset($params['slink']);
    }

    if (!empty($params['autocomplete_city']) && empty($params['city'])) {
      $params['city'] = $params['autocomplete_city'];
    }
    if (!empty($params['autocomplete_street']) && empty($params['street'])) {
      $params['street'] = $params['autocomplete_street'];
    }
    if (!empty($params['autocomplete_cottageVillage']) && empty($params['cottageVillage'])) {
      $params['cottageVillage'] = $params['autocomplete_cottageVillage'];
    }
    if (!empty($params['autocomplete_estate']) && empty($params['estate'])) {
      $params['estate'] = $params['autocomplete_estate'];
    }

    $this->form = BaseSearchForm::getInstance($type);
    $this->form->bind($params);

    $query_params = $this->form->getValues();
    if (!empty($params['under_construction'])) {
      $query_params['under_construction'] = 'Строящийся';
    }
    if (!empty($query_params['street'])) {
      $signs_del = array('.', ',');
      $query_params['street'] = str_replace($signs_del, '', $query_params['street']);
    }


    if (!empty($params['lots'])) {
      $query = Doctrine::getTable('Lot')->getLotsFilteredQuery($type, array('lots' => $params['lots']));
    }
    else {
      $query = Doctrine::getTable('Lot')->getLotsFilteredQuery($type, $query_params);
    }

    $query->ratedSort($request->getParameter('by', 'rating'), $request->getParameter('dir', 'asc'));
    $this->per_page = $request->getParameter('per_page', 20);

    $this->pager = new sfDoctrinePager('Lot', $this->per_page);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->setQuery($query);
    $this->pager->init();
    $this->results = $this->pager->getResults();

    Lot::fillLotsWithBrokers($this->results);
    Lot::fillLotsWithParams($this->results);
    Lot::fillLotsWithParents($this->results, $request->hasParameter('cottageVillage'));

    if (!$request->hasParameter('page') && $request->hasParameter('price_from')) {
      Query::log($this->form->getValues(), $request->getParameter('type'));
    }
  }

  public function executeShow(sfWebRequest $request)
  {
    if($request->getPathInfo() == '/offers/flatrent/arenda_zhk_vorobevy_gory/74/')
      {
        $this->redirect('/offers/flatrent/vorobyovy_gory/', 301);
      }

    $this->route = $request->getPathInfo();
    $this->lot = $this->getRoute()->getObject();
    if ($this->lot->shortcut && !$request->hasParameter('shortcut')) {
      $this->redirect($this->getContext()->getRouting()->generate('offer_shortcut', $this->lot), 301);
    }
    sfConfig::set('current_page', $this->lot);

    if ($request->getRemoteAddress() != '195.210.139.130' && $request->getReferer() == $this->getContext()->getRouting()->generate('homepage', array(), true)) {
      Click::log($this->lot->name);
    }

    $this->lots_alike = $this->fetchLotsAlike($this->lot);
    Lot::fillLotsWithParents($this->lots_alike);
    Lot::fillLotsWithParams($this->lots_alike);
  }

  public function executeSearch(sfWebRequest $request)
  {
    if ($this->needRedirect = SeoText::checkLandRedirect($request)) {
      $this->redirect($this->needRedirect, 301);
    }
    if ($request->hasParameter('landing')) {
      $this->arrayOfParams = SeoText::getAllParams($request->getParameter('landing'));
      foreach($this->arrayOfParams as $key => $value)
      {
        $request->setParameter($key, $value);
      }
    }
    if ('lot' == $request->getParameter('field')) {
      $lot_id = $request->getParameter('value');
      $object = ctype_digit($lot_id)
        ? Doctrine::getTable('Lot')->find($lot_id)
        : false;

      $this->setLayout(false);
      $route = $object->shortcut ? 'offer_shortcut' : 'offer';
      return $this->renderText(json_encode(array(
        'url' => false !== $object && ($object->status == 'active' || ($object->status == 'hidden' && Broker::isAuth()))
          ? $this->getContext()->getRouting()->generate($route, $object)
          : false
      )));
    }

    $query = Doctrine::getTable('Lot')->createQuery('l')
      ->joinParams()
      ->active()
      ->filter(array($request->getParameter('field') => $request->getParameter('value')))
      ->ratedSort($request->getParameter('by', 'rating'), $request->getParameter('dir', 'asc'));

    if ($ids = preg_split('/,/', $request->getParameter('ids'), null, PREG_SPLIT_NO_EMPTY)) {
      $query->andWhereIn('l.id', $ids);
    }

    $cnt_query = clone $query;
    $cnt_query
      ->select('type, count(distinct l.id) cnt')
      ->addSelectActualType()
      ->groupByActualType()
      ->orderBy('l.id');
    $this->counts = $cnt_query->fetchArray();


    $filter_types = $request->getParameter('types', array());
    if ($filter_types && count($filter_types) != count($this->counts)) {
      $query->type($filter_types);
    }


    $this->per_page = $request->getParameter('per_page', 10);

    $this->pager = new sfDoctrinePager('Lot', $this->per_page);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->setQuery($query);
    $this->pager->init();

    $this->results = $this->pager->getResults();

    Lot::fillLotsWithBrokers($this->results);
    Lot::fillLotsWithParents($this->results);
    Lot::fillLotsWithParams($this->results);
  }

  public function executeHideParent(sfWebRequest $request)
  {
    $this->forward404Unless($request->isXmlHttpRequest());
    $this->lot = Doctrine::getTable('Lot')->find($request->getParameter('id'));

    return $this->renderPartial('single-item', array(
          'lot'   => $this->lot,
          'mode'  => 'supobject',
          'acts'  => false,
          'isparent' => true,
    ));
  }

  public function executeCalc(sfWebRequest $request)
  {
    $this->sum = $request->getParameter('sum');
    $this->setLayout(false);
  }

  public function executePresentation(sfWebRequest $request)
  {
    $this->is_auth = $this->getUser()->isAuthenticated();
    $this->lot = $this->getRoute()->getObject();

    $params = $this->lot->getParamsGrouppedFiltered($this->lot->is_commercial_type ? false : true);
    $p_params = $this->lot->is_child
      ? $this->lot->Parent->getParamsGrouppedFiltered($this->lot->Parent->is_commercial_type ? false : true)
      : null;

    if($p_params) {
      foreach($params['both'] as $group=>$values) {
        foreach($values as $name=>$value) {
          if(!empty($p_params['supobject'][$group][$name])){
            $params['both'][$group][$name] = $p_params['supobject'][$group][$name];
          }
        }
      }
    }
    $this->params_groupped = $params;
    $this->forward404Unless($this->lot);
  }

  public function executeImages(sfWebRequest $request)
  {
    $this->is_auth = $this->getUser()->isAuthenticated();
    $this->lot = $this->getRoute()->getObject();
    $this->forward404Unless($this->lot);
  }

  public function executePdf(sfWebRequest $request)
  {
    preg_match('/<!-- phone:"(.*?)" -->/i', $request->getParameter('html'), $phone);

    $html = sprintf('<html>%s</html>', preg_replace_callback('/<.+>/U', function($matches) {
      return preg_replace('/([a-z])=([^"\'\s>]+)/i', '\1="\2"', $matches[0]);
    }, $request->getParameter('html')));

    $headers = $request->getParameter('headers', true) == 'true';

    if (!is_dir(sfConfig::get('sf_upload_dir') . '/pdf')){
      mkdir(sfConfig::get('sf_upload_dir') . '/pdf', 0777);
    }
    $path = sfConfig::get('sf_upload_dir') . '/pdf/' . microtime(true) . '.pdf';
    $file = $request->getUriPrefix() . str_replace(sfConfig::get('sf_web_dir'), '', $path);

    $margins = array();
    $margins['bottom'] = $headers ? -10 : 10;

    $mpdf = new mPDF(
      'utf-8-s', // mode     (Default: "")
      'A4',      // format            (A4)
      '',        // default_font_size ("")
      '',        // default_font      ("")
      // Content margins
      15,        // margin_left       (15)
      15,        // margin_right      (15)
      10,        // margin_top        (16)
      $margins['bottom'],          // (16)

      // Header/footer margins
      5,         // margin_header      (6)
      5          // margin_footer      (6)
    );
    $mpdf->SetDefaultFont('DejaVuSansCondensed');
    $mpdf->SetDefaultFontSize('10');
    $mpdf->ignore_invalid_utf8 = true;
    $mpdf->allow_charset_conversion = true;
    $mpdf->allow_html_optional_endtags = false;
    $mpdf->SetDisplayMode('fullpage','two');
    $mpdf->use_kwt = true;
    if ($headers) {
      $mpdf->SetHTMLHeader($this->getPartial('pdf-top', array('phone' => $phone[1])));
      $mpdf->SetHTMLFooter($this->getPartial('pdf-bottom', array('lot_id' => $request->getParameter('id'))));
    }
    $mpdf->AddPage();
    $mpdf->WriteHTML($html);
    $mpdf->Output($path,'F');


    $this->setLayout(false);
    return $this->renderText($file);
  }

  public function executeSavePdf(sfWebRequest $request)
  {
    $pdf = $request->getParameter('pdf');
    $path = sfConfig::get('sf_upload_dir') . '/pdf/' . $pdf;

    $response = $this->getResponse();
    $response->clearHttpHeaders();
    $response->setContentType('application/pdf');
    $response->setHttpHeader('Content-Disposition', 'attachment; filename="'.$pdf.'"');
    $response->setHttpHeader('Content-Description', 'File Transfer');
    $response->setHttpHeader('Content-Transfer-Encoding', 'binary');
    $response->setHttpHeader('Content-Length', filesize($path));
    $response->setHttpHeader('Cache-Control', 'public, must-revalidate');
    $response->setHttpHeader('Pragma', 'public');
    $response->sendHttpHeaders();
    readfile($path);

    return sfView::NONE;
  }

  public function executeRedirectId(sfWebRequest $request)
  {
    if ($id = LotTable::getNewId($request->getParameter('type'), $request->getParameter('id'))) {
      $url = $this->generateUrl('offer', array(
        'type'   => $request->getParameter('type'),
        'id'     => $id,
      ));
    }
    else {
      $url = $this->generateUrl('offers');
    }

    $this->redirect($url);
  }


  private function fetchLotsAlike($lot)
  {
    $params = array();

    if ((int)$lot->price_all_from > 0 && !$lot->is_commercial_type) {
      $price = array(
        'value_from'  => (int)$lot->price_all_from,
        'value_to'    => ($lot->price_all_to > 0 ? (int)$lot->price_all_to : (int)$lot->price_all_from),
      );
    }
    else {
      $price = array(
        'value_from'  => (int)$lot->price_from,
        'value_to'    => ($lot->price_to > 0 ? (int)$lot->price_to : (int)$lot->price_from),
      );
    }

    if ((int)$lot->area_to > 0) {
      $area = array(
        'area_from' => (int)$lot->area_from,
        'area_to'   => (int)$lot->area_to,
      );
    }
    else {
      $area = array(
        'area_from' => (int)$lot->area_from,
        'area_to'   => (int)$lot->area_from,
      );
    }

    switch ($lot->type) {
      case 'penthouse':
      case 'eliteflat':
      case 'elitenew':
      case 'flatrent':
        if (!empty($lot->lng) && !empty($lot->lat)) {
          $params['lat'] = $lot->lat;
          $params['lng'] = $lot->lng;
          $params['radius'] = 1;
        }

        $params['price_from'] = round($price['value_from'] * 0.9);
        $params['price_to']   = round($price['value_to']   * 1.1);
        $params['area_from']  = round($area['area_from'] * 0.9);
        $params['area_to']    = round($area['area_to']   * 1.1);
        break;

      case 'outoftown':
      case 'cottage':
        if (!empty($lot->ward) || !empty($lot->ward2)) {
          $params['distance_mkad_from'] = $lot->params['distance_mkad']-5;
          $params['distance_mkad_to']   = $lot->params['distance_mkad']+5;
          $params['wards'] = array_filter(array($lot->ward, $lot->ward2));
        }
        if (!empty($lot->params['objecttype']) && $lot->params['objecttype'] == 'Участок') {
          $params['spaceplot_from'] = round($lot->params['spaceplot'] * 0.9);
          $params['spaceplot_to']   = round($lot->params['spaceplot']   * 1.1);
        }
        else {
          $params['area_from'] = round($area['area_from'] * 0.9);
          $params['area_to']   = round($area['area_to']   * 1.1);
        }
        if (!empty($lot->params['objecttype'])) {
          $params['objecttype'] = array($lot->params['objecttype']);
        }

        $params['price_from'] = round($price['value_from'] * 0.9);
        $params['price_to']   = round($price['value_to']   * 1.1);
        break;

      case 'comsell':
      case 'comrent':
        if (!empty($lot->params['objecttype'])) {
          $params['objecttype'] = array($lot->params['objecttype']);
        }

        $params['price_from'] = round($price['value_from'] * 0.8);
        $params['price_to']   = round($price['value_to']   * 1.2);
        $params['area_from']  = round($area['area_from'] * 0.8);
        $params['area_to']    = round($area['area_to']   * 1.2);

        //потому что тупо сделан в LotQuery фильтр по price! он проверяет значения из request
        $this->getRequest()->setParameter('type', $lot->type);
        $this->getRequest()->setParameter('action', 'list');
        break;
    }

    $params['type'] = $lot->type;
    $params['currency'] = $lot->currency;
    $params['exclude']  = $lot->id;

    $form = BaseSearchForm::getInstance($params['type']);
    $form->bind($params);
    $filter_params = $form->getValues();

    $query = Doctrine::getTable('Lot')->getLotsFilteredQuery($params['type'], $filter_params);

    if ($lot->is_city_type && $query->count() < 2) {
      if (!empty($params['radius'])) {
        $filter_params['radius'] = 1.5;
      }

      $filter_params['price_from'] = round($price['value_from'] * 0.85);
      $filter_params['price_to']   = round($price['value_to']   * 1.15);
      $filter_params['area_from']  = round($area['area_from'] * 0.85);
      $filter_params['area_to']    = round($area['area_to']   * 1.15);

      $query = Doctrine::getTable('Lot')->getLotsFilteredQuery($params['type'], $filter_params);
    }


    if (!empty($filter_params['objecttype'])) {
      foreach ($filter_params['objecttype'] as $i => $objecttype) {
        $filter_params[sprintf('objecttype[%d]', $i)] = $objecttype;
      }
    }
    if (!empty($filter_params['wards'])) {
      foreach ($filter_params['wards'] as $i => $ward) {
        $filter_params[sprintf('wards[%d]', $i)] = $ward;
      }
    }
    if (!empty($filter_params['lat']) && !empty($filter_params['lng'])) {
      $filter_params['lat'] = str_replace(',', '.', strval($filter_params['lat']));
      $filter_params['lng'] = str_replace(',', '.', strval($filter_params['lng']));
    }
    if (!empty($filter_params['radius'])) {
      $filter_params['radius'] = str_replace(',', '.', strval($filter_params['radius']));
    }

    $filter_params['type'] = $params['type'];
    $filter_params = array_filter($filter_params);
    $this->_params = $filter_params;

    return $query->execute();
  }
}
