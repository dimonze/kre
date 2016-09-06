<div class="cside c_full">
  <div class="padding">
    <p class="print"><noindex><a href="?print=1" target="_blank" rel="nofollow">Распечатать</a></noindex></p>
    <?php include_component('menu', 'breadcrumbs') ?>
    <div class="separator"></div>
    <div id="content">
      <h2><!--HB--><?=$page->name?><!--HE--></h2>
      <?php if (isset($pager)): ?>
        <?php $route = strpos($sf_params->get('action'), 'news') !== false ? 'news' : 'analytics' ?>
        <?php include_partial('page/archive-list', array('pager' => $pager, 'route' => $route)) ?>
      <?php else: ?>
        <?php include_partial('page/archive-item', array('item' => $page)) ?>
      <?php endif ?>
    </div>
  </div>
</div>