<?php foreach ($params as $group_name => $params): ?>
  <div class="presentation-no-break">
  <h3 class="cat_type"><?= $group_name ?></h3>

  <table class="table-content in_cat">
    <tr>
      <td class="separator_image" colspan="2">
        <div><img src="/pics/separator-wide.gif" alt="" /></div>
      </td>
    </tr>

    <?php foreach ($params as $name => $value): ?> 
      <?php if (!empty($presentation) && $name == 'Рынок') continue ?>
      <?php if (!empty($presentation) && $lot->type == 'elitenew' && $name == 'Количество комнат') continue ?>
      <tr>
        <td style="width: 50%">
          <?= $name ?>
        </td>
        <td <?= ($presentation && $sf_user->isAuthenticated() ? '><span contenteditable="true"' : '') ?>>
          <?php if ($value instanceOf sfOutputEscaperArrayDecorator): ?>
            <?= implode(', ', $value->getRawValue()) ?>
          <?php else: ?>
            <?= $value ?>
          <?php endif ?>
          <?= ($presentation && $sf_user->isAuthenticated() ? '</span>' : '') ?></td>
      </tr>
    <?php endforeach ?>

    <tr>
      <td class="separator_image" colspan="2">
        <div><img src="/pics/separator-wide.gif" alt="" /></div>
      </td>
    </tr>
  </table>
  </div>
<?php endforeach ?>