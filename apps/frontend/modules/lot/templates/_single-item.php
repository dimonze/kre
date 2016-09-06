<?php use_javascript('http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU') ?>

<h1 class="upd-title-h1"><?= $lot->combined_name ?></h1>

<?php include_partial('item-links', array(
  'lot'       => $lot,
  'lots_alike'=> $lots_alike,
  '_params'   => $_params,
  'isparent'  => $isparent,
)) ?>

<div class="map"></div>

<div class="cat_info">
  <div class="l_col">
    <?= $lot->image_source ? link_to(image_tag($lot->getImage('item')), $lot->getImage('pres'),
            array('class' => 'gallery', 'rel' => 'gal-' . $lot->id, 'title' => '')) : '' ?>

    <p class='upd-cat-phone-title'>Свяжитесь с нами:</p>
    <p class='cat_phone'>
      <?php if ($phone = $lot->office_phone): ?><span class="cp1"><?= $phone ?></span><?php endif ?>
      <?php if ($phone = $lot->broker_phone): ?><span class="cp2"><?= $phone ?></span><?php endif ?>
    </p>
    <div class="fb-widgets" rel="<?= url_for($lot->route, $lot, true) ?>"></div>

    <?php foreach ($lot->getPhotosGroupped($sf_user->isAuthenticated()) as $title => $photos): ?>
      <?php list($part1, $part2) = array(array_slice($photos->getRawValue(), 0, 2), array_slice($photos->getRawValue(), 2)) ?>

      <h3 class="cat_type"><?= $title ?></h3>
      <table class="cat_gallery">
        <?php foreach ($part1 as $i => $photo): ?>
          <?php if (! ($i % 2)): ?><tr><?php endif ?>
          <th>
            <?php if ($photo->is_pdf || $photo->is_xls): ?>
              <?= link_to(image_tag($photo->getImage('thumb')), $photo->getImageSource(false)) ?>
            <?php else: ?>
              <?= link_to(image_tag($photo->getImage('thumb'), array('alt' => $photo->image_name)), $photo->getImage('full'),
                          array('class' => 'gallery', 'rel' => 'gal-' . $lot->id, 'title' => $photo->image_alt)) ?>
            <?php endif ?>
          </th>
          <td></td>

        <?php endforeach ?>
      </table>

      <?php if ($part2): ?>
        <a href="#" class="upd-cat-gallery-control">Все изображения (еще <?= count($part2) ?>)</a>
        <div class="upd-cat-gallery-more" style="display: none">
          <table class="cat_gallery">
            <?php foreach ($part2 as $i => $photo): ?>
              <?php if (! ($i % 2)): ?><tr><?php endif ?>
              <th>
                <?php if ($photo->is_pdf || $photo->is_xls): ?>
                  <?= link_to(image_tag($photo->getImage('thumb')), $photo->getImageSource(false)) ?>
                <?php else: ?>
                  <?= link_to(image_tag($photo->getImage('thumb'), array('alt' => $photo->image_name)), $photo->getImage('full'),
                              array('class' => 'gallery', 'rel' => 'gal-' . $lot->id, 'title' => $photo->image_alt)) ?>
                <?php endif ?>
              </th>
              <td></td>

            <?php endforeach ?>
          </table>
        </div>
      <?php endif ?>

    <?php endforeach ?>
  </div>
</div>

