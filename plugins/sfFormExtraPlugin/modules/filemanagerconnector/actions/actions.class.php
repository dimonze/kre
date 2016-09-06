<?php
class filemanagerconnectorActions extends filemanagerconnectorBaseActions {
  protected function auth(sfWebRequest $request) {
    if (false) {
      return self::$auth_error;
    }
    else {
      return false;
    }
  }
}
