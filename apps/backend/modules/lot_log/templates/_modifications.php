<?php
if (!is_file(sprintf('%s/autoLot/lib/BaseLotGeneratorConfiguration.class.php', sfConfig::get('sf_module_cache_dir')))) {
  $config = sfYaml::load(sprintf('%s/lot/config/generator.yml', sfConfig::get('sf_app_module_dir')));
  $generator = new $config['generator']['class'](new sfGeneratorManager($sf_context->getConfiguration()->getRawValue()));
  $generator->generate(array_merge($config['generator']['param'], array('moduleName' => 'lot')));
}
else {
  require sprintf('%s/autoLot/lib/BaseLotGeneratorConfiguration.class.php', sfConfig::get('sf_module_cache_dir'));
  require sprintf('%s/lot/lib/lotGeneratorConfiguration.class.php', sfConfig::get('sf_app_module_dir'));
}

$lot_config = new lotGeneratorConfiguration();
$lot_fields = $lot_config->getFieldsDefault();
?>

<div class="sf_admin_form_row">
  <label>Изменения:</label>

  <table class="modifications_table">
    <thead>
      <tr>
        <th>Параметр</th>
        <th>Было</th>
        <th>Стало</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($lot_log->modifications as $k => $v): ?>
        <?php if ($v instanceof sfOutputEscaperArrayDecorator) $v = $v->getRawValue() ?>
        <?php if ($k == 'images'): ?>
          <?php foreach ($v as $kk => $vv): ?>
            <tr>
              <td><?= $kk == 'title' ? 'Титульное изображение' : 'Фотографии' ?></td>
              <td colspan="2">
                <?php foreach ($vv as $kkk => $vvv) {
                  switch ($kkk) {
                    case 'new':     echo 'Добавлено:'; break;
                    case 'update':  echo 'Обновлено:'; break;
                    case 'delete':  echo 'Удалено:';   break;
                  }
                  echo $vvv, '; ';
                } ?>
              </td>
            </tr>
          <?php endforeach ?>
        <?php elseif (is_numeric($k)): ?>
          <tr>
            <td>
              <?php foreach (Param::$_map[$lot_log->Lot->type] as $c) {
                foreach ($c as $cc) {
                  if ($cc['property_id'] == $k) {
                    echo $cc['name'];
                    break 2;
                  }
                }
              } ?>
            </td>
            <td><?= $v[0] ?></td>
            <td><?= $v[1] ?></td>
          </tr>
        <?php else: ?>
          <tr>
            <td><?= isset($lot_fields[$k]['label']) ? $lot_fields[$k]['label'] : $k ?></td>
            <td><?= $v[0] ?></td>
            <td><?= $v[1] ?></td>
          </tr>
        <?php endif ?>
      <?php endforeach ?>
    </tbody>
  </table>
</div>

<style type="text/css">
  table.modifications_table { width: 80%; border-collapse: collapse; }

  table.modifications_table,
  table.modifications_table th,
  table.modifications_table td { border: 1px solid black; }

  table.modifications_table th,
  table.modifications_table td { padding: 5px; }

  table.modifications_table th { font-weight: bold; }
</style>