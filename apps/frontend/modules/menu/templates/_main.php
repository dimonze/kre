<?php $main_page_link_name = 'Главная'; ?>
<div class="upd-menu">
  <ul class="upd-menu-box">
    <?php foreach ($menu as $item): ?>
      <li class="upd-menu-item">
        <?php $name = ($item['name']=='main_page' ? $main_page_link_name : $item['name']) ?>
        <?= link_to_unless($item['is_active'], $name, $item['uri']) ?>
      </li>
    <?php endforeach ?>

    <li class="upd-menu-item">
      <a href="<?= url_for('@claim') ?>">
        <span class="upd-menu-item-request"></span>Оставить заявку
      </a>
    </li>
  </ul>
</div>