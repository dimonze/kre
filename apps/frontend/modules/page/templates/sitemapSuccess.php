<div class="cside c_full">
  <div class="padding">
    <p class="print"><noindex><a href="?print=1" target="_blank" rel="nofollow">Распечатать</a></noindex></p>
    <?php include_component('menu', 'breadcrumbs') ?>
    <div class="separator"></div>
    <div id="content">
      <h2><!--HB-->Карта сайта<!--HE--></h2>
      <ul>
      <?php foreach($map as $item): ?>
        <li><?= link_to($item['name'], $item['route']) ?>
        <?php if(!empty($item['_children'])): ?>
          <ul>
          <?php foreach($item['_children'] as $child): ?>
            <li><a href="<?= url_for($child['route'])?>"><?= $child['name']?></a></li>
          <?php endforeach ?>
          </ul>
          <?php endif ?>
        </li>
      <?php endforeach ?>
      </ul>
    </div>
  </div>
</div>