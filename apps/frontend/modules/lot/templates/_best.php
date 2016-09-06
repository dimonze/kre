<h2 class="title_h2 h2_best_offers">Лучшие предложения</h2>
<div class="separator"></div>
<noindex>
  <?php if (count($lots) > 0): ?>
    <?php foreach($lots as $lot): ?>
      <?php if(!empty($lot)): ?>
        <?php $link = url_for($lot->route, $lot) ?>
        <div class="upd-best-offer">
          <div class="upd-best-offer-img">
            <?php if ($lot->image_source): ?>
              <?= link_to(image_tag($lot->getImage('list')), $link) ?>
            <?php endif ?>
          </div>
          <div class="upd-best-offer-body">
            <div class="upd-best-offer-path"><?= link_to2($lot->type_text, 'offers_list', array('type' => $lot->type)) ?></div>
            <h3><?= link_to($lot, $link) ?></h3>
            <?php if (!empty($lot->special_text)):?>
              <?php $text = $lot->getRaw('special_text') ?>
            <?php else: ?>
              <?php $text = $lot->getRaw('anons') ?>
            <?php endif ?>
            <?php $text = preg_replace('/^<p>/', '', $text);?>
            <?php $text = preg_replace('/<\/p>$/', '', $text);?>
            <?= first_paragraph(
                  simple_format_text(
					trim($text) . '&nbsp; <span></span>'
                )); ?>
          </div>
        </div>
      <?php endif ?>
    <?php endforeach ?>
  <?php else: ?>
    <p>
      <?= sfConfig::get('app_default_spec_text')?>
    </p>
  <?php endif ?>
</noindex>