<div class="cside c_full">
  <div class="padding">
    <h1 class="title_h1 h1_offers"><!--HB-->Наши предложения<!--HE--></h1>
    <p class="print"><a target="_blank" href="<?= url_for2('offers', array('print' => 1))?>">Распечатать</a></p>
    <div class="separator"></div>
    <div id="content" class="content_full cat_root">
      <table class="upd-table-cat">
        <?php for ($n=0, $c=$offers->count(); $n<$c; $n+=2): ?>

          <tr>
            <?php for ($i=0; $i<2; $i++): ?>
              <?php $offer = $offers->get($n+$i) ?>
              <?php include_partial('main-cat-info', array(
                'type'  => $offer->type,
                'cnts'  => $counters[$offer->type],
                'desc'  => $offer->description,
              )) ?>
            <?php endfor ?>
          </tr>
          <tr>
            <?php next($offers) ?>
            <?php for ($i=0; $i<2; $i++): ?>
              <?php $offer = $offers->get($n+$i) ?>
              <?php include_partial('lot/main-cat-link', array('object' => $offer->lot, 'anons' => $offer->lot_anons))?>
            <?php endfor ?>
          </tr>
          <tr><td colspan="2" class="upd-table-cat-gap">&nbsp;</td></tr>
        <?php endfor ?>
      </table>

      <?php if (has_slot('SeoText') && ($seotext = get_slot('SeoText'))): ?>
      <?= $seotext ?>
      <?php endif ?>
    </div>
  </div>
</div>