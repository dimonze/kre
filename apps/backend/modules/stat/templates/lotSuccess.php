<?php use_helper('I18N', 'Date') ?>
<div id="sf_admin_container"> 
   <?php include_partial('menu'); ?>
<div class="sf_admin_list ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix" style="width:99%">
    <table cellspacing="0">
      <caption class="ui-widget-header ui-corner-top" align="top">
        <h1><span class="ui-icon ui-icon-triangle-1-s"></span> Список лотов которые не будут выгружены</h1>
      </caption>
      <thead class="ui-widget-header">
        <tr>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">
           <?php if ('l.id' == $sort[0]): ?>
            <?php echo link_to(__('ID', array(), 'messages'), '@statId?id=bad&sort=l.id&sort_type='.($sort[1] == 'asc' ? 'desc' : 'asc')) ?>
            <?php echo image_tag(sfConfig::get('sf_admin_module_web_dir').'/images/'.$sort[1].'.png', array('alt' => __($sort[1], array(), 'sf_admin'), 'title' => __($sort[1], array(), 'sf_admin'))) ?>
          <?php else: ?>
            <?php echo link_to(__('ID', array(), 'messages'), '@statId?id=bad&sort=l.id&sort_type=asc') ?>
          <?php endif; ?>
          </th>          
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">
           <?php if ('l.type' == $sort[0]): ?>
            <?php echo link_to(__('ТИП', array(), 'messages'), '@statId?id=bad&sort=l.type&sort_type='.($sort[1] == 'asc' ? 'desc' : 'asc')) ?>
            <?php echo image_tag(sfConfig::get('sf_admin_module_web_dir').'/images/'.$sort[1].'.png', array('alt' => __($sort[1], array(), 'sf_admin'), 'title' => __($sort[1], array(), 'sf_admin'))) ?>
          <?php else: ?>
            <?php echo link_to(__('ТИП', array(), 'messages'), '@statId?id=bad&sort=l.type&sort_type=asc') ?>
          <?php endif; ?>
          </th>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">скрыта цена</th>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">Не заполненно поле общая цена от</th>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">Экспортируемость</th>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">Если загород является надобъектом</th>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">Если коммерция и заполненно поле цена до</th>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">Если коммерция и заполненно поле площадь до</th>
        </tr>
      </thead>   
      <tbody>
        <?php foreach ($badLots as $value): ?>  
          <tr class="sf_admin_row ui-widget-content odd">
            <td class="sf_admin_text sf_admin_list_td_id"><a class="admin-link" href="/backend.php/lot/<?= $value->id ?>/edit"><?= $value->id ?></a></td>
            <td class="sf_admin_text sf_admin_list_td_id">
              <?php if ($value->type): ?>
                <?= $types[$value->type] ?>
              <?php endif; ?>
            </td>
            <td class="sf_admin_text sf_admin_list_td_id">
              <?php if ($value->hide_price == '1'): ?>          
                скрыта цена
              <?php endif; ?>
            </td>
            <td class="sf_admin_text sf_admin_list_td_name">
              <?php if ($value->price_all_from <= 0): ?>          
                Не заполненно поле общая цена от.
              <?php endif; ?>
            </td>
            <td class="sf_admin_text sf_admin_list_td_name">
              <?php if ($value->exportable != '1'): ?>          
                не проставленна экспортируемость
              <?php endif; ?>
            </td>
            <td class="sf_admin_text sf_admin_list_td_name">
              <?php if (($value->type == 'cottage' || $value->type == 'outoftown') && $value->has_children == '1'): ?>          
                Является надобъектом загорода
              <?php endif; ?>
            </td>                  
            <td class="sf_admin_text sf_admin_list_td_name">
              <?php if ($value->price_all_to > 0): ?>
                заполненна цена до
              <?php endif; ?>
            </td>
            <td class="sf_admin_text sf_admin_list_td_name">
              <?php if ($value->area_to > 0): ?>
                заполненна площадь до 
              <?php endif; ?>
            </td>        
          </tr>
        <?php endforeach; ?>
      </tbody>       
    </table>
  </div>
</div>