<?php if ($claim->types): ?>
  <?php foreach ($claim->types as $type => $value): ?>
    <i><?= Claim::$_types[$type] ?></i></br>
  <?php endforeach ?>
<?php else: ?>
  <i>Тип не указан</i>
<?php endif ?>
