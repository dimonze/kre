<div id="contacts-form">
<h2>Связаться с нами:</h2>
<form action="<?= url_for('@contacts') ?>" method="post" class="form">
  <div class="upd-form">

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
        <div class="input"><input type="text" name="contacts[fio]" value="<?= $form['fio']->getValue() ?>"></div>
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
        <div class="input"><input type="text" name="contacts[email]" value="<?= $form['email']->getValue() ?>"></div>
      </div></div>
    </div>

    <?php $text_error = $form['text']->getError(); ?>
    <div class="upd-form-field <?= !empty($text_error) ? 'upd-form-field-error' : ''?>">
      <div class="upd-form-label">Сообщение:</div>
      <div class="select select_ta upd-form-select-ta">
        <div class="select_r_bg">
          <div class="input">
            <div class="input2">
              <textarea rows="5" cols="40" name="contacts[text]"><?= $form['text']->getValue()?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

      <div class="select select_btn select_left upd-form-submit">
          <div class="select_r_bg"> <span class="select_button">Отправить сообщение</span>
            <input type="submit" value="Отправить сообщение">
          </div>
      </div>

      <div class="clear"></div>

      <div class="upd-form-footer">
        <span class="upd-form-ast">*</span> – поля, обязательные к заполнению
      </div>

  </div>
</form>
</div>