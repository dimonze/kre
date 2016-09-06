<?php

/**
 * page actions.
 *
 * @package    kre
 * @subpackage page
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $el
 */
class pageActions extends sfActions
{
  public function postExecute()
  {
    if (isset($this->page)) {
      sfConfig::set('current_page', $this->page);
      sfConfig::set('body_class', 'textpage');
    }
    sfConfig::set('print_version', sfContext::getInstance()->getRequest()->hasParameter('print'));
    MetaParse::setMetas($this);
  }

  public function execute404()
  { }

  public function executeHomepage(sfWebRequest $request)
  {
    sfConfig::set('body_class', 'mainpage');
    $this->about = Doctrine::getTable('Page')->findOneById(Page::ABOUT_ID);
    $this->getResponse()->addMeta('google-site-verification', 'eIc7NMlEUB60gRb0Jj6dfxt0_xLxRlYrbvtA83V6FgA');
  }

  public function executeIndex(sfWebRequest $request)
  {
    $this->forwardIf(
        $request->getParameter('id') == 0
     && $request->getParameter('elem') == 'analytics',
        'page', 'reviewArchive');

    $this->redirectIf(
        $request->getParameter('id') == 0
     && $request->getParameter('elem') == 'about',
        '@news_archive');
    $this->page = Doctrine::getTable('Page')->find($request->getParameter('id'));
    $this->forward404Unless($this->page && $this->page->is_active);

    if (in_array($this->page->id, array(Page::SERVICE_ID, Page::ADVICES_ID))) {
      $body = strip_tags($this->page->body);
      if (empty($body)) {
        $this->page = Doctrine::getTable('Page')->getFirstChildPage($this->page);
        $this->forward404Unless($this->page);
      }
    }

    if ($this->page->route == 'contacts') {
      $this->initContactForm();
    }
  }

  public function executeNewsArchive(sfWebRequest $request)
  {
    $this->page = Doctrine::getTable('Page')->find(Page::NEWS_ID);
    $query = Doctrine::getTable('Page')
      ->getChildrenYearQuery($this->page, $request->getParameter('year'))
      ->andWhere('is_active = ?', true);
    $this->pager = $this->getPager($query);
    $this->setTemplate('archive');
  }

  public function executeNews(sfWebRequest $request)
  {
    $this->forwardUnless($request->getParameter('id'), 'page', 'newsArchive');
    $this->page = Doctrine::getTable('Page')->find($request->getParameter('id'));
    $this->forward404Unless($this->page->is_active);
    $this->setTemplate('archive');
  }

  public function executeReviewArchive(sfWebRequest $request)
  {
    $this->page = Doctrine::getTable('Page')->find(Page::REVIEW_ID);
    $query = Doctrine::getTable('Page')
      ->getChildrenYearQuery($this->page, $request->getParameter('year'))
      ->andWhere('is_active = ?', true);
    $this->pager = $this->getPager($query);
    $this->setTemplate('archive');
  }

  public function executeReview(sfWebRequest $request)
  {
    $this->forwardUnless($request->getParameter('id'), 'page', 'reviewArchive');
    $this->page = Doctrine::getTable('Page')->find($request->getParameter('id'));
    $this->forward404Unless($this->page->is_active);
    $this->setTemplate('archive');
  }

  public function executeSitemap(sfWebRequest $request)
  {
    $pages = Doctrine::getTable('Page')->getMainPages();
    $map = array();
    foreach($pages as $page) {
      $elem = array(
        'route' => '@'.$page->getRoute(),
        'name'  => $page->name,
      );
      if($page->getRoute() == 'services') {
        $offers = array(
          'route'       => '@offers',
          'name'      => 'Предложения',
          '_children' => array(),
        );

        foreach(Lot::$_types as $type => $name) {
          $offers['_children'][] = array(
            'route'  => 'lot/list?type='.$type,
            'name' => $name,
          );
        }
        $map[] = $offers;
      }
      $map[] = $elem;
    }
    $this->map = $map;
  }


  private function getPager(Doctrine_Query $query, $limit = 20)
  {
    $pager = new sfDoctrinePager('Page', $limit);
    $pager->setQuery($query);
    $pager->setPage($this->getRequest()->getParameter('page', 1));
    $pager->init();

    return $pager;
  }

  private function initContactForm()
  {
    $contact_form = new ContactForm();

    if ($this->getRequest()->isMethod('post')) {
      $data = $this->getRequest()->getParameter('contacts');
      if (!empty($data)) {
        $contact_form->bind($data);
        if ($contact_form->isValid()) {
          $data['fio']    = $contact_form->getValue('fio');
          $data['email']  = $contact_form->getValue('email');
          $data['text']   = trim(strip_tags($contact_form->getValue('text')));

          $this->sendMail($data);
          $this->send = true;
        }
      }
    }
    $this->contact_form = $contact_form;
  }

  protected function sendMail($data = null)
  {
    if ($data) {
      $send_from = 'contacts@kre.ru';
      $send_to = sfConfig::get('app_contacts_email');

      $subject = 'Письмо со страницы "Контакты" - kre.ru';

      $body  = "<p>Посетитель сайта: " . $data['fio'] . "</p>";
      $body .= "<p>Email: " . $data['email'] . "</p>";
      $body .= "<p>Вопрос: " .$data['text'] . "</p>";

      $mailer = $this->getMailer();
      $message = $mailer->compose();
      $message->setSubject($subject);
      $message->setTo($send_to);
      $message->setFrom($send_from);
      $message->setBody($body, 'text/html');

      $send = $mailer->send($message);
      return $send;
    }
    return false;
  }
}
