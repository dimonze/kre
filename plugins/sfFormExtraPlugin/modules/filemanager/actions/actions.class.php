<?php

/**
 * filemanager actions.
 *
 * @package    bonaman
 * @subpackage special
 * @author     Eugeniy Belyaev (eugeniy.b@garin-studio.ru)
 */
class filemanagerActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    if ($request->getParameter('iframe')) {
      $this->setLayout('simple');
    }
  }

}
