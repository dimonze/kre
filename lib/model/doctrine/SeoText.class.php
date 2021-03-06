<?php

/**
 * SeoText
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    kre
 * @subpackage model
 * @author     Garin Studio
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class SeoText extends BaseSeoText
{
  const
    CACHE_KEY_LIST_HRURL_BY_URL = 'list_hrurl_by_url';


  public static function getAllSeo($url)
  {
    $url = preg_replace("/http:\/\/".sfContext::getInstance()->getRequest()->getHost(). "/", '', $url);
    $url = preg_replace('/\&curren/', '&_curren', $url);
    $url = urldecode($url);
    $url = preg_replace('/\s/', '+', $url);
    $seoUrl = Doctrine::getTable('SeoText')->findOneBy('url', $url);
    $seoHrurl = Doctrine::getTable('SeoText')->findOneBy('hrurl', $url);
    if (!empty($seoUrl)) return $seoUrl;
    if (!empty($seoHrurl)) return $seoHrurl;
    return false;
  }

  public static function IsUrlExist($url, $id)
  {
    $url = preg_replace('/\&curren/', '&_curren', $url);
    $seo = Doctrine::getTable('SeoText')->findOneBy('url', $url);
    if (!empty($seo) && $seo->id != $id) return true;
    return false;
  }

  public static function IsHrurlExist($hrurl, $id)
  {
    $seo = Doctrine::getTable('SeoText')->findOneBy('hrurl', $hrurl);
    if (!empty($seo) && $seo->id != $id) return true;
    return false;
  }

  public static function getHrurlByUrl($url)
  {
    $url = str_replace('http://'.sfContext::getInstance()->getRequest()->getHost(), '', $url);
    $url = preg_replace('/\&curren/', '&_curren', $url);
    $url = urldecode($url);
    $url = preg_replace('/\s/', '+', $url);

    $hrurl = Doctrine::getTable('SeoText')->fetchHrurlByUrl($url);
    if ($hrurl){
      $hrurl = preg_split('/\//', $hrurl);
    }

    return !empty($hrurl) ? $hrurl[3] : false;
  }

  public static function getUrlByHrurl($hrurl)
  {
    $seo = Doctrine::getTable('SeoText')->findOneBy('hrurl', $hrurl);
    if (!empty($seo->url)) return preg_replace("/http:\/\/".sfContext::getInstance()->getRequest()->getHost(). "/", '',urldecode($seo->url));
    return false;
  }

  public static function checkLandRedirect($request)
  {
    if(preg_match('/\?/', $request->getUri())){
        $url = self::checkEmpty($request->getUri());
        $uriAndParamtrs = preg_split('/\?/', $url);
        $reqUrl = $request->getPathInfo() . "?" . $uriAndParamtrs[1];
        if($hrurl = self::getHrurlByUrl($reqUrl)){
            return $uriAndParamtrs[0] . $hrurl . '/';
        }
      }
    return false;
  }

  public static function getAllParams($hrurl)
  {
     if ($url = self::getUrlByHrurl($hrurl)) {
       $url = preg_split('/\#/', $url);
       $url = preg_replace('/\&_curren/', '&curren', $url[0]);
       $arrayOfParams = array();
       $uriAndParams = preg_split('/\?/', $url);
       $parametrs = preg_split('/\&/', $uriAndParams[1]);
       for ($i = 0; $i < count($parametrs); $i++) {
         $parametrsValue = preg_split('/\=/', $parametrs[$i]);
         if (preg_match('/\[\]/', $parametrsValue[0])) {
           $parametrsValue[0] = preg_replace('/\[\]/', '', $parametrsValue[0]);
           $arrayOfParams[$parametrsValue[0]][] = $parametrsValue[1];
         } else {
           $arrayOfParams[$parametrsValue[0]] = $parametrsValue[1];
         }
       }
       return $arrayOfParams;
     }
     return false;
   }

   public static function checkEmpty($url)
   {
     $url = preg_replace('/\&curren/', '&_curren', $url);
     $url = preg_replace("/http:\/\/".sfContext::getInstance()->getRequest()->getHost(). "/", '',urldecode($url));
     $url = preg_replace('/\s/', '+', $url);
     if (preg_match('/\/offers\/eliteflat\/\?id\=\&area_from\=\&area_to\=\&price_from\=\&price_to\=\&_currency\=\&districts\[\]\=[0-9]+\&street\=\&autocomplete_street\=\&estate\=\&autocomplete_estate\=$/', $url))
      {
        $url = preg_replace('/id\=\&area_from\=\&area_to\=\&price_from\=\&price_to\=\&_currency\=\&/', '', $url);
        $url = preg_replace('/\&street\=\&autocomplete_street\=\&estate\=\&autocomplete_estate\=/', '', $url);
        return $url;
      }
      if (preg_match('/\/offers\/elitenew\/\?id\=\&area_from\=\&area_to\=\&price_from\=\&price_to\=\&_currency\=\&districts\[\]=[0-9]+\&street\=\&autocomplete_street\=\&estate\=\&autocomplete_estate\=$/', $url))
      {
        $url = preg_replace('/id\=\&area_from\=\&area_to\=\&price_from\=\&price_to\=\&_currency\=\&/', '', $url);
        $url = preg_replace('/\&street\=\&autocomplete_street\=\&estate\=\&autocomplete_estate\=/', '', $url);
        return $url;
      }
     /*if (preg_match('\/offers\/penthouse\/\?id\=\&area_from\=\&area_to\=\&price_from\=\&price_to\=\&_currency\=\&districts\[\]\=[0-9]+\&street\=\&autocomplete_street\=\&estate\=\&autocomplete_estate\=$/', $url))
      {
        $url = preg_replace('/id\=\&area_from\=\&area_to\=\&price_from\=\&price_to\=\&_currency\=\&/', '', $url);
        $url = preg_replace('/\&street\=\&autocomplete_street\=\&estate\=\&autocomplete_estate\=/', '', $url);
        return $url;
      }*/
      if (preg_match('/\/offers\/flatrent\/\?id\=\&rooms_from\=\&rooms_to\=\&price_from\=\&price_to\=\&_currency\=\&districts\[\]=[0-9]+\&street\=\&autocomplete_street\=\&estate\=\&autocomplete_estate\=$/', $url))
      {
        $url = preg_replace('/id\=\&rooms_from\=\&rooms_to\=\&price_from\=\&price_to\=\&_currency\=\&/', '', $url);
        $url = preg_replace('/\&street\=\&autocomplete_street\=\&estate\=\&autocomplete_estate\=/', '', $url);
        return $url;
      }
      if (preg_match('/\/offers\/outoftown\/\?id\=\&area_from\=\&area_to\=\&spaceplot_from\=\&spaceplot_to\=\&price_from\=\&price_to\=\&_currency=&wards\[\]=[0-9]+\&locality\=\&autocomplete_locality\=\&cottageVillage\=\&autocomplete_cottageVillage\=\&distance_mkad_from\=\&distance_mkad_to\=$/', $url))
      {
        $url = preg_replace('/id\=\&area_from\=\&area_to\=\&spaceplot_from\=\&spaceplot_to\=\&price_from\=\&price_to\=\&_currency=&/', '', $url);
        $url = preg_replace('/\&locality\=\&autocomplete_locality\=\&cottageVillage\=\&autocomplete_cottageVillage\=\&distance_mkad_from\=\&distance_mkad_to\=/', '', $url);
        return $url;
      }
      if (preg_match('/\/offers\/cottage\/\?id\=\&area_from\=\&area_to\=\&spaceplot_from\=\&spaceplot_to\=\&price_from\=\&price_to\=\&_currency\=\&wards[]=[0-9]+\&locality\=\&autocomplete_locality\=\&cottageVillage\=\&autocomplete_cottageVillage\=\&distance_mkad_from\=\&distance_mkad_to\=$/', $url))
      {
        $url = preg_replace('/id\=\&area_from\=\&area_to\=\&spaceplot_from\=\&spaceplot_to\=\&price_from\=\&price_to\=\&_currency\=\&/', '', $url);
        $url = preg_replace('/\&locality\=\&autocomplete_locality\=\&cottageVillage\=\&autocomplete_cottageVillage\=\&distance_mkad_from\=\&distance_mkad_to\=/', '', $url);
        return $url;
      }
      if (preg_match('/\/offers\/comsell\/\?id\=\&area_from\=\&area_to\=\&price_from\=\&price_to\=\&_currency\=\&street\=\&autocomplete_street\=\&objecttype\[\]\=[a-zа-яё\+]+$/ui', $url))
      {
        $url = preg_replace('/id\=\&area_from\=\&area_to\=\&price_from\=\&price_to\=\&_currency\=\&street\=\&autocomplete_street\=\&/', '', $url);
        return $url;
      }
     if (preg_match('/\/offers\/comrent\/\?id\=\&area_from\=\&area_to\=\&price_from\=\&price_to\=\&_currency\=\&street\=\&autocomplete_street\=\&objecttype\[\]\=[a-zа-яё\+]+$/ui', $url))
      {
        $url = preg_replace('/id\=\&area_from\=\&area_to\=\&price_from\=\&price_to\=\&_currency\=\&street\=\&autocomplete_street\=\&/', '', $url);
        return $url;
      }
     return $url;
   }

   public static function getH1($hrurl)
   {
    if ($hrurl == '') return false;
    $seo = Doctrine::getTable('SeoText')->findOneBy('hrurl', $hrurl);
    if (!empty($seo->name)) return $seo->name;
    return false;
   }


   public function postDelete($event)
   {
     $cache = KreCache::getInstance();
     $cache->remove(self::CACHE_KEY_LIST_HRURL_BY_URL);

     parent::postDelete($event);
   }

   public function postSave($event)
   {
     $cache = KreCache::getInstance();
     $cache->remove(self::CACHE_KEY_LIST_HRURL_BY_URL);

     parent::postSave($event);
   }
}
