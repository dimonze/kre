<?php

/**
 * SeoText form.
 *
 * @package    kre
 * @subpackage form
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SeoTextForm extends BaseSeoTextForm
{
  public function configure()
  {
    $this->mergePostValidator(new SeoTextValidatorSchema());
    $this->setWidget('text', new sfWidgetFormTextareaTinyMCE($this->_tinymce_options));
    $this->getValidator('name')->setOption('trim', true);
    $this->getValidator('url')->setOption('trim', true);
    $this->getValidator('text')->setOption('trim', true);
    $this->getValidator('text')->setOption('required', false);
    $this->getValidator('hrurl')->setOption('required', false);
    $this->getValidator('title')->setOption('required', false);
  }
}
