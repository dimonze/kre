<?php

/**
 * Base project form.
 *
 * @package    kre
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: BaseForm.class.php 20147 2009-07-13 11:46:57Z FabianLange $
 */
class BaseSearchForm extends BaseForm
{
  public static function getInstance($type)
  {
    switch ($type) {
      case 'eliteflat':   return new EliteflatSearchForm();
      case 'penthouse':   return new PenthouseSearchForm();
      case 'elitenew':    return new ElitenewSearchForm();
      case 'flatrent':    return new FlatrentSearchForm();
      case 'outoftown':   return new OutoftownSearchForm();
      case 'cottage':     return new CottageSearchForm();
      case 'comsell':     return new ComsellSearchForm();
      case 'comrent':     return new ComrentSearchForm();
      default:            return new self();
    }
  }

  public function setup()
  {
    $this->setWidgetSchema(new sfWidgetFormSchema(array(
      'pid'                => new sfWidgetFormInputHidden(),
      'id'                 => new sfWidgetFormInputText(),
      'street'             => new sfWidgetFormJQueryAutocompleter(array(
        'url' => '/data/list.json?param=streets&type=' . strtolower(str_replace('SearchForm', '', get_called_class())),
        'config' => '{cacheLength: 0}'
      )),
      'locality'           => new sfWidgetFormJQueryAutocompleter(array(
        'url' => '/data/list.json?param=locality&type=' . strtolower(str_replace('SearchForm', '', get_called_class())),
        'config' => '{cacheLength: 0}'
      )),

      'districts'          => new sfWidgetFormDistricts(array(
        'choices' => sfConfig::get('app_districts'),
      )),
      'wards'              => new sfWidgetFormDistricts(array(
        'choices' => sfConfig::get('app_wards'),
      )),

      'price_from'         => new sfWidgetFormInputText(),
      'price_to'           => new sfWidgetFormInputText(),
      'price_all_from'         => new sfWidgetFormInputText(),
      'price_all_to'           => new sfWidgetFormInputText(),
      'currency'           => new sfWidgetFormInputHidden(array('default' => 'RUR')),
      'currencyAll'           => new sfWidgetFormInputHidden(array('default' => 'RUR')),

      'no_price_ok'        => new sfWidgetFormInputCheckbox(),
      'only_new'           => new sfWidgetFormInputCheckbox(),
      'only_new_price'     => new sfWidgetFormInputCheckbox(),
      'under_construction' => new sfWidgetFormInputCheckbox(),
    )));

    $this->setValidatorSchema(new sfValidatorSchema(array(
      'pid'                => new sfValidatorNumber(array('required' => false)),
      'id'                 => new sfValidatorNumber(array('required' => false)),
      'street'             => new sfValidatorString(array('required' => false)),
      'locality'           => new sfValidatorString(array('required' => false)),

      'districts'          => new sfValidatorPass(array('required' => false)),
      'wards'              => new sfValidatorPass(array('required' => false)),

      'price_from'         => new sfValidatorNumber(array('required' => false)),
      'price_to'           => new sfValidatorNumber(array('required' => false)),
      'price_all_from'         => new sfValidatorNumber(array('required' => false)),
      'price_all_to'           => new sfValidatorNumber(array('required' => false)),
      'currency'           => new sfValidatorString(array('required' => false)),
      'currencyAll'           => new sfValidatorString(array('required' => false)),

      'no_price_ok'        => new sfValidatorBoolean(array('required' => false)),
      'only_new'           => new sfValidatorBoolean(array('required' => false)),
      'only_new_price'     => new sfValidatorBoolean(array('required' => false)),
      'under_construction' => new sfValidatorBoolean(array('required' => false)),

      'lat'                => new sfValidatorNumber(array('required' => false)),
      'lng'                => new sfValidatorNumber(array('required' => false)),
      'radius'             => new sfValidatorNumber(array('required' => false)),
      'exclude'            => new sfValidatorInteger(array('required' => false)),
    )));

    $this->getValidatorSchema()->setOption('allow_extra_fields', true);
    $this->disableLocalCSRFProtection();
  }


  protected function includeEstate()
  {
    $this->setWidget('estate', new sfWidgetFormJQueryAutocompleter(array(
      'url' => '/data/param.json?param=estate&type=' . strtolower(str_replace('SearchForm', '', get_called_class())),
      'config' => $this->getAutocompleterConfig('estate'),
    )));
    $this->setValidator('estate', new sfValidatorString(array('required' => false)));
  }

