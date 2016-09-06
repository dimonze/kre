<?php


class defaultActions extends sfActions
{
  public function executeLogin(sfWebRequest $request)
  {
    $auth = array(
      'kre' => array(
        'pass' => 'contact956', 'credentials' => array('admin', 'log'), 'suptype' => null
      ),
      'VipritskayaMK' => array(
        'pass' => '0497mk', 'credentials' => 'admin', 'suptype' => Lot::$_suptypes['city']
      ),
      'akozyreva' => array(
        'pass' => 'apple5', 'credentials' => 'admin', 'suptype' => Lot::$_suptypes['commercial']
      ),
      'alsou' => array(
        'pass' => 'naza74#30', 'credentials' => 'admin', 'suptype' => Lot::$_suptypes['country']
      ),
      'seo' => array(
        'pass' => 'seonizm2000', 'credentials' => 'seo', 'suptype' => null
      ),
    );

    $this->form = new sfForm();
    $this->form->setWidgets(array(
      'login'     => new sfWidgetFormInput(),
      'pass'      => new sfWidgetFormInputPassword()
    ));
    $this->form->getWidgetSchema()->setNameFormat('auth[%s]');

    if ($request->isMethod('post') && $request->hasParameter('auth')) {
      $values = $request->getParameter('auth');
      if (!in_array($values['login'], array_keys($auth))) {
        $this->getUser()->setFlash('error', 'Неверный логин или пароль');
        return;
      }

      $this->form->setValidators(array(
        'login'     => new sfValidatorChoice(array('required' => true, 'choices' => array($values['login']))),
        'pass'      => new sfValidatorChoice(array('required' => true, 'choices' => array($auth[$values['login']]['pass'])))
      ));

      $this->form->bind($values);
      if ($this->form->isValid()) {
        $this->getUser()->setAuthenticated(true);
        $this->getUser()->addCredentials($auth[$values['login']]['credentials']);
        $this->getUser()->setAttribute('suptype', $auth[$values['login']]['suptype']);
        $this->getUser()->setAttribute('username', $values['login']);
        if ($request->getReferer() != $this->getController()->genUrl('default/login', true)) {
          $this->redirect($request->getReferer());
        }
        else {
          $this->redirect('@homepage');
        }
      } else {
        $this->getUser()->setFlash('error', 'Неверный логин или пароль');
      }
    }
  }

  public function executeLogout()
  {
    $this->getUser()->clearCredentials();
    $this->getUser()->setAuthenticated(false);
    $this->getUser()->getAttributeHolder()->clear();

    $this->getController()->redirect('default/login');
  }


  public function executeSecure()
  {
    $this->getResponse()->setStatusCode('403');
  }

  public function executeConfig(sfWebRequest $request)
  {
    $this->params = array(
      'default_title'           => 'HTML Title по-умолчанию',
      'default_description'     => 'HTML Description по-умолчанию',
      'default_keywords'        => 'HTML Keywords по-умолчанию',
      'contact_email'           => 'Контактный e-mail ?????',
      'order_catalog_email'     => 'Email для получения заказов на каталог',
      'default_spec_text'       => 'Текст по-умолчанию для блока "Лучшие предложения"',
      'phones[office]'          => 'Основной',
      'phones[office_comsell]'  => Lot::$_types['comsell'],
      'phones[office_comrent]'  => Lot::$_types['comrent'],
      'phones[office_cottage]'  => Lot::$_types['cottage'],
      'phones[office_outoftown]'=> Lot::$_types['outoftown'],
      'phones[office_eliteflat]'=> Lot::$_types['eliteflat'],
      'phones[office_elitenew]' => Lot::$_types['elitenew'],
      'phones[office_penthouse]'=> Lot::$_types['penthouse'],
      'phones[office_flatrent]' => Lot::$_types['flatrent'],
      'claim[emails][1]'        => Claim::$_types[1],
      'claim[emails][2]'        => Claim::$_types[2],
      'claim[emails][3]'        => Claim::$_types[3],
      'claim[emails][4]'        => Claim::$_types[4],
      'claim[emails][5]'        => Claim::$_types[5],
      'claim[emails][6]'        => Claim::$_types[6],
      'claim[emails][7]'        => Claim::$_types[7],
      'claim[emails][8]'        => Claim::$_types[8],
      'claim[emails][9]'        => Claim::$_types[9],
      'claim[emails][10]'       => Claim::$_types[10],
      'claim[emails][11]'       => Claim::$_types[11],
      'claim[emails][12]'       => Claim::$_types[12],
    );

    if ($request->isMethod('post')) {
      $app_config = sfYaml::load(sfConfig::get('sf_config_dir').'/app.yml');

      foreach (array_keys($this->params) as $param) {
        if ($pos = mb_strpos($param, '[')) {
          $key = mb_strcut($param, 0, $pos);
          $app_config['all'][$key] = $request->getParameter($key);
        }
        else {
          $app_config['all'][$param] = $request->getParameter($param);
        }
      }

      file_put_contents(sfConfig::get('sf_config_dir').'/app.yml', sfYaml::dump($app_config, 4));
      Tools::removeCacheFile();

      $this->getUser()->setFlash('notice', 'Изменения успешно сохранены.');
      $this->redirect('@default?module=default&action=config');
    }
  }
}
