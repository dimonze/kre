<?php use_helper('Tag') ?>
<script type="text/javascript">
  tinyMCE.init({
    mode:                              "exact",
    elements:                          "default_spec_text",
    theme:                             "advanced",
    width:                             "925px",
    height:                            "600px",
    theme_advanced_toolbar_location:   "top",
    theme_advanced_toolbar_align:      "left",
    theme_advanced_statusbar_location: "bottom",
    theme_advanced_resizing:           true
    ,

      plugins : "safari,table,advimage,advlink,advlist,paste,nonbreaking,template",
      theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,formatselect,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,|,link,unlink,|,sub,sup,charmap,|,undo,redo,|,pasteword,cleanup,code,preview",
      theme_advanced_buttons2 : "tablecontrols,|,image,|,removeformat,visualaid,nonbreaking",
      theme_advanced_buttons3 : "",
      file_browser_callback : "ajaxfilemanager",
      content_css : "/css/tinymce.css",
      body_class : "content",
      valid_elements: "@[style|class|id],-noindex,-h1,-h2,-h3,-h4,#p,em/i,strong/b,br,-sub,-sup,-ol,-ul,li,-span,-div,strike,a[!href|rel|target],-table[width|cellspacing|cellpadding|border],-tr,#td[colspan|rowspan],#th[colspan|rowspan],tbody,thead,tfoot,img[!src|!alt|title|width|height]",
      fix_table_elements : false,
      button_tile_map : true,
      theme_advanced_toolbar_location : "top",
      theme_advanced_toolbar_align : "left",
      theme_advanced_statusbar_location : "bottom",
      theme_advanced_resizing : false,
      theme_advanced_blockformats : "p,h1,h2,h3,h4",
      skin : "o2k7",
      skin_variant : "silver",
      language : "ru",
      cleanup : true,
      convert_urls: false,
      custom_undo_redo_levels : 10

  });

</script>

<div id="sf_admin_container">
  <div id="sf_admin_content">
    <?php include_partial('global/flashes') ?>

    <form action="<?= url_for('@default?module=default&action=config') ?>" method="post">

      <div class="sf_admin_actions_block ui-widget">
        <ul class="sf_admin_actions">
          <li class="sf_admin_action_save"><button class="sf_button ui-priority-primary ui-corner-all ui-state-default" type="submit">Сохранить</button></li>
        </ul>
      </div>

      <div id="configs">
        <div class="sf_admin_list ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">

          <table cellspacing="0">
            <caption align="top" class="ui-widget-header ui-corner-top">Опции</caption>
            <tfoot>
              <tr>
                <th colspan="2">
                  <div class="ui-state-default ui-th-column ui-corner-bottom">
                    <div id="sf_admin_pager" class="sf_admin_pagination">&nbsp;</div>
                  </div>
                </th>
              </tr>
            </tfoot>
            <tbody>

              <?php $params = new ArrayIterator($params->getRawValue()) ?>
              <?php while ($params->valid() && $params->key() != 'phones[office]'): ?>
                <tr class="sf_admin_row ui-widget-content">
                  <td class="sf_admin_text sf_admin_list_td_title"><?= $params->current() ?></td>
                  <td class="sf_admin_input">
                    <?php if ($params->key() != 'default_spec_text'): ?>
                      <?= tag('input', array(
                        'type'  => 'text',
                        'class' => 'wide',
                        'name'  => $params->key(),
                        'value' => escape_once(sfConfig::get('app_'. get_id_from_name($params->key()))),
                      )) ?>
                    <?php else: ?>
                      <textarea rows="20" cols="120" name="<?= $params->key()?>">
                        <?= escape_once(sfConfig::get('app_'. get_id_from_name($params->key()))); ?>
                      </textarea>
                    <?php endif ?>
                  </td>
                </tr>
                <?php $params->next() ?>
              <?php endwhile ?>

              <tr class="sf_admin_row ui-widget-content">
                <th colspan="2">
                  <div class="ui-state-default ui-th-column">Контактные телефоны</div>
                </th>
              </tr>

              <?php while ($params->valid() && $params->key() != 'claim[emails][1]'): ?>
                <tr class="sf_admin_row ui-widget-content">
                  <td class="sf_admin_text sf_admin_list_td_title"><?= $params->current() ?></td>
                  <td class="sf_admin_input">
                    <?= tag('input', array(
                      'type'  => 'text',
                      'name'  => $params->key(),
                      'value' => escape_once(sfConfig::get('app_'. get_id_from_name($params->key()))),
                    )) ?>
                  </td>
                </tr>
                <?php $params->next() ?>
              <?php endwhile ?>


              <tr class="sf_admin_row ui-widget-content">
                <th colspan="2">
                  <div class="ui-state-default ui-th-column">E-mail`ы заявок</div>
                </th>
              </tr>

              <?php $emails = sfConfig::get('app_claim_emails') ?>
              <?php while ($params->valid()): ?>
                <tr class="sf_admin_row ui-widget-content">
                  <td class="sf_admin_text sf_admin_list_td_title"><?= $params->current() ?></td>
                  <td class="sf_admin_input">
                    <?php if (sscanf($params->key(), 'claim[emails][%d]', $key)): ?>
                      <?= tag('input', array(
                        'type'  => 'text',
                        'name'  => $params->key(),
                        'value' => escape_once($emails[$key]),
                        'class' => 'wide',
                      )) ?>
                    <?php else: ?>
                      <?= tag('input', array(
                        'type'  => 'text',
                        'name'  => $params->key(),
                        'value' => escape_once(sfConfig::get('app_'. get_id_from_name($params->key()))),
                        'class' => 'wide',
                      )) ?>
                    <?php endif ?>
                  </td>
                </tr>
                <?php $params->next() ?>
              <?php endwhile ?>

            </tbody>
          </table>

        </div>
      </div>

      <div class="sf_admin_actions_block ui-widget">
        <ul class="sf_admin_actions">
          <li class="sf_admin_action_save"><button class="sf_button ui-priority-primary ui-corner-all ui-state-default" type="submit">Сохранить</button></li>
        </ul>
      </div>
    </form>

  </div>
</div>