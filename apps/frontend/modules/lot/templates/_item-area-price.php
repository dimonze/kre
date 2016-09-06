<?php
$nb_tds   =  4;
$td_class = $sf_params->get('action') == 'show' && $lot->is_price_on_request ? 'tdlt' : 'tdrt';
$tr_num   = 0;
if(!empty($mode) && $mode == 'both') {
  $mode = 'object';
}
?>

<div class="upd-table-content-wrap"><div class="upd-table-content-wrap-box">
  <table class="table-content in_cat">
    <tr>
      <td colspan="4" class="separator_image">
        <div><img src="/pics/separator-wide.gif" alt="" /></div>
      </td>
    </tr>

    <?php if ($lot->area_from > 0): ?>
      <tr<?= fmod(++$tr_num, 2) == 0 ? ' class="upd-cat-info-all-odd"' : '' ?>>
        <td>Площадь м&sup2;:</td>
        <td class="<?= $td_class ?>" <?= (!empty($presentation) && $sf_user->isAuthenticated() ? '><span contenteditable="true"' : '') ?>>
          <?= single_or_range($lot, 'area') ?>
          <?= ($presentation && $sf_user->isAuthenticated() ? '</span>' : '') ?></td>
        <?php for ($i=2; $i<$nb_tds; $i++): ?><td></td><?php endfor ?>
      </tr>
    <?php endif ?>

    <?php if ($lot->is_country_type && !empty($lot->params['spaceplot'])): ?>
      <tr<?= fmod(++$tr_num, 2) == 0 ? ' class="upd-cat-info-all-odd"' : '' ?>>
        <td>Площадь участка (сот.):</td>
        <td class="<?= $td_class ?>" <?= (!empty($presentation) && $sf_user->isAuthenticated() ? '><span contenteditable="true"' : '') ?>>
          <?= $lot->params['spaceplot'] ?>
          <?= ($presentation && $sf_user->isAuthenticated() ? '</span>' : '') ?></td>
        <?php for ($i=2; $i<$nb_tds; $i++): ?><td></td><?php endfor ?>
      </tr>
    <?php endif ?>

    <?php if (isset($mode) && isset($lot->params['rooms']) && $lot->param_visibility_settings['Количество комнат'] == $mode && $lot->type != 'elitenew'): ?>
      <tr<?= fmod(++$tr_num, 2) == 0 ? ' class="upd-cat-info-all-odd"' : '' ?>>
        <td>Количество комнат</td>
        <td class="<?= $td_class ?>" <?= (!empty($presentation) && $sf_user->isAuthenticated() ? '><span contenteditable="true"' : '') ?>>
          <?= $lot->params['rooms'] ?>
          <?= ($presentation && $sf_user->isAuthenticated() ? '</span>' : '') ?></td>
        <?php for ($i=2; $i<$nb_tds; $i++): ?><td></td><?php endfor ?>
      </tr>
    <?php endif ?>

    <?php if (!empty($lot->price_from) && ($lot->has_price || $sf_user->isAuthenticated())): ?>
      <tr<?= fmod(++$tr_num, 2) == 0 ? ' class="upd-cat-info-all-odd"' : '' ?>>
        <td><?= price($lot->type) ?>:</td>
        <?php foreach (array('RUR', 'USD', 'EUR') as $currency): ?>
          <td class="tdrt" <?= (!empty($presentation) && $sf_user->isAuthenticated() ? '><span contenteditable="true"' : '') ?>>
            <?php if ($currency == $lot->currency): ?><b><?php endif ?>
              <?= single_or_range_price_converted($lot, 'price', $currency) ?>
            <?php if ($currency == $lot->currency): ?></b><?php endif ?>
            <?= (!empty($presentation) && $sf_user->isAuthenticated() ? '</span>' : '') ?></td>
        <?php endforeach ?>
      </tr>
    <?php endif ?>

      <?php if (!empty($lot->params['price_land_from']) && $lot->params['price_land_from'] > 0 && (!$lot->hide_price || $sf_user->isAuthenticated())): ?>
      <tr<?= fmod(++$tr_num, 2) == 0 ? ' class="upd-cat-info-all-odd"' : '' ?>>
        <td><?= price_land($lot->type) ?>:</td>
        <?php foreach (array('RUR', 'USD', 'EUR') as $currency): ?>
          <td class="tdrt" <?= (!empty($presentation) && $sf_user->isAuthenticated() ? '><span contenteditable="true"' : '') ?>>
            <?php if ($currency == $lot->currency): ?><b><?php endif ?>
              <?= single_or_range_price_converted($lot, 'price_land', $currency) ?>
            <?php if ($currency == $lot->currency): ?></b><?php endif ?>
            <?= (!empty($presentation) && $sf_user->isAuthenticated() ? '</span>' : '') ?></td>
        <?php endforeach ?>
      </tr>
    <?php endif ?>

    <?php if (!empty($lot->price_all_from) && !($lot->type == 'comrent' && $sf_params->get('action') == 'list') && (!$lot->hide_price || $sf_user->isAuthenticated())): ?>
      <tr<?= fmod(++$tr_num, 2) == 0 ? ' class="upd-cat-info-all-odd"' : '' ?>>
        <td><?= price_all($lot->type) ?>:</td>
        <?php foreach (array('RUR', 'USD', 'EUR') as $currency): ?>
          <td class="tdrt" <?= (!empty($presentation) && $sf_user->isAuthenticated() ? '><span contenteditable="true"' : '') ?>>
            <?php if ($currency == $lot->currency): ?><b><?php endif ?>
              <?= single_or_range_price_converted($lot, 'price_all', $currency) ?>
            <?php if ($currency == $lot->currency): ?></b><?php endif ?>
            <?= (!empty($presentation) && $sf_user->isAuthenticated() ? '</span>' : '') ?></td>
        <?php endforeach ?>
      </tr>
    <?php elseif ($lot->hide_price && !$sf_user->isAuthenticated()): ?>
      <tr<?= fmod(++$tr_num, 2) == 0 ? ' class="upd-cat-info-all-odd"' : '' ?>>
        <td><?= price_all($lot->type) ?>:</td>
        <td class="<?= $td_class ?>">
          <i>по запросу</i>
        </td>
        <?php for ($i=2; $i<$nb_tds; $i++): ?><td></td><?php endfor ?>
      </tr>
    <?php endif ?>

    <?php if (!empty($lot->params['m_a_p']) && !empty($lot->params['m_a_p_Currency']) && $lot->params['objecttype'] == 'Готовый арендный бизнес' && $lot->type != 'comrent'): ?>
      <tr<?= fmod(++$tr_num, 2) == 0 ? ' class="upd-cat-info-all-odd"' : '' ?>>
        <td><?= m_a_p($lot->type) ?>:</td>
        <?php foreach (array('RUR', 'USD', 'EUR') as $currency): ?>
          <td class="tdrt" <?= (!empty($presentation) && $sf_user->isAuthenticated() ? '><span contenteditable="true"' : '') ?>>            
            <?php if ($currency == $lot->params['m_a_p_Currency']): ?><b><?php endif ?>
              <?= single_or_range_price_converted($lot, 'm_a_p', $currency) ?>
            <?php if ($currency == $lot->params['m_a_p_Currency']): ?></b><?php endif ?>
            <?= (!empty($presentation) && $sf_user->isAuthenticated() ? '</span>' : '') ?></td>
        <?php endforeach ?>
      </tr>
    <?php endif ?>  
    <?php if (!empty($lot->params['payback'])&& $lot->params['objecttype'] == 'Готовый арендный бизнес' && $lot->type != 'comrent'): ?>
      <tr<?= fmod(++$tr_num, 2) == 0 ? ' class="upd-cat-info-all-odd"' : '' ?>>
        <td>Окупаемость :</td>
        <td class="<?= $td_class ?>" id="payback" <?= (!empty($presentation) && $sf_user->isAuthenticated() ? '><span contenteditable="true"' : '') ?>>
          <?= $lot->params['payback'] . ' ' . to_normal_alt($lot->params['payback']) ?> 
          <?= ($presentation && $sf_user->isAuthenticated() ? '</span>' : '') ?></td>
        <?php for ($i=2; $i<$nb_tds; $i++): ?><td></td><?php endfor ?>
      </tr>
    <?php endif ?> 
      
    <?php if (!empty($lot->params['yield']) && $lot->params['objecttype'] == 'Готовый арендный бизнес' && $lot->type != 'comrent'): ?>
      <tr<?= fmod(++$tr_num, 2) == 0 ? ' class="upd-cat-info-all-odd"' : '' ?>>
        <td>Доходность :</td>
        <td class="<?= $td_class ?>" <?= (!empty($presentation) && $sf_user->isAuthenticated() ? '><span contenteditable="true"' : '') ?>>
          <?= $lot->params['yield']. ' %' ?>
          <?= ($presentation && $sf_user->isAuthenticated() ? '</span>' : '') ?></td>
        <?php for ($i=2; $i<$nb_tds; $i++): ?><td></td><?php endfor ?>
      </tr>
    <?php endif ?> 
    <tr>
      <td colspan="4" class="separator_image">
        <div><img src="/pics/separator-wide.gif" alt="" /></div>
      </td>
    </tr>
  </table>
</div></div>
