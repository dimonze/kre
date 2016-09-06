<?php

/**
 * filemanagerconnector actions.
 *
 * @package    bonaman
 * @subpackage special
 * @author     Eugeniy Belyaev (eugeniy.b@garin-studio.ru)
 */

abstract class filemanagerconnectorBaseActions extends sfActions {

  static $auth_error = array('error' => array('msg' => 'Access denided'));
  static $read_error = array('error' => array('msg' => "Can't open directory for reading"));
  static $write_error = array('error' => array('msg' => "Can't open directory for writing"));
  static $no_error = array('ok' => array('msg' => "OK"));
  static $write_lock = '.lock_write';
  static $read_lock = '.lock_read';
  static $move_error = array('error' => array('msg' => 'Can`t move file'));
  # absolute paths! NO TRAILING SLASHES
  static $base_path = null;  # sf_upload_dir
  static $web_path = null;   # sf_upload_dir - sf_web_dir

  protected abstract function auth(sfWebRequest $request);



  public function executeContent(sfWebRequest $request) {
    if ($msg = $this->auth($request)) return $this->render($request, $msg);

    return $this->render($request, $this->getContent($request));
  }

  public function executeCreatefolder(sfWebRequest $request) {
    if ($msg = $this->auth($request)) return $this->render($request, $msg);

    $dir = self::getPath($request->getParameter('path').'/'.self::cleanName($request->getParameter('name')));
    if (self::isWriteable(dirname($dir)) && !file_exists($dir)) {
      mkdir($dir);
      return $this->render($request, self::$no_error);
    }
    return $this->render($request, self::$write_error);
  }

  public function executeUpload(sfWebRequest $request) {
    if ($msg = $this->auth($request)) return $this->render($request, $msg);

    if (!isset($_FILES['file'])) {
      return $this->render($request, self::$write_error);
    }

    $path = self::getPath($request->getParameter('path').'/'.self::cleanName($_FILES['file']['name']));
    if (!self::isWriteable(dirname($path)) || file_exists($path)) {
      return $this->render($request, self::$write_error);
    }

    if (move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
      return $this->render($request, self::$no_error);
    }
    else {
      return $this->render($request, self::$write_error);
    }
  }

  public function executeDelete(sfWebRequest $request) {
    if ($msg = $this->auth($request)) return $this->render($request, $msg);

    $nodes = $request->getParameter('nodes', array());
    foreach ($nodes as $node) {
      self::deleteNode($node);
    }
    return $this->render($request, self::$no_error);
  }

  public function executeTobuffer(sfWebRequest $request) {
    if ($msg = $this->auth($request)) return $this->render($request, $msg);

    $user = $this->getUser();
    $nodes = $request->getParameter('nodes', array());

    $user->setAttribute('fm_files_buffer', $nodes);
    $user->setAttribute('fm_files_buffer_move', $request->getParameter('move'));

    return $this->render($request, self::$no_error);
  }

  public function executePaste(sfWebRequest $request) {
    if ($msg = $this->auth($request)) return $this->render($request, $msg);

    $user = $this->getUser();
    $nodes = $user->getAttribute('fm_files_buffer');

    if(!empty($nodes)) {
      $dir = self::getPath($request->getParameter('path'));
      if (self::isWriteable($dir)) {
        foreach ($nodes as $node) {
          $this->copyNode($node, $dir);
          if($user->getAttribute('fm_files_buffer_move')) self::deleteNode($node);
        }
      }
      $user->setAttribute('fm_files_buffer', null);
      $user->setAttribute('fm_files_buffer_move', null);
    }
    return $this->render($request, self::$no_error);
  }

  private static function deleteNode($node) {
    if (!self::isWriteable($node)) {
      return false;
    }

    if (!is_dir($node)) {
      return unlink($node);
    }
    else {
      $dir_handler = opendir($node);
      while (false != ($subnode = readdir($dir_handler))) {
        if ($subnode != '.' && $subnode != '..') {
          self::deleteNode($node.'/'.$subnode);
        }
      }
      rmdir($node);
    }
  }

  private function copyNode($source, $dest) {
    if(is_file($source)) {
      $file = $this->getFileInfo($source);
      copy($source, $dest . '/' . $file['name']);
    }
    else {
      $dir_handle=opendir($source);
      mkdir($dest."/".basename($source), 0750);
      while ($file = readdir($dir_handle)) {
        if ($file != "." && $file != "..") {
          if (!is_dir($source . "/" . $file))
            copy($source . "/" . $file, $dest . "/" . basename($source) . "/" . $file);
          else
            $this->copyNode ($source . "/" . $file, $dest. "/" . basename($source));
        }
      }
      closedir($dir_handle);
    }
    return true;
  }