<div class="r_col">
  <div class="upd-rcol-box">

    <?php include_partial('badges', array('lot' => $lot)) ?>

    <span class="upd-lot">Лот <?= $lot->id ?></span> &nbsp;
    <span class="upd-service"><?= Lot::$_types_genetive[$sf_params->get('type')]?></span>

    <p>
      <?php if ($lot->is_country_type): ?>
        <?php if ($wards = $lot->array_wards): ?>
          <b>Направление:</b> <span class="upd-service">
            <?php foreach ($wards as $i => $name): ?>
              <?php if ($i) echo ', ' ?>
              <?php if ($i): ?>
                <?= link_to($name, 'lot/list?type=' . $lot->type . '&wards[]=' . $lot->ward2) ?>
              <?php else: ?>
                <?= link_to($name, 'lot/list?type=' . $lot->type . '&wards[]=' . $lot->ward) ?>
              <?php endif ?>
            <?php endforeach ?>
          </span><br>
        <?php endif ?>
        <?php if (isset($lot->params['distance_mkad'])): ?>
          <span class="distance-mkad"><b>Удаленность от МКАД:</b> <span class="upd-service"><?= $lot->params['distance_mkad'] ?> км.</span></span>
        <?php endif ?>
        <?php if (isset($lot->params['locality'])): ?>
          <br/>
          <b>Населённый пункт:</b> <span class="upd-service"><?= $lot->params['locality'] ?></span>
        <?php endif ?>
        <?php if (isset($lot->params['cottageVillage'])): ?>
          <br/>
          <b>Коттеджный посёлок:</b> <span class="upd-service"><?= $lot->params['cottageVillage'] ?></span>
        <?php endif ?>
      <?php else: ?>
        <?php if ($lot->district): ?>
          <b>Район:</b> <span class="upd-service">
             <?= link_to($lot->district, 'lot/list?type=' . $lot->type . '&districts[]=' . $lot->district_id) ?>
          </span> &nbsp;
        <?php endif ?>
        <?php if ($lot->metro): ?>
          <span class="upd-metro">
            <?= $lot->metro ?>
          </span>
        <?php endif ?>
      <?php endif ?>
    </p>

    <?php include_partial('item-area-price', array('lot' => $lot, 'mode' => $mode, 'presentation' => false)) ?>
    <?php include_partial('item-params', array(
      'lot'     => $lot,
      'params'  => isset($params_groupped) ? $params_groupped[$mode] : $lot->params_groupped_filtered[$mode],
      'presentation' => false,
    )) ?>

    <div class="cat_description">
      <div><?= clean_desc($lot) ?></div>
      <?php if ($sf_user->isAuthenticated() && $comments = $lot->getRaw('hidden_text')): ?>
        <h3>Комментарии</h3>
        <div><?= $comments ?></div>
      <?php endif ?>
    </div>
    <div class="clear"></div>
    <div class="upd-cat-buttons">
      <div class="select select_btn select_left"><div class="select_r_bg">
        <?php $params = array('lot_id' => $lot->id) ?>
        <?php foreach (Claim::$_groups as $k => $types): ?>
          <?php if (in_array($lot->type, $types)) $params['types'][] = $k ?>
        <?php endforeach ?>
        <?= link_to('Оставить заявку', '@claim?' . http_build_query($params)) ?>
      </div></div>
      <?php if ($lot->price_all_from && !$lot->hide_price && !in_array($lot->type, array('cottage', 'flatrent'))): ?>
        <div class="select select_btn select_left upd-service-btn"><div class="select_r_bg">
          <a href="<?= url_for2('calc', array('sum' => round(Currency::convert($lot->price_all_from, $lot->currency, 'USD'))))?>" class="mortgage_button">Купить с помощью ипотеки</a>
        </div></div>
      <?php endif ?>
    </div>

    <?php if (!empty($acts) && $lot->status != 'inactive'): ?>
      <?php include_partial('item-actions', array('lot' => $lot, 'presentation' => false)) ?>
    <?php else: ?>
      <br/><br/>
    <?php endif ?>

    <div class="clear"></div>
  </div>
</div>

<?php if (!empty($acts) && $lot->status != 'inactive' && $lot->has_children != 1): ?>
  <?php include_partial('lots-alike', array('lot' => $lot, 'lots_alike' => $lots_alike, '_params' => $_params)) ?>
<?php else: ?>
  <br/><br/>
<?php endif ?>