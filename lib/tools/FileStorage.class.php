<?php

class FileStorage
{
  protected $options = array();


  public function __construct($options = array())
  {
    $this->options = array_merge(
      array(
        'level'       => 2,
        'name_format' => '%s',
        'root'        => sfConfig::get('sf_upload_dir'),
        'prefix'      => '_source',
        'target_mime' => null,
        'umask'       => 0000,
      ),
      $options
    );

    $this->options['level'] = (int) $this->options['level'];
    if ($this->options['level'] < 1) {
      $this->options['level'] = 1;
    }

    $this->options['umask'] &= 0777;

    if (!is_dir($this->options['root'])) {
      mkdir($this->options['root'], $this->getPermissions(true), true);
    }

    if (!is_writeable($this->options['root'])) {
      throw new RuntimeException(sprintf('Cannot create storage at "%s".', $this->options['root']));
    }

    $this->options['root'] = realpath($this->options['root']);
  }


  public function setNameFormat($frmt)
  {
    $this->options['name_format'] = (string) $frmt;
  }

  public function setPrefix($prefix)
  {
    $this->options['prefix'] = $prefix;
  }

  public function setOption($option, $value)
  {
    $this->options[$option] = $value;
  }

  public function getOptions()
  {
    return $this->options;
  }

  public function getOption($option)
  {
    $option = (string) $option;
    return isset($this->options[$option]) ? $this->options[$option] : null;
  }


  public function buildPath($id, $prefix = null)
  {
    $path = $this->getPath($id, $prefix);

    if (!is_dir($path)) {
      // fuck mkdir's recursive arg. we need proper permissions!
      $_path = '';
      foreach (explode('/', $path) as $part) {
        $_path .= $part . '/';
        if (!is_dir($_path)) {
          mkdir($_path);
          chmod($_path, $this->getPermissions(true));
        }
      }
    }

    return $path;
  }

  public function getPath($id, $prefix = null)
  {
    $path = $this->translateToPath($id, $prefix);

    return implode(DIRECTORY_SEPARATOR, $path);
  }

  public function getName($id)
  {
    return sprintf($this->getOption('name_format'), $id);
  }

  public function generateFilename($id, $prefix = null, $real = null)
  {
    $filename = $this->translateToPath($id, $prefix);
    $name = $this->getName($id);
    if (!is_null($real) && $ext = pathinfo($real, PATHINFO_EXTENSION)) {
      $name .= '.'.$ext;
    }
    $filename[] = $name;

    return implode(DIRECTORY_SEPARATOR, $filename);
  }

  public function getFilename($id, $prefix = null)
  {
    $files = glob($this->generateFilename($id, $prefix).'.*');
    if (empty($files)) return null;

    return array_shift($files);
  }

  /**
   * The options are:
   *  * width
   *  * height
   *  * scale
   *  * inflate
   *  * quality
   *  * adapter_class
   *  * adapter_options
   *
   *  * crop
   *
   * @param int $id
   * @param string $prefix
   * @param array $options
   * @return string
   * @throws Exception
   */
  public function getThumb($id, $prefix, array $options)
  {
    if ($file = $this->getFilename($id, $prefix)) {
      return $file;
    }
    else {
      if (!($source = $this->getFilename($id, $this->getOption('prefix')))) {
        return '/images/1px.gif';
        //throw new Exception(sprintf('Can\'t find source image for id=%d at %s', $id, $this->generateFilename($id, 'source').'.jpg'));
      }
      $this->buildPath($id, $prefix);
      $target = $this->generateFilename($id, $prefix, $source);

      $options = array_merge(
        array(
          'width'   => null,
          'height'  => null,
          'scale'   => true,
          'inflate' => true,
          'crop'    => false,
          'quality' => 80,
          'watermark' => false,
          'adapter_class' => 'sfImageMagickAdapter',
          'adapter_options' => array(),
        ),
        $options
      );

      if ($options['crop']) {
        $options['scale'] = false;
        if (empty($options['adapter_options']['method'])) {
          $options['adapter_options']['method'] = 'shave_all';
        }
      }

      $options['adapter_options']['watermark'] = null;
      if($options['watermark']) {
        $options['adapter_options']['watermark'] = sfConfig::get('sf_root_dir') . sfConfig::get('app_watermark');
      }

      $thumb = new sfThumbnail($options['width'], $options['height'], $options['scale'],
                               $options['inflate'], $options['quality'],
                               $options['adapter_class'], $options['adapter_options']);
      $thumb->loadFile($source);
      $thumb->save($target, $this->getOption('target_mime'));
      $thumb->freeAll();
      $this->chmod($target);

      return $target;
    }
  }


  public function isStored($id, $prefix = null)
  {
    return is_file($this->getFilename($id, $prefix));
  }

  public function add($filename, $id, $move = false, $prefix = null)
  {
    if ($this->isStored($id, $prefix)) return false;

    $this->buildPath($id, $prefix);
    $newFilename = $this->generateFilename($id, $prefix, $filename);
    $move ? rename($filename, $newFilename) : copy($filename, $newFilename);

    return $this->chmod($newFilename);
  }

  public function chmod($file)
  {
    return chmod($file, $this->getPermissions());
  }

  public function store($filename, $id, $move = false, $prefix = null)
  {
    $this->delete($id, $prefix);
    return $this->add($filename, $id, $move, $prefix);
  }

  public function delete($id, $prefix = null)
  {
    if (!$this->isStored($id, $prefix)) return false;
    return unlink($this->getFilename($id, $prefix));
  }


  protected function translateIdentifier($id)
  {
    $id = base_convert((integer) $id, 10, 36);
    $id = sprintf("%'0" . $this->getOption('level') . 's', $id);

    $path = array_slice(str_split($id), -$this->getOption('level'));
    $path = array_reverse($path);

    return $path;
  }

  protected function translateToPath($id, $prefix = null)
  {
    $path[] = $this->getOption('root');

    $prefix = is_null($prefix) ? $this->getOption('prefix') : $prefix;
    if (!is_null($prefix)) {
      $path[] = $prefix;
    }

    $path = array_merge($path, $this->translateIdentifier($id));

    return $path;
  }

  protected function getPermissions($forDir = false)
  {
    return ($forDir ? 0777 : 0666) & ~$this->getOption('umask');
  }
}

