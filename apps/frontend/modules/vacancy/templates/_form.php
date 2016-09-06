<form action="<?= url_for('resume_send') ?>" method="post" class="form" enctype="multipart/form-data">
  <input type="hidden" name="resume[vacancy_id]" value="<?= $vacancy_id ?>"/>
  <fieldset>
    <div>
      <table style="width: 100%;">
        <tbody>
          <tr>
            <td style="width: 40%;">
              <strong>ФИО: <sup>*</sup></strong>
              <div class="select">
                <div class="select_r_bg">
                  <div class="input"><input name="resume[fio]" class="input-fio"/></div>
                </div>
              </div>
            </td>
            <td></td>
          </tr>

          <tr>
            <td>
              <strong>Адрес электронной почты: <sup>*</sup></strong>
              <div class="select">
                <div class="select_r_bg">
                  <div class="input"><input name="resume[email]" class="input-email"/></div>
                </div>
              </div>
            </td>
            <td></td>
          </tr>

          <tr>
            <td>
              <strong>Контактный телефон:</strong>
              <div class="select">
                <div class="select_r_bg">
                  <div class="input"><input name="resume[phone]" class="input-phone"/></div>
                </div>
              </div>
            </td>
            <td></td>
          </tr>

          <tr>
            <td>
              <strong>Короткое описание:</strong>
              <div class="select_place sb">
                <div class="select">
                  <div class="select_r_bg">
                    <div class="input"><textarea name="resume[text]" class="input-text"></textarea></div>
                  </div>
                </div>
              </div>
            </td>
            <td></td>
          </tr>

          <tr>
            <td>
              <strong>Приложить файл:</strong>
              <input type="file" name="resume[file]" class="input-file"/>
            </td>
            <td></td>
          </tr>

          <tr>
            <td>
              <div class="select select_btn" style="float:left">
                <div class="select_r_bg">
                  <span class="select_button" style="color: #fff;">Отправить</span>
                  <input type="submit" value="Отправить"/>
                </div>
              </div>
            </td>
            <td></td>
          </tr>

          <tr>
            <td><sup>*</sup> — <i>поля, обязательные к заполнению</i></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
  </fieldset>
</form>