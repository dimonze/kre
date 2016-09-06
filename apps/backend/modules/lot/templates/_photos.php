<div id="lot_photos" class="sf_admin_form_row sf_admin_text sf_admin_form_field_Photos">
  <div>

    <table>
      <tbody>
        <?php foreach ($photos as $i => $photo): ?>
          <tr>
            <td colspan="2">
              <?= image_tag($photo->getImage('thumb')) ?>
              <br/>
              <input type="checkbox" id="lot_Photos_<?= $i ?>_file_delete" name="lot[Photos][<?= $i ?>][file_delete]"> удалить
              <br/>
              <input type="file" id="lot_Photos_<?= $i ?>_file" name="lot[Photos][<?= $i ?>][file]"/>
            </td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>
              <?= link_to(image_tag('/csDoctrineActAsSortablePlugin/images/sortable/icons/promote.png'), '@default?module=lot&action=photoPromote&id='.$photo->id.'&lot_id='.$photo->lot_id) ?>
              <?= link_to(image_tag('/csDoctrineActAsSortablePlugin/images/sortable/icons/demote.png'), '@default?module=lot&action=photoDemote&id='.$photo->id.'&lot_id='.$photo->lot_id) ?>
              <input type="hidden" id="lot_Photos_<?= $i ?>_id" value="<?= $photo->id ?>" name="lot[Photos][<?= $i ?>][id]"/>
              <input type="hidden" id="lot_Photos_<?= $i ?>_lot_id" value="<?= $photo->lot_id ?>" name="lot[Photos][<?= $i ?>][lot_id]"/>
              <input type="hidden" id="lot_Photos_<?= $i ?>_position" value="<?= $photo->position  ?>" name="lot[Photos][<?= $i ?>][position]"/>
            </td>
          </tr>
          <tr>
            <td>Подпись</td>
            <td><input type="text" id="lot_Photos_<?= $i ?>_name" value="<?= $photo->name ?>" name="lot[Photos][<?= $i ?>][name]"/></td>
          </tr>
          <tr>
            <td>Тип</td>
            <td>
              <select id="lot_Photos_<?= $i ?>_photo_type_id" class="param-type-select" name="lot[Photos][<?= $i ?>][photo_type_id]">
                <?php foreach (array('' => '') + Photo::$_types as $key => $val): ?>
                  <option<?= $photo->photo_type_id == $key ? ' selected="selected"' : '' ?> value="<?= $key ?>"><?= $val ?></option>
                <?php endforeach ?>
              </select>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>

  </div>
</div>