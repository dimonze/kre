<?php use_helper('I18N', 'Date') ?>
<div id="sf_admin_container">

    <?php include_partial('menu'); ?>

  <table class="eq-table">
    <tr>
      <td>
        <div class="sf_admin_list ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
          <table cellspacing="0">
            <caption class="ui-widget-header ui-corner-top" align="top">
              <h1><span class="ui-icon ui-icon-triangle-1-s"></span> Отчет по выгрузкe в Cian</h1>
            </caption>
            <tbody>
              <tr class="sf_admin_row ui-widget-content odd">
                <td class="sf_admin_text sf_admin_list_td_id">Общее количество лотов</td>
                <td class="sf_admin_text sf_admin_list_td_id"><?= $totalLots ?></td>
              </tr>
              <tr class="sf_admin_row ui-widget-content odd">
                <td class="sf_admin_text sf_admin_list_td_id">Количество лотов доступных к выгрузке</td>
                <td><?= $goodLots ?></td>
              </tr>
              <tr class="sf_admin_row ui-widget-content odd">
                <td class="sf_admin_text sf_admin_list_td_id">Количество лотов которые не будут выгружены</td>
                <td><?= $pager->getNbResults() ?></td>
              </tr>
              <tr class="sf_admin_row ui-widget-content odd">
                <td class="sf_admin_text sf_admin_list_td_id">Количество лотов у которых Неверный тип недвижимости</td>
                <td><?= $badType?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </td>
      <td>
        <div class="ui-widget info-block">
          <h1>Требования к заполнению лотов</h1>
          <ol>
            <li>Не должна быть скрытая цена(общее)</li>
            <li>Должна быть указана общая цена от(общее)</li>
            <li>Должна стоять галочка экспортируемость(общее)</li>
            <li>Лот должен быть активный(общее)</li>
            <li>Надобъекты загорода не выгружаются(загород)</li>
            <li>Аренда не выгружаются(общее)</li>
            <li>Для квартир обязательное поле Конструкция дома(квартиры)</li>

            <li>площадь участка(загород)</li>
            <li>тип объекта(загород)</li>
            <li>тип земельного участка(загород)</li>
            <li>не заполненно шоссе(загород)</li>

            <li>должен быть указан - тип объекта(коммерция)</li>
            <li>Лоты Коммерческой недвижимости, у которых проставлена цена и площадь от и до не выгружаются(коммерция)</li>
          </ol>
        </div>
      </td>
    </tr>
  </table>

  <div class="sf_admin_list ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix" style="width:100%">
    <table cellspacing="0">
      <caption class="ui-widget-header ui-corner-top" align="top">
        <h1><span class="ui-icon ui-icon-triangle-1-s"></span> Список лотов которые не будут выгружены</h1>
      </caption>
      <thead class="ui-widget-header">
        <tr>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">
           <?php if ('l.id' == $sort[0]): ?>
            <?php echo link_to(__('ID', array(), 'messages'), '@statId?id=cian&page='.$pager->getPage().'&sort=l.id&sort_type='.($sort[1] == 'asc' ? 'desc' : 'asc')) ?>
            <?php echo image_tag(sfConfig::get('sf_admin_module_web_dir').'/images/'.$sort[1].'.png', array('alt' => __($sort[1], array(), 'sf_admin'), 'title' => __($sort[1], array(), 'sf_admin'))) ?>
          <?php else: ?>
            <?php echo link_to(__('ID', array(), 'messages'), '@statId?id=cian&page='.$pager->getPage().'&sort=l.id&sort_type=asc') ?>
          <?php endif; ?>
          </th>          
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">
           <?php if ('l.type' == $sort[0]): ?>
            <?php echo link_to(__('ТИП', array(), 'messages'), '@statId?id=cian&page='.$pager->getPage().'&sort=l.type&sort_type='.($sort[1] == 'asc' ? 'desc' : 'asc')) ?>
            <?php echo image_tag(sfConfig::get('sf_admin_module_web_dir').'/images/'.$sort[1].'.png', array('alt' => __($sort[1], array(), 'sf_admin'), 'title' => __($sort[1], array(), 'sf_admin'))) ?>
          <?php else: ?>
            <?php echo link_to(__('ТИП', array(), 'messages'), '@statId?id=cian&page='.$pager->getPage().'&sort=l.type&sort_type=asc') ?>
          <?php endif; ?>
          </th>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">скрыта цена</th>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">Не заполненно поле общая цена от</th>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">Экспортируемость</th>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">Если загород является надобъектом</th>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">Для квартир обязательное поле Конструкция дома</th>

          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">площадь участка</th>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">тип объекта</th>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">тип земельного участка</th>
          <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">шоссе</th>

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
              <?php if (in_array($value->type, array('eliteflat','elitenew','penthouse')) && (empty($value->params['construction']) || $value->params['construction'] == '--')): ?>
                Не заполненна Конструкция дома
              <?php endif; ?>
            </td>
            <td class="sf_admin_text sf_admin_list_td_name">
              <?php if (($value->type == 'cottage' || $value->type == 'outoftown') && (empty($value->params['spaceplot']) || $value->params['spaceplot'] == '--')): ?>
                площадь участка не заполненна
              <?php endif; ?>
            </td>
            <td class="sf_admin_text sf_admin_list_td_name">
              <?php if (($value->type == 'outoftown' || $value->type == 'cottage'  || $value->type == 'comsell') && (empty($value->params['objecttype']) || $value->params['objecttype'] == '--')): ?>
                тип объекта не заполнен
              <?php endif; ?>
            </td>
            <td class="sf_admin_text sf_admin_list_td_name">
              <?php if (($value->type == 'cottage' || $value->type == 'outoftown') && (empty($value->params['type_of_land']) || $value->params['type_of_land'] == '--')): ?>
                Категория земель не заполненна
              <?php endif; ?>
            </td>
            <td class="sf_admin_text sf_admin_list_td_name">
              <?php if (($value->type == 'cottage' || $value->type == 'outoftown') && empty($value->ward)): ?>
                шоссе не заполненно
              <?php endif; ?>
            </td>

            <td class="sf_admin_text sf_admin_list_td_name">
              <?php if ($value->type == 'comsell' && $value->price_all_to > 0): ?>
                заполненна цена до
              <?php endif; ?>
            </td>
            <td class="sf_admin_text sf_admin_list_td_name">
              <?php if ($value->type == 'comsell' && $value->area_to > 0): ?>
                заполненна площадь до
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="13">
      <div class="ui-state-default ui-th-column ui-corner-bottom">
        <div class="sf_admin_pagination" id="sf_admin_pager">
          <?php
          $first = ($pager->getPage() * $pager->getMaxPerPage() - $pager->getMaxPerPage() + 1);
          $last = $first + $pager->getMaxPerPage() - 1;
          ?>
          <?php
          echo __('%1% - %2% of %3%', array(
              '%1%' => $first,
              '%2%' => ($last > $pager->getNbResults()) ? $pager->getNbResults() : $last,
              '%3%' => $pager->getNbResults()
                  )
          );
          ?>
          <?php if ($pager->haveToPaginate()): ?>
            | <?php echo link_to_if($pager->getPage() > 1, __('First'), '@statId?id=cian&page=1&sort=' . $sort[0] . '&sort_type=' . $sort[1]) ?>
            | <?php echo link_to_if($pager->getPage() > 1, __('Prev'), '@statId?id=cian&page=' . $pager->getPreviousPage().'&sort=' . $sort[0].'&sort_type='.$sort[1]) ?>
            | <?php echo link_to_if($pager->getPage() < $pager->getLastPage(), __('Next'), '@statId?id=cian&page=' . $pager->getNextPage(). '&sort=' . $sort[0].'&sort_type='.$sort[1]) ?>
            | <?php echo link_to_if($pager->getPage() < $pager->getLastPage(), __('Last'), '@statId?id=cian&page=' . $pager->getLastPage(). '&sort=' . $sort[0].'&sort_type='.$sort[1]) ?>
          <?php endif; ?>
        </div>
      </div>
      </th>
      </tr>
      </tfoot>
    </table>
  </div>
</div>
