<?php

if(!empty($_SERVER['REQUEST_URI']) && '/index.php' === $_SERVER['REQUEST_URI']) { 
    header("HTTP/1.1 301 Moved Permanently");
    header(sprintf("Location: http://%s/", $_SERVER['HTTP_HOST']));
    exit();  
}

require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
sfContext::createInstance($configuration)->dispatch();
