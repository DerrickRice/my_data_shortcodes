<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class MDSC_Hacks {
  static private function get_real_input($source) {
    $content = '';

    if ($source == 'POST') {
      $content = file_get_contents("php://input");
      if (!empty($_SERVER['CONTENT_TYPE']) &&
        stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        // it's json - stop here.
        return json_decode($content, TRUE);
      }
    } else {
      $content = $_SERVER['QUERY_STRING'];
    }

    $pairs = explode("&", $content);
    $vars = array();
    foreach ($pairs as $pair) {
      $nv = explode("=", $pair);
      $name = urldecode($nv[0]);
      $value = urldecode($nv[1]);
      $vars[$name] = $value;
    }
    return $vars;
  }

  // Wrapper functions specifically for GET and POST:
  static public function get_real_get() { return self::get_real_input('GET'); }
  static public function get_real_post() { return self::get_real_input('POST'); }
}
