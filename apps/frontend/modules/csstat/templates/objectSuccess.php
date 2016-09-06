<div class="cside c_full">
  <div class="padding">
    <div class="upd-cside-path"></div>
    <div class="separator"></div>
    <div id="content">
      <h2>Статистика по объектам</h2>
      <form method="get">
        <table>
          <tr>
            <td><b>Статус</b><br/><?= $form['status'] ?></td>
            <td><b>Раздел</b><br/></b><?= $form['type'] ?></td>
            <td><b>Рынок</b><br/></b><?= $form['market'] ?></td>
            <td><b>Тип Объекта</b><br/></b><?= $form['objectType'] ?></td>
            <td><br>&nbsp;<input type="submit" value="Найти"></td>
          </tr>
        </table>
        <script>
              jQuery( document ).ready(function() {
                
                var types = [
                        'comsell',
                        'comrent',
                        'cottage',
                        'outoftown'
                ];   
                var commerce = '<option value="all" selected="selected">Все</option>' + jQuery("optgroup[label='Коммерция']").html();
                var country = '<option value="all" selected="selected">Все</option>' + jQuery("optgroup[label='Загород']").html();
              jQuery( "#type" ).change(function() {
                if(types.indexOf(jQuery("#type").val()) > -1){
                  jQuery("#objectType").removeAttr('disabled');  
                  if(jQuery("#type").val() === 'cottage' || jQuery("#type").val() === 'outoftown'){
                   jQuery("#objectType").html(country);
                  }
                  if(jQuery("#type").val() === 'comsell' || jQuery("#type").val() === 'comrent'){
                    jQuery("#objectType").html(commerce);
                  }
                }else{
                  jQuery("#objectType").attr('disabled', 'disabled');
                }
              });
              
              if(types.indexOf(jQuery("#type").val()) > -1){
                  jQuery("#objectType").removeAttr('disabled');  
                  if(jQuery("#type").val() === 'cottage' || jQuery("#type").val() === 'outoftown'){
                   jQuery("#objectType").html(country);
                  }
                  if(jQuery("#type").val() === 'comsell' || jQuery("#type").val() === 'comrent'){
                    jQuery("#objectType").html(commerce);
                  }
                }else{
                  jQuery("#objectType").attr('disabled', 'disabled');
                }
          });
        </script>
    
      </form>
      <hr>
      <?php if(!empty($result2)): ?>
      <p>
        <?php foreach($result2['counters'] as $type=>$counters) :?>
          <?php if($counters['sum'] > 0): ?>
            в разделе <b><?= link_to2(
                '&laquo;' . Lot::$_types[$type] . '&raquo;',
                'csstat_acts',
                array('action' => 'object', 'status' => $sf_request->getParameter('status'), 'type' => $sf_request->getParameter('type')))
              ?></b>
            <?php if($sf_request->getParameter('objectType') != 'all' && $sf_request->getParameter('objectType') != ''): ?>
            с типом недвижимости <b>«<?= $sf_request->getParameter('objectType') ?>»</b>,
            <?php endif; ?>             
            <b><?= $counters['active'] ?></b> активных,
            <b><?= $counters['hidden'] ?></b> скрытых и <b><?= $counters['inactive'] ?></b> деактивированных предложений<br/>
          <?php endif; ?>
        <?php endforeach; ?>
      </p>
      <hr>
      <?php endif; ?>
      <?php if(!empty($result)): ?><?= link_to2(
      'Всё на одной странице',
      'csstat_acts',
      array(
        'action' => 'object',
        'status' => $sf_request->getParameter('status'),
        'type'   => $sf_request->getParameter('type'),
        'market' => $sf_request->getParameter('market'),
        'objectType' => $sf_request->getParameter('objectType'),
        'by'     => $sf_request->getParameter('by'),
        'dir'    => $sf_request->getParameter('dir'),
        'page'   => 'all',
      ))
      ?>
      <?php if($sf_params->get('page') !== 'all'): ?>
      <?php include_partial('global/paginator', array('pager' => $pager, 'params' => array(
        'status' => $sf_request->getParameter('status'),
        'type'   => $sf_request->getParameter('type'),
        'market' => $sf_request->getParameter('market'),
        'objectType' => $sf_request->getParameter('objectType'),
        'by'     => $sf_request->getParameter('by'),
        'dir'    => $sf_request->getParameter('dir'),
      ))) ?>
      <?php endif; ?>
      <table class="table-content csstat object">
        <tr>
          <?php foreach($fields as $name=>$value): ?>
            <th><?= link_to2(
              $value . ($sf_request->getParameter('by') == $name
                     ? ($sf_request->getParameter('dir') == 'asc' ? '&dArr;' : '&uArr;')
                     : ''),
              'csstat_acts',
              array(
                'action' => 'object',
                'status' => $sf_request->getParameter('status'),
                'type'   => $sf_request->getParameter('type'),
                'market' => $sf_request->getParameter('market'),
                'objectType' => $sf_request->getParameter('objectType'),
                'by'     => $name,
                'dir'    => $sf_request->getParameter('by') == $name
                  ? ($sf_request->getParameter('dir') == 'asc' ? 'desc' : 'asc')
                  : 'asc',
              ))
              ?></th>
          <?php endforeach;?>
        </tr>
        <?php foreach($result['types'] as $type=>$lots): ?>
          <?php if($result['counters'][$type] > 0): ?>
            <tr><td colspan="6"><?= Lot::$_types[$type] ?></td></tr>
            <?php foreach($lots as $lot): ?>
            <tr>
              <td><?= link_to2($lot['id'], 'offer', array('type' => $type, 'id' => $lot['id'])) ?></td>
              <td><?= $lot['rating'] ?></td>
              <td><?= $lot['name'] ?></td>
              <td><?= Lot::$_status[$lot['status']] ?></td>
              <td><?= $lot['area_from'] ?></td>
              <td><?= $lot['price_all_from'] ?> <?= $lot['currency']?></td>              
            </tr>
            <?php endforeach; ?>
          <?php endif;?>
        <?php endforeach; ?>
      </table>
      <?php endif;/*!empty($result)*/?>
      <p></p>
    </div>
  </div>
</div>
