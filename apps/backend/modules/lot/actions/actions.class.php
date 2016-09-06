<?php

require_once dirname(__FILE__).'/../lib/lotGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/lotGeneratorHelper.class.php';

/**
 * lot actions.
 *
 * @package    kre
 * @subpackage lot
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class lotActions extends autoLotActions
{
  public function executeShow(sfWebRequest $request)
  {
    $this->forward('lot', 'edit');
  }

  public function executeFrontendShow(sfWebRequest $request)
  {
    $object = $this->getRoute()->getObject();
    $this->forward404Unless($object);

    $url = Tools::getFrontendContext()->getRouting()->generate('offer', $object, true);
    $this->redirect($url);
  }

  public function executePhotoPromote(sfWebRequest $request)
  {
    if ($request->hasParameter('lot_id') && $request->hasParameter('id')) {
      $photo = Doctrine::getTable('Photo')->findOneBy('id', $request->getParameter('id'));
      if ($photo) $photo->promote();
    }

    return $this->renderComponent('lot', 'photos', array('lot_id' => $request->getParameter('lot_id')));
  }

  public function executePhotoDemote(sfWebRequest $request)
  {
    if ($request->hasParameter('lot_id') && $request->hasParameter('id')) {
      $photo = Doctrine::getTable('Photo')->findOneBy('id', $request->getParameter('id'));
      if ($photo) $photo->demote();
    }

    return $this->renderComponent('lot', 'photos', array('lot_id' => $request->getParameter('lot_id')));
  }

  public function executeParamPromote(sfWebRequest $request)
  {
    if ($request->hasParameter('param_id') && $request->hasParameter('lot_id')) {
      $param = Doctrine::getTable('LotParam')->findOneByParamIdAndLotId($request->getParameter('param_id'), $request->getParameter('lot_id'));
      if ($param) $param->promote();
    }

    return $this->renderComponent('lot', 'params', array('lot_id' => $request->getParameter('lot_id')));
  }

  public function executeParamDemote(sfWebRequest $request)
  {
    if ($request->hasParameter('param_id') && $request->hasParameter('lot_id')) {
      $param = Doctrine::getTable('LotParam')->findOneByParamIdAndLotId($request->getParameter('param_id'), $request->getParameter('lot_id'));
      if ($param) $param->demote();
    }

    return $this->renderComponent('lot', 'params', array('lot_id' => $request->getParameter('lot_id')));
  }

  public function executeParamDelete(sfWebRequest $request)
  {
    if ($request->hasParameter('param_id') && $request->hasParameter('lot_id')) {
      $param = Doctrine::getTable('LotParam')->findOneByParamIdAndLotId($request->getParameter('param_id'), $request->getParameter('lot_id'));
      if ($param) $param->delete();
    }

    return $this->renderComponent('lot', 'params', array('lot_id' => $request->getParameter('lot_id')));
  }

  public function executeMatchParam(sfWebRequest $request)
  {
    return $this->match($request->getParameter('q', ''), 'Param');
  }

  public function executeMatchPhotoType(sfWebRequest $request)
  {
    return $this->match($request->getParameter('q', ''), 'PhotoType');
  }

  public function executeParamsForm(sfWebRequest $request)
  {
    if (!$request->hasParameter('type')) {
      return $this->renderText(json_encode(array('error' => 'not all data')));
    }

    $this->type = $request->getParameter('type');
    if (empty(Lot::$_types[$this->type])) {
      return $this->renderText(json_encode(array('error'  => 'what a type')));
    }

    if ($id = $request->getParameter('id')) {
      $lot = Doctrine::getTable('Lot')->find($id);
    }
    else {
      $lot = new Lot();
    }
    $this->form = new LotParamsForm(array(), array('type' => $this->type));
    if($lot->id) {
      $params = $lot->params;
      foreach (array('about_decoration', 'decoration', 'infra_parking', 'territory') as $field) {
        if (isset($params[$field])) {
          if (is_array($params[$field])) {
            $params[$field] = array_map('mb_strtolower', $params[$field]);
            $params[$field] = implode(', ', $params[$field]);
          }
          else {
            $params[$field] = mb_strtolower($params[$field]);
          }
        }
      }
      $this->form->setDefaults($params);
    }
    $this->setLayout(false);
  }

  public function executePidsValues(sfWebRequest $request)
  {
    if (!$request->hasParameter('type')) {
      return $this->renderText(json_encode(array('error' => 'not all data')));
    }

    $text = '<option value="" selected="selected"></option>' . PHP_EOL;
    $type = Lot::$_subobjectTypeShift[$request->getParameter('type')];
    $query = Doctrine::getTable('Lot')->getSupLotsQuery($type);

    foreach ($query->execute() as $lot) {
      $text .= sprintf('<option value="%d">%s</option>', $lot->id, $lot->name_with_id) . PHP_EOL;
    }

    return $this->renderText($text);
  }

//  public function executeExportParams(sfWebRequest $request)
//  {
//    $map = Param::$_map;
//    $fh = fopen(sfConfig::get('sf_data_dir') . '/type-params.csv', 'a');
//    foreach ($map as $type => $groups) {
////      echo '"' . Lot::$_types[$type] . '"<br>';
//      fwrite($fh, '"' . iconv('UTF8', 'CP1251', Lot::$_types[$type]) . '"' . PHP_EOL);
//      foreach ($groups as $group => $fields) {
////        echo $group . "<br>";
////        echo '"", "'. Param::$_types[$group] . '"<br>';
//        fwrite($fh, '"", "'. iconv('UTF8', 'CP1251', Param::$_types[$group]) . '"' . PHP_EOL);
//        foreach ($fields as $field) {
////           echo '"", "","' . $field['name'] . '"<br>';
//          fwrite($fh, '"", "","' . iconv('UTF8', 'CP1251', $field['name']) . '"'  . PHP_EOL);
//        }
//      }
//    }
//    fclose($fh);
//    $this->setLayout(false);
//    $this->setTemplate(false);
//  }

  public function executeGeocode(sfWebRequest $request)
  {
    $data = array_fill_keys(array_keys(Param::$_addressStructure), '');
    unset($data['string']);
    $_content = curl_init('http://geocode-maps.yandex.ru/1.x/?results=3&format=json&geocode=' . $request->getParameter('coords'));
    curl_setopt($_content, CURLOPT_RETURNTRANSFER, 1);
    $answer = curl_exec($_content);
    curl_close($_content);

    $geocode = json_decode($answer);
    $details = $geocode->response->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData->AddressDetails;
    if ($request->hasParameter('kind')) {
      $depLocalName = $geocode->response->GeoObjectCollection->featureMember[2]->GeoObject->metaDataProperty->GeocoderMetaData->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality;
    }
    $path = $details->Country;
    if(!empty($path->AdministrativeArea)){
      $path = $path->AdministrativeArea;
      $data['region'] = $path->AdministrativeAreaName;
    }

    if(!empty($path->SubAdministrativeArea)){
      $path = $path->SubAdministrativeArea;
      $data['district'] = $path->SubAdministrativeAreaName;
    }

    if(!empty($depLocalName->DependentLocality) && $request->hasParameter('kind')){
      $path = $depLocalName->DependentLocality;
      $data['kind'] = $path->DependentLocalityName;
    }

    $data['city']   = $path->Locality->LocalityName . '';
    $data['street'] = $path->Locality->Thoroughfare->ThoroughfareName . '';

    $house = $path->Locality->Thoroughfare->Premise->PremiseNumber;

    $house = explode('с', $house);
    $data['construction'] = !empty($house[1]) ? $house[1] : '' ;
    $house = $house[0];

    $house = explode('к', $house);
    $data['building'] = !empty($house[1]) ? $house[1] : '' ;
    $house = $house[0];

    $data['house'] = $house;

    return $this->renderText(json_encode($data));
    $this->setLayout(false);
  }

  public function executeCheckShortcut(sfWebRequest $request)
  {
    $data = array('value' => Tools::slugify($request->getParameter('shortcut')));
    $lot = Doctrine::getTable('Lot')->findOneBy('shortcut', $data['value']);
    $data['unique'] = '' == $data['value'] || !$lot || $lot->id == $request->getParameter('id');
    return $this->renderText(json_encode($data));
    $this->setLayout(false);
  }

  public function executeChangeStatus(sfWebRequest $request)
  {
    if ($request->hasParameter('id') && in_array($request->getParameter('status'), array_keys(Lot::$_status))) {
      $object = Doctrine::getTable('Lot')->find($request->getParameter('id'));

      if ($object) {
        $object->status = $request->getParameter('status');
        $object->save();
      }
    }

    return sfView::NONE;
  }

  public function executeBatchActivate(sfWebRequest $request)
  {
    $this->batchChangeStatus($request->getParameter('ids'), 'active');
    $this->redirect('@lot');
  }

  public function executeBatchDeactivate(sfWebRequest $request)
  {
    $this->batchChangeStatus($request->getParameter('ids'), 'inactive');
    $this->redirect('@lot');
  }

  public function executeBatchHide(sfWebRequest $request)
  {
    $this->batchChangeStatus($request->getParameter('ids'), 'hidden');
    $this->redirect('@lot');
  }


  protected function executeBatchDelete(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');

    if ($ids) {
      $q = Doctrine_Query::create()
              ->from('Lot')
              ->whereIn('id', $ids);

      $count = 0;
      foreach ($q->execute() as $object) {
        $count += (int) $object->delete();
      }

      if ($count >= count($ids)) {
        $this->getUser()->setFlash('notice', 'The selected items have been deleted successfully.');
      }
      else {
        $this->getUser()->setFlash('error', 'A problem occurs when deleting the selected items.');
      }
    }

    $this->redirect('@lot');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    if (($values = $request->getParameter($form->getName())) && ($params = $request->getParameter('LotParams'))) {
      $values['params'] = $params;
      $request->setParameter($form->getName(), $values);
    }

    parent::processForm($request, $form);
  }

  private function match($q, $model)
  {
    if (empty($q) || mb_strlen($q) < 3) return $this->renderText(json_encode(array()));

    $data = array();
    foreach (Doctrine::getTable($model)->match($q) as $item) {
      $data[$item['id']] = $item['name'];
    }

    return $this->renderText(json_encode($data));
  }

  private function batchChangeStatus(array $ids, $status)
  {
    if ($ids) {
      $q = Doctrine_Query::create()
              ->from('Lot')
              ->whereIn('id', $ids)
              ->orWhereIn('pid', $ids);

      $count = 0;
      foreach ($q->execute() as $object) {
        $object->status = $status;
        $object->save();
        $count++;
      }

      if ($count >= count($ids)) {
        $this->getUser()->setFlash('notice', 'The selected items have been changed successfully.');
      }
      else {
        $this->getUser()->setFlash('error', 'A problem occurs when changing some items.');
      }
    }
  }

  public function executeUpdate(sfWebRequest $request)
  {
    parent::executeUpdate($request);
    if($id = sfContext::getInstance()->getRequest()->getCookie('please_redirect_me_to')) {
      die('222');
      sfContext::getInstance()->getContext()->getResponse()->setCookie('please_redirect_me_to', false);
      $this->redirect(sprintf('%s/lot/%d/edit#', $_SERVER['SCRIPT_NAME'], $id));
    }
  }

  public function executeEdit(sfWebRequest $request)
  {
    if($id = sfContext::getInstance()->getRequest()->getCookie('please_redirect_me_to')) {
      sfContext::getInstance()->getResponse()->setCookie('please_redirect_me_to', false);
      $this->redirect(sprintf('%s/lot/%d/edit#', $_SERVER['SCRIPT_NAME'], $id));
    }
    parent::executeEdit($request);
  }

  public function executeTbaList(sfWebRequest $request)
  {
    $q = $request->getParameter('q');
    $this->_settlements = array();
    $url = 'http://topba.ru/assets/directory.php?type=settlements';
    $_content = curl_init($url);
    curl_setopt($_content, CURLOPT_RETURNTRANSFER, 1);
    $geo = curl_exec($_content);
    curl_close($_content);
    $result = (array) simplexml_load_string($geo);
    foreach ($result['item'] as $value)
    {
      if ($a = $this->consistsAllOccurrencies($q, (string)$value['name'])) {
        $this->_settlements[] = (string)$value['name'];
      }
    }

    return $this->renderText(implode("\n", $this->_settlements));
  }

   protected function consistsAllOccurrencies($needle, $haystack) {

    $needle = str_replace(str_split(',."\':;/?\\<>~`!@#$%^&*()_+='), array(), $needle);
    $n_parts = explode(' ', trim($needle));

    $haystack = str_replace(str_split(',."\':;/?\\<>~`!@#$%^&*()_+='), array(), $haystack);
    $h_parts = explode(' ', trim($haystack));
    usort($n_parts, function($a,$b){
      $a = mb_strlen($a, 'utf-8');
      $b = mb_strlen($b, 'utf-8');
      if($a == $b) {
        return 0;
      }
      //strlen-reverse sort
      return $a > $b ? -1 : 1;
    });

    if(count($h_parts) >= count($n_parts)) {
      foreach($n_parts as $n_key => $n_part) {
        foreach($h_parts as $h_key => $h_part) {
          if(!empty($n_part) && false !== mb_stripos($h_part, $n_part)){
            unset($n_parts[$n_key]); // I found this
            unset($h_parts[$h_key]); // I found here
            continue;
          }
        }
      }
      //If I have nothing to find - ok
      if(count($n_parts) == 0) {
        return true;
      }
    }
    return false;
  }

}
