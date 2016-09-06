<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormTextareaTinyMCE represents a Tiny MCE widget.
 *
 * You must include the Tiny MCE JavaScript file by yourself.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWidgetFormTextareaTinyMCE.class.php 11894 2008-10-01 16:36:53Z fabien $
 */
class sfWidgetFormTextareaTinyMCE extends sfWidgetFormTextarea
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * theme:  The Tiny MCE theme
   *  * width:  Width
   *  * height: Height
   *  * config: The javascript configuration
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('theme', 'advanced');
    if (in_array('filemanager', sfConfig::get('sf_enabled_modules', array()))) {
      $this->addOption('plugins', array('table', 'filemanager'));
    }
    else {
      $this->addOption('plugins', array('table'));
    }
    $this->addOption('width');
    $this->addOption('height');
    $this->addOption('config', '');
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value selected in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $attributes = array_merge($this->attributes, $attributes);

    if (!isset($attributes['class'])) {
      $attributes['class'] = 'textarea_mce_' . $this->generateId($name);
    }

    $textarea = parent::render($name, $value, $attributes, $errors);

    if ($this->getOption('theme') == 'simple') {
      $js = sprintf(<<<EOF
  <script type="text/javascript">
    tinyMCE.init({
      mode:                              "exact",
      theme:                             "advanced",
      elements:                          "%s",
      language:                          "ru",
      %s
      %s
      plugins:                           "paste",
      relative_urls:                     false,
      theme_advanced_toolbar_location:   "bottom",
      theme_advanced_toolbar_align:      "left",
      theme_advanced_statusbar_location: "none",
      theme_advanced_resizing:           true,
      theme_advanced_buttons1 : "bold,italic,underline,separator,link,unlink,separator,bullist,numlist,blockquote,separator,undo,redo,separator,pastetext,pasteword,removeformat,code",
      theme_advanced_buttons2 : "",
      theme_advanced_buttons3 : ""
    });
  </script>
EOF
      ,
        $this->generateId($name),
        $this->getOption('width')  ? sprintf('width:  "%spx",', $this->getOption('width')) : 'width: "650px",',
        $this->getOption('height') ? sprintf('height: "%spx",', $this->getOption('height')) : ''
      );
    }
    else {
      $js = sprintf(<<<EOF
  <script type="text/javascript">
    tinyMCE.init({
      mode:                              "exact",
      theme:                             "advanced",
      elements:                          "%s",
      language:                          "ru",
      %s
      %s
      relative_urls:                     false,
      theme_advanced_toolbar_location:   "top",
      theme_advanced_toolbar_align:      "left",
      theme_advanced_statusbar_location: "bottom",
      theme_advanced_resizing:           true,
      theme_advanced_buttons1 : "formatselect,separator,fontsizeselect,separator,fontselect,separator,bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,separator,outdent,indent,separator,undo,redo,separator,link,unlink,separator,cleanup,code%s",
      theme_advanced_buttons2 : "%s",
      theme_advanced_buttons3 : ""
      %s
      %s
    });
  </script>
EOF
      ,
        $this->generateId($name),
        $this->getOption('width')  ? sprintf('width:   "%spx",', $this->getOption('width')) : 'width: "650px",',
        $this->getOption('height') ? sprintf('height: "%spx",', $this->getOption('height')) : '',
        $this->getOptions('width') >= 640 ? '' : ',separator,sub,sup,separator,cleanup,code',
        $this->getPluginOptions() ? ($this->getOptions('width') < 640 ? '' : 'sub,sup,' ) . 'pastetext,pasteword,visualaid,removeformat,charmap,hr' : '',
        $this->getPluginOptions() ? ",\n".$this->getPluginOptions() : '',
        $this->getOption('config') ? ",\n".$this->getOption('config') : ''
      );
    }

    return $textarea.$js;
  }

  private function getPluginOptions() {
    $options = array();
    $options['filemanager'] = array('config' => '', 'buttons' => 'image,filemanager_images,filemanager_files');
    $options['embedvideo'] = array('config' => '', 'buttons' => 'embedvideo');
    $options['table'] = array('config' => '
    table_styles : "Header 1=header1;Header 2=header2;Header 3=header3",
    table_cell_styles : "Header 1=header1;Header 2=header2;Header 3=header3;Table Cell=tableCel1",
    table_row_styles : "Header 1=header1;Header 2=header2;Header 3=header3;Table Row=tableRow1",
    table_cell_limit : 500,
    table_row_limit : 100,
    table_col_limit : 20', 'buttons' => 'tablecontrols');

    $plugins = array();
    $buttons = array();
    $configs = array();
    foreach ($options as $plugin => $param) {
      if (in_array($plugin, $this->getOption('plugins'))) {
        $plugins[] = $plugin;
        $configs[] = $param['config'];
        $buttons[] = $param['buttons'];
      }
    }

    if (count($plugins)) {
      $str = 'plugins: "safari,table,advimage,advlink,advlist,paste,nonbreaking,template,'.implode(',', $plugins).'"';
      $str .= ",\n".'theme_advanced_buttons2_add: "separator,'.implode(',separator,', $buttons).'"';
      foreach ($configs as $config) {
        if ($config) {
          $str .= ",\n".$config;
        }
      }
      return $str;
    }
    return null;
  }


  /**
   * Gets the JavaScript paths associated with the widget.
   *
   * @return array An array of JavaScript paths
   */
  public function getJavascripts()
  {
    return array('/sfFormExtraPlugin/js/tiny_mce/tiny_mce.js');
  }
}
