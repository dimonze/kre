<?php
class KreCache extends sfCache
{
  private static $_instance;

  protected
    $_cache,
    $_options;


  public static function getInstance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new KreCache();
    }

    return self::$_instance;
  }


  public function initialize($options = array())
  {
    $this->_options = array_merge(
      sfConfig::get('app_cache_params', array()),
      $options
    );
    $class = sfConfig::get('app_cache_class');

    if ('sfMemcacheCache' == $class && function_exists('memcache_get')) {
      $this->_cache = new sfMemcacheCache($this->_options);
    }
    elseif('sfAPCCache' == $class && function_exists('apc_store')) {
      $this->_cache = new sfAPCCache($this->_options);
    }
    elseif('sfXCacheCache' == $class && function_exists('xcache_set')) {
      $this->_cache = new sfXCacheCache($this->_options);
    }
    elseif('sfEAcceleratorCache' == $class && function_exists('eaccelerator_put')) {
      $this->_cache = new sfEAcceleratorCache($this->_options);
    }
    elseif('sfSQLiteCache' == $class && extension_loaded('SQLite')) {
      $this->_cache = new sfSQLiteCache($this->_options);
    }
    else {
      $this->_cache = new sfFileCache($this->_options);
    }
  }

  /**
   * Gets the cache content for a given key.
   *
   * @param string $key     The cache key
   * @param mixed  $default The default value is the key does not exist or not valid anymore
   *
   * @return mixed The data of the cache
   */
  public function get($key, $default = null)
  {
    return $this->getCache()->get($key, $default);
  }

  /**
   * Returns true if there is a cache for the given key.
   *
   * @param string $key The cache key
   *
   * @return Boolean true if the cache exists, false otherwise
   */
  public function has($key)
  {
    return $this->getCache()->has($key);
  }

  /**
   * Saves some data in the cache.
   *
   * @param string $key      The cache key
   * @param mixed  $data     The data to put in cache
   * @param int    $lifetime The lifetime
   *
   * @return Boolean true if no problem
   */
  public function set($key, $data, $lifetime = null)
  {
    return $this->getCache()->set($key, $data, $lifetime);
  }

  /**
   * Removes a content from the cache.
   *
   * @param string $key The cache key
   *
   * @return Boolean true if no problem
   */
  public function remove($key)
  {
    return $this->getCache()->remove($key);
  }

  /**
   * Removes content from the cache that matches the given pattern.
   *
   * @param string $pattern The cache key pattern
   *
   * @return Boolean true if no problem
   *
   * @see patternToRegexp
   */
  public function removePattern($pattern)
  {
    return $this->getCache()->removePattern($pattern);
  }

  /**
   * Cleans the cache.
   *
   * @param string $mode The clean mode
   *                     sfCache::ALL: remove all keys (default)
   *                     sfCache::OLD: remove all expired keys
   *
   * @return Boolean true if no problem
   */
  public function clean($mode = self::ALL)
  {
    return $this->getCache()->clean($mode);
  }

  /**
   * Returns the timeout for the given key.
   *
   * @param string $key The cache key
   *
   * @return int The timeout time
   */
  public function getTimeout($key)
  {
    return $this->getCache()->getTimeout($key);
  }

  /**
   * Returns the last modification date of the given key.
   *
   * @param string $key The cache key
   *
   * @return int The last modified time
   */
  public function getLastModified($key)
  {
    return $this->getCache()->getLastModified($key);
  }


  /**
   * Gets the cache object.
   *
   * @return object
   */
  public function getCache()
  {
    return $this->_cache;
  }


  public function getAllKeys($prefix = null)
  {
    if ($this->getCache() instanceof sfMemcacheCache) {
      return $this->getAllKeysMemcache($prefix);
    }
    elseif ($this->getCache() instanceof sfFileCache) {
      return $this->getAllKeysFile($prefix);
    }
    else {
      throw new sfException('This cache class does not have yet getAllKeys() method implementation.');
    }
  }

  private function getAllKeysMemcache($prefix = null)
  {
    $mem_keys = array();
    $regexp = $prefix ? sprintf('/^%s_(\d+)$/', $prefix) : '/^(\d+)$/';
    $allSlabs = $this->getCache()->getBackend()->getExtendedStats('slabs');

    foreach($allSlabs as $server => $slabs) {
      if (!is_array($slabs)) continue;

      foreach($slabs AS $slabId => $slabMeta) {
        if (!is_int($slabId)) continue;

        $cdump = $this->getCache()->getBackend()->getExtendedStats('cachedump',(int)$slabId, 1000);
        foreach($cdump AS $keys => $arrVal) {
          if (!is_array($arrVal)) continue;

          foreach($arrVal AS $k => $v) {
            $key = explode(':', $k);
            if (preg_match($regexp, $key[1])){
              $mem_keys[] = $key[1];
            }
          }
        }
      }
    }

    return $mem_keys;
  }

  private function getAllKeysFile($prefix = null)
  {
    $mem_keys = array();
    $name = $prefix ? $prefix.'_*' : '/^\d+\.*/';
    foreach (sfFinder::type('file')->name($name)->in($this->_options['cache_dir']) as $filename) {
      $filename = str_replace($this->_options['cache_dir'], '', $filename);
      preg_match('/(\d+)\.cache$/', $filename, $matches);
      if (!empty($matches[1])) {
        $mem_keys[] = (int)$matches[1];
      }
    }

    return $mem_keys;
  }
}