<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class HtmlGen {
	// string for HTML attributions given as assoc. array.
	static public function attrs ( $attrs ) {
		$html = '';
		foreach ($attrs as $name => $value) {
			self::safe_name($name);
			$html .= ' ' . $name . '="' . esc_attr($value) . '"';
		}
		return $html;
	}

	static public function new_id ( $prefix = '' ) {
		return uniqid($prefix);
	}

	// string for a self-closing HTML element.
	static public function celem ($name, $attrs = null, $extra = null) {
		return self::elem_impl($name, $attrs, $extra, true);
	}

	// string for empty HTML element
	static public function eelem ($name, $attrs = null, $extra = null) {
		return self::elem_impl($name, $attrs, $extra, false) . "</$name>";
	}

	// string for opening an HTML element.
	static public function elem ($name, $attrs = null, $extra = null) {
		return self::elem_impl($name, $attrs, $extra, false);
	}

	static private function elem_impl ( $name, $attrs, $extra, $close) {
		self::safe_name($name);

		$attrs = is_null($attrs) ? array() : $attrs;

		$html = '<' . $name . self::attrs($attrs);
		$html .= is_null($extra) ? '' : " $extra";
		$html .= $close ? '/>' : '>';
		return $html;
	}

	/*
	 * Given a class string or an array of classes, return a string or null.
	 * The result should still be esc_attr'd before being emitted.
	 */
	static public function classes($classes) {
		if (is_string($classes) && strlen($classes) > 0) {
			return $classes;
		} elseif (is_array($classes) && sizeof($classes) > 0) {
			return implode(' ', $classes);
		}
	}

	static public function div_wrap($html, $classes=null) {
		$attrs = array();
		$class = self::classes($classes);
		if ($class) {
			$attrs['class'] = $class;
		}

		return self::elem('div', $attrs) . $html . '</div>';
	}

	static private function safe_name ( $name ) {
		$rv = preg_match('/^[-\w]+$/', $name);
		if ($rv === false) {
			throw new Exception('Error evaluating HtmlGen::safe_name '.preg_last_error());
		} elseif ($rv == 0) {
			throw new Exception("Invalid HTML element or attribute name: $name");
		}
	}

	static public function debug_wrap ( $html, $title = '' ) {
		$id = self::new_id('debug_');
		$onclick = "(function(){jQuery('#$id').toggle();})();";

		$rv = self::elem('div', array('class' => 'debug_wrap'));

		$rv .= self::elem('span', array('class' => 'debug_toggle', 'onclick' => $onclick));
		if ($title) {
			$rv .= 'Debug information. Click to show/hide.';
		} else {
			$rv .= "Debug information: $title. Click to show/hide.";
		}
		$rv .= '</span>';

		$rv .= self::elem(
			'div',
			array(
				'class' => 'debug_info',
				'id' => $id,
				'style' => 'display: none;'
			)
		);
		$rv .= $html;
		$rv .= '</div>';

		$rv .= '</div>';

		return $rv;
	}
}

1;
