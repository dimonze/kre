<?php

/**
 * data actions.
 *
 * @package    kre
 * @subpackage data
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class dataActions extends sfActions
{
  public function executeParam(sfWebRequest $request)
  {
    $this->forward404Unless(isset(LotQuery::$_filter_params[$request->getParameter('param')]));
    $param = LotQuery::$_filter_params[$request->getParameter('param')];


    $params = array(
      $param[0], '%' . $request->getParameter('q') . '%', '', 'active',
      $request->getParameter('type') == 'penthouse' ? 'eliteflat' : $request->getParameter('type'),
    );
    $piece = '';
    if($request->getParameter('type') == 'penthouse'){
       $piece = 'AND l.is_penthouse = ?';
       array_push($params, 1);
    }

    $stmt = Doctrine::getTable('LotParam')->getConnection()->prepare('
      SELECT DISTINCT lp.value FROM lot_param lp
      INNER JOIN lot l ON (l.id = lp.lot_id) or (l.pid = lp.lot_id)
      WHERE lp.param_id = ? AND lp.value LIKE ? AND lp.value <> ? AND l.status = ? AND l.type = ? ' . $piece . ' AND (lp.lot_id <> l.id OR l.pid IS NULL)
      ORDER BY lp.value
    ');
    $stmt->execute($params);
    $data = $stmt->fetchAll(Doctrine::FETCH_COLUMN);

    return $this->renderText(json_encode($data ? array_combine($data, $data) : array()));
  }

  public function executeList(sfWebRequest $request)
  {
    $param = $request->getParameter('param');
    $q = $request->getParameter('q');

    if ('locality' == $param) {
      $conn = Doctrine::getTable('LotParam')->getConnection();
      $stmt = $conn->prepare('SELECT DISTINCT p.value FROM lot_param p LEFT JOIN lot l ON (l.id = p.lot_id) OR (l.pid = p.lot_id)
                              WHERE p.param_id = ? AND p.value LIKE ? AND l.status = ? AND l.type = ?
                              ORDER BY p.value');
      $stmt->execute(array(43, "%$q%", 'active', $request->getParameter('type')));
      $data = array_filter($stmt->fetchAll(Doctrine::FETCH_COLUMN));
    }
    else {
      $list_name = $request->getParameter('type')
          ? sprintf('%s_%s', $param, $request->getParameter('type'))
          : $param;
      $list = UniqueList::getInstance($list_name)->getData();
      if ($q) {
        $data = array();
        foreach ($list as $item) {
          if($a = $this->consistsAllOccurrencies($q, $item)){
            $data[] = $item;
          }
        }
      }
      else {
        $data = $list;
      }
    }

    return $this->renderText(implode("\n", $data));
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
