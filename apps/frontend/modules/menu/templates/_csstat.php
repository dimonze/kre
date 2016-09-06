<?php
$type = $sf_params->has('type') ? $sf_params->get('type') : 'eliteflat';
?>
<p class="back_first"></p>
<ul class="ul_list ul_noborder ul_content">
  <?php foreach ($menu as $action=>$name): ?>
    <li>
      <?= link_to2($name, 'csstat_acts', array('action'=>$action), array('class' => ($action==$current ? 'active' : '')))?>
    </li>
    <?php if($current == 'query' && $action == $current): ?>
      <?php foreach (Lot::$_types as $key=>$value): ?>
        <li style="margin-left: 15px;"><?= link_to2(
          $value,
          'csstat_acts',
          array('action'=>$action, 'type' => $key),
          array('class' => ($key==$type ? 'active' : ''))
        )?></li>
        <?php endforeach;?>
      <?php endif;?>
  <?php endforeach ?>
</ul>
<p>&nbsp;</p>