<?php

//if (!in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1','::1','80.251.133.198','195.62.58.154')) && substr(@$_SERVER['REMOTE_ADDR'], 0, 7) != '192.168') {
 // die('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
//}

require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'cache', true);
sfContext::createInstance($configuration)->dispatch();
