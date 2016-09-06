<div id="lot_params">
  <?php foreach (Param::$_map[$type]['base'] as $field): ?>            
  <?php if($form[$field['field']]->getName() == 'payback' || 
          $form[$field['field']]->getName() == 'yield' || 
          $form[$field['field']]->getName() == 'm_a_p' ||
          $form[$field['field']]->getName() == 'm_a_p_Currency'): ?> 
  <div class="sf_admin_form_row sf_admin_text sf_admin_form_<?= $form[$field['field']]->getName()?>">
          <div>              
            <?= $form[$field['field']]->renderLabel() ?> 
            <?= $form[$field['field']]->render() ?>
          </div>
        </div>
  <?php endif; ?> 
  <?php endforeach ?> 
  <?php $flag = false; ?>
  <?php if ($form): ?>
    <?php foreach (Param::$_map[$type] as $group => $fields): ?>
      <h1><?= Param::$_types[$group] ?></h1>      
      <?php foreach ($fields as $field): ?>
      <?php if($form[$field['field']]->getName() == 'payback' || 
              $form[$field['field']]->getName() == 'yield' || 
              $form[$field['field']]->getName() == 'm_a_p_Currency' ||
              $form[$field['field']]->getName() == 'm_a_p') continue;?>      
      <?php if($form[$field['field']]->getName() == 'price_land_from' || $form[$field['field']]->getName() == 'price_land_to'):?>
        <?php if($form[$field['field']]->getName() == 'price_land_from'):?>
          <?php $price_land_from = $form[$field['field']]; ?>
        <?php endif; ?>
        <?php if($form[$field['field']]->getName() == 'price_land_to'):?>
          <?php  $price_land_to = $form[$field['field']]; ?>
            <?php if($price_land_from && $price_land_to && $flag == false): ?> 
              <div class="sf_admin_form_row sf_admin_text sf_admin_form_<?= $price_land_from->getName()?>">
                 <div> 
                 <label for="$price_land_from->getName()">Стоимость за сотку</label>
                 <?= $price_land_from->render() ?>
                 —
                 <?= $price_land_to->render() ?>
                 <?php $flag = true; ?>
                </div>
             </div>  
          <?php endif; ?> 
        <?php endif; ?>        
      <?php else: ?>
        <div class="sf_admin_form_row sf_admin_text sf_admin_form_<?= $form[$field['field']]->getName()?>">
          <div>                 
            <?= $form[$field['field']]->renderLabel() ?>
            <?= $form[$field['field']]->render() ?>
          </div>
        </div>
      <?php endif; ?>
      <?php endforeach ?>
      <hr />
    <?php endforeach ?>
  <?php endif; ?>
</div>
<script type="text/javascript">
  jQuery("#LotParams_settlements_tba")
  .autocomplete('<?= sfContext::getInstance()->getController()->genUrl('@default?module=lot&action=TbaList', true) ?>', {cacheLength: 0})
  .result(function(event, data) { jQuery("#LotParams_settlements_tba").val(data[1]); });
</script>
<script type="text/javascript"> 
  jQuery( document ).ready(function() {
  if(jQuery("#lot_has_children").is(':checked')){
    jQuery("#LotParams_premium_cian").attr('disabled', 'disabled');
  }
  });
    jQuery("#lot_has_children").change(function() {
      if(jQuery("#lot_has_children").is(':checked')){
         jQuery("#LotParams_premium_cian").attr('disabled', 'disabled');
      }
      else{
        jQuery("#LotParams_premium_cian").removeAttr('disabled');
      }
  });
</script>