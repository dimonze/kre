<?php
$type = $sf_params->has('type') ? $sf_params->get('type') : 'eliteflat';
?>
<div class="cside c_full">
  <div class="padding">
    <div class="upd-cside-path"></div>
    <div class="separator"></div>
    <div id="content">
      <h2>Поисковые запросы</h2>
      <hr>
      <b>Год:</b>
      <?php for($i = $first['y']; $i<=$last['y']; $i++): ?>
      <?php if($act['y']!=$i):?>
        <?= link_to2($i, 'csstat_acts', array(
          'action' => 'query',
          'd' => '01',
          'm' => '01',
          'y' => $i,
          'type' => $type,
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
          'action' => 'query',
          'd' => '01',
          'm' => $key > 9 ? $key : '0'.$key ,
          'y' => $act['y'],
          'type' => $type,
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
          'action' => 'query',
          'd' => $i > 9 ? $i : '0'.$i,
          'm' => $act['m'],
          'y' => $act['y'],
          'type' => $type,
        ));?>
        <?php else: ?>
        <?= $i ?>
        <?php endif; ?>
      <?php endfor;?> <br/>
      <?php if(count($result)):?>
      <h2>Поисковые запросы за <?= $act['d'] ?>.<?= $act['m'] ?>.<?= $act['y'] ?></h2>
      <table class="table-content csstat recommendation">
        <tr>
          <td style="background-color:#E5E6EA;">
            <h3 style="margin:0px">Время</h3>
          </td>
          <td style="background-color:#E5E6EA;">
            <h3 style="margin:0px">Поисковый запрос</h3>
          </td>
        </tr>        
        <?php foreach($result as $hour => $queries): ?>
        <tr class="<?= ($key & 1 ? 'odd' : 'even') ?>">
          <td class="<?= ($hour & 1 ? 'odd' : 'even') ?>"><?= $hour?></td>
          <td class="<?= ($hour & 1 ? 'odd' : 'even') ?>">
            <?php foreach($queries as $key=>$query): ?>
            <?php if($query != ''): ?>           
              <b><?= ++$key ?>:</b> <?= $query ?> <br/>
             <?php endif; ?>
            <?php endforeach; ?>
          </td>
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