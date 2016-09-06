<h2 id="childs">Предложения в <?= $lot ?></h2>

<?php foreach ($children as $type => $items): ?>
  <h3><?= Lot::$_types_genetive[$type] ?></h3>

  <table class="upd-cat-info-all">
    <thead>
      <tr>
        <th class="tdcentr">Лот</th>
        <?php if ($items[0]->is_city_type): ?>
          <th class="tdlt">Комнат</th>
        <?php endif ?>

        <?php if ($items[0]->is_country_type): ?>
          <th class="tdlt">Площадь дома, м²</th>
          <th class="tdlt">Площадь участка</th>
          <th class="tdrt"><?= price_all($items[0]->type) ?></th>

        <?php else: ?>
          <th class="tdlt">Площадь, м²</th>
          <th class="tdlt">Этаж</th>
          <th class="tdrt"><?= price($items[0]->type) ?></th>
          <th class="tdrt"><?= price_all($items[0]->type, 'Общая цена') ?></th>
        <?php endif ?>

        <th class="tdlt">Отделка</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($items as $i => $child): ?>
        <?php if ($i > (in_array($type, Lot::$_statuses_rent) ? 1 : 4)): $has_more = true; continue; ?>
        <?php else: $has_more = false; endif ?>

        <tr>
          <td class="tdcentr"><?= link_to($child->id, url_for($child->route, $child)) ?></td>

          <?php if ($child->is_city_type): ?>
            <td class="tdlt"><?= isset($child->params['rooms']) ? $child->params['rooms'] : '&mdash;' ?></td><!--комнат-->
          <?php endif ?>


          <?php if ($child->is_country_type): ?>
          <td class="tdlt"><?= $child->area_from > 0 ? single_or_range($child, 'area') : '&mdash;' ?></td><!--площадь дома-->
            <td class="tdlt"><?= isset($child->params['spaceplot']) ? str_replace('--', '&mdash;', $child->params['spaceplot']) : '&mdash;' ?></td><!--площадь участка-->

          <?php else: ?>
            <td class="tdlt"><?= $child->area_from > 0 ? single_or_range($child, 'area') : '&mdash;' ?></td><!--площадь-->

            <?php if ($child->is_city_type): ?>
              <td class="tdlt"><?= isset($child->params['about_floor']) ? $child->params['about_floor'] : '&mdash;' ?></td><!--этаж-->
            <?php elseif ($child->is_commercial_type): ?>
              <td class="tdlt"><?= isset($child->params['floor']) ? $child->params['floor'] : '&mdash;' ?></td><!--этаж-->
            <?php endif ?>

            <td class="upd-cat-info-all-price tdrt"><!--Цена за м2-->
              <?php if ($child->hasPrice || $sf_user->isAuthenticated()): ?>
                <?php foreach (array('RUR', 'USD', 'EUR') as $currency): ?>
                  <?php if ($currency == $lot->currency): ?><b><?php endif ?>
                    <span class="span-bottom-padding"><?= single_or_range_price_converted($child, 'price', $currency) ?></span>
                  <?php if ($currency == $lot->currency): ?></b><?php endif ?>
                <?php endforeach ?>
              <?php else: ?>
                &mdash;
              <?php endif ?>
            </td>
          <?php endif ?>


          <td class="upd-cat-info-all-price tdrt"><!--Общая цена-->
            <?php if ($child->hasPriceAll || ($sf_user->isAuthenticated() && !empty($child->price_all_from))): ?>
              <?php foreach (array('RUR', 'USD', 'EUR') as $currency): ?>
                <?php if ($currency == $lot->currency): ?><b><?php endif ?>
                  <span class="span-bottom-padding"><?= single_or_range_price_converted($child, 'price_all', $currency) ?></span>
                <?php if ($currency == $lot->currency): ?></b><?php endif ?>
              <?php endforeach ?>
            <?php elseif($sf_user->isAuthenticated() && empty($child->price_all)): ?>
              <i>&mdash;</i>
            <?php else: ?>
              <i>по запросу</i>
            <?php endif ?>
          </td>

          <?php if ($child->is_city_type): ?>
            <td class="tdlt"><?= isset($child->params['about_decoration']) ? str_replace('--', '&mdash;', $child->params['about_decoration']) : '&mdash;' ?></td><!--Отделка-->
          <?php else: ?>
            <td class="tdlt"><?= isset($child->params['decoration']) ? str_replace('--', '&mdash;', $child->params['decoration']) : '&mdash;' ?></td><!--Отделка-->
          <?php endif ?>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>

  <?php if ($has_more): ?>
    <?php if ($child->type == 'penthouse'): ?>
    <?php $child->type = 'eliteflat'; ?>
    <?php endif ?>
    <p class="la__all"><?= link_to('Все предложения', url_for_params($child, 'pid')) ?>&nbsp;(<?= count($items) ?>)</p>
  <?php endif ?>
<?php endforeach ?>