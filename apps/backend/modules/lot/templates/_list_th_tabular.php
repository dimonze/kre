<?php
$fields = array(
  'id'             => 'Лот',
  'name'           => 'Наименование',
  'type'           => 'Тип',
  'price_all_from' => 'Цена',
  'area_from'      => 'Площадь',
  'status'         => 'Активность',
  'rating'         => 'Рейтинг',
);
?>

<?php foreach ($fields as $field => $name): ?>
  <th class="sf_admin_text sf_admin_list_th_name ui-state-default ui-th-column">
    <?php if ($field == $sort[0]): ?>
      <?php echo link_to($name, '@lot?sort='.$field.'&sort_type='.($sort[1] == 'asc' ? 'desc' : 'asc')) ?>
      <?php echo image_tag(sfConfig::get('sf_admin_module_web_dir').'/images/'.$sort[1].'.png', array('alt' => __($sort[1], array(), 'sf_admin'), 'title' => __($sort[1], array(), 'sf_admin'))) ?>
    <?php else: ?>
      <?php echo link_to($name, '@lot?sort='.$field.'&sort_type=asc') ?>
    <?php endif; ?>
  </th>
<?php endforeach ?>