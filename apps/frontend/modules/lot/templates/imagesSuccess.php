<?php slot('no_stat', ' ') ?>
<?php $used = explode(',',trim($sf_params->get('s'), ',')); ?>
<div style="border-bottom: 1px solid black;">
<input type="checkbox" id="all"> Выбрать все фото
</div>

<table class="select-images" style="width: 400px;">
  <tr>
    <?php foreach($lot->Photos as $key=>$photo):?>
      <?php if(!Broker::isAuth() && $photo->photo_type_id == 5) continue; ?>
      <?php $id = 'img' . $lot->id . '_' . $photo->id ?>
      <td class="check"><input type="checkbox" id="<?= $id ?>" rel="<?=$photo->getImage('full')?>"
        <?php $photo->getImage('full_'); ?>
        <?= (in_array($id, $used) ? 'checked="checked"' : '') ?>></td>
      <td class="img">
        <label for="<?= $id ?>">
        <?= image_tag($photo->getImage('thumb')) ?></label>
      </td>
      <?php if(++$key%3==0):?>
        </tr><tr>
      <?php endif; ?>
    <?php endforeach;?>
    <?php if($lot->Parent): ?>
      <?php foreach($lot->Parent->Photos as $key=>$photo):?>
        <?php if(!Broker::isAuth() && $photo->photo_type_id == 5) continue; ?>
        <?php $id = 'img' . $lot->id . '_' . $photo->id; ?>
        <td class="check"><input type="checkbox" id="<?= $id ?>" rel="<?=$photo->getImage('full')?>"></td>
        <?php $photo->getImage('full_'); ?>
        <td class="img">
          <label for="<?= $id ?>">
            <?= image_tag($photo->getImage('thumb')) ?></label>
        </td>
        <?php if(++$key%3==0):?>
          </tr><tr>
        <?php endif; ?>
      <?php endforeach;?>
    <?php endif; ?>
  </tr>
</table>