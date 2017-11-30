<?php

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
require_once( 'schema.php' );

class MDSC_Data {

	/**
	 * The single instance of MDSC_Data.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	public function __construct ( $parent ) {
		$this->parent = $parent;
	}

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->make_or_update_classes_table();
	} // End install ()

	// Part of install()
	private function make_or_update_classes_table () {
		global $wpdb;
		global $MDSC_schema;

		$charset_collate = $wpdb->get_charset_collate();

		foreach ( $MDSC_schema as $type => $tinfo ) {
			$create_sql = 'CREATE TABLE ' . $this->table_name($type) . " (\n";
			$create_sql .= "id varchar(64) NOT NULL,\n";
			foreach ( $tinfo['schema'] as $field => $finfo ) {
				$create_sql .= "$field {$finfo['type']} NOT NULL,\n";
				if ( $finfo['tba'] ) {
					$create_sql .= "{$field}_tba boolean NOT NULL,\n";
				}
			}
			$create_sql .= "PRIMARY KEY  (id)\n) $charset_collate;";

			error_log("#dfr-trace: Creating table for '$type'.");
			dbDelta( $create_sql );
		}
	}

	/*
	 * Get the name of the $wpdb table for the given type.
	 */
	public function table_name ( $type ) {
		global $wpdb;
		$this->valid_type_check( $type );
		return $wpdb->prefix . 'mdsc_' . $type;
	}

	public function valid_type_check ( $type ) {
		global $MDSC_schema;
		if ( ! isset($MDSC_schema[$type]) ) {
			throw new Exception("Invalid type: '$type'.");
		}

		$rv = preg_match('/^\w+$/', $type);
		if ($rv === false) {
			throw new Exception('Error evaluating MDSC_Data::valid_type_check '.preg_last_error());
		} elseif ($rv == 0) {
			throw new Exception("Invalid MDSC type name: $type");
		}
	}

	/*
	 * Get a full set of data as a map of maps.
	 * 
	 * @returns Map ( ID ) => ( Map ( ColName ) => ( Value ) )
	 */
	public function get_data ( $type ) {
		global $wpdb;
		global $MDSC_schema;

		$this->valid_type_check($type);

		$table_name = $this->table_name( $type );
		$data = $wpdb->get_results( "SELECT * FROM $table_name;", ARRAY_A );

		$rv = array();
		foreach ( $data as $entry ) {
			$rv[$entry['id']] = $entry;
		}

		return $rv;
	}

	public function get_schema ( $type ) {
		global $MDSC_schema;
		$this->valid_type_check($type);
		return $MDSC_schema[$type];
	}

	/*
	 * Get a map from column name to a default value.
	 *
	 * Columns with no default value will be present in the map with the value
	 * as null.
	 */
	public function get_default_values ( $type ) {
		$schema = $this->get_schema($type);
		$rv = array('id' => null);
		foreach ($schema['schema'] as $col_name => $col_info) {
			if ($this->is_text_datatype($col_info['type'])) {
				$rv[$col_name] = '';
			} else {
				throw new Exception("SQL format unknown for type {$col_info['type']}");
			}

			if (isset($col_info['tba']) && $col_info['tba']) {
				$rv[$col_name . '_tba'] = 0;
			}
		}
		return $rv;
	}

	/*
	 * Get a map from column name to SQL format string for the given type.
	 */
	public function get_sql_format_strings ( $type ) {
		$schema = $this->get_schema($type);
		$rv = array('id' => '%s');
		foreach ($schema['schema'] as $col_name => $col_info) {
			if ($this->is_text_datatype($col_info['type'])) {
				$rv[$col_name] = '%s';
			} else {
				throw new Exception("SQL format unknown for type {$col_info['type']}");
			}

			
			if (isset($col_info['tba']) && $col_info['tba']) {
				$rv[$col_name . '_tba'] = '%d';
			}
		}
		return $rv;
	}

	/*
	 * Get data and sql format strings (as a tuple) for DB ops.
	 */
	public function format_for_sql ( $type, $entry ) {
		$sql_formats = $this->get_sql_format_strings($type);
		$data = array();
		$formats = array();

		/*
		 * add to the data map and push back onto the format array.
		 * It's a bit quirky that the data is an assoc. array and
		 * the formats is an ordered/numeric array, but it is.
		 */
		foreach ($sql_formats as $col_name => $col_format) {
			if ( ! array_key_exists($col_name, $entry) ) {
				throw new Exception("Validation error. No data for '$col_name'");
			}
			$formats[] = $col_format;
			$data[$col_name] = $entry[$col_name];
		}

		return array($data, $formats);
	}

	private function is_text_datatype ( $datatype) {
		if (strpos($datatype, 'varchar(') === 0) {
			return true;
		}
		return false;
	}


	/**
	 * Main MDSC_Data Instance
	 *
	 * Ensures only one instance of MDSC_Data is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}