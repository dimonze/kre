<div class="cside c_full">
  <div class="padding">
    <?php include_component('menu', 'breadcrumbs') ?>
    <div class="separator"></div>
    <div id="content">
      <h2>Заказать каталог элитной недвижимости:</h2>
      <?php if(empty($save)): ?>
        <p>Заполните форму ниже и мы отправим вам каталог элитной недвижимости.</p>
        <p>
          <a href="/images/ordercatalog-1.jpeg" rel="gal" class="gallery cboxElement">
            <img src="/images/ordercatalog-1s.jpeg" style="border:1px solid #CCC">
          </a>
          <a href="/images/ordercatalog-2.jpeg" rel="gal" class="gallery cboxElement">
            <img src="/images/ordercatalog-2s.jpeg" style="border:1px solid #CCC">
          </a>
        </p>

        <div id="ord-cat-form">
          <form action="<?= url_for('@ordercatalog') ?>" method="post" class="form">
            <div class="upd-form">
              <?= $form['_csrf_token']->render(); ?>

              <?php $fio_error = $form['fio']->getError(); ?>
              <div class="upd-form-field <?= !empty($fio_error) ? 'upd-form-field-error' : ''?>">
                <div class="upd-form-label">Представьтесь, пожалуйста: <span class="upd-form-ast">*</span></div>
                <div class="upd-form-hint"><table><tbody><tr>
                  <td>
                    <?php if (empty($fio_error)): ?>
                      Введите Ваше имя/отчество
                    <?php else: ?>
                      <?= $fio_error ?>
                    <?php endif ?>
                  </td>
                  </tr></tbody></table>
                </div>
                <div class="select upd-form-select"><div class="select_r_bg">
                  <div class="input"><input type="text" name="ordercatalog[fio]" value="<?= $form['fio']->getValue() ?>"></div>
                </div></div>
              </div>

              <?php $email_error = $form['email']->getError(); ?>
              <div class="upd-form-field <?= !empty($email_error) ? 'upd-form-field-error' : ''?>">
                <div class="upd-form-label">Адрес электронной почты: <span class="upd-form-ast">*</span></div>
                <div class="upd-form-hint"><table><tbody><tr>
                  <td>
                    <?php if (empty($email_error)): ?>
                      <?= "Введите Ваш адрес электронной почты, по которому мы можем прислать наши предложения" ?>
                    <?php else: ?>
                      <?= $email_error ?>
                    <?php endif ?>
                  </td></tr></tbody></table></div>
                <div class="select upd-form-select"><div class="select_r_bg">
                  <div class="input"><input type="text" name="ordercatalog[email]" value="<?= $form['email']->getValue() ?>"></div>
                </div></div>
              </div>

              <?php $phone_error = $form['phone']->getError()?>
              <div class="upd-form-field <?= !empty($phone_error) ? 'upd-form-field-error' : ''?>">
                <div class="upd-form-label">Контактный телефон: <span class="upd-form-ast">*</span></div>
                <div class="upd-form-hint"><table><tr>
                    <?php if (empty($phone_error)): ?>
                      <td>Введите пожалуйста, номер Вашего телефона в формате +7 (495) 956-77-99</td>
                    <?php else: ?>
                      <td><?= $phone_error ?></td>
                    <?php endif ?>
                  </tr></table>
                </div>
                <div class="select upd-form-select"><div class="select_r_bg">
                  <div class="input">
                    <input type="text" name="ordercatalog[phone]" value="<?= $form['phone']->getValue()?>" />
                  </div>
                </div></div>
              </div>

              <?php $budget_error = $form['budget']->getError()?>
              <div class="upd-form-field <?= !empty($budget_error) ? 'upd-form-field-error' : ''?>">
                <div class="upd-form-label">Предложения в каком бюджете вам интересны: <span class="upd-form-ast">*</span></div>
                <div class="upd-form-hint"><table><tr>
                    <?php if (empty($budget_error)): ?>
                      <td></td>
                    <?php else: ?>
                      <td><?= $budget_error ?></td>
                    <?php endif ?>
                  </tr></table>
                </div>
                <div class="select upd-form-select"><div class="select_r_bg">
                  <div class="select">
                    <div class="select_r_bg">
                      <?php $value = $form['budget']->getValue(); if(empty($value)) $value = 0; ?>
                      <input type="hidden" id="sel_id1_v" name="ordercatalog[budget]" value="<?= $value ?>" />
                      <ul style="top: 0px; cursor:pointer;" id="sel_id1" class="sel-order-cat-budget">
                        <li id="sel_id1_0"><span rel="0">дешевле 1&nbsp;000&nbsp;000$</span></li>
                        <li id="sel_id1_0"><span rel="0">дешевле 1&nbsp;000&nbsp;000$</span></li>
                        <li id="sel_id1_1"><span rel="1">1&nbsp;000&nbsp;000$–3&nbsp;000&nbsp;000$</span></li>
                        <li id="sel_id1_2"><span rel="2">3&nbsp;000&nbsp;000$–5&nbsp;000&nbsp;000$</span></li>
                        <li id="sel_id1_3"><span rel="3">дороже 5&nbsp;000&nbsp;000$</span></li>
                      </ul>
                      <span class="sel_type_upd1"></span>
                    </div>
                  </div>
                </div></div>
              </div>

              <?php $version_error = $form['version']->getError()?>
              <div class="upd-form-field <?= !empty($version_error) ? 'upd-form-field-error' : ''?>">
                <div class="upd-form-label">Выберите версию каталога: <span class="upd-form-ast">*</span></div>
                <div class="upd-form-hint"><table><tr>
                    <?php if (empty($version_error)): ?>
                      <td></td>
                    <?php else: ?>
                      <td><?= $version_error ?></td>
                    <?php endif ?>
                  </tr></table>
                </div>
                <div class="select upd-form-select"><div class="select_r_bg">
                  <?php $value = $form['version']->getValue(); if(empty($value)) $value = 0; ?>
                  <input type="hidden" id="sel_id2_v" name="ordercatalog[version]" value="<?= $value ?>" />
                  <ul style="top: 0px; cursor:pointer;" id="sel_id2" class="sel-order-cat-version">
                    <li id="sel_id2_0" rel="0"><span rel="0">Электронная</span></li>
                    <li id="sel_id2_0" rel="0"><span rel="0">Электронная</span></li>
                    <li id="sel_id2_1" rel="1"><span rel="1">Печатная</span></li>
                  </ul>
                  <span class="sel_type_upd1"></span>
                </div></div>
              </div>

              <?php $address_error = $form['address']->getError(); ?>
              <div class="upd-form-field <?= !empty($address_error) ? 'upd-form-field-error' : ''?>">
                <div class="upd-form-label">Адрес:</div>
                <div class="select select_ta upd-form-select-ta">
                  <div class="select_r_bg">
                    <div class="input">
                      <div class="input2">
                        <textarea rows="5" cols="40" name="ordercatalog[address]"><?= $form['address']->getValue()?></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="select select_btn select_left upd-form-submit">
                <div class="select_r_bg"> <span class="select_button">Заказать каталог</span>
                </div>
              </div>

              <div class="clear"></div>

              <div class="upd-form-footer">
                <span class="upd-form-ast">*</span> – поля, обязательные к заполнению
              </div>

            </div>
          </form>
        </div>	
      <?php else: ?>
        <p>
          Ваша заявка принята. В ближайшее время с Вами свяжется наш сотрудник, для уточнения деталей.
        </p>
      <?php endif ?>
    </div>
  </div>
</div>