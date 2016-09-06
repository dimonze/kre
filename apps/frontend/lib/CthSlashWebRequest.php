<?php
class CthSlashWebRequest extends sfWebRequest
{
  public function getPathInfo()
  {
    $pathInfo = parent::getPathInfo();

    // cut off trailing slash
    $pathInfo = preg_replace('/\/$/', '', $pathInfo);

    return $pathInfo;
  }
}