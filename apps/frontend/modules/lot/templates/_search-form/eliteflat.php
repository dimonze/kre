<?php slot('filter-map', get_partial('map')) ?>

<form action="<?= url_for('lot/list?type=' . $type) ?>" method="get">

  <div class="upd-form upd-form-search">
    <div class="upd-form-col">
      <div class="upd-form-field">
        <div class="upd-form-label">Номер лота:</div>
        <div class="select upd-form-select">
          <div class="select_r_bg">
            <div class="input"><?= $form['id'] ?></div>
          </div>
        </div>
        <div id="wait" class="search-form-wait search_wait" style="display: none;">
          <p style="margin: 5px 10px 0px -10px;">Пожалуйста, подождите, идет поиск лота…</p>
        </div>
        <div id="error" class="search-form-not-found" style="display: none">
          <p style="margin: 5px 10px 0px 10px; color: #9d1c20;">Лот с таким номером не найден!</p>
        </div>          
      </div>

      <div class="upd-form-field">
        <div class="upd-form-label">Площадь квартиры:</div>
        <div class="select upd-form-select upd-form-select-num"><div class="select_r_bg">
            <div class="input"><?= $form['area_from'] ?></div>
          </div></div>
        <div class="select upd-form-select upd-form-select-num"><div class="select_r_bg">
            <div class="input"><?= $form['area_to'] ?></div>
          </div></div>
        <span class="upd-form-select-text">м<sup>2</sup></span>
      </div>

      <div class="upd-form-field">
        <div class="upd-form-label">Цена квартиры:</div>
        <div class="select upd-form-select upd-form-select-num"><div class="select_r_bg">
          <div class="input"><?= $form['price_from'] ?></div>
        </div></div>
        <div class="select upd-form-select upd-form-select-num"><div class="select_r_bg">
          <div class="input"><?= $form['price_to'] ?></div>
        </div></div>

        <div class="upd-iwant-select"><div class="upd-iwant-select-rbg">
          <?= $form['currency'] ?>
          <?php $currencies = array('RUR' => 'руб.', 'EUR' => '€', 'USD' => '$'); ?>
          <div class="upd-iwant-select-control">
            <table><tr><td><?= ($form['currency']->getValue()) ? $currencies[$form['currency']->getValue()] : $currencies['RUR']?></td></tr></table>
          </div>
          <ul class="upd-iwant-select-list upd-select-list" style="display: none;">
            <li id="currency_RUR"><table><tr><td>руб.</td></tr></table></li>
            <li id="currency_USD"><table><tr><td>$</td></tr></table></li>
            <li id="currency_EUR"><table><tr><td>€</td></tr></table></li>
          </ul>
        </div></div>
      </div>

      <div class="upd-form-checkbox">
        <?= $form['no_price_ok'] ?>
        <?= $form['no_price_ok']->renderLabel('Учитывать объекты без цены') ?>
      </div>
    </div>

    <div class="upd-form-col">
      <div class="upd-form-field">
        <div class="upd-form-label"><br /></div>
        <span class="upd-form-select-text">
          <a href="#" onclick="showMap(); return false;">Выбрать район</a>
        </span>
        <span id="districtS">
          <?= $form['districts'] ?>
        </span>
      </div>

      <div class="upd-form-field">
        <div class="upd-form-label">Адрес:</div>
        <div class="select upd-form-select upd-form-select-wide">
          <div class="select_r_bg">
            <div class="input"><?= $form['street'] ?></div>
          </div>
        </div>
      </div>

      <div class="upd-form-field">
        <div class="upd-form-label">Жилой комплекс:</div>
        <div class="select upd-form-select upd-form-select-wide"><div class="select_r_bg">
            <div class="input"><?= $form['estate'] ?></div>
          </div></div>
      </div>
    </div>

    <div class="upd-form-col upd-form-col-extra">

      <?php if($sf_user->isAuthenticated()): ?>
        <div class="upd-form-label">Рынок:</div>

        <div class="upd-iwant-select" style="width: 121px;"><div class="upd-iwant-select-rbg">
            <?= $form['market'] ?>
            <?php $values = array('' => '') + Param::$_widget_properties['eliteflat']['market']['values']; ?>
            <?php $values = array_combine($values, $values); ?>
            <div class="upd-iwant-select-control">
              <table><tr><td><?= ($form['market']->getValue()) ? $values[$form['market']->getValue()] : 'Не важно'?></td></tr></table>
            </div>
            <ul class="upd-iwant-select-list upd-select-list" style="display: none;">
              <li id="market_"><table><tr><td>Не важно</td></tr></table></li>
              <li id="market_Первичный"><table><tr><td>Первичный</td></tr></table></li>
              <li id="market_Вторичный" style="background: url('/pics/upd-select.gif') no-repeat 0 -369px !important"><table><tr><td>Вторичный</td></tr></table></li>
            </ul>
          </div></div>
        <div class="clear" style="height: 16px;"><br/></br></div>
      <?php endif; ?>

      <div class="upd-form-label">Дополнительно:</div>

      <?= $form['decoration'] ?>
      <?= $form['balcony'] ?>
      <?= $form['parking'] ?>
    </div>

    <div class="clear"></div>

    <div class="upd-form-field upd-form-field-line">
      <table style=""><tr><td>
            <div class="upd-form-label">Выводить объявления:</div>
          </td><td>
            <div class="upd-form-checkbox">
              <?= $form['under_construction'] ?>
              <?= $form['under_construction']->renderLabel('Только строящиеся') ?>
            </div>
            <div class="upd-form-checkbox">
              <?= $form['only_new'] ?>
              <?= $form['only_new']->renderLabel('Только новые объекты') ?>
            </div><br>
            <div class="upd-form-checkbox">
              <?= $form['only_new_price'] ?>
              <?= $form['only_new_price']->renderLabel('Только объекты с новой ценой') ?>
            </div>
          </td></tr></table>
    </div>

    <div class="select select_btn select_left upd-form-submit">
      <div class="select_r_bg"> <span class="select_button">Найти</span>
        <input type="submit" value="Найти"/>
      </div>
    </div>

    <div class="upd-form-reset">
      <?= link_to('Очистить форму', 'lot/list?type=' . $type, 'class=upd-service') ?>
    </div>

    <div class="clear"></div>
  </div>
</form>