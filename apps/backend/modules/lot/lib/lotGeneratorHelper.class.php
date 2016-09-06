<?php

/**
 * lot module helper.
 *
 * @package    kre
 * @subpackage lot
 * @author     Garin Studio
 * @version    SVN: $Id: helper.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class lotGeneratorHelper extends BaseLotGeneratorHelper
{
  public function linkToStatus($object, $options)
  {
    $html = '';
    $html .= '<select name="lot_status">'.PHP_EOL;
    foreach (Lot::$_status as $status => $title) {
      $html .= sprintf('<option value="%s"%s>%s</option>',
                $status,
                $object->status == $status ? 'selected="selected"' : '',
                $title
              );
      $html .= PHP_EOL;
    }
    $html .= '</select>'.PHP_EOL;
    $html .= link_to('Сменить', 'lot/changeStatus?id='.$object->id.'&status=', array(
      'class'   => 'sf_button_inline ui-state-default ui-priority-secondary sf_button ui-corner-all',
      'onclick' => 'var link=this.href+$(this).siblings("select").val();$.ajax({url: link});return false;',
    ));

    return $html;
  }

  public function linkToSetMain($object, $options)
  {
    $count = Doctrine::getTable('MainOffer')->createQuery()->where('lot_id = ?', $object->id)->count();

    if ($count) {
      return link_to('Удалить из предложений', 'main_offer/clear?type='.$object->type, $options['params']);
    }
    else {
      return link_to('Закрепить в предложения', '@default?module=main_offer&action=set&lot_id='.$object->id, $options['params']);
    }
  }

  public function linkToFrontendShow($object, $options)
  {
    if ($object->status == 'active') {
      return link_to('Посмотреть на сайте', 'lot/frontendShow?id='.$object->id, $options['params'].' target=_blank');
    }
    else {
      return '';
    }
  }

  public function linkToClone($object, $options)
  {
    return link_to('Клонировать', sprintf('%s/lot/%d/edit#', $_SERVER['SCRIPT_NAME'], $object->id), $options['params']);
  }
}
