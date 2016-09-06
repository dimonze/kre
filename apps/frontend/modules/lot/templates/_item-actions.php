<div class="clear"></div>
<ul class="upd-cat-action">
  <?php if ($lot->pid && ($lot->Parent->status == 'active' || ($lot->Parent->status != 'inactive' && Broker::isAuth()))): ?>
    <?php if ($lot->is_country_type): ?>
      <li>
        <a href="<?= url_for2($lot->Parent->route, $lot->Parent) ?>#childs">
          <img src="/pics/upd-icon-list.png" alt="" />
          Другие предложения в этом коттеджном поселке
        </a>
      </li>
    <?php elseif (!$lot->is_country_type && !$lot->is_commercial_type): ?>
      <li>
        <a href="<?= url_for2($lot->Parent->route, $lot->Parent) ?>#childs">
          <img src="/pics/upd-icon-list.png" alt="" />
          Другие предложения в этом доме
        </a>
      </li>
    <?php else: ?>
      <li>
        <a href="<?= url_for2($lot->Parent->route, $lot->Parent) ?>#childs">
          <img src="/pics/upd-icon-list.png" alt="" />
          Другие предложения в этом доме
        </a>
      </li>
    <?php endif; ?>
  <?php endif ?>

  <?php if ($lot->price_all_from && (!$lot->hide_price || Broker::isAuth())): ?>
    <li>
      <a href="<?= url_for_params($lot, array('price_from','price_to','currency')) ?>">
        <img src="/pics/upd-icon-pics.png" alt="" />
        Другие предложения в этом бюджете
      </a>
    </li>
  <?php endif ?>

  <?php if ($lot->district_id): ?>
    <li>
      <a href="<?= url_for_params($lot, array('district')) ?>">
        <img src="/pics/upd-icon-map.png" alt="" />
        Другие предложения в этом районе
      </a>
    </li>

  <?php elseif ($lot->is_country_type && $lot->pid && ($lot->Parent->status == 'active' || ($lot->Parent->status != 'inactive' && Broker::isAuth()))): ?>
    <li>
      <a href="<?= url_for_params($lot, array('ward')) ?>">
        <img src="/pics/upd-icon-map.png" alt="" />
        Другие предложения в этом направлении
      </a>
    </li>
  <?php endif ?>

  <li>
    <a href="<?= url_for('offers_list', array('type' => $lot->type)) ?>#content">
      <img src="/pics/upd-icon-search.png" alt="" />Поиск по параметрам
    </a>
  </li>
  <li><a href="<?= url_for2('presentation', array('id' => $lot->id, 'type' => $lot->type))?>"><img src="/pics/upd-icon-view.png" alt="" />Создать презентацию</a></li>
</ul>