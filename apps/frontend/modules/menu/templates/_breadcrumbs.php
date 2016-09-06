<div class="upd-cside-path">
  <?=link_to('Главная', '@homepage')?> <span>/</span>
  <?php foreach ($crumbs as $page): ?>
    <?=link_to($page['name'], $page['uri'])?> <span>/</span>
  <?php endforeach ?>
  <?=$current_page['name']?>
</div>