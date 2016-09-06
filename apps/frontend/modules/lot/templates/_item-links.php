<div class="upd-links">
  <?php if ($lot->parent_name): ?>
    <span class="upd-links-item upd-links-item-community">
      <?= link_to_if($lot->pid && $lot->Parent->is_visible, $lot->parent_name, $lot->Parent->route, $lot->Parent, 'class=upd-service') ?>
    </span>
  <?php endif ?>

  <?php if ($lot->has_children && count($lot->Lots)): ?>
    <span class="upd-links-item">
      <?php $url = $lot->has_children ? url_for2($lot->route, $lot) : ''; ?>
      <?php if($lot->is_country_type): ?>
        <a href="<?= $url ?>#childs">Предложения в этом коттеджном поселке</a>
      <?php else: ?>
        <a href="<?= $url ?>#childs">Предложения в этом объекте (доме)</a>
      <?php endif;?>
    </span>
  <?php endif ?>


  <?php if ($lot->lat && $lot->lng): ?>
    <span class="upd-links-item">
      <a href="#" class="upd-service upd-service-marker show-map"
         data-lat="<?= $lot->lat ?>" data-lng="<?= $lot->lng ?>" data-title="<?= $lot ?>">
        Показать на карте
      </a>
    </span>
  <?php endif ?>


  <?php if ($isparent || $lots_alike->count() > 0): ?>
    <span class="upd-links-item">
      <?php if ($isparent): ?>
        <a href="<?= url_for_params($lot, array('price_from','price_to','currency','area_from','area_to')) ?>">
      <?php else: ?>
        <a href="<?= url_for_params2($lot, $_params)?>">
      <?php endif ?>
          Похожие предложения
        </a>
    </span>
  <?php endif ?>
</div>