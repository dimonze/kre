<!-- UPD --><h2 class="title_h2 upd-title-h2">Карьера</h2><!-- /UPD -->
<div class="separator"></div>

<ul class="upd-menu-list">
  <li>
    <ul class="upd-submenu-list">
      <?php foreach ($submenu as $e): ?>
        <li>
          <?php if ($e['is_current']): ?>
            <strong><?=$e['name']?></strong>
          <?php elseif ($e['is_active']): ?>
            <?= link_to($e['name'], $e['uri'], array('class' => 'active')) ?>
          <?php else: ?>
            <?= link_to($e['name'], $e['uri']) ?>
          <?php endif ?>
        </li>
      <?php endforeach ?>
    </ul>
  </li>
  <?php foreach ($menu as $e): ?>
    <li<?=$e['level'] > 0 ? ' style="margin-left: '.($e['level']*15).'px;"' : '' ?>>
      <?php if ($e['is_current']): ?>
        <strong><?=$e['name']?></strong>
      <?php elseif ($e['is_active']): ?>
        <?= link_to($e['name'], $e['uri'], array('class' => 'active')) ?>
      <?php else: ?>
        <?= link_to($e['name'], $e['uri']) ?>
      <?php endif ?>
    </li>
  <?php endforeach ?>
</ul>

<div class="upd-lside-info">
  <img src="/images/pshcherbinina.jpg" alt="Директор по персоналу Муратова Светлана"><br />
  Директор по персоналу<br />
  Щербинина Полина<br />
  тел. 956-77-99,
  <a href="mailto:pshcherbinina@kre.ru">pshcherbinina@kre.ru</a>
</div>