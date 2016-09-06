<div class="cside c_full">
  <div class="padding">
    <div class="upd-cside-path"></div>
    <div class="separator"></div>
    <div id="content">
      <h2>Статистика по брокерам</h2>
      <form method="get">
        <?= $form['user'] ?> <?= $form['type'] ?> <?= $form['market'] ?><?= $form['objectType'] ?><input type="submit" value="Найти">
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
      <?php if(!empty($result)): ?>
      <p>
        <?= $broker['name'] ?> - <b><?= $broker['phone'] ?></b><br/>
        <?php foreach($result['counters'] as $type=>$counters) :?>
          <?php if($counters['sum'] > 0): ?>
            в разделе <b><?= link_to2(
              '&laquo;' . Lot::$_types[$type] . '&raquo;',
              'csstat_acts',
              array('action' => 'broker', 'user' => $sf_request->getParameter('user'), 'type' => $type))
            ?></b> имеет <b><?= $counters['active'] ?></b> активных,
          <b><?= $counters['hidden'] ?></b> скрытых и <b><?= $counters['inactive'] ?></b> деактивированных предложений<br/>
          <?php endif; ?>
        <?php endforeach; ?>
      </p>
      <hr>
      <table class="table-content csstat broker">
        <?php foreach($result['types'] as $type => $parts): ?>
          <?php if($result['counters'][$type]['sum'] > 0): ?>
            <tr>
              <th colspan="5"><b><?= link_to2(
                Lot::$_types[$type],
                'csstat_acts',
                array('action' => 'broker', 'user' => $sf_request->getParameter('user'), 'type' => $type))
                ?></b></th>
            </tr>
            <?php foreach($parts as $status=>$lots): ?>
              <?php if($result['counters'][$type][$status] > 0): ?>
              <tr>
                <td colspan=5><strong>
                  <?php if($status == 'active'): ?>
                    Активные предложения
                  <?php elseif($status == 'hidden'): ?>
                    Скрытые предложения
                  <?php elseif($status == 'inactive'): ?>
                    Деактивированые предложения
                  <?php endif; ?>
                </strong><br/></td>
              </tr>
              <tr>
                <?php foreach($fields as $name=>$value): ?>
                  <td class="<?= 'broker-'.$name ?>"><?= link_to2(
                    $value . ($sf_request->getParameter('by') == $name
                           ? ($sf_request->getParameter('dir') == 'asc' ? '&dArr;' : '&uArr;')
                           : ''),
                    'csstat_acts',
                    array(
                      'action' => 'broker',
                      'user'   => $sf_request->getParameter('user'),
                      'type'   => $sf_request->getParameter('type'),
                      'market' => $sf_request->getParameter('market'),
                      'objectType' => $sf_request->getParameter('objectType'),
                      'by'     => $name,
                      'dir'    => $sf_request->getParameter('by') == $name
                               ? ($sf_request->getParameter('dir') == 'asc' ? 'desc' : 'asc')
                               : 'asc',
                    ))
                    ?></td>
                <?php endforeach; ?>
              </tr>
              <?php foreach($lots as $lot): ?>
                <tr>
                  <td><?= link_to2($lot['id'], 'offer', array('type' => $type, 'id' => $lot['id'])) ?></td>
                  <td><?= $lot['rating'] ?></td>
                  <td><?= $lot['name'] ?></td>
                  <td><?= $lot['area_from'] ?></td>
                  <td><?= $lot['price_all_from'] ?> <?= $lot['currency']?></td>
                </tr>
              <?php endforeach; ?>
              <?php endif; /*$result['counters'][$type][$status]*/?>
            <?php endforeach; ?>
          <?php endif; /*$result['counters'][$type]['sum']*/?>
        <?php endforeach;?>
      </table>
      <?php endif;/*!empty($result)*/?>
      <p></p>
    </div>
  </div>
</div>
