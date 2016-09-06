<?php

class importTextTask extends sfBaseTask
{
  public
    $phones_old = array(),
    $brokers    = array();
  private $_conn, $_conn_old;

  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Skip the question'),
      new sfCommandOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Limit'),
    ));

    $this->namespace        = '';
    $this->name             = 'importText';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    if (!$options['no-confirmation'] && !$this->ask('All Lot data will be erased. Continue?')) {
      exit;
    }

    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->_conn = $databaseManager->getDatabase($options['connection'])->getConnection();

    $this->_conn_old = new PDO(
      'mysql:dbname=kre_old;host=localhost', 'kre', 'kre',
      array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      )
    );

    $tables = array(
      Page::NEWS_ID   => 'news',
      Page::REVIEW_ID => 'analytics',
    );

    foreach ($tables as $id => $table) {
      $this->importPagesTable($table, $id);
    }

    $this->logSection('done', 'Completed successfully');
  }

  private function importPagesTable($table, $id) {
    $method = sfInflector::camelize(sprintf('import_table_%s', $table));
    if (is_callable(array($this, $method))) {
      return $this->$method($id);
    }
  }

  private function importTableNews($id)
  {
    $_fields = array(
      'header' => 'name',
      'date' => 'created_at',
      'lead' => 'anons',
      'text' => 'body',
      'active' => 'is_active',
    );
    $parent = Doctrine::getTable('Page')->find($id);
    $this->logSection('news', 'Clean up...');
    $stmt = $this->_conn->prepare('select id from page WHERE ' . 'lft > (select lft from page where id = ' . $id . ') and rgt < (select rgt from page where id = ' . $id . ')');
    $stmt->execute();
    $pages = Doctrine::getTable('Page');
    while($news = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $pages->find($news['id'])->delete();
    }
    $this->logSection('news', 'old news deleted');

    $stmt = $this->_conn_old->prepare('select * from news order by date');
    $stmt->execute();
    $s = $stmt->rowCount();
    $i = 0;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $i++;
      $news = new Page();
      $data = array();
        foreach ($row as $key=>$value) {
          if(!empty($_fields[$key])){
            $data[$_fields[$key]] = $value;
          }
        }
      $news->fromArray($data);
      $news->getNode()->insertAsLastChildOf($parent);
      $this->logSection('news', $i . ' of ' . $s. ' / ' . round($i/$s*100, 2) . '%' );
    }
  }

  private function importTableAnalytics($id)
  {
    $_fields = array(
      'header' => 'name',
      'date' => 'created_at',
      'lead' => 'anons',
      'text' => 'body',
      'active' => 'is_active',
    );
    $parent = Doctrine::getTable('Page')->find($id);
    $this->logSection('analytics', 'Clean up...');
    $stmt = $this->_conn->prepare('select id from page WHERE ' . 'lft > (select lft from page where id = ' . $id . ') and rgt < (select rgt from page where id = ' . $id . ')');
    $stmt->execute();
    $pages = Doctrine::getTable('Page');
    while($news = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $pages->find($news['id'])->delete();
    }
    $this->logSection('analytics', 'old news deleted');

    $stmt = $this->_conn_old->prepare('select * from analytics where parent = 6 order by date');
    $stmt->execute();
    $s = $stmt->rowCount();
    $i = 0;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $i++;
      $news = new Page();
      $data = array();
      foreach ($row as $key=>$value) {
        if(!empty($_fields[$key])){
          $data[$_fields[$key]] = $value;
        }
      }

      $data['anons'] = importTask::processHtml($data['anons']);
      $data['body'] = importTask::processHtml($data['body']);

      $news->fromArray($data);
      $news->getNode()->insertAsLastChildOf($parent);
      $this->logSection('analytics', $i . ' of ' . $s. ' / ' . round($i/$s*100, 2) . '%' );
    }
  }
}