  private function getContent(sfWebRequest $request) {
    $items = array();
    $path = self::getPath($request->getParameter('path'));
    if(!self::isReadable($path) || ! $dir_handler = opendir($path) ) {
      return self::$read_error;
    }
    while (false != ($node = readdir($dir_handler))) {
      if ((strpos($node, '.') !== 0 || $node == '.') && self::isReadable($path.'/'.$node) && $node != 'thumbs') {
        $items[] = $path.'/'.$node;
      }
    }
    natsort($items);
    $items = $this->filterItems($request, $items);
    $items = $this->parseItems($items);
    self::clearThumbsCache();
    return $items;
  }

  private static function isReadable ($path) {
    if (!is_readable($path)) {
      return false;
    }
    if (is_file($path)) {
      return self::isReadable(dirname($path));
    }

    if (strpos($path, self::$base_path) !== 0) {
      return false;
    }
    $path = explode('/', substr($path, strlen(self::$base_path) + 1));
    $current_path = self::$base_path.'/';
    for ($i = 0; $i <= count($path); $i++) {
      if (file_exists($current_path.self::$read_lock)) {
        return false;
      }
      if (isset($path[$i])) {
        $current_path .= $path[$i].'/';
      }
    }
    return true;
  }

  private static function isWriteable ($path) {
    if (!is_writable($path)) {
      return false;
    }
    if (is_file($path)) {
      return self::isWriteable(dirname($path));
    }
    if (strpos($path, self::getPath()) !== 0) {
      return false;
    }
    $path = explode('/', substr($path, strlen(self::$base_path) + 1));
    $current_path = self::$base_path.'/';
    for ($i = 0; $i <= count($path); $i++) {
      if (file_exists($current_path.self::$write_lock)) {
        return false;
      }
      if (isset($path[$i])) {
        $current_path .= $path[$i].'/';
      }
    }
    return true;
  }

  private function filterItems(sfWebRequest $request, $items) {
    if ($request->hasParameter('type')) {
      $filtered = array();
      foreach ($items as $item) {
        switch ($request->getParameter('type')) {
          case 'file':
            if (is_file($item)) {
              $filtered[] = $item;
            }
            break;
          case 'dir':
            if (is_dir($item)) {
              $filtered[] = $item;
            }
            break;
        }
      }
      return $filtered;
    }
    return $items;
  }

  private function parseItems($items) {
    $parsed = array();
    foreach ($items as $item) {
      $parsed[] = $this->getFileInfo($item);
    }
    return $parsed;
  }

  private function getFileInfo($file) {
    $info = array();
    $info['full_path'] = $file;
    $info['web_path'] = $this->getWebPath($file);
    $info['name'] = basename($file);
    $info['size'] = $this->getFileSize($file);
    $info['intsize'] = filesize($file);
    $info['mime'] = $this->getMimeType($file);
    $info['type'] = is_dir($file) ? 'folder' : 'file';
    $info['ico'] = $this->getIcon($info['mime']);
    if ($info['ico'] == 'image') {
      $info['thumb'] = $this->getThumb($file);
    }
    if (self::isWriteable($file)) {
      $info['writable'] = true;
    }
    return $info;
  }



  private static function getPath($path = null) {
    if (self::$base_path === null) {
      self::$base_path = sfConfig::get('sf_upload_dir');
    }
    $path = str_replace('..', '', $path);
    if ($path !== null && substr($path, -1) == '/') {
      $path = substr($path, 0, strlen($path) - 1);
    }
    while (substr($path, 0, 1) == '/') {
      $path = substr($path, 1);
    }
    return self::$base_path.($path ? '/'.$path : '');
  }

  private function cleanName ($name) {
    $chars = str_split('~!@#$%^&*()-+=\|/;:"\',?');
    return str_replace($chars, '', $name);
  }
  
  private function getWebPath($path = '') {
    if (self::$web_path === null) {
      self::$web_path = str_replace(sfConfig::get('sf_web_dir'), '', self::getPath());
    }
    return str_replace(self::getPath(), self::$web_path, $path);
    
  }

  private function getMimeType($file) {
    if (is_dir($file)) {
      return 'dir';
    }

    if (function_exists("mime_content_type")) {
      return mime_content_type($file);
    }
    elseif (function_exists('finfo_open')) {
      if (!isset(self::$finfo)) {
        self::$finfo = finfo_open(FILEINFO_MIME);
      }
      return finfo_file(self::$finfo, $file);
    }
    else {
      return GuessMime::file($file);
    }
  }

