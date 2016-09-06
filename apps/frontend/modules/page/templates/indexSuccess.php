<div class="cside c_full">
  <div class="padding">
    <p class="print"><noindex><a href="?print=1" target="_blank" rel="nofollow">Распечатать</a></noindex></p>
    <?php include_component('menu', 'breadcrumbs') ?>
    <div class="separator"></div>
    <div id="content">
      <h2><!--HB--><?=$page->name?><!--HE--></h2>
      <?=$page->getRaw('body')?><!--TE-->

      <?php if ($page->route == 'contacts'): ?>
        <div id="ymaps-map-id_134884157478737282468" style="width: 600px; height: 377px;"></div>
        <div style="width: 600px; text-align: right;"></div>
        <script type="text/javascript">function fid_134884157478737282468(ymaps) {var map = new ymaps.Map("ymaps-map-id_134884157478737282468", {center: [37.59742300000001, 55.75176334341499], zoom: 16, type: "yandex#map"});map.controls.add("zoomControl").add("mapTools").add(new ymaps.control.TypeSelector(["yandex#map", "yandex#satellite", "yandex#hybrid", "yandex#publicMap"]));map.geoObjects.add(new ymaps.Placemark([37.597423, 55.751249], {balloonContent: "Contact Real Estate"}, {preset: "twirl#redDotIcon"}));};</script>
        <script type="text/javascript" src="http://api-maps.yandex.ru/2.0/?coordorder=longlat&load=package.full&wizard=constructor&lang=ru-RU&onload=fid_134884157478737282468"></script>
        <br />
      <?php endif ?>
      <div class="separator"></div>
      <?php if ($page->route == 'contacts'): ?>
        <?php if (empty($send)): ?>
          <?php include_partial('form/contacts_form', array('form' => $contact_form))?>
        <?php else: ?>
          <?php include_partial('form/contacts_form_success')?>
        <?php endif ?>
      <?php endif ?>
    </div>
  </div>
</div>