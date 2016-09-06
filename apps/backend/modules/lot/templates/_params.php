<div id="lot_params" class="sf_admin_form_row sf_admin_text sf_admin_form_field_LotParams">
  <div>
    <?php if ($params): ?>
      <?php $type = $params[0]->Lot->type ?>
      <?php foreach (Param::$_map[$type] as $group => $fields): ?>
        <h1><?= Param::$_types[$group] ?></h1>
        <?php foreach ($fields as $field): ?>
          <?php foreach ($params as $i => $param): ?>
            <?php if ($field['property_id'] == $param->param_id): ?>
              <div class="sf_admin_form_row sf_admin_text sf_admin_form_LotParams">
                <div>
                  <label><?= $param->Params->name ?></label>
                  <input type="text" id="lot_LotParams_<?= $i ?>_value" value="<?= $param->value ?>" name="lot[LotParams][<?= $field['field'] ?>][value]"/>
                  <input type="hidden" id="lot_LotParams_<?= $i ?>_lot_id" value="<?= $param->lot_id ?>" name="lot[LotParams][<?= $field['field'] ?>][lot_id]"/>
                  <input type="hidden" id="lot_LotParams_<?= $i ?>_param_id" value="<?= $param->param_id ?>" name="lot[LotParams][<?= $field['field'] ?>][param_id]"/>
                  <input type="hidden" id="lot_LotParams_<?= $i ?>_position" value="<?= $param->position ?>" name="lot[LotParams][<?= $field['field'] ?>][position]"/>
                </div>
              </div>
              <?php break; ?>
            <?php else: ?>
              <div class="sf_admin_form_row sf_admin_text sf_admin_form_LotParams">
                <div>
                  <label><?= $field['name'] ?></label>
                  <input type="text" id="lot_LotParams_<?= $i ?>_value" value="" name="lot[LotParams][<?= $field['field'] ?>][value]"/>
                  <input type="hidden" id="lot_LotParams_<?= $i ?>_lot_id" value="<?= $param->lot_id ?>" name="lot[LotParams][<?= $field['field'] ?>][lot_id]"/>
                  <input type="hidden" id="lot_LotParams_<?= $i ?>_param_id" value="" name="lot[LotParams][<?= $field['field'] ?>][param_id]"/>
                  <input type="hidden" id="lot_LotParams_<?= $i ?>_position" value="" name="lot[LotParams][<?= $field['field'] ?>][position]"/>
                </div>
              </div>
            <?php endif ?>
          <?php endforeach ?>
        <?php endforeach ?>
        <hr />
      <?php endforeach ?>
    <?php endif ?>
  </div>
</div>