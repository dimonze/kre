<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns:fb="https://www.facebook.com/2008/fbml"  dir="ltr" lang="ru-RU">
  <head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <link rel="shortcut icon" href="/favicon.ico" />
    <?php print_friendly_include_stylesheets() ?>
    <?php print_friendly_include_javascripts() ?>
    <script type="text/javascript">
      swfobject.registerObject("myId", "9.0.0", "/swf/expressInstall.swf");
    </script>
    <?php include_title() ?>
    <?php include_partial('global/ga') ?>
  </head>
  <body<?= sfConfig::has('body_class') ? ' class="'.sfConfig::get('body_class').'"' : '' ?>>
    <?php include_partial('global/ya') ?>
    <?php if (has_slot('filter-map')): ?><?= get_slot('filter-map') ?><?php endif ?>

    <div class="minwidth">
      <?php $visual = get_component('menu', 'visual') ?>
      <?php $current_route = $sf_context->getRouting()->getCurrentRouteName() ?>
      <div id="header" class="<?= sfConfig::get('header_class') ?>">
        <div id="top">
          <a href="<?=url_for('@homepage')?>" class="top_logo">Contact Real Estate<img id="header-logo" src="/pics/contact-logo.gif"></a>
          <?php if ('homepage' == $current_route): ?>
            <strong class="<?= get_title_class($sf_request) ?>">Ваш главный CONTACT в мире элитной недвижимости</strong>
          <?php endif ?>
          <div class="mobile-top">
            <span></span>
          </div>
        </div>
        <?= $visual ?>
        <?php include_component('menu', 'main') ?>
      </div>
      <div class="hide_block_top"></div>
      <div class="page<?= sfConfig::get('print_version') ? ' print-version' : '' ?>">
		<table id="print_header" style="display: <?= sfConfig::get('print_version') ? 'block' : 'none' ?>"><tr><th><img src="/pics/contact-logo-print.gif" alt="Contact Real Estate"></th><td><p>Москва, ул. Арбат, д. 13/36<br/>Телефон: +7 (495) 956 77 99<br/><a href="http://www.kre.ru/">http://www.kre.ru/</a></p></td></tr></table>
			<?php if ('homepage' == $current_route): ?><div class="page-bg"><?php endif ?>
			<?php include_partial('global/lside') ?>
			<?php echo $sf_content ?>
			<?php if ('homepage' == $current_route): ?></div><?php endif ?>
		<p id="print-footer" style="display: <?= sfConfig::get('print_version') ? 'block' : 'none' ?>">Данная страница распечатана с сайта <a href="http://www.kre.ru/">http://www.kre.ru</a></p>
      </div>

      <?php if (has_slot('seotext') && ($seotext = get_slot('seotext'))): ?>
        <div class="clear"></div>
        <div class="justify">
          <div class="padding">
            <div class="separator"></div>
            <br/>
            <?= $seotext ?>
            <br/>
          </div>
        </div>
        <div class="clear"></div>
      <?php endif ?>

      <div class="hide_block_bottom"></div>



      <div class="copyright">
        <p>© 2004—<?=date('Y')?> «Contact Real Estate»</p>
      </div>
      <div class="soc_likes">
        <div class="like like_fb">
          <div class="s-facebook fb-like"></div>
        </div>
        <div class="like like_tw">
          <a class="s-twitter twitter-share-button"></a>
        </div>
        <div class="like like_vk">
          <div id="vk_like"></div>
        </div>
      </div>

      <div id="footer">
        <span class="sitemap"><a href="/sitemap/">Карта сайта</a></span>
        <div class="footer_extras">
          <div style="float: left; margin-top: 30px; margin-right:25px;">
            <img style="float:left;" src="/pics/contact-logo-footer.gif" width="134" height="29" alt="Contact Real Estate"/>
            <p>Москва, ул. Арбат, д. 13/36<br/>Телефон: +7 (495) 956 77 99</p>
          </div>

          <div class="footer-button">
            <div class="select select_btn select_left"><div class="select_r_bg">
              <?php if(!Broker::isAuth()): ?>
              <a href="<?= url_for2('csstat') ?>">Войти</a>
              <?php else: ?>
              <a href="<?= url_for2('csstat_acts', array('action' => 'object')) ?>">Кабинет</a>
            </div></div>
            <div class="select select_btn select_left"><div class="select_r_bg">
              <a href="<?= url_for2('csstat_acts', array('action' => 'logout')) ?>">Выход</a>
              <?php endif; ?>
            </div></div>
          </div>
        </div>
        <div style="position:absolute; left:350px; top:27px;"> </div>
        <?php if ('homepage' == $current_route): ?>
          <?php include_partial('global/fb_main') ?>
        <?php endif ?>
      </div>
    </div>
    <div class="stats">
      <?php include_partial('global/stats') ?>
    </div>
  </body>
</html>
