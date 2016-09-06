<?php

require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    mb_internal_encoding('UTF-8');
    date_default_timezone_set('Europe/Moscow');
    setlocale(LC_ALL, 'ru_RU.UTF-8');
    sfConfig::set('sf_tmp_dir', sfConfig::get('sf_root_dir').'/tmp');

    $this->registerErrbit();

    $sf_application = sfConfig::get('sf_app', false);
    if (!$sf_application) {
      $this->enableAllPluginsExcept('sfPropelPlugin');
    }
    else {
      $common_plugins_array = array(
        'sfDoctrinePlugin',
        'csDoctrineActAsSortablePlugin',
        'sfThumbnailPlugin',
        'sfFormExtraPlugin',
      );

      switch ($sf_application) {
        case 'frontend':
          $custom_plugins_array = array();
          break;

        case 'backend':
          $custom_plugins_array = array(
            'sfAdminThemejRollerPlugin',
          );
          break;
      }

      $this->enablePlugins(array_merge($common_plugins_array, $custom_plugins_array));
    }
  }

  protected function registerErrbit()
  {
    // Production only
    if (strpos(__DIR__, 'new.kre.ru')) {
      require_once sfConfig::get('sf_lib_dir') . '/vendor/errbit/lib/Errbit.php';
      Errbit::instance()
        ->configure(array(
          'api_key'           => '83c61cc90c5a62f79f112bebdbcc42b6',
          'host'              => 'errbit.garin.su',
          'port'              => 80,
          'secure'            => false,
          'project_root'      => sfConfig::get('sf_root_dir'),
          'environment_name'  => 'production',
        ))
        ->start(array('error', 'fatal'));

      $this->getEventDispatcher()->connect('application.throw_exception', function(sfEvent $e) {
        Errbit::instance()->notify($e->getSubject());
      });
    }
  }
}
