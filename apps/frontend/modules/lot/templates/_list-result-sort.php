<?php
$params = $sf_params->getAll();
if ($params instanceof sfOutputEscaperArrayDecorator) {
  $params = $params->getRawValue();
}
foreach ($params as $k => $v) if (empty($v)) unset($params[$k]);
$params['by'] = isset($params['by']) ? $params['by'] : null;
$dir = !empty($params['dir']) && $params['dir'] == 'asc' ? 'desc' : 'asc';
$params = prepare_params_for_url($params);
?>

<div class="upd-form-search-result-sort">
  <div class="upd-form-search-result-sort-title">Сортировать по:</div>
  <noindex>
  <?= link_to('цене', route_for_list($params), array_merge($params, array(
    'type'  => $sf_params->get('type'),
    'by'    => 'price',
    'dir'   => ($params['by'] == 'price') ? $dir : 'asc',
  )), array(
     'rel' => 'nofollow',
     'class' => 'upd-form-search-result-sort-item' .
         ($params['by'] != 'price' ? '' : ($dir == 'desc' ? ' upd-form-search-result-sort-active' : ' upd-form-search-result-sort-active upd-form-search-result-sort-desc'))
  )) ?>
  <?= link_to('площади', route_for_list($params), array_merge($params, array(
    'type'  => $sf_params->get('type'),
    'by'    => 'area',
    'dir'   => ($params['by'] == 'area') ? $dir : 'asc',
  )), array(
    'rel' => 'nofollow',
    'class' => 'upd-form-search-result-sort-item ' .
      ($params['by'] != 'area' ? '' : ($dir == 'desc' ? 'upd-form-search-result-sort-active' : ' upd-form-search-result-sort-active upd-form-search-result-sort-desc'))
  )) ?>
</noindex></div>