<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once('util.php');

class MDSC_Menus {

	/**
	 * The single instance.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	// The main plugin object.
	private $control = null;

	public function __construct ( $control ) {
		$this->control = $control;		

		// Add menus
		add_action( 'admin_menu' , array( $this, 'add_menu_items' ), 9 );

		// settings menu gets added by settings.php
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_items () {
		global $MDSC_schema;
		$slug_top = 'mdsc_menu_top';

		$title = __( 'My Data Shortcodes', 'mdsc' );

		$p_top = add_menu_page(
 			$title,
			$title,
        	'edit_posts',
        	$slug_top,
        	array( $this, 'menu_top_html' )
        );

		foreach ( $MDSC_schema as $type => $tinfo ) {
			$renderer = new MenuRenderer($type, $this->control);
			$title = __( 'Data:', 'mdsc') . ' ' . $tinfo['print_name'];

			// TODO: relax back from manage_options.
			add_submenu_page(
				$slug_top,
	 			$title,
				$title,
				'edit_posts',		
				'mdsc_menu_' . $type,
				array( $renderer, 'get_html' ) 
			);
		}
	}

	public function menu_top_html () {
		$html = '<div class="wrap mdsc_menu" id="mdsc_menu_top">';
		
		$html .= esc_html('Hello, user. ');

		$html .= '</div>';

		// wordpress wants us to echo it.
		echo $html;
	}

	/**
	 * Main Instance
	 *
	 * Ensures only one instance is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 */
	public static function instance ( $control ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $control );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->control->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->control->_version );
	} // End __wakeup()

}

class MenuRenderer {
	public $type;
	private $control;

	public function __construct ( $type, $control ) {
		$this->type = $type;
		$this->control = $control;
	}

	public function get_schema () {
		return $this->control->data->get_schema($this->type);
	}

	public function get_sql_format_strings () {
		return $this->control->data->get_sql_format_strings($this->type);
	}

	public function get_default_values ($tba = false) {
		return $this->control->data->get_default_values($this->type, $tba);
	}

	public function format_for_sql($entry) {
		return $this->control->data->format_for_sql($this->type, $entry);
	}

	public function table_name() {
		return $this->control->data->table_name($this->type);
	}

	private function form_id () {
		return 'mdsc_menu_' . $this->type;
	}

	public function get_html () {
		$form_id = $this->form_id();
	
		$html = HtmlGen::elem('div', array('class' => 'wrap mdsc_menu', 'id' => $form_id));

		$html .= $this->handle_post();

		$html .= HtmlGen::elem(
			'form',
			array(
				'action' => $_SERVER['REQUEST_URI'],
				'method' => 'post'
			)
		);

		// get the data AFTER post is handled, because we want to get new data.
		$data = $this->control->data->get_data($this->type);
		foreach ($data as $entry) {
			$html .= HtmlGen::elem('div', array('class' => 'mdsc_admin_entry'));
			$html .= $this->form_fields($entry);
			$html .= '</div>';
		}

		$html .= HtmlGen::elem('div', array('class' => 'mdsc_admin_entry new_entry'));
		$html .= $this->textarea_input(
			'__new_ids__',
			'__new_ids__',
			'',
			__('New IDs', 'mdsc'),
			''
		);
		$html .= '</div>';

		$html .= HtmlGen::celem(
			'input',
			array(
				'type' => 'submit',
				'name' => $form_id,
				'value' => 'Save Changes'
			)
		);

		$html .= '</form></div>';

		$html .= HtmlGen::debug_wrap(
			'<pre class="schema_printout">' .
			esc_html(print_r($this->get_schema(), true)) .
			'</pre>',
			'Schema definition'
		);

		// wordpress wants us to echo it.
		echo $html;
	}

	private function new_entries_from_post_data () {
		// array_key_exists 
		if ( ! isset($_POST[$this->form_id()]) ) {
			return null;
		}

		$data = MDSC_Hacks::get_real_post();
		unset($data[$this->form_id()]);

		$new_entries = array();

		if ( isset($data['__new_ids__']) ) {
			$parts = preg_split('/\s+/', $data['__new_ids__']);
			foreach ($parts as $new_id) {
				if ( empty($new_id) ) {
					continue;
				}
				$new_entries[$new_id] = $this->get_default_values(true);
				$new_entries[$new_id]['id'] = $new_id;
			}
			unset($data['__new_ids__']);
		}

		foreach ($data as $name => $value) {
			$parts = explode('-', $name);
			if (count($parts) != 2 ) {
				return $this->post_error("Invalid format of input field: '$name'");
			}

			$original_id = $parts[0];
			$field_name = $parts[1];

			if ( ! isset($new_entries[$original_id]) ) {
				$new_entries[$original_id] = $this->get_default_values();
			}

			$new_entries[$original_id][$field_name] = $value;
		}

		return $new_entries;
	}

	private function handle_post () {
		global $wpdb;

		$new_entries = $this->new_entries_from_post_data();
		if (is_null($new_entries)) {
			return '';
		}

		// VALIDATE HERE
		// Must not be any duplicate IDs in union of new and old.

		$old_entries = $this->control->data->get_data($this->type);

		$changes = array();
		foreach ($new_entries as $original_id => $entry) {
			$old_entry = null;
			if (isset($old_entries[$original_id])) {
				$old_entry = $old_entries[$original_id];
			}

			if ($old_entry == null && $original_id == $entry['id']) {
				// INSERTION
				list($data, $formats) = $this->format_for_sql($entry);

				$rv = $wpdb->insert($this->table_name(), $data, $formats);

				if ($rv === false) {
					$changes[] = 'Failed to insert new entry';
				} else {
					$changes[] = 'Added new entry with ID ' . $entry['id'];
				}
			} elseif ($old_entry == null) {
				$changes[] = "Failed to find $original_id in existing data.";
			} elseif (isset($entry['__delete'])) {
				// DELETIONS
				$rv = $wpdb->delete(
					$this->table_name(),
					array('id' => $original_id),
					array('%s')
				);
				if ($rv === false) {
					$changes[] = "Failed to delete $original_id";
				} else {
					$changes[] = "$rv rows deleted for $original_id";
				}
			} elseif ($this->differs($entry, $old_entry)) {
				// UPDATES
				list($data, $formats) = $this->format_for_sql($entry);

				$rv = $wpdb->update(
					$this->table_name(),
					$data,
					array('id' => $original_id),	// where
					$formats,
					array('%s')						// where format
				);

				if ($rv === false) {
					$changes[] = "Failed to update $original_id";
				} else {
					$changes[] = "$rv rows updated for $original_id";
				}
			}
		}

		if (count($changes) == 0) {
			$changes[] = 'No changes applied.';
		}

		$change_text = HtmlGen::elem(
			'ul',
			array('id' => 'mdsc_changereport')
		);
		foreach ($changes as $change) {
			$change_text .= '<li>' . esc_html($change) . '</li>' . "\n";
		}
		$change_text .= '</ul>';

		$debug = HtmlGen::debug_wrap(
			'<pre>' . esc_html(print_r($new_entries, true)) . '</pre>',
			'Post data'
		);

		return $debug . $change_text;
	}

	private function differs( $entryA, $entryB ) {
		$all_keys = array_keys($entryA + $entryB);
		foreach ($all_keys as $key) {
			if ($key == '__delete') {
				continue;
			}
			if ( ! array_key_exists($key, $entryA) ) {
				// found in B but not found in A
				return true;
			}
			elseif ( ! array_key_exists($key, $entryB)) {
				// found in A but not found in B
				return true;
			}
			elseif ($entryA[$key] != $entryB[$key]) {
				// different values
				return true;
			}
		}

		return false;
	}

	private function post_error($msg) {
		return '<p style="color:red;">' . esc_html($msg) . '</p>';
	}

	private function form_fields ($entry) {
		// add the implicit (but important) id field
		$html = $this->text_input(
			'id',
			$entry['id'] . '-id',
			$entry['id'],
			__('Entry ID', 'mdsc'),
			'placeholder="New ID here"'
		);

		foreach ($this->get_schema()['schema'] as $field => $finfo) {
			if ($finfo['input'] == 'text') {
				$html .= $this->text_input(
					$field,
					$entry['id'] . '-' . $field,
					isset($entry[$field]) ? $entry[$field] : '',
					$finfo['print_name'],
					$finfo['attrs']
				);
			} elseif ($finfo['input'] == 'textarea') {
				$html .= $this->textarea_input(
					$field,
					$entry['id'] . '-' . $field,
					isset($entry[$field]) ? $entry[$field] : '',
					$finfo['print_name'],
					$finfo['attrs']
				);
			} else {
				$html .= 'NOT SUPPORTED<br/>';
			}

			if ($finfo['tba']) {
				$tba_field = $field . '_tba';
				$html .= $this->checkbox_input(
					$tba_field,
					$entry['id'] . '-' . $tba_field,
					isset($entry[$tba_field]) ? $entry[$tba_field] : false,
					$finfo['print_name'] . __(' TBA?', 'mdsc'),
					''
				);
			}
		}

		$html .= $this->checkbox_input(
			'__delete',
			$entry['id'] . '-__delete',
			false,
			__('Delete this entry?', 'sweemo'),
			''
		);

		return $html;
	}

	private function textarea_input ($field, $input_id, $value, $label, $extra_attr) {
		$html = HtmlGen::elem('div', array(
			'class' => 'mdsc_admin_field',
			'data-field' => $field
		));

		$html .= HtmlGen::elem('label', array('for' => $input_id));
		$html .= esc_html($label) . '</label>';

		$html .= HtmlGen::elem(
			'textarea',
			array(
				'name' => $input_id,
				'id' => $input_id
			),
			$extra_attr
		);
		$html .= esc_textarea($value);
		$html .= '</textarea>';

		$html .= '</div>';

		return $html;
	}

	private function text_input ($field, $input_id, $value, $label, $extra_attr) {
		$html = HtmlGen::elem('div', array(
			'class' => 'mdsc_admin_field',
			'data-field' => $field
		));

		$html .= HtmlGen::elem('label', array('for' => $input_id));
		$html .= esc_html($label) . '</label>';

		$html .= HtmlGen::celem(
			'input',
			array(
				'type' => 'text',
				'name' => $input_id,
				'id' => $input_id,
				'value' => $value
			),
			$extra_attr
		);

		$html .= '</div>';

		return $html;
	}

	private function checkbox_input ($field, $input_id, $checked, $label, $extra_attr) {
		$html = HtmlGen::elem('div', array(
			'class' => 'mdsc_admin_field',
			'data-field' => $field
		));

		$html .= HtmlGen::elem('label', array('for' => $input_id));
		$html .= esc_html($label) . '</label>';

		$attrs = array(
			'type' => 'checkbox',
			'name' => $input_id,
			'id' => $input_id,
			'value' => '1'
		);
		if ($checked) {
			$attrs['checked'] = 'checked';
		}

		$html .= HtmlGen::celem('input', $attrs, $extra_attr);
		$html .= '</div>';

		return $html;
	}
}