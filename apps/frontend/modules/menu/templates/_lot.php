<div class="title_h2 h2_offers">Наши предложения</div>
<span class="upd-lside-title">Новые</span>

<div class="separator"></div>

<ul class="ul_list">
  <?php foreach($types as $type => $name): ?>
    <li>
      <?php $url = url_for('lot/list?type=' . $type) ?>
      <a href="<?= $url ?>"<?= $type == $cur_type ? ' class="active"' : '' ?>><?= $name ?></a>
      <span>
        <a href="<?= $url ?>"><?= $counters[$type]['count'] ?></a>
        <a href="<?= $url . '?only_new=on' ?>">
          <span class="upd-lside-count"><?= $counters[$type]['new'] ?: '' ?></span>
        </a>
      </span>
    </li>

    <?php if (!empty($counters[$type]['sub'])): ?>
        <li class="upd-ul-sublist"<?= isset($nb_show[$type]) ? ' value="'.$nb_show[$type].'"' : '' ?>>
          <ul>
            <?php foreach ($counters[$type]['sub'] as $district => $data): ?>
              <li>                
                  <?php $url = url_for('lot/list?type=' . $type . '&' . $counters[$type]['sub_url_option'] . '=' . $district) ?>
                  <a href="<?= $url ?>"><?= $data['name'] ?></a>
                  <span>
                    <a href="<?= $url ?>"><?= $data['count'] ?></a>
                    <?php if (stristr($url, '?')): ?>
                    <a href="<?= $url . '&only_new=on' ?>">
                     <?php else: ?>
                      <a href="<?= $url . '?only_new=on' ?>">
                      <?php endif; ?>
                      <span class="upd-lside-count"><?= $data['new'] ?: '' ?></span>
                    </a>
                  </span>
               
              </li>
            <?php endforeach ?>
          </ul>
      </li>
    <?php endif ?>

  <?php endforeach ?>
</ul>