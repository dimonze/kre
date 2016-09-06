<?php

/**
 * Vacancy form.
 *
 * @package    kre
 * @subpackage form
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class VacancyForm extends BaseVacancyForm
{
  public function configure()
  {
    $this->setWidget('description', new sfWidgetFormTextareaTinyMCE($this->_tinymce_options));

    $this->getWidget('type')->setOption('choices', array('' => '') + Vacancy::$_types);

    $this->getValidator('name')->setOption('trim', true);
    $this->getValidator('email')->setOption('trim', true);
    $this->getValidator('phone')->setOption('trim', true);
    $this->getValidator('fio')->setOption('trim', true);
    $this->getValidator('phone')->setOption('empty_value', null);
    $this->getValidator('fio')->setOption('empty_value', null);
  }
}
