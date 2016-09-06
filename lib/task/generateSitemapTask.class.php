<?php

class generateSitemapTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = '';
    $this->name             = 'generateSitemap';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [generateSitemap|INFO] task does things.
Call it with:

  [php symfony generateSitemap|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $m1 = memory_get_usage();
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $this->tab  = ' ';
    $this->host = 'http://www.kre.ru';

    $xml_file = sfConfig::get('sf_web_dir') . '/sitemap.xml';
    $tmp_file = sfConfig::get('sf_web_dir') . '/sitemap.tmp';


    $file = fopen($tmp_file, 'w');
    $u = 0;
    fwrite($file, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
    fwrite($file, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");

    foreach($this->getAllUrls() as $elem) {
      fwrite($file, sprintf('%s<url>%s', $this->tab, "\n"));
      foreach(array('loc', 'lastmod', 'changefreq', 'priority') as $tag) {
        if(!empty($elem[$tag])) {
          if($tag == 'loc') {
            $elem[$tag] = $this->host . $elem[$tag];
            $this->logSection('loc', $elem[$tag]);
          }
          fwrite($file, sprintf('%s<%s>%s</%2$s>%s', str_repeat($this->tab, 2), $tag, $elem[$tag], "\n"));
        }
      }
      fwrite($file, sprintf('%s</url>%s', $this->tab,  "\n"));
      $u++;
    }
    $this->logSection('total', $u . ' items');

    fwrite($file, '</urlset>' . "\n");
    fclose($file);

    rename($tmp_file, $xml_file);
    $this->logSection('memory', round(memory_get_usage()/1024/1024, 2) . 'MiB');
  }

  private function getAllUrls()
  {
    return array_merge(
      $this->getOfferUrls(),
      $this->getNewsUrls(),
      $this->getAnalyticsUrls(),
      $this->getAboutUrls(),
      $this->getServicesUrls(),
      $this->getAdvicesUrls(),
      $this->getVacancyUrls(),
      $this->getOtherPages()
    );
  }

  private function getOfferUrls()
  {
    $urls = array();

    $urls[] = array('loc' => '/offers/');
    $config = sfYaml::load(sfConfig::get('sf_config_dir') . '/app.yml');

    foreach(array_keys(Lot::$_types) as $loc) {
      $urls[] = array('loc' => sprintf('/offers/%s/', $loc));
      foreach(array_keys($config['all']['presets_h1'][$loc]) as $preset) {
        $urls[] = array('loc' => sprintf('/offers/%s/%s/', $loc, $preset));
      }
    }

    $query = $this->connection->prepare('SELECT type, count(*) as cnt FROM `lot` WHERE `status` = ? GROUP BY type');
    $query->execute(array('active'));
    foreach($query->fetchAll(PDO::FETCH_ASSOC) as $elem) {
      for($i = 1; $i <= ceil($elem['cnt']/50); $i++){
        $urls[] = array('loc' => sprintf('/offers/%s/?per_page=50&amp;page=%d', $elem['type'], $i));
      }
    }

    $query = $this->connection->prepare('SELECT `id`, `type`, `shortcut`
      FROM `lot` WHERE `status` = ?');
    $query->execute(array('active'));
    foreach($query->fetchAll(PDO::FETCH_ASSOC) as $elem) {
      $urls[] = array(
        'loc' => $elem['shortcut']
          ? sprintf('/offers/%s/%s/%d/', $elem['type'], $elem['shortcut'], $elem['id'])
          : sprintf('/offers/%s/%d/', $elem['type'], $elem['id'])
      );
    }
    return $urls;
  }

  private function getNewsUrls()
  {
    $urls = array();
    $query = $query = $this->connection->prepare('SELECT lft, rgt FROM `page`
      WHERE id = ? ');
    $query->execute(array(6));
    $page = $query->fetch(PDO::FETCH_ASSOC);

    $query = $this->connection->prepare('SELECT YEAR(created_at) AS yr, COUNT(id) AS  cnt FROM `page`
      WHERE `lft` > ? AND `rgt` < ? AND is_active = ? GROUP BY yr');
    $query->execute(array($page['lft'], $page['rgt'], 1));
    foreach($query->fetchAll(PDO::FETCH_ASSOC) as $elem) {
      $urls[] = array('loc' => sprintf('/news/archive/%d/', $elem['yr']));
      for($i = 2; $i <= ceil($elem['cnt']/20); $i++){
        $urls[] = array('loc' => sprintf('/news/archive/%d/?page=%d', $elem['yr'], $i));
      }
    }

    $query = $this->connection->prepare('SELECT id FROM `page` WHERE `lft` > ? AND `rgt` < ? AND is_active = ?');
    $query->execute(array($page['lft'], $page['rgt'], 1));
    foreach($query->fetchAll(PDO::FETCH_ASSOC) as $elem) {
      $urls[] = array('loc' => sprintf('/news/%d/', $elem['id']));
    }

    return $urls;
  }

  private function getAnalyticsUrls()
  {
    $urls = array();
    $query = $query = $this->connection->prepare('SELECT lft, rgt FROM `page`
      WHERE id = ? ');
    $query->execute(array(19));
    $page = $query->fetch(PDO::FETCH_ASSOC);

    $query = $this->connection->prepare('SELECT YEAR(created_at) AS yr, COUNT(id) AS  cnt FROM `page`
      WHERE `lft` > ? AND `rgt` < ? AND is_active = ? GROUP BY yr');
    $query->execute(array($page['lft'], $page['rgt'], 1));
    foreach($query->fetchAll(PDO::FETCH_ASSOC) as $elem) {
      $urls[] = array('loc' => sprintf('/analytics/6/archive/%d/', $elem['yr']));
      for($i = 2; $i <= ceil($elem['cnt']/20); $i++){
        $urls[] = array('loc' => sprintf('/analytics/6/archive/%d/?page=%d', $elem['yr'], $i));
      }
    }

    $query = $this->connection->prepare('SELECT id FROM `page` WHERE `lft` > ? AND `rgt` < ? AND is_active = ?');
    $query->execute(array($page['lft'], $page['rgt'], 1));
    foreach($query->fetchAll(PDO::FETCH_ASSOC) as $elem) {
      $urls[] = array('loc' => sprintf('/analytics/%d/', $elem['id']));
    }

    return $urls;
  }

  private function getAboutUrls()
  {
    $urls = array();
    $query = $query = $this->connection->prepare('SELECT lft, rgt FROM `page`
      WHERE id = ? ');
    $query->execute(array(2));
    $page = $query->fetch(PDO::FETCH_ASSOC);


    $query = $this->connection->prepare('SELECT id FROM `page` WHERE `lft` > ? AND `rgt` < ? AND is_active = ? AND level = ? AND id != ?');
    $query->execute(array($page['lft'], $page['rgt'], 1, 2, 6));
    foreach($query->fetchAll(PDO::FETCH_ASSOC) as $elem) {
      $urls[] = array('loc' => sprintf('/about/%d/', $elem['id']));
    }

    return $urls;
  }

  private function getServicesUrls()
  {
    $urls = array();
    $query = $query = $this->connection->prepare('SELECT lft, rgt FROM `page`
      WHERE id = ? ');
    $query->execute(array(11));
    $page = $query->fetch(PDO::FETCH_ASSOC);

    $query = $this->connection->prepare('SELECT id FROM `page` WHERE `lft` > ? AND `rgt` < ? AND is_active = ? AND level = ?');
    $query->execute(array($page['lft'], $page['rgt'], 1, 2));
    foreach($query->fetchAll(PDO::FETCH_ASSOC) as $elem) {
      $urls[] = array('loc' => sprintf('/services/%d/', $elem['id']));
    }

    return $urls;
  }

  private function getAdvicesUrls()
  {
    $urls = array();
    $query = $query = $this->connection->prepare('SELECT lft, rgt FROM `page`
      WHERE id = ? ');
    $query->execute(array(16));
    $page = $query->fetch(PDO::FETCH_ASSOC);

    $query = $this->connection->prepare('SELECT id FROM `page` WHERE `lft` > ? AND `rgt` < ? AND is_active = ? AND level = ?');
    $query->execute(array($page['lft'], $page['rgt'], 1, 2));
    foreach($query->fetchAll(PDO::FETCH_ASSOC) as $elem) {
      $urls[] = array('loc' => sprintf('/advices/%d/', $elem['id']));
    }

    return $urls;
  }

  private function getVacancyUrls()
  {
    $urls = array();

    $urls[] = array('loc' => '/vacancies/');
    foreach(array_keys(Vacancy::$_types) as $type) {
      $urls[] = array('loc' => sprintf('/vacancies/%s/', $type));
    }

    return $urls;
  }

  private function getOtherPages()
  {
    return array(
      array('loc' => '/claim/'),
      array('loc' => '/contacts/'),
      array('loc' => '/'),
    );
  }
}
