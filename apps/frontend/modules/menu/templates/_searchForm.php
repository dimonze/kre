<h2 class="title_h2 h2_search">Поиск лота</h2>
<div class="separator"></div>
<form action="<?= url_for('@offer_search') ?>" method="get" class="form search-form">
  <fieldset>
    <div class="upd-search-cond">
      <div class="upd-search-cond-title">Искать:</div>
      <?php if('homepage' == $mode): ?>
        <div class="upd-search-cond-item"><a href="#" rel="address|Введите адрес">Адрес</a></div>
        <div class="upd-search-cond-slash"></div>
        <div class="upd-search-cond-item"><a href="#" rel="name|Введите название">Название</a></div>
        <div class="upd-search-cond-slash"></div>
      <?php endif; ?>
      <div class="upd-search-cond-item upd-search-cond-active"><a href="#" rel="lot|Введите номер">Лот</a></div>
    </div>
    <div class="select_place">
      <table style="width: 100%;">
        <tr>
          <td>
            <div class="select">
              <div class="select_r_bg">
                <div class="input">
                  <input type="hidden" name="field" value="lot">
                  <input name="value" id="lot" rel="Введите номер" value="Введите номер" style="width: 80%;">
                </div>
              </div>
            </div>
          </td>
          <td style="width: 1%;padding-left: 10px;">
            <div class="select select_btn">
              <div class="select_r_bg">
                <span class="select_button" style="color: #fff;">Найти</span>
                <input type="submit" value="Найти"/>
              </div>
            </div>
          </td>
        </tr>
      </table>
    </div>
    <div class="search-form-wait search_wait" id="wait" style="display: none;"><p style="margin: 2px 10px 0px;">Пожалуйста, подождите, идет поиск лота…</p></div>
    <div style="display: none;" id="error" class="search-form-not-found"><p style="margin: 2px 10px 0px; color: #9d1c20;">Лот с таким номером не найден!</p></div>
  </fieldset>
</form>