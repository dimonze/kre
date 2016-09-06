<?php foreach($pager->getResults() as $item): ?>
  <p class="pub_date"><?= format_date(strtotime($item->created_at), 'd MMM yyyy', 'ru') ?></p>
  <h3 class="pub_title">
    <?= link_to2($item->name, $route, array('id' => $item->id)) ?>
  </h3>
  <p><?= $item->getAnonsText(ESC_RAW) ?></p>
  <div class="separator"></div>
<?php endforeach ?>

<?php include_partial('global/paginator', array('pager' => $pager)) ?>