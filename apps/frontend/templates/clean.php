<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns:fb="https://www.facebook.com/2008/fbml"  dir="ltr" lang="ru-RU">
  <head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <link rel="shortcut icon" href="/favicon.ico" />
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
    <?php include_title() ?>
    <?php if(!include_slot('no_stat')): ?>
      <?php include_partial('global/ga') ?>
    <?php endif; ?>

  </head>
  <body>
    <?php if(!include_slot('no_stat')): ?>
      <?php include_partial('global/ya') ?>
    <?php endif; ?>

    <?= $sf_content ?>
    <?php if(!include_slot('no_stat')): ?>
      <div class="stats">
        <?php include_partial('global/stats') ?>
      </div>
    <?php endif; ?>
  </body>
</html>
