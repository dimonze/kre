<div class="cside c_full">
  <div class="padding">
    <p class="print"><noindex><a target="_blank" href="<?= url_for2('offer_search', array_merge($sf_params->getAll()->getRawValue(), array('print' => 1))) ?>" rel="nofollow">Распечатать</a></noindex></p>
    <?php include_component('menu', 'breadcrumbs') ?>
    <div class="separator"></div>

    <div id="content" class="content_full">
      <h1 class="upd-title-h1">Поиск</h1>

      <div class="separator"></div>

      <div class="upd-form-search-result">
        <div class="upd-form-search-result-summary">
          Найдено <strong><?= $pager->getNbResults() ?></strong> предложения.
        </div>
        
        <form action="<?= url_for('lot/search') ?>" method="get">
          <input type="hidden" name="field" value="<?= $sf_params->get('field') ?>" />
          <input type="hidden" name="value" value="<?= $sf_params->get('value') ?>" />
          <input type="hidden" name="ids"   value="<?= $sf_params->get('ids') ?>" />

          <div class="upd-form upd-form-search">
            <div class="upd-form-field upd-form-field-line">
            <?php $types = $sf_params->getRaw('types') ?>
            <?php foreach ($counts as $item): ?>
              <div class="upd-form-checkbox">
                <input type="checkbox" name="types[]" 
                       value="<?= $item['actual_type'] ?>" 
                       id="type_<?= $item['actual_type'] ?>"
                       onclick="jQuery(this).closest('form').submit()"
                       <?= !$types || in_array($item['actual_type'], $types) ? 'checked="checked"' : '' ?>
                 />

                <label for="type_<?= $item['actual_type'] ?>">
                  <?= Lot::$_types[$item['actual_type']] ?>
                  (<?= $item['cnt'] ?>)
                </label>
              </div>
            <?php endforeach ?>
            </div>
          </div>
        </form>

        <div class="separator"></div>

        <div class="upd-form-search-result-link">
          <a href="<?= $_SERVER['REQUEST_URI']?>" class="upd-service">Постоянная ссылка</a> на эту страницу
        </div>
        <div class="upd-form-search-result-sort">
        <div class="upd-form-search-result-sort-title">Сортировать по:</div>
          <?php
            $by =  $sf_params->get('by');
            $dir = $sf_params->get('dir');
          ?>
          <?= link_to2('цене', 'offer_search', array_merge($sf_params->getAll()->getRawValue(), array(
              'by' => 'price',
              'dir' => (!empty($dir) && $by == 'price' && $dir == 'asc') ? 'desc' : 'asc',
            )),
            array(
               'class' => 'upd-form-search-result-sort-item ' .
                 (empty($dir) || $by != 'price' ? '' : ($dir == 'asc' ? 'upd-form-search-result-sort-active' : ' upd-form-search-result-sort-active upd-form-search-result-sort-desc'))
          )) ?>
          <?= link_to2('площади', 'offer_search', array_merge($sf_params->getAll()->getRawValue(), array(
              'by' => 'area',
              'dir' => (!empty($dir) && $by == 'area' && $dir == 'asc') ? 'desc' : 'asc',
            )),
            array(
              'class' => 'upd-form-search-result-sort-item ' .
                (empty($dir) || $by != 'area' ? '' : ($dir == 'asc' ? 'upd-form-search-result-sort-active' : ' upd-form-search-result-sort-active upd-form-search-result-sort-desc'))
          )) ?>

        </div>
        <div class="clear"></div>
      </div>

      <form>
        <?php foreach ($results as $lot): ?>
        <?php include_partial('list-item', array('lot' => $lot)) ?>
        <div class="separator upd-cat-info-separator"></div>
        <?php endforeach ?>
      </form>

      <?php $route_params = $sf_params->getAll()->getRawValue(); unset($route_params['page']) ?>
      <?php include_partial('global/paginator', array('pager' => $pager, 'params' => $route_params)) ?>
    </div>

    <p class="cat_present">
      <a class="present_check some-lots" href="#">Выбранные объекты</a>
      <a class="present_clear clearSelected" href="#">Cнять выделение</a>
    </p>
  </div>
</div>