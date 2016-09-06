<?php $link = url_for($lot->route, $lot) ?>
<?php $br = false ?>
<div class="cat_info<?= ($lot->status == 'hidden' ? ' hidden' : '')?>">
  <div class="l_col_s">
    <?php if ($lot->image_source): ?>
      <a href="<?= $link ?>" target="_blank">
        <?= image_tag($lot->getImage('list')) ?>
      </a>
    <?php endif ?>

    <p class='upd-cat-phone-title'>Свяжитесь с нами:</p>
    <p class='cat_phone'>
      <?php if($phone = $lot->office_phone): ?>
        <span class='cp1'><?= $phone ?></span>
      <?php endif;?>
      <?php if($phone = $lot->broker_phone): ?>
        <span class='cp2'><?= $phone ?></span>
      <?php endif;?>
    </p>
    <div class="fb-widgets" rel="<?= url_for($lot->route, $lot, true) ?>"></div>

    <div class='upd-cat-links'>
      <?= link_to('Оставить заявку', '@claim?lot_id=' . $lot->id) ?>
      <br />
      <?php if ($lot->price_all_from && !$lot->hide_price && !in_array($lot->type, array('cottage', 'flatrent'))): ?>
        <a href="<?= url_for2('calc', array('sum' => round(Currency::convert($lot->price_all_from, $lot->currency, 'USD'))))?>" class="mortgage_button">Купить с помощью ипотеки</a>
        <br />
      <?php endif ?>
    </div>
  </div>

  <div class="r_col_s">
    <div class="upd-lot-check">
      <div class="upd-form-checkbox">
        <input type="checkbox" name="lot_check" value="1" id="lot_check_1" rel="<?= $lot->id ?>" />
        <label for="lot_check_1">Добавить в список</label>
      </div>
    </div>
    <span class="upd-lot">Лот <?= $lot->id ?></span> &nbsp;
    <span class="upd-service"><?= Lot::$_types_genetive[$lot->type]?></span>

    <div class="upd-lot-title">
      <div class="upd-lot-title__header"><?= link_to($lot->combined_name, $link, array('target' => '_blank')) ?></div>
    </div>

    <?php include_partial('badges', array('lot' => $lot)) ?>

    <div class="upd-lot-info">
      <?php if ($lot->pid): ?>
        <?php if ($lot->parent_name): ?>
          <span class="upd-service"><?= $lot->parent_name ?></span>
        <?php else: ?>
          <span class="upd-service"><?= $lot->Parent ?></span>
        <?php endif ?>
      <?php endif ?>
      <br />
      <?php if ($lot->is_country_type): ?>
        <?php $br = false ?>
        <?php if ($wards = $lot->array_wards): ?>
          <b>Направление:</b> <span class="upd-service">
            <?php foreach ($wards as $i => $name): ?>
              <?php if ($i) echo ', '; ?>  
             <?php if ($i): ?>  
             <?= link_to($name, 'lot/list?type=' . $lot->type . '&wards[]=' . $lot->ward2) ?>
            <?php else: ?>
            <?= link_to($name, 'lot/list?type=' . $lot->type . '&wards[]=' . $lot->ward) ?>
            <?php endif; ?>
            <?php endforeach ?>
          </span>
          <?php $br = true ?>
        <?php endif ?>
        <?php if (isset($lot->params['distance_mkad'])): ?>
          <?php if ($br): ?><br/><?php endif ?>
          <span class="distance-mkad"><b>Удаленность от МКАД:</b> <span class="upd-service"><?= $lot->params['distance_mkad'] ?> км.</span></span>
          <?php $br = true ?>
        <?php endif ?>
        <?php if (isset($lot->params['locality'])): ?>
          <?php if ($br): ?><br/><?php endif ?>
          <b>Населённый пункт:</b> <span class="upd-service"><?= $lot->params['locality'] ?></span>
          <?php $br = true ?>
        <?php endif ?>
        <?php if (isset($lot->params['cottageVillage'])): ?>
          <?php if ($br): ?><br/><?php endif ?>
          <b>Коттеджный посёлок:</b> <span class="upd-service"><?= $lot->params['cottageVillage'] ?></span>
        <?php endif ?>

      <?php else: ?>
        <?php if ($lot->district): ?>
          <b>Район:</b> <span class="upd-service">
            <?= link_to($lot->district, 'lot/list?type=' . $lot->type . '&districts[]=' . $lot->district_id) ?>           
          </span> &nbsp;
        <?php endif ?>
        <?php if ($lot->metro): ?>
          <span class="upd-metro"><?= $lot->metro ?></span>
        <?php endif ?>
      <?php endif ?>

      <?php if ($lot->is_commercial_type): ?>
      <?php if (isset($lot->params['distance_metro'])): ?>
        <?php if ($br): ?><br/><?php endif ?>
        &nbsp;<b>Удаленность от метро:</b> <span class="upd-service"><?= $lot->params['distance_metro'] ?></span>
        <?php $br = true ?>
        <?php endif ?>
      <?php endif ?>
    </div>


    <?php include_partial('item-area-price', array('lot' => $lot, 'mode' => 'both', 'presentation' => false)) ?>

    <div class="short-desc" rel="<?= $link ?>">
      <?= $lot->getRaw('anons') ?>
      <p class="last_short_link"></p>
    </div>
  </div>
</div>