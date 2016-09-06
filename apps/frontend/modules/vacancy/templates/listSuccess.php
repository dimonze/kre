<div class="cside c_full">
  <div class="padding">
  <!--<p class="print upd-print-plaintext"><a href="javascript:offprint();">Распечатать</a></p>-->
  <?php include_component('menu', 'breadcrumbs') ?>
  <div class="separator"></div>
  <div id="content">
    <h1 class="upd-title-h1"><?= $page->name ?></h1>
    <?= $page->getRaw('body') ?>

    <?php if ($sf_params->has('type')): ?>
      <?php include_component('vacancy', 'list') ?>
    <?php endif ?>
  </div>
  </div>
</div>