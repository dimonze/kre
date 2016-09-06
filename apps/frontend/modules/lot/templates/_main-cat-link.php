<td class="upd-table-cat-link">
  <?php if ($object):?>
    <p>
      <?php if ($file = $object->getImage('item')): ?>
        <?= link_to(image_tag($file, array('alt' => $object->name)), $object->route, $object) ?>
      <?php endif ?>
    </p>
    <h3><?= link_to($object->name, $object->route, $object) ?></h3>
    <div class="offer-anons">
    <?php if ($anons): ?>
      <?= $sf_data->getRaw('anons') ?>
      <?= link_to(image_tag('/pics/upd-more.gif'), $object->route, $object, array('class' => 'upd-pub-more'))?>
    <?php elseif ($object->anons): ?>
      <p>
        <?= $object->getRaw('anons')?>
        <?= link_to(image_tag('/pics/upd-more.gif'), $object->route, $object, array('class' => 'upd-pub-more'))?>
      </p>
    <?php endif ?>
    </div>
  <?php else: ?>
    <p></p>
  <?php endif ?>
</td>