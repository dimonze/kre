
<div id="sf_admin_container">

   <?php include_partial('menu'); ?>
  
  <table class="eq-table">
    <tr>
      <td>
        <div class="sf_admin_list ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
          <table cellspacing="0">
            <caption class="ui-widget-header ui-corner-top" align="top">
              <h1><span class="ui-icon ui-icon-triangle-1-s"></span> Отчет по выгрузкам</h1>
            </caption>
            <tbody>
              <tr class="sf_admin_row ui-widget-content odd"> 
                <td class="sf_admin_text sf_admin_list_td_id">Общее количество лотов</td>
                <td class="sf_admin_text sf_admin_list_td_id"><?= $totalLots ?></td>       
              </tr>
              <tr class="sf_admin_row ui-widget-content even">
                <td class="sf_admin_text sf_admin_list_td_id">Количество лотов доступных к выгрузке</td>
                <td><?= $goodLots ?></td> 
              </tr>
              <tr class="sf_admin_row ui-widget-content odd">
                <td class="sf_admin_text sf_admin_list_td_id">Количество лотов которые не будут выгружены</td>
                <td><a href="stat/bad" class="admin-link"><?= $badLotsCount ?></a></td> 
              </tr>
            </tbody>
          </table>
        </div>
      </td>
      <td>
        <div class="ui-widget info-block">
          <h1>Требования к заполнению лотов</h1>
          <ol>
            <li>Не должна быть скрытая цена</li>
            <li>Должна быть указана общая цена от</li>
            <li>Должна стоять галочка экспортируемость</li>
            <li>Лот должен быть активный</li>
            <li>Надобъекты загорода не выгружаются</li>
            <li>Лоты Коммерческой недвижимости, у которых проставлена цена и площадь от и до не выгружаются</li>
          </ol>
        </div>
      </td>
    </tr>
</div>
