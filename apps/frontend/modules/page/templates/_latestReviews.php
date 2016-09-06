<?php if (!empty($items)): ?>
  <h2 class="title_h2 h2_analytics">Аналитика</h2>
  <div class="separator"></div>
  <?php include_partial('page/latest', array('items' => $items, 'route' => 'analytics')) ?>
<?php endif ?>