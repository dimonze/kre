<?php if ($lots_alike->count() > 0): ?>
  <?php $class = 'la__item' ?>
  <div class="clear"></div>
  <h2 class="upd-title-h1 la__box-title">Похожие предложения</h2>
  <div class="la__box">
    <?php $counter = 0; ?>
    <?php foreach ($lots_alike as $_lot): ?>
      <?php
        if ($_lot->id == $lot->id) continue;
        $counter++;
        if ($lots_alike->count() >= 4) {
          if ($counter == 5) {
            $class = 'la__item la__item_last';
          }
          if ($counter == 4) {
            $class = 'la__item la__item_prelast';
          }
        }
      ?>
      <div class="<?= $class ?>">
        <?php if ($_lot->image_source): ?>
          <a href="<?= url_for($_lot->route, $_lot) ?>" target="_blank" >
            <?= image_tag($_lot->getImage('list'), array('class' => 'la__img')) ?>
          </a>
        <?php endif ?>
        <div class="la__descr">
          <span class="la__number">Лот <?= $_lot->id ?></span>
          <span class="la__type"><?= Lot::$_types_genetive[$_lot->type] ?></span>
        </div>
        <div class="la__title"><?= link_to($_lot->combined_name, url_for($_lot->route, $_lot), array('target' => '_blank')) ?></div>
        <?php if ($_lot->area_from > 0): ?>
          <div>
            <b>Площадь м²:</b>
            <span class="la__district">
              <?= single_or_range($_lot, 'area') ?>
            </span>
          </div>
        <?php endif ?>

        <?php
          if ($_lot->is_country_type && !empty($_lot->params['spaceplot']) &&
                $_lot->params['objecttype'] != 'Коттеджный поселок' &&
                $_lot->type != 'cottage'):
        ?>
          <div>
            <b>Площадь участка (сот.):</b>
            <span class="la__district"><?= $_lot->params['spaceplot'] ?> </span>
          </div>

        <?php endif ?>
          <?php if (isset($_lot->params['rooms']) && $_lot->type != 'elitenew'): ?>
          <div><b>Количество комнат</b>
            <span class="la__district"><?= $_lot->params['rooms'] ?></span>
          </div>
        <?php endif ?>

        <?php if ($_lot->type == 'elitenew' && !empty($_lot->price_from) && ($_lot->has_price || $sf_user->isAuthenticated())): ?>
          <div>
            <b>Цена за м²:</b>
            <span class="la__district"><?= single_or_range_price_converted($_lot, 'price', $_lot->currency) ?></span>
          </div>
        <?php endif ?>

        <?php if ($_lot->type != 'flatrent' && $_lot->type != 'elitenew' && !empty($_lot->price_all_from) && !($_lot->type == 'comrent' && $sf_params->get('action') == 'list') && (!$_lot->hide_price || $sf_user->isAuthenticated())): ?>
          <div>
            <b>Цена:</b>
            <span class="la__district"><?= single_or_range_price_converted($_lot, 'price_all', $_lot->currency) ?></span>
          </div>
        <?php elseif ($_lot->type == 'flatrent'): ?>
          <div>
            <b>Цена за месяц:</b>
            <span class="la__district"><?= single_or_range_price_converted($_lot, 'price_all', $_lot->currency) ?></span>
          </div>
        <?php elseif ($_lot->hide_price && !$sf_user->isAuthenticated()): ?>
          <div>
            <b>Цена:</b>
            <span class="la__district"><i>по запросу</i></span>
          </div>
        <?php elseif ($_lot->type == 'comrent'): ?>
          <div>
            <b>Цена за м² в год:</b>
            <span class="la__district"><?= single_or_range_price_converted($_lot, 'price', $_lot->currency) ?></span>
          </div>
        <?php endif ?>
      </div>
      <?php if ($counter == 5) break; ?>
    <?php endforeach ?>

  </div>
  <div class="clear"></div>
  <div class="la__all">
    <a href="<?= url_for_params2($_lot, $_params) ?>">Все похожие предложения</a> (<?= $lots_alike->count() ?>)
  </div>
<?php endif ?>