<?php

/**
 * MainOffer form.
 *
 * @package    kre
 * @subpackage form
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MainOfferForm extends BaseMainOfferForm
{
  public function configure()
  {
    $this->setWidget('lot_id', new sfWidgetFormInputHidden());
    $this->setWidget('description', new sfWidgetFormTextareaTinyMCE(array_merge($this->_tinymce_options, array('height' => 200))));
    $this->setWidget('lot_anons', new sfWidgetFormTextareaTinyMCE(array_merge($this->_tinymce_options, array('height' => 200))));
  }

  public function processValues($values)
  {
    unset($values['lot_object']);
    return parent::processValues($values);
  }
}