  private function getIcon($mime) {
    $icons = array();
    $icons['archive'] = array('application/x-bzip', 'application/x-bzip2', 'application/x-zip', 'application/x-gzip', 'application/x-rar', 'application/x-tar');
    $icons['exe'] = array('application/exe','application/x-exe','application/dos-exe','vms/exe','application/x-winexe','application/msdos-windows','application/x-msdos-program');
    $icons['pdf'] = array('application/pdf','application/x-pdf','application/acrobat','applications/vnd.pdf','text/pdf','text/x-pdf');
    $icons['spreadsheet'] = array('application/vnd.ms-excel','application/msexcel','application/x-msexcel','application/x-ms-excel','application/x-excel','application/xls','application/x-xls','text/comma-separated-values','text/csv','application/csv','application/excel','text/tab-separated-values', 'application/vnd.oasis.opendocument.spreadsheet', 'application/x-vnd.oasis.opendocument.spreadsheet');
    $icons['word'] = array('application/doc','application/msword','application/rtf','application/vnd.msword','application/vnd.ms-word','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.oasis.opendocument.text','application/winword','application/word','application/x-msword','application/x-vnd.oasis.opendocument.text','application/x-soffice','application/x-rtf','text/rtf','text/richtext');
    $icons['sound'] = array('application/ogg');

    $mime = preg_split('/\s+/', $mime);
    $mime = $mime[0];

    if ($mime == 'dir') return 'folder';
    if (strpos($mime, 'image/') === 0) return 'image';
    if (strpos($mime, 'video/') === 0) return 'image';
    if (strpos($mime, 'audio/') === 0) return 'sound';

    foreach ($icons as $icon => $types) {
      if (in_array($mime, $types)) return $icon;
    }

    if ($mime == 'text/html') return 'html';
    if (strpos($mime, 'text/') === 0) return 'txt';

    return 'unknown';
  }

  private function getFileSize($file){
    if (is_dir($file)){
      return null;
    }

    $b = filesize($file);
    $s = array('B', 'kB', 'MB', 'GB', 'TB');
    if($b < 0){
        return "0 ".$s[0];
    }
    $con = 1024;
    $e = (int) log($b, $con);
    return pow($con, $e) ? number_format($b / pow($con, $e), 2, ',', '.') . ' ' . $s[$e] : null;
  }

  private function getThumb($file) {
    $path = self::getPath('thumbs');

    if (!file_exists($path)) {
      mkdir($path);
    }

    preg_match("|\.([a-z0-9]{2,4})$|i", $file, $suffix);
    $dim = sfContext::getInstance()->getRequest()->getParameter('thumbs', '32x32');
    $thumb = md5($file).$dim.'.'.$suffix[1];

    $dim = explode('x', $dim);
    $width = (int) $dim[0];
    $height = (int) $dim[1];

    if (!file_exists($path.'/'.$thumb) && $width > 0&& $height > 0) {
      $thumbnail = new sfThumbnail($width, $height);
      $thumbnail->loadFile($file);
      $thumbnail->save($path.'/'.$thumb);
    }
    touch($path.'/'.$thumb);

    $dims = getimagesize($path.'/'.$thumb);
    return array('path' => self::$web_path.'/thumbs/'.$thumb, 'width' => $dims[0], 'height' => $dims[1]);
  }

  private static function clearThumbsCache($time = 259200) {
    if(! $dir_handler = @opendir(self::getPath('thumbs')) ) {
      return false;
    }
    while (false != ($node = readdir($dir_handler))) {
      if (strpos($node, '.') !== 0 && time() - fileatime(self::getPath('thumbs/'.$node)) > $time) {
        self::deleteNode(self::getPath('thumbs/'.$node));
      }
    }
  }




  private function render(sfWebRequest $request, array $array) {
    $response = $this->getResponse();

    switch($request->getParameter('format', 'xml')) {
      case 'xml':
        $response->setContentType('text/xml');
        $doc = new DOMDocument();
        $this->createDomNodes($doc, $array);
        $out = $doc->saveXML();
        break;

      case 'json':
        $response->setContentType('application/json');
        //$response->setContentType('text/plain');
        $out = json_encode($array);
        break;

      default:
        $response->setContentType('text/plain');
        $out = 'Unknown format: '.$request->getParameter('format');
    }

    if (isset($array['error'])) {
      $response->setStatusCode(500);
    }
    else {
      $response->setStatusCode(200);
    }

    return $this->renderText($out);
  }

  private function createDomNodes(DOMDocument $doc, array $items, DOMElement $parent = null) {
    if ($parent === null) {
      if (count($items) > 1 || count($items) == 0) {
        $parent = $doc->createElement('items');
        $doc->appendChild($parent);
      }
      else {
        $parent = $doc;
      }
    }

    foreach ($items as $key => $val) {
      if (is_int($key)) {
        $el = $doc->createElement('item');
      }
      else {
        $el = $doc->createElement($key);
      }

      if (is_array($val)) {
        $this->createDomNodes($doc, $val, $el);
      }
      else {
        if ($el->hasChildNodes()) {
          $el->setAttribute('name', $val);
        }
        else {
          $el->appendChild($doc->createTextNode($val));
        }
      }

      $parent->appendChild($el);
    }
  }
}
