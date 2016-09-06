<?php
	/**
	 * access control login form
	 * @author Logan Cai (cailongqun [at] yahoo [dot] com [dot] cn)
	 * @link www.phpletter.com
	 * @since 22/April/2007
	 *
	 */
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "inc" . DIRECTORY_SEPARATOR . "config.php");
if(isset($_POST['username']))
{
	if($auth->login())
	{
		header('Location: ' . appendQueryString(CONFIG_URL_HOME, makeQueryString()));
		exit;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link type="text/css" href="theme/<?php echo CONFIG_THEME_NAME; ?>/css/login.css" rel="stylesheet" />
<title><?php echo LOGIN_PAGE_TITLE; ?></title>
</head>
<body>
<div id="container">
  <div id="content">
    <h2>¬ы не авторизовались, либо врем€ вашей сессии истекло. ќбновите основную страницу и авторизуйтесь на сайте.</h2>
  </div>
</div>
</body>
</html>
