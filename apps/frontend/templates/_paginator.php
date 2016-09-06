<?php
if (!$pager->haveToPaginate()) {
  return;
}
if (empty($params)) {
  $params = array();
}
else {
  $params = $params->getRawValue();
}
$params['page'] = '';

$route = sfContext::getInstance()->getRouting()->getCurrentInternalUri(false);
$route .= (strpos($route, '?') ? '&' : '?') . http_build_query($params);

?>

<div class="upd-paginator">
  <div class="upd-paginator-title">Страницы</div>

  <?php if (1 != $pager->getPage()): ?>
    <?= link_to('предыдущая', $route . ($pager->getPage() - 1), 'class=upd-paginator-prev') ?>
  <?php else: ?>
    <a class="upd-paginator-prev upd-paginator-inactive">предыдущая</a>
  <?php endif ?>

  <?php if ($pager->getLastPage() != $pager->getPage()): ?>
    <?= link_to('следующая', $route . ($pager->getPage() + 1), 'class=upd-paginator-next') ?>
  <?php else: ?>
    <a class="upd-paginator-next upd-paginator-inactive">следующая</a>
  <?php endif ?>
  <?php if($pager->getPage() != 1): ?>
  <?php sfContext::getInstance()->getResponse()->setTitle(sfContext::getInstance()->getResponse()->getTitle(). ' - страница ' . $pager->getPage()); ?>
  <?php endif ?>
  <div class="clear"></div>
  <div class="upd-paginator-pages">
    <?php foreach ($pager->getLinks(6) as $page): ?>
      <?php $text = (($page - 1) * $pager->getMaxPerPage() + 1) . '-' . ($page * $pager->getMaxPerPage()) ?>
      
      <?php if ($pager->getPage() != $page): ?>
        <?= link_to($text, $route . $page) ?>
      <?php else: ?>
        <a class="upd-paginator-pages-active"><?= $text ?></a>
      <?php endif ?>
    <?php endforeach ?>
  </div>
</div>