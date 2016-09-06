<div class="cside c_full">
  <div class="padding">
    <div class="upd-cside-path"></div>
    <div class="separator"></div>
    <div id="content">
      <h2>Переходы из рекомендаций</h2>
      <hr>
      <b>Год:</b>
      <?php for($i = $first['y']; $i<=$last['y']; $i++): ?>
        <?php if($act['y']!=$i):?>
          <?= link_to2($i, 'csstat_acts', array(
            'action' => 'recommendation',
            'd' => '01',
            'm' => '01',
            'y' => $i,
          ));?>
        <?php else: ?>
          <?= $i ?>
        <?php endif; ?>
      <?php endfor;?> <br/>
      <b>Месяц:</b>
      <?php foreach($months as $key=>$value): ?>
        <?php if($last['y']==$act['y'] && $key > (int)$last['m']){ continue; } ?>
        <?php if($first['y']==$act['y'] && $key < (int)$first['m']){ continue; } ?>
        <?php if($act['m']!=$key):?>
          <?= link_to2($value, 'csstat_acts', array(
            'action' => 'recommendation',
            'd' => '01',
            'm' => $key > 9 ? $key : '0'.$key ,
            'y' => $act['y'],
          ));?>
        <?php else: ?>
          <?= $value ?>
        <?php endif; ?>
      <?php endforeach;?> <br/>
      <b>День:</b>
      <?php for($i = 1; $i<=date('t', mktime(12,0,0,$act['m'], $act['d'], $act['y'])); $i++): ?>
        <?php if($last['y']==$act['y'] && $last['m']==$act['m'] && $i > $last['d']){ break; } ?>
        <?php if($first['y']==$act['y'] && $first['m']==$act['m'] && $i < $first['d']){ continue; } ?>
        <?php if($act['d']!=$i):?>
          <?= link_to2($i, 'csstat_acts', array(
            'action' => 'recommendation',
            'd' => $i > 9 ? $i : '0'.$i,
            'm' => $act['m'],
            'y' => $act['y'],
          ));?>
        <?php else: ?>
          <?= $i ?>
        <?php endif; ?>
      <?php endfor;?> <br/>
      <?php if(!empty($result[0])):?>
        <h2>Переходы за <?= $act['d'] ?>.<?= $act['m'] ?>.<?= $act['y'] ?></h2>
        <table class="table-content csstat recommendation">
          <tr>
            <td style="background-color:#E5E6EA;">
              <h3 style="margin:0px">Объект</h3>
            </td>
            <td style="background-color:#E5E6EA;">
              <h3 style="margin:0px">Количество переходов</h3>
            </td>
          </tr>
          <?php foreach($result as $key => $object): ?>
            <tr class="<?= ($key & 1 ? 'odd' : 'even') ?>">
              <td class="<?= ($key & 1 ? 'odd' : 'even') ?>"><?= $object['name']?></td>
              <td class="<?= ($key & 1 ? 'odd' : 'even') ?>"><?= $object['amount']?></td>
            </tr>
          <?php endforeach;?>
        </table>
        <p></p>
      <?php else: ?>
      <h2>За <?= $act['d'] ?>.<?= $act['m'] ?>.<?= $act['y'] ?> данных нет</h2>
      <?php endif; ?>
    </div>
  </div>
</div>
