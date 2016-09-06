<?php if ($main_offer->lot_id): ?>
  <?= link_to($main_offer->lot_id, 'lot_edit', array('id' => $main_offer->lot_id), array('popup' => true)) ?>
<?php else: ?>
  &mdash;
<?php endif ?>