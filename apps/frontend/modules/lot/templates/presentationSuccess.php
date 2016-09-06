<?php slot('no_stat', ' ') ?>
<div class="smallwidth presentation">
<div class="h-hide">
<table id="print_header" class="contact-info">
	<tr>
		<th><img src="/pics/contact-logo-print.gif" width="283" height="62" alt="Агентство «Контакт — Элитная Недвижимость»"/></th>
		<td><p>Москва, ул. Арбат, д. 13/36<br/>Телефон: +7 <?= sfConfig::get('app_phones_office_' . $lot->type) ?><br/><?= link_to('http://www.kre.ru', '@homepage') ?></p></td>
	</tr>
</table>
</div>
<div class="page presentation">
<div class="cside c_full"><div class="padding">
	<div class="clear29"></div>
	<div class="separator"></div>
	<div id="content" class="presentation print-pres<?= ($sf_user->isAuthenticated() ? ' pr_editable' : '')?>">
		<p class="lot" value="<?= $lot->id ?>">Лот: <?= $lot->id ?></p>
		<h2><?= $lot ?></h2>
		<p class="pres-image"><?= $lot->image_source ? image_tag($lot->getImage('pres')) : '' ?><?php $lot->getImage('pres_') ?></p>
		<div <?= ($sf_user->isAuthenticated() ? 'contenteditable="true"' : '') ?> class="presentation description <?= ($sf_user->isAuthenticated() ? 'auth' : '')?>">
		<P><?= $lot->getRaw('anons')?></P>
		</div>
		<div id='lot-info-table' class='lot-info-table'>
      <p>
        <?php if ($lot->is_country_type): ?>
          <?php if($lot->pretty_wards): ?>
            <b>Направление:</b> <span class="upd-service" <?= ($sf_user->isAuthenticated() ? 'contenteditable="true"' : '') ?>><?= $lot->pretty_wards ?></span> &nbsp;
          <?php endif ?>
          <?php if (isset($lot->params['distance_mkad'])): ?>
            <br/>
            <b>Удаленность от МКАД:</b> <span class="upd-service" <?= ($sf_user->isAuthenticated() ? 'contenteditable="true"' : '') ?>><?= $lot->params['distance_mkad'] ?></span>
          <?php endif ?>
          <?php if (isset($lot->params['locality'])): ?>
            <br/>
            <b>Населённый пункт:</b> <span class="upd-service" <?= ($sf_user->isAuthenticated() ? 'contenteditable="true"' : '') ?>><?= $lot->params['locality'] ?></span>
          <?php endif ?>
          <?php if (isset($lot->params['cottageVillage'])): ?>
            <br/>
            <b>Коттеджный посёлок:</b> <span class="upd-service" <?= ($sf_user->isAuthenticated() ? 'contenteditable="true"' : '') ?>><?= $lot->params['cottageVillage'] ?></span>
          <?php endif ?>
        <?php endif ?>
        <?php if (!$lot->is_country_type && $lot->district): ?>
          <b>Район:</b> <span class="upd-service" <?= ($sf_user->isAuthenticated() ? 'contenteditable="true"' : '') ?>><?= $lot->district ?></span> &nbsp;
        <?php endif ?>
        <?php if (!$lot->is_country_type && $lot->metro): ?>
          <?= image_tag('/pics/i/metro.gif')?> <span <?= ($sf_user->isAuthenticated() ? 'contenteditable="true"' : '') ?>><?= $lot->metro ?></span>
        <?php endif ?>
        <?php if ($lot->is_city_type && !empty($lot->params['estate']) && $lot->type != 'elitenew'): ?>
          <br />
          <b>Жилой комплекс:</b> <span class="upd-service" <?= ($sf_user->isAuthenticated() ? 'contenteditable="true"' : '') ?>><?= $lot->params['estate'] ?></span>
        <?php endif ?>
      </p>
      <?php include_partial('item-area-price', array('lot' => $lot, 'mode' => 'both', 'presentation' => true)) ?>

		</div>
		<div class="clear"></div>

    <?php $params = isset($params_groupped) ? $params_groupped : $lot->getParamsGrouppedFiltered($lot->is_commercial_type ? false : true)?>
    <?php include_partial('item-params', array(
      'lot'     => $lot,
      'params'  => $lot->is_sterile ? $params['supobject'] : $params['both'],
      'presentation'  => true
    )) ?>
    <p class="not-for-pdf"><a class="icons i_photos" rel="<?= $lot->id ?>|<?= $lot->type ?>" href="#">Выбрать фото для размещения в презентацию</a>.</p>
    <div class="for-images print-images"></div>
    <div class="clear"></div>
    <p class="pult not-for-pdf">
      <a class="icons i_print" href="javascript:print()">Распечатать</a> |
      <a class="icons i_pdf" href="#" id="openlink">Открыть в PDF</a> |
      <a class="icons i_pdf" href="<?= url_for('@save_pdf') ?>" id="savelink">Сохранить в PDF</a>
      <?php if ($is_auth): ?>
        <span style="float: right; width: 150;">
          <a href="#" class="trigger no-headers">Без заголовков</a> |
          <a  href="#" class="trigger no-watermarks">Без водяных знаков</a>
        </span>
      <?php endif ?>
    </p>
		<div class="separator"></div>
		<p class="h-hide bottom">Данная страница распечатана с сайта <?= link_to('http://www.kre.ru', '@homepage') ?></p>
	</div>
</div></div>
</div></div>
<!-- phone:"<?= sfConfig::get('app_phones_office_' . $lot->type) ?>" -->