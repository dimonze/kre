<?php

/**
 * vacancy actions.
 *
 * @package    kre
 * @subpackage vacancy
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class vacancyActions extends sfActions
{
  public function postExecute()
  {
    if (isset($this->page)) {
      sfConfig::set('current_page', $this->page);
      sfConfig::set('body_class', 'textpage');
    }
    MetaParse::setMetas($this);
  }

  public function executeList(sfWebRequest $request)
  {
    $this->page = Doctrine::getTable('Page')->getVacancyPage($request->getParameter('type'));
    $this->forward404Unless($this->page);
  }

  public function executePage()
  {
    $this->page = $this->getRoute()->getObject();
    $this->forward404Unless($this->page);
    $this->setTemplate('list');
  }

  public function executeSend(sfWebRequest $request)
  {
    $c = 0;
    if ($request->isMethod(sfWebRequest::POST) && $request->hasParameter('resume')) {
      $form = new ResumeForm();
      $form->bind($request->getParameter('resume'), $request->getFiles('resume'));

      if ($form->isValid()) {
        $vacancy = Doctrine::getTable('Vacancy')->findOneBy('id', $form->getValue('vacancy_id'));

        $subject  = sprintf('%s - %s', $vacancy->name, $vacancy->type_text);
        $send_to  = $vacancy->email;
        $send_fr  = $form->getValue('email');
        $body = '';
        $body .= sprintf('<h1>%s</h1><h3>%s</h3>', $vacancy->name, $vacancy->type_text);
        $body .= sprintf('<br/><strong><i>%s</i></strong><br/><br/>', $form->getValue('fio'));
        $body .= $form->getValue('text');
        $body .= '<p>';
        if ($form->getValue('phone')) $body .= sprintf('Телефон: %s<br/>', $form->getValue('phone'));
        $body .= sprintf('E-mail: <a href="mailto:%s">%s</a>', $form->getValue('email'), $form->getValue('email'));
        $body .= '</p>';

        $mailer = $this->getMailer();
        $message = $mailer->compose();
        $message->setSubject($subject);
        $message->setTo($send_to);
        $message->setFrom($send_fr);
        $message->setBody($body, 'text/html');

        if ($file = $form->getValue('file')) {
          $path = sprintf('%s/%s', sfConfig::get('sf_tmp_dir'), $file->getOriginalName());
          $file->save($path);
          $message->attach(Swift_Attachment::fromPath($path, $file->getType()));
        }

        $c = $mailer->send($message);
        if ($file) unlink($path);
      }
      else {
        $errors = array();
        foreach ($form->getErrorSchema()->getNamedErrors() as $field => $data) {
          $errors[$field] = $data->getMessage();
        }

        return $this->renderText(json_encode(array('success' => false, 'errors' => $errors)));
      }
    }

    return $this->renderText(json_encode(array('success' => (bool) $c)));
  }
}
