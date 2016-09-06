<?php

/**
 * Base project form.
 *
 * @package    kre
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: BaseForm.class.php 20147 2009-07-13 11:46:57Z FabianLange $
 */
class BaseForm extends sfFormSymfony
{
  protected $_tinymce_options = array(
    'height' => 600,
    'width'  => 925,
    'theme'  => 'advanced',
    'config' => '
      content_css : "/css/tinymce.css",
      body_class : "content",
      valid_elements: "@[style|class|id|itemprop|itemscope|itemtype],-noindex,-h1,-h2,-h3,-h4,#p,em/i,strong/b,br,-sub,-sup,-ol,-ul,li,-span,-div,strike,a[!href|rel|target],-table[width|cellspacing|cellpadding|border],-tr,#td[colspan|rowspan],#th[colspan|rowspan],tbody,thead,tfoot,img[!src|!alt|title|width|height],iframe[name|src|framespacing|border|frameborder|scrolling|title|height|width],object[declare|classid|codebase|data|type|codetype|archive|standby|height|width|usemap|name|tabindex|align|border|hspace|vspace],param[name|value],embed[src|type|wmode|width|height]",
      fix_table_elements : false,
      button_tile_map : true,
      theme_advanced_toolbar_location : "top",
      theme_advanced_toolbar_align : "left",
      theme_advanced_statusbar_location : "bottom",
      theme_advanced_resizing : false,
      skin : "o2k7",
      skin_variant : "silver",
      language : "ru",
      cleanup : true,
      convert_urls: false,
      custom_undo_redo_levels : 10
    ',
  ),
  $_tinymce_mini_options = array(
    'height' => 150,
    'width'  => 925,
    'theme'  => 'advanced',
    'config' => '
      content_css : "/css/tinymce.css",
      body_class : "content",
      valid_elements: "@[style|class|id|itemprop|itemscope|itemtype],-noindex,-h1,-h2,-h3,-h4,#p,em/i,strong/b,br,-sub,-sup,-ol,-ul,li,-span,-div,strike,a[!href|rel|target],-table[width|cellspacing|cellpadding|border],-tr,#td[colspan|rowspan],#th[colspan|rowspan],tbody,thead,tfoot,img[!src|!alt|title|width|height]",
      fix_table_elements : false,
      button_tile_map : true,
      theme_advanced_buttons1 : "cut,copy,paste,pastetext,pasteword,|,bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,sub,sup,|,link,unlink,anchor,image,|,code",
      theme_advanced_buttons2 : "",
      theme_advanced_buttons3 : "",
      theme_advanced_toolbar_location : "top",
      theme_advanced_toolbar_align : "left",
      theme_advanced_statusbar_location : "bottom",
      theme_advanced_resizing : false,
      skin : "o2k7",
      skin_variant : "silver",
      language : "ru",
      cleanup : true,
      convert_urls: false,
      custom_undo_redo_levels : 10
    ',
  );
}