  protected function includeArea()
  {
    $this->setWidget('area_from', new sfWidgetFormInputText());
    $this->setWidget('area_to', new sfWidgetFormInputText());

    $this->setValidator('area_from', new sfValidatorNumber(array('required' => false)));
    $this->setValidator('area_to', new sfValidatorNumber(array('required' => false)));
  }

  protected function includeHouseAreas()
  {
    $this->setWidget('spaceplot_from', new sfWidgetFormInputText());
    $this->setWidget('spaceplot_to', new sfWidgetFormInputText());
    $this->setWidget('space_from', new sfWidgetFormInputText());
    $this->setWidget('space_to', new sfWidgetFormInputText());

    $this->setValidator('spaceplot_from', new sfValidatorNumber(array('required' => false)));
    $this->setValidator('spaceplot_to', new sfValidatorNumber(array('required' => false)));
    $this->setValidator('space_from', new sfValidatorNumber(array('required' => false)));
    $this->setValidator('space_to', new sfValidatorNumber(array('required' => false)));
  }

  protected function includeOutOfTown()
  {
    $this->setWidget('cottageVillage', new sfWidgetFormJQueryAutocompleter(array(
      'url' => '/data/param.json?param=cottageVillage&type=' . strtolower(str_replace('SearchForm', '', get_called_class())),
      'config' => $this->getAutocompleterConfig('cottageVillage'),
    )));
    $this->setWidget('distance_mkad_from', new sfWidgetFormInputText());
    $this->setWidget('distance_mkad_to', new sfWidgetFormInputText());

    $this->setValidator('distance_mkad_from', new sfValidatorNumber(array('required' => false)));
    $this->setValidator('distance_mkad_to', new sfValidatorNumber(array('required' => false)));
    $this->setValidator('cottageVillage', new sfValidatorString(array('required' => false)));
  }

  protected function includeDecoration()
  {
    $values = Param::$_widget_properties['eliteflat']['about_decoration']['values'];

    $this->setWidget('decoration', new sfWidgetFormChoice(array(
      'choices'  => array_combine($values, $values),
      'multiple' => true,
      'expanded' => true,
    )));
    $this->setValidator('decoration', new sfValidatorChoice(array(
      'required' => false,
      'choices'  => $values,
      'multiple' => true,
    )));
  }

  protected function includeBalcony()
  {
    $values = Param::$_widget_properties['eliteflat']['about_balcony']['values'];

    $this->setWidget('balcony', new sfWidgetFormChoice(array(
      'choices'  => array_combine($values, $values),
      'multiple' => true,
      'expanded' => true,
    )));
    $this->setValidator('balcony', new sfValidatorChoice(array(
      'required' => false,
      'choices'  => $values,
      'multiple' => true,
    )));
  }

  protected function includeParking()
  {
    $values = Param::$_widget_properties['eliteflat']['infra_parking']['values'];

    $this->setWidget('parking', new sfWidgetFormChoice(array(
      'choices'  => array_combine($values, $values),
      'multiple' => true,
      'expanded' => true,
    )));
    $this->setValidator('parking', new sfValidatorChoice(array(
      'required' => false,
      'choices'  => $values,
      'multiple' => true,
    )));
  }

  protected function includeterritory()
  {
    $values = Param::$_widget_properties['eliteflat']['territory']['values'];

    $this->setWidget('territory', new sfWidgetFormChoice(array(
      'choices'  => array_combine($values, $values),
      'multiple' => true,
      'expanded' => true,
    )));
    $this->setValidator('territory', new sfValidatorChoice(array(
      'required' => false,
      'choices'  => $values,
      'multiple' => true,
    )));
  }

  protected function includeMarket()
  {
    $values = array('' => '') + Param::$_widget_properties['eliteflat']['market']['values'];
    $this->setWidget('market', new sfWidgetFormInputHidden(array('default' => 'RUR')));
    $this->setValidator('market', new sfValidatorChoice(array(
      'required' => false,
      'choices'  => $values,
      'multiple' => false,
    )));

  }

  protected function getAutocompleterConfig($id)
  {
    return sprintf(<<<EOF
    {
      dataType: 'json',
      parse:    function(data) {
        var parsed = [];
        for (key in data) {
          parsed[parsed.length] = { data: [ data[key], key ], value: data[key], result: data[key] };
        }
        if (!parsed.length) {
          jQuery('#%s').val('');
        }
        return parsed;
      }
    }
EOF
  , $id);
  }
}
