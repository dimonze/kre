<div class="cside">
  <div class="padding">
    <h2 class="title_h2 h2_about">О компании</h2>
    <div class="separator"></div>
    <?= $about->getRaw('anons') ?>
    <?php include_component('page', 'latestNews') ?>
    <?php include_component('page', 'latestReviews') ?>
  </div>
</div>
<div class="rside">
  <div class="padding">
    <?php include_component('lot', 'best') ?>
  </div>
</div>

<?php slot('seotext'); ?> 
    <?php if (has_slot('SeoText') && ($seotext = get_slot('SeoText'))): ?>
    <?= $seotext ?>
    <?php endif ?>
<?php end_slot() ?>
