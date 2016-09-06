<?php

/**
 * Page form.
 *
 * @package    kre
 * @subpackage form
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PageForm extends BasePageForm
{
  private $_parent;


  public function configure()
  {
    $this->setWidget('parent_id', new sfWidgetFormDoctrineChoice(array(
      'model'         => 'Page',
      'table_method'  => 'findTreeExceptRootNewsReview',
      'method'        => 'getIndentedName',
    )));
    $this->setWidget('anons', new sfWidgetFormTextareaTinyMCE(array_merge($this->_tinymce_options, array('height' => 200))));
    $this->setWidget('body', new sfWidgetFormTextareaTinyMCE($this->_tinymce_options));
    $this->setWidget('created_at', new sfWidgetFormI18nDate(array('culture' => 'ru')));
    $this->setWidget('seo_description', new sfWidgetFormTextarea());


    $this->setValidator('parent_id', new sfValidatorCallback(array('callback' => array($this, 'validateParent'))));


    $years = range(date("Y")+5,date("Y", mktime(0, 0, 0, 1, 1, 2003)));    
    $this->getWidget('created_at')->setOption("years", array_combine($years, $years));    
    $this->getWidget('created_at')->setDefault(time()); 
    

    $this->getValidator('name')->setOption('trim', true);
    $this->getValidator('seo_title')->setOption('trim', true);
    $this->getValidator('seo_description')->setOption('trim', true);
    $this->getValidator('seo_keywords')->setOption('trim', true);

    $this->getValidator('body')->setOption('empty_value', null);
    $this->getValidator('seo_title')->setOption('empty_value', null);
    $this->getValidator('seo_description')->setOption('empty_value', null);
    $this->getValidator('seo_keywords')->setOption('empty_value', null);


    if (!$this->isNew()) {
      $this->getWidget('parent_id')->setDefault($this->getObject()->getNode()->getParent()->id);
    }

    unset($this['is_active'], $this['lft'], $this['rgt'], $this['level']);
  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    if ($this->_parent) {
      if ($this->isNew()) {
        $this->getObject()->getNode()->insertAsLastChildOf($this->_parent);
      }
      else {
        $node = $this->getObject()->getNode();
        if ($node->getParent()->id != $this->_parent->id) {
          $node->moveAsLastChildOf($this->_parent);
        }
      }
    }
    else {
     Doctrine::getTable('Page')->getTree()->createRoot($this->getObject());
    }
  }

  public function validateParent(sfValidatorCallback $validator, $value)
  {
    if (empty($value)) {
      throw new sfValidatorError($validator, 'required');
    }

    $parent = Doctrine::getTable('Page')->find($value);
    if (!$parent || !$parent->getNode()->isValidNode()) {
      throw new sfValidatorError($validator, 'invalid');
    }

    $this->_parent = $parent;

    return $value;
  }
}
