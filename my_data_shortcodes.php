<?php
/*
 * Plugin Name: My Data Shortcodes
 * Version: 1.0
 * Plugin URI: 
 * GitHub Plugin URI: https://github.com/DerrickRice/my_data_shortcodes
 * Description: Create custom (tabular) data and surface it in posts and pages via shortcodes.
 * Author: Derrick Rice
 * Author URI: http://www.rice.io/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: mdsc
 * Domain Path: /lang/
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/control.php' );
require_once( 'includes/menus.php' );
require_once( 'includes/settings.php' );
require_once( 'includes/data.php' );

// Load plugin libraries
require_once( 'includes/lib/mydata-admin-api.php' );

/**
 * Returns the main instance of MDSC to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object MDSC
 */
function MDSC () {
	$instance = MDSC::instance( __FILE__, '1.0.0' );

	return $instance;
}

MDSC();