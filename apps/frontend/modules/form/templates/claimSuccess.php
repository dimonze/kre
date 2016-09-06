<div class="cside c_full">
  <div class="padding">
    <?php include_component('menu', 'breadcrumbs') ?>
  <div class="separator"></div>
  <div id="content">
    <h1 class="upd-title-h1">Заявка</h1>
    <?php if(empty($save)): ?>
      <div class="upd-subtitle">Пожалуйста, заполните форму, чтобы мы могли использовать введенные вами данные для решения вашего вопроса.</div>
      <div class="separator"></div>
        <form method="post" action="<?= url_for('@claim') ?>">
        <div class="upd-form">
          <div class="upd-form-field">
              <?= $form['_csrf_token']->render(); ?>
              <div class="upd-form-label">Вы хотите:</div>
              <?php $types = $form['types']->getValue()?>
              <?php foreach(Claim::$_types as $type_id => $name): ?>
                <?php if ($type_id == 1 || $type_id == 7): ?>
                  <div class="upd-form-col">
                <?php endif ?>
                <?php if ($type_id % 2 == 1): ?>
                  <div class="upd-form-checkbox-group">
                <?php endif ?>
                <div class="upd-form-checkbox">
                  <input type="checkbox" name="claim[types][<?= $type_id ?>]" <?= (!empty($types) && array_key_exists($type_id, $types)) ? ' checked="checked"' : ''?> />
                  <label><?= $name ?></label>
                </div>
                <?php if ($type_id % 2 == 0): ?>
                  </div>
                <?php endif ?>
                <?php if ($type_id % 6 == 0): ?>
                  </div>
                <?php endif ?>
              <?php endforeach ?>
            </div>

            <?php $lot_error = $form['lot_id']->getError()?>
            <div class="upd-form-field <?= !empty($lot_error) ? 'upd-form-field-error' : ''?>">
                <div class="upd-form-label">Номер лота:</div>
                <div class="upd-form-hint"><table><tr>
                    <?php if (empty($lot_error)): ?>
                      <td>Введите номер интересующего Вас лота. Например, 1789</td>
                    <?php else: ?>
                      <td><?= $lot_error ?></td>
                    <?php endif ?>
                      </tr></table></div>
                <div class="select upd-form-select upd-form-select-short"><div class="select_r_bg">
                    <div class="input">
                      <input type="text" name="claim[lot_id]" value="<?= !empty($lot_id) ? $lot_id : $form['lot_id']->getValue() ?>" />
                    </div>
                </div></div>
            </div>

            <?php $fio_error = $form['fio']->getError()?>
            <div class="upd-form-field <?= !empty($fio_error) ? 'upd-form-field-error' : ''?>">
                <div class="upd-form-label">Представьтесь, пожалуйста: <span class="upd-form-ast">*</span></div>
                <div class="upd-form-hint"><table><tr>
                    <?php if (empty($fio_error)): ?>
                      <td>Введите Ваше имя/отчество</td>
                    <?php else: ?>
                      <td><?= $fio_error ?></td>
                    <?php endif ?>
                  </tr></table></div>
                <div class="select upd-form-select"><div class="select_r_bg">
                    <div class="input">
                      <input type="text" name="claim[fio]" value="<?= $form['fio']->getValue() ?>" />
                    </div>
                </div></div>
            </div>

            <?php $email_error = $form['email']->getError()?>
            <div class="upd-form-field <?= !empty($email_error) ? 'upd-form-field-error' : ''?>">
                <div class="upd-form-label">Адрес электронной почты: <span class="upd-form-ast">*</span></div>
                <div class="upd-form-hint"><table><tr>
                    <?php if (empty($email_error)): ?>
                      <td>Введите Ваш адрес электронной почты, по которому мы можем прислать наши предложения</td>
                    <?php else: ?>
                      <td><?= $email_error ?></td>
                    <?php endif ?>
                  </tr></table></div>
                <div class="select upd-form-select"><div class="select_r_bg">
                    <div class="input">
                      <input type="text" name="claim[email]" value="<?= $form['email']->getValue()?>" />
                    </div>
                </div></div>
            </div>

            <?php $phone_error = $form['phone']->getError()?>
            <div class="upd-form-field <?= !empty($phone_error) ? 'upd-form-field-error' : ''?>">
                <div class="upd-form-label">Контактный телефон: <span class="upd-form-ast">*</span></div>
                <div class="upd-form-hint"><table><tr>
                    <?php if (empty($phone_error)): ?>
                      <td>Для более удобного общения введите, пожалуйста, номер Вашего телефона в формате +7 (495) 956-77-99</td>
                    <?php else: ?>
                      <td><?= $phone_error ?></td>
                    <?php endif ?>
                  </tr></table>
                </div>
                <div class="select upd-form-select"><div class="select_r_bg">
                    <div class="input">
                      <input type="text" name="claim[phone]" value="<?= $form['phone']->getValue()?>" />
                    </div>
                </div></div>
            </div>

            <?php $description_error = $form['description']->getError()?>
            <div class="upd-form-field">
                <div class="upd-form-label">Дополнения:</div>
                <div class="upd-form-hint"><table><tr>
                    <?php if (!empty($description_error)): ?>
                      <td><?= $description_error ?></td>
                    <?php endif ?>
                  </tr></table>
                </div>
                <div class="select select_ta upd-form-select-ta">
                    <div class="select_r_bg">
                      <div class="input">
                        <div class="input2">
                          <textarea rows="5" cols="40" name="claim[description]"><?= $form['description']->getValue()?></textarea>
                        </div>
                      </div>
                    </div>
                </div>
            </div>

            <div class="select select_btn select_left upd-form-submit">
                <div class="select_r_bg"> <span class="select_button">Оставить заявку</span>
                  <input type="submit" onclick="yaCounter19895512.reachGoal('ADD'); return true;" value="Оставить заявку"/>
                </div>
            </div>

            <div class="clear"></div>

            <div class="upd-form-footer">
              <span class="upd-form-ast">*</span> &ndash; поля, обязательные к заполнению
            </div>
        </div>
      </form>
    <?php else: ?>
      <p>Ваша заявка принята. В ближайшее время с Вами свяжется наш сотрудник.</p>
    <?php endif ?>
  </div>
  </div>
</div>