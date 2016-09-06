<p class="back_first"><?=link_to('На главную', '@homepage')?></p>
<ul class="ul_list ul_noborder ul_content">
  <?php foreach ($menu as $e): ?>
    <li<?=$e['level'] > 0 ? ' style="margin-left: '.($e['level']*15).'px;"' : '' ?>>
      <?php if ($e['is_current']): ?>
        <strong><?=$e['name']?></strong>
      <?php elseif ($e['is_active']): ?>
        <?= link_to($e['name'], $e['uri'], array('class' => 'active')) ?>
      <?php else: ?>
        <?= link_to($e['name'], $e['uri']) ?>
      <?php endif ?>
    </li>
  <?php endforeach ?>
</ul>
<p>&nbsp;</p>