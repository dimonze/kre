<h2 class="title_h2 h2_services">Услуги</h2>
<div class="separator"></div>
<?php if (!empty($services)): ?>
  <ul class="ul_list ul_noborder">
    <?php foreach ($services as $service): ?>
      <li>
        <?php if ($service->id == '2135'): ?>
        <?= link_to($service->name, '@services?id=' . $service->id, 'rel="nofollow"') ?>
        <?php else: ?>
        <?= link_to($service->name, '@services?id=' . $service->id) ?>
        <?php endif;?>
      </li>
    <?php endforeach ?>
  </ul>
<?php endif ?>
<h2 class="title_h2 h2_facebook">Contact Real Estate на Facebook</h2>
<div class="separator"></div>
<div class="fb-like-box" data-href="http://www.facebook.com/ContactRealEstate" data-rel="nofollow" data-width="293" data-show-faces="false" data-stream="false" data-header="false"></div>
