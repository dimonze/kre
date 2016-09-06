<?php

/**
 * form actions.
 *
 * @package    kre
 * @subpackage data
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class formActions extends sfActions
{
  public function postExecute()
  {
    MetaParse::setMetas($this);
  }
  public function executeClaim(sfWebRequest $request)
  {
    $this->form = new ClaimForm();
    if ($request->isMethod('get')) {
      if ($request->hasParameter('lot_id')) {
        $this->lot_id = $request->getParameter('lot_id');
      }

      if ($request->hasParameter('types')) {
        $this->form->setDefault('types', array_fill_keys($request->getParameter('types'), 'on'));
      }
    }

    if ($request->isMethod('post')) {
      $data = $request->getParameter('claim');
      $this->form->bind($data);
      if ($this->form->isValid()) {
        $this->form->save();
        $this->save = true;
        $this->sendMail($this->form->getValues());
      }
    }
  }

  public function executeOrderCatalog(sfWebRequest $request)
  {
    $this->form = new OrderCatalogForm();

    if($request->isMethod('post')) {
      $data = $request->getParameter('ordercatalog');
      $this->form->bind($data);
      if ($this->form->isValid()) {
        $this->save = true;
        $this->sendCatalog(array_merge(
          $this->form->getValues(),
          array(
            'budget_human'  => $this->form->getBudgetHuman($data['budget']),
            'version_human' => $this->form->getVersionHuman($data['version'])
          )));
      }
    }
  }

  protected function sendMail($data = null)
  {
    if ($data) {
      $claim_emails = sfConfig::get('app_claim_emails');
      $send_to = array();

      foreach($data['types'] as $type => $value) {
        if (array_key_exists($type, $claim_emails)) {
          $send_to = array_merge($send_to, preg_split('/[,; ]+/', $claim_emails[$type]));
        }
      }
      $send_to = array_unique($send_to);
      $send_to = array_map('trim', $send_to);

      $send_from  = 'sales@kre.ru';
      $subject    = 'Новая заявка на сайте kre.ru';

      $body       = "<p>Посетитель сайта: " . $data['fio'] . "</p>";
      $body      .= "<p>Email: " . $data['email'] . "</p>";
      $body      .= "<p>Телефон: " . $data['phone'] . "</p>";

      if(!empty($data['lot_id'])) {
        $body .= "<p>Интересующий лот: " . $data['lot_id'] . "</p>";
      }

      if (!empty($data['types'])) {
        $body .= "<p>Интересующие предложения:<br>";
        foreach ($data['types'] as $type => $value) {
          $body .= '<i>' . Claim::$_types[$type] . '</i><br>';
        }
        $body .= '</p>';
      }

      $body .= "<p>Описание: " . trim(strip_tags($data['description'])) . "</p>";

      $mailer = $this->getMailer();
      $message = $mailer->compose();
      $message->setSubject($subject);
      $message->setTo(array_shift($send_to));

      foreach($send_to as $email) {
        $message->addCc($email);
      }

      $message->setFrom($send_from);
      $message->setBody($body, 'text/html');

      return $mailer->send($message);
    }
    return false;
  }

  protected function sendCatalog($data = null)
  {
    if ($data) {

      $send_to    = sfConfig::get('app_order_catalog_email');
      $send_from  = 'ordercatalog@kre.ru';
      $subject    = 'Заказ каталога с сайта KRE.RU';

      $body       = "<p>Поступил новый заказ каталога. Ниже информация по заказу.</p><br />";

      $body      .= "<p>Посетитель сайта: " . $data['fio'] . "</p>";
      $body      .= "<p>Email: " . $data['email'] . "</p>";
      $body      .= "<p>Телефон: " . $data['phone'] . "</p>";

      $body      .= "<p>Бюджет: " . $data['budget_human'] . "</p>";
      $body      .= "<p>Версия каталога: " . $data['version_human'] . "</p>";

      $body      .= "<p>Адрес: " . $data['address'] . "</p>";

      $mailer     = $this->getMailer();
      $message    = $mailer->compose();

      $message->setSubject($subject);
      $message->setTo($send_to);

      $message->setFrom($send_from);
      $message->setBody($body, 'text/html');

      return $mailer->send($message);
    }
  }
}