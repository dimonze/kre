<noindex>
  <?php foreach ($items as $item): ?>
  <?php $link = sprintf('@%s?id=%d', $route, $item->id); ?>
    <div class="pub">
      <p class="upd-pub-date"><?= format_date(strtotime($item->created_at), 'd MMM yyyy', 'ru') ?></p>
      <p class="upd-pub-title"><?= link_to($item->name, $link) ?></p>
      <?= simple_format_text(
            trim(strip_tags($item->getAnonsText(ESC_RAW), '<a>')) . '&nbsp;'. link_to(
              image_tag('/layout/pics/upd-more.gif'),
              $link,
              array('class' => 'upd-pub-more')
      )); ?>
    </div>
  <?php endforeach ?>
</noindex>
