<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
  <head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="/favicon.ico" />
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
  </head>
  <body>
    <div id="global-loading"></div>
    <ul class="backend_nav">
      <?php if ($sf_user->hasCredential('admin')): ?>
        <li<?= $sf_params->get('module') == 'page' ? ' class="current"' : '' ?>>
          <?= link_to('Страницы', '@page') ?>
        </li>
      <?php endif ?>
      <?php if ($sf_user->hasCredential(array('admin','seo'), false)): ?>
        <li<?= $sf_params->get('module') == 'lot' ? ' class="current"' : '' ?>>
          <?= link_to('Объекты', '@lot') ?>
        </li>
      <?php endif ?>
      <?php if ($sf_user->hasCredential('admin')): ?>
        <li<?= $sf_params->get('module') == 'broker' ? ' class="current"' : '' ?>>
          <?= link_to('Сотрудники', '@broker') ?>
        </li>
      <?php endif ?>
      <?php if ($sf_user->hasCredential('admin')): ?>
        <li<?= $sf_params->get('module') == 'vacancy' ? ' class="current"' : '' ?>>
          <?= link_to('Вакансии', '@vacancy') ?>
        </li>
      <?php endif ?>
      <?php if ($sf_user->hasCredential('admin')): ?>
        <li<?= $sf_params->get('module') == 'main_offer' ? ' class="current"' : '' ?>>
          <?= link_to('Предложения', '@main_offer') ?>
        </li>
      <?php endif ?>
      <?php if ($sf_user->hasCredential('admin')): ?>
        <li<?= $sf_params->get('module') == 'claim' ? ' class="current"' : '' ?>>
          <?= link_to('Заявки', '@claim') ?>
        </li>
      <?php endif ?>
      <?php if ($sf_user->hasCredential(array('admin','seo'), false)): ?>
        <li<?= $sf_params->get('module') == 'seotext' ? ' class="current"' : '' ?>>
          <?= link_to('Посадочные страницы', '@seo_text') ?>
        </li>
      <?php endif ?>
      <?php if ($sf_user->hasCredential('admin')): ?>
        <li<?= $sf_params->get('module') == 'stat' ? ' class="current"' : '' ?>>
          <?= link_to('Статистика выгрузок', '@stat') ?>
        </li>
      <?php endif ?>
      <?php if ($sf_user->hasCredential(array('admin','log'), true)): ?>
        <li<?= $sf_params->get('module') == 'lot_log' ? ' class="current"' : '' ?>>
          <?= link_to('Изменения объектов', '@lot_log') ?>
        </li>
      <?php endif ?>
      <?php if ($sf_user->hasCredential('admin')): ?>
        <li<?= $sf_params->get('module') == 'default' ? ' class="current"' : '' ?>>
          <?= link_to('Опции', '@default?module=default&action=config') ?>
        </li>
      <?php endif ?>

      <li style="font-style: italic;">
        <?= link_to('Сайт', '/', 'target=_blank') ?>
      </li>
      <li style="font-style: italic;">
        <?= link_to('Выход', 'default/logout') ?>
      </li>
      <li style="font-style: italic; position: absolute; right: 0px; margin-right: 15px;">
        Добро пожаловать, <strong><?= $sf_user->getAttribute('username') ?></strong>!
      </li>
    </ul>

    <?= $sf_content ?>

  </body>
</html>
