<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of checkLinks
 *
 * @author dimonze
 */
class checkLinksTask extends sfBaseTask  
{
  private $conn;

  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));    
    
    $this->addArgument('tables', sfCommandArgument::OPTIONAL | sfCommandArgument::IS_ARRAY, 'tables to dump. eg: {event,page,user}');
    $this->namespace        = '';
    $this->name             = 'checkLinks';
    $this->briefDescription = '';
    $this->detailedDescription = '';
    $this->resultLog = array();
  }
  
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    sfContext::createInstance(sfProjectConfiguration::getApplicationConfiguration('frontend', 'prod', true));
    $this->conn = $databaseManager->getDatabase($options['connection'])->getConnection();
    $this->checkLinks();
  }
  
  private function check_url($url) {
    if((stristr($url, 'http://') && stristr($url, 'kre.ru')) || !stristr($url, 'http://')){
      $c = curl_init();
      curl_setopt($c, CURLOPT_URL, $url);
      curl_setopt($c, CURLOPT_HEADER, 1); // читать заголовок
      curl_setopt($c, CURLOPT_NOBODY, 1); // читать ТОЛЬКО заголовок без тела
      curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($c, CURLOPT_FRESH_CONNECT, 1); // не использовать cache
      curl_exec($c);
      $httpcode = curl_getinfo($c, CURLINFO_HTTP_CODE);
      if($httpcode != '200' && !stristr($url, 'http://')){      
        $c = null;
        $httpcode = null;
        $url = 'http://kre.ru' . $url;
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_HEADER, 1); // читать заголовок
        curl_setopt($c, CURLOPT_NOBODY, 1); // читать ТОЛЬКО заголовок без тела
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_FRESH_CONNECT, 1); // не использовать cache
        curl_exec($c);
        $httpcode = curl_getinfo($c, CURLINFO_HTTP_CODE);
      } 
      if($httpcode != '200' && !stristr($url, 'http://www.')){
        $c = null;
        $httpcode = null;
        $url = preg_replace('#http://#is', 'http://www.', $url);
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_HEADER, 1); // читать заголовок
        curl_setopt($c, CURLOPT_NOBODY, 1); // читать ТОЛЬКО заголовок без тела
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_FRESH_CONNECT, 1); // не использовать cache
        curl_exec($c);
        $httpcode = curl_getinfo($c, CURLINFO_HTTP_CODE);
      }  
      return $httpcode;
    }
    return '200';
}
  
  private function checkLinks()
  {     
    $queryLot = Doctrine::getTable('Lot')->createQuery('l')
      ->select('id, anons, description')
      ->where("CONVERT(anons USING utf8) LIKE '%href%' 
               OR CONVERT(description USING utf8) LIKE '%href%'")
      ->execute();
    $queryPage = Doctrine::getTable('Page')->createQuery('p')
      ->select('id, anons, body')
      ->where("CONVERT(anons USING utf8 ) LIKE '%href%'
               OR CONVERT(body USING utf8 ) LIKE '%href%'")
      ->execute();
    $querySeo_text = Doctrine::getTable('SeoText')->createQuery('s')
      ->select('id, text, description')
      ->where("CONVERT( text USING utf8 ) LIKE '%href%'
               OR CONVERT( description USING utf8 ) LIKE '%href%'")
      ->execute();
    

    foreach ($queryLot as $key => $value) {
      preg_match_all('#href="(.*?)"#is', $value->description, $res); 
      if(($res[1]) != NULL){
        foreach($res[1] as $val){
          $output = $this->check_url($val);
          if ($output != '200')
          {
            var_dump($output);            
            var_dump($val);
            var_dump(preg_replace('#<a href="'.$val.'">(.*?)</a>#is', '$2', $value->description));
            var_dump($value->description);
            //$this->resultLog['Lot'][$value->id] = array('description' => $value->description);
            //$value->description = preg_replace('#<a href="'.$val.'">(.*?)</a>#is', '$2', $value->description);
            //$value->save();
          }
        }
      }
      preg_match_all('#href="(.*?)"#is', $value->anons, $res); 
      if(($res[1]) != NULL){
        foreach($res[1] as $val){
          $output = $this->check_url($val);
          if ($output != '200')
          {
            var_dump($output);            
            var_dump($val);
            var_dump(preg_replace('#<a href="'.$val.'">(.*?)</a>#is', '$2', $value->anons));
            var_dump($value->anons);
            //$this->resultLog['Lot'][$value->id] = array('anons' => $value->anons);
            //$value->anons = preg_replace('#<a href="'.$val.'">(.*?)</a>#is', '$2', $value->anons);
            //$value->save();
          }
        }
      }
    }

    foreach ($queryPage as $key => $value) {
      preg_match_all('#href="(.*?)"#is', $value->body, $res); 
      if(($res[1]) != NULL){
        foreach($res[1] as $val){
          $output = $this->check_url($val);
          if ($output != '200')
          {            
            $this->resultLog['Lot'][$value->id] = array('body' => $value->body);
            $value->body = preg_replace('#<a href="'.$val.'">(.*?)</a>#is', '$2', $value->body);
            $value->save();
          }
        }
      }
      preg_match_all('#href="(.*?)"#is', $value->anons, $res); 
      if(($res[1]) != NULL){
        foreach($res[1] as $val){
          $output = $this->check_url($val);
          if ($output != '200')
          {
            $this->resultLog['Lot'][$value->id] = array('anons' => $value->anons);
            $value->anons = preg_replace('#<a href="'.$val.'">(.*?)</a>#is', '$2', $value->anons);
            $value->save();
          }
        }
      }
    }

    foreach ($querySeo_text as $key => $value) {
      preg_match_all('#href="(.*?)"#is', $value->description, $res); 
      if(($res[1]) != NULL){
        foreach($res[1] as $val){
          $output = $this->check_url($val);
          if ($output != '200')
          {
            $this->resultLog['Lot'][$value->id] = array('description' => $value->description);
            $value->description = preg_replace('#<a href="'.$val.'">(.*?)</a>#is', '$2', $value->description);
            $value->save();
          }
        }
      }
      preg_match_all('#href="(.*?)"#is', $value->text, $res); 
      if(($res[1]) != NULL){
        foreach($res[1] as $val){
          $output = $this->check_url($val);
          if ($output != '200')
          {
            $this->resultLog['Lot'][$value->id] = array('text' => $value->text);
            $value->text = preg_replace('#<a href="'.$val.'">(.*?)</a>#is', '$2', $value->text);
            $value->save();
          }
        }
      }
    }
    //var_dump($this->resultLog);
  }
}
