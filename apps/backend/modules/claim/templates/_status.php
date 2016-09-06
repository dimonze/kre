<select name="claim_status">
  <?php foreach (Doctrine::getTable('Claim')->getEnumValues('status') as $status): ?>
    <option value="<?= $status ?>"<?= $claim->status == $status ? ' selected="selected"' : '' ?>><?= Claim::$_statuses[$status] ?></option>
  <?php endforeach ?>
</select>
<?= link_to('Сменить', 'claim/changeStatus?id='.$claim->id.'&status=', array(
  'class'   => 'sf_button_inline ui-state-default ui-priority-secondary sf_button ui-corner-all',
  'onclick' => 'var link=this.href+$(this).siblings("select").val();$.ajax({url: link});return false;',
)) ?>