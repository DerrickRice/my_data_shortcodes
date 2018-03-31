<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class MDSC_Hacks {
	static private function get_real_input($source) {
	    $pairs = explode("&", $source == 'POST' ? file_get_contents("php://input") : $_SERVER['QUERY_STRING']);
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
