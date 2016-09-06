<div class="smallwidth">
  <table id="print_header" class="vis">
    <tr>
      <th><a href="http://www.kre.ru/"><img src="/pics/contact-logo-print.gif" alt="Агентство «Контакт — Элитная Недвижимость»"></a></th>
      <td><p>Москва, ул. Арбат, д. 13/36<br/>Телефон: (495) 956-77-99<br/><a href="http://www.kre.ru/">http://www.kre.ru/</a></p></td>
    </tr>
  </table>
  <div class="page" style="margin-top: 29px;">
    <div class="cside c_full"><div class="padding">
      <div class="separator"></div>
      <div id="content">
        <h2>Страница не найдена</h2>

        <p>Приносим свои извинения, Вы попали на страницу объекта, который был продан.</p>

        <p>
          У&nbsp;Вас есть возможность выбрать другой более подходящий вариант
          в&nbsp;<?= link_to('нашей базе', 'lot/main') ?>
          по&nbsp;следующим направлениям:
        </p>

        <ul>
          <?php foreach (Lot::$_types as $type => $name): ?>
            <li><?= link_to($name, 'lot/list?type=' . $type) ?></li>
          <?php endforeach ?>
        </ul>

        <p>Вы также можете связаться с&nbsp;нашими специалистами по телефону: <nobr>+7 (495) 956 77 99</nobr>.</p>

      </div>
    </div></div>
  </div>
</div>