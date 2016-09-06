<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of importDistricts
 *
 * @author dimonze
 */
class importDistrictsTask extends sfBaseTask 
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
    $this->name             = 'importDistricts';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }
  
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    sfContext::createInstance(sfProjectConfiguration::getApplicationConfiguration('frontend', 'prod', true));
    $this->conn = $databaseManager->getDatabase($options['connection'])->getConnection();
    $this->importDistricts();
  }
  
  private function getLink($type, $param, $value)
  {          
	return '/offers/' . $type . '/' . '?' . $param . '[]=' . $value; 
  }
  
  private function importDistricts()
  {  
          
     $presets = sfConfig::get('app_search_presets');
	 $h1   = sfConfig::get(sprintf('app_presets_h1_%s', $type));
	 $type = array('districts' => array('eliteflat', 'elitenew', 'penthouse', 'flatrent'), 'wards' => array('outoftown', 'cottage'), 'objecttype' => array('comsell', 'comrent'));
   $oldSeo = false;
	 foreach($presets as $key => $value)
	 {
		foreach($type[$key] as $typeValue)
		{
			$h1   = sfConfig::get(sprintf('app_presets_h1_%s', $typeValue));
			 foreach($presets[$key] as $subKey => $subValue)
			 {
				$seo = new SeoText();
				$seo->setUrl($this->getLink($typeValue, $key, $subKey));				
				$seo->setHrurl('/offers/' . $typeValue . '/' . $subValue . '/');			
				$seo->setName($h1[$subValue]);
        $oldSeo = Doctrine::getTable('SeoText')->findOneBy('url', '/offers/' . $typeValue . '/' . $subValue . '/');
        if($oldSeo)
        {
          if($oldSeo->name) $seo->setTitle($oldSeo->name);
          if($oldSeo->text) $seo->setText($oldSeo->text);
          if($oldSeo->description) $seo->setDescription($oldSeo->description);
          if($oldSeo->keywords) $seo->setKeywords($oldSeo->keywords);
        }
				$seo->save();	
        $oldSeo = false;
			 }
		 }
	 }	 
  }
}