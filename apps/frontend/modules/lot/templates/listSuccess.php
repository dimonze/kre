<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>
<?php
 $rawParams = $sf_params->getAll()->getRawValue();
 $params    = prepare_params_for_url($rawParams);
?>
<div class="cside c_full">
  <div class="padding">
    <p class="print"><noindex><a href="<?= url_for2(route_for_list($params), array_merge($params, array('print' => 1)))?>" rel="nofollow">Распечатать</a></noindex></p>
    <?php include_component('menu', 'breadcrumbs') ?>
    <div class="separator"></div>

  <div id="content" class="content_full">
    <h1 class="upd-title-h1"><?= h1(Lot::$_types[$type]) ?></h1>
    <div class="cat_description">
      <p>
        Всего предложений в разделе «<?= h1(Lot::$_types[$type], true) ?>»:
        <strong><?= $pager->getNbResults() ?></strong>.
        Вы можете просматривать по
        <span id="perPages">
          <noindex>
          <?php foreach (array(10, 20, 50) as $i => $pp): ?>
            <?php if ($pp == $per_page): ?>
              <b><?= $pp ?></b>
            <?php else: ?>
              <a href="?<?= http_build_query(array_merge($params, array('per_page' => $pp)))?>" rel="nofollow"><?= $pp ?></a>
            <?php endif ?>
            <?= 2 != $i ? '/' : '' ?>
          <?php endforeach ?>
          </noindex>
         </span> объектов,
          <span id="searchphrase">
            воспользоваться
            <a href="#" onclick="showSearch(); return false;">поиском по параметрам</a>
          </span>.
        Вы также можете выделить некоторые объекты и <a href="#" class="some-lots">просмотреть их отдельным списком</a>, либо <a href="#" onclick="clearSelected(); return false;" class="clearSelected">cнять выделение</a> (в настоящий момент выделенных объектов: <strong id="selectedOffersCount">0</strong>). <span id="slink"></span>
      </p>
    </div>
    <div class="separator"></div>

    <?php include_partial('lot/search-form/' . $type, array('form' => $form, 'type' => $type)) ?>

    <div class="separator"></div>

    <div class="upd-form-search-result">
      <div class="upd-form-search-result-summary">
        Найдено <strong><?= $pager->getNbResults() ?></strong> предложения.
      </div>
      <div class="upd-form-search-result-link">
        <a href="<?= url_for2(route_for_list($params), $params)?>" class="upd-service">Постоянная ссылка</a> на эту страницу
      </div>
      <?php include_partial('lot/list-result-sort') ?>
      <div class="clear"></div>
    </div>

    <form>
    <?php foreach ($results as $lot): ?>
      <?php include_partial('list-item', array('lot' => $lot)) ?>
      <div class="separator upd-cat-info-separator"></div>
    <?php endforeach ?>
    </form>

    <?php unset($rawParams['page']) ?>
    <?php include_partial('global/paginator', array('pager' => $pager, 'params' => $rawParams)) ?>
  </div>
  <?php if ($pager->haveToPaginate()): ?>
    <div class="separator"></div>
  <?php endif ?>
	<p class="cat_present">
		<a class="present_check some-lots" href="#">Выбранные объекты</a>
		<a class="present_clear clearSelected" href="#">Cнять выделение</a>
	</p>
  </div>
</div>