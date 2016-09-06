<td class="upd-table-cat-info">
  <h3><a href="/offers/<?= $type ?>/"><?= Lot::$_types[$type] ?></a></h3>
  <p class="no_italic">Предложений: <?= $cnts['count'] ?><?php if (!empty($cnts['new'])): ?>, новых: <?= $cnts['new'] ?><?php endif ?></p>
  <p class="upd-table-cat-info-desc"><?= $sf_data->getRaw('desc') ?></p>
</td>