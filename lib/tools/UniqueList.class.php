<?php

class UniqueList
{
  public $file;
  protected $_name, $_data = array();
  protected static $_instances = array();

  /**
   * @param string $name
   * @return YamlUniqueList
   */
  public static function getInstance($name)
  {
    if (!isset(self::$_instances[$name])) {
      self::$_instances[$name] = new self($name);
    }

    return self::$_instances[$name];
  }

  public function __construct($name)
  {
    $this->_name = $name;
    $this->file = sprintf('%s/%s.yml', sfConfig::get('sf_data_dir'), $name);
    $this->read();
  }

  public function read()
  {
    if (file_exists($this->file)) {
      $this->_data = array_filter(array_map('trim', file($this->file)));
    }
  }

  public function save()
  {
    sort($this->_data);
    return file_put_contents($this->file, implode("\n", $this->_data));
  }

  public function getData()
  {
    return $this->_data;
  }

  public function clear($save = true)
  {
    $this->_data = array();

    if ($save) {
      $this->save();
    }
  }

  public function add($value, $save = true, $trim = true)
  {
    if ($trim) {
      $value = trim($value);
    }

    if (!in_array($value, $this->_data)) {
      $this->_data[] = $value;
    }

    if ($save) {
      $this->save();
    }
  }

  public function remove($value, $save = true)
  {
    $key = array_search($value, $this->_data);
    if(false !== $key){
      unset($this->_data[$key]);
      if($save) {
        $this->save();
      }
    }
  }
}