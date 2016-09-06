<style>
  table.geocode, table.geocode td {
    border : 1px solid #eee;
    border-collapse: collapse;
    padding: 2px 5px;
  }
  table.geocode {
    width: 500px
  }
  table.geocode input {
    width: 100%
  }
</style>
<table class="geocode">
<?php $address = $form->getObject()->address; ?>
<?php foreach(Param::$_addressStructure as $key => $value): ?>
  <?php if($key == 'string') continue; ?>
  <tr>
    <td><strong><?= $value ?></strong></td>
    <td width="95%">
      <input type="text" name="lot[address_<?= $key ?>]" value="<?= (!empty($address[$key]) ? $address[$key] : '') ?>" id="lot_address_<?= $key ?>" />
    </td>
  </tr>

<?php endforeach;?>
</table>
<br>
<div id="ymap"></div>