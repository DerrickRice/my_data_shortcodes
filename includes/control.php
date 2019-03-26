<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( 'schema.php' );

class MDSC {
  /**
   * The single instance of MDSC.
   * @var   object
   * @access  private
   * @since   1.0.0
   */
  private static $_instance = null;

  /**
   * Settings class object
   * @var     object
   * @access  public
   * @since   1.0.0
   */
  public $settings = null;

  /**
   * The version number.
   * @var     string
   * @access  public
   * @since   1.0.0
   */
  public $_version;

  /**
   * The main plugin file.
   * @var     string
   * @access  public
   * @since   1.0.0
   */
  public $file;

  /**
   * The main plugin directory.
   * @var     string
   * @access  public
   * @since   1.0.0
   */
  public $dir;

  /**
   * The plugin assets directory.
   * @var     string
   * @access  public
   * @since   1.0.0
   */
  public $assets_dir;

  /**
   * The plugin assets URL.
   * @var     string
   * @access  public
   * @since   1.0.0
   */
  public $assets_url;

  /**
   * Constructor function.
   * @access  public
   * @since   1.0.0
   * @return  void
   */
  public function __construct ( $file = '', $version = '1.0.0' ) {
    $this->_version = $version;

    // Load plugin environment variables
    $this->file = $file;
    $this->dir = dirname( $this->file );
    $this->assets_dir = trailingslashit( $this->dir ) . 'assets';
    $this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

    // Activation takes place just once
    register_activation_hook( $this->file, array( $this, 'install' ) );
    // using plugins_loaded to detect updates.
    add_action( 'plugins_loaded', array( $this, 'check_for_update' ) );

    // Load admin JS & CSS
    add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
    add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

    // Load API for generic admin functions
    if ( is_admin() ) {
      $this->admin = new MDSC_Admin_API();
    }

    // Handle localisation
    $this->load_plugin_textdomain();
    add_action( 'init', array( $this, 'load_localisation' ), 0 );
    add_action( 'init', array( $this, 'add_shortcodes' ));

    // Load sub-classes which we always expect to have.
    $this->menus = MDSC_Menus::instance( $this );
    $this->settings = MDSC_Settings::instance( $this );
    $this->data = MDSC_Data::instance( $this );
  } // End __construct ()

  public function add_shortcodes () {
      add_shortcode(
        get_option( 'MDSC_shortcode_name', 'mdsc' ),
        array( $this, 'handle_shortcode' )
      );

      add_shortcode(
        "mdsc_json_list",
        array( $this, 'handle_json_list' )
      );
  }

  public function shortcode_error ($msg, $tag_text) {
    $text = "Error handling tag: $tag_text -> $msg";
    return '<br/><span style="color:red;"> &#9760; '.esc_html($text).' &#9760; </span><br/>';
  }

  public function rebuild_tag ( $tag, $attrs ) {
    $rv = "[$tag";
    foreach ( $attrs as $key => $value ) {
      $rv .= " $key=\"$value\"";
    }
    $rv .= "]";
    return $rv;
  }

  public function handle_json_list ($attrs = null, $content = null, $tag = null) {
    if (! $attrs) {
      $attrs = [];
    }
    /* Assume tag is the default tag */
    $tag_text = $this->rebuild_tag( $tag, $attrs );

    // normalize all attrs to lowercase
    $attrs = array_change_key_case( (array) $attrs, CASE_LOWER );

    if ( ! isset($attrs['id'])) {
      return $this->shortcode_error('No id value provided.', $tag_text);
    }

    $parts = explode('-', $attrs['id']);

    if (count($parts) != 1) {
      return $this->shortcode_error('Malformed id. Cannot parse $type.', $tag_text);
    }

    global $MDSC_schema;
    $type = $parts[0];

    // look up schema for the given type and confirm that field is valid.
    if ( ! isset($MDSC_schema[$type]) ) {
      return $this->shortcode_error("Type '$type' is unknown.", $tag_text);
    }

    $data = array_values($this->data->get_data($type));

    if (isset($attrs['filter_by']) && $attrs['filter_by']) {
      $filter_by = $attrs['filter_by'];
      $reverse = false;
      if (substr($filter_by, 0, 1) === '!') {
        $reverse = -1;
        $filter_by = substr($filter_by, 1);
      }

      $data = array_filter($data, function($x) use ($reverse, $filter_by) {
        return $reverse xor ((bool)$x[$filter_by]);
      });
    }

    $reverse = 1;
    $order_by = 'id';
    if (isset($attrs['order_by']) && $attrs['order_by']) {
      $order_by = $attrs['order_by'];
      if (substr($order_by, 0, 1) === '!') {
        $reverse = -1;
        $order_by = substr($order_by, 1);
      }
    }

    usort($data, function($a, $b) use ($reverse, $order_by) {
      $left = is_null($a[$order_by]) ? '' : $a[$order_by];
      $right = is_null($b[$order_by]) ? '' : $b[$order_by];
      if ($left == $right) {
        return 0;
      } elseif ($left < $right) {
        return $reverse * -1;
      } else {
        return $reverse * 1;
      }
    });

    return json_encode($data);
  }

  public function handle_shortcode ($attrs = null, $content = null, $tag = null) {
    if (! $attrs) {
      $attrs = [];
    }
    /* Assume tag is the default tag */
    $tag_text = $this->rebuild_tag( $tag, $attrs );

    // normalize all attrs to lowercase
    $attrs = array_change_key_case( (array) $attrs, CASE_LOWER );

    if ( ! isset($attrs['id'])) {
      return $this->shortcode_error('No id value provided.', $tag_text);
    }

    $parts = explode('-', $attrs['id']);

    $out = $this->fetch_by_id($parts, $tag_text);

    return $out;
  }

  public function fetch_by_id ( $parts, $tag_text ) {
    if (count($parts) < 3) {
      return $this->shortcode_error('Malformed id. Cannot parse $type-$key-$field.', $tag_text);
    }

    global $MDSC_schema;
    $type = $parts[0];
    $key = $parts[1];
    $field = $parts[2];

    // look up schema for the given type and confirm that field is valid.
    if ( ! isset($MDSC_schema[$type]) ) {
      return $this->shortcode_error("Type '$type' is unknown.", $tag_text);
    }

    $schema = $MDSC_schema[$type]['schema'];

    if ( ! isset($schema[$field]) ) {
      return $this->shortcode_error("Field '$field' is unknown.", $tag_text);
    }

    $data = $this->data->get_data($type);

    if ( ! isset($data[$key]) ) {
      return $this->shortcode_error("No data for key '$key'.", $tag_text);
    }

    if ( $data[$key][$field.'_tba'] ) {
      return '<span class="tba">TBA</span>';
    }

    return $data[$key][$field];
  }

  /**
   * Load admin CSS.
   * @access  public
   * @since   1.0.0
   * @return  void
   */
  public function admin_enqueue_styles ( $hook = '' ) {
    wp_register_style(
      'mdsc-admin',
      esc_url( $this->assets_url ) . 'css/admin.css',
      array(),
      $this->_version
    );
    wp_enqueue_style( 'mdsc-admin' );
  } // End admin_enqueue_styles ()

  /**
   * Load admin Javascript.
   * @access  public
   * @since   1.0.0
   * @return  void
   */
  public function admin_enqueue_scripts ( $hook = '' ) {
    wp_register_script(
      'mdsc-admin',
      esc_url( $this->assets_url ) . 'js/admin.js',
      array( 'jquery' ),
      $this->_version
    );
    wp_enqueue_script( 'mdsc-admin' );
  } // End admin_enqueue_scripts ()

  /**
   * Load plugin localisation
   * @access  public
   * @since   1.0.0
   * @return  void
   */
  public function load_localisation () {
    load_plugin_textdomain(
      'mdsc',
      false,
      dirname( plugin_basename( $this->file ) ) . '/lang/'
    );
  } // End load_localisation ()

  /**
   * Load plugin textdomain
   * @access  public
   * @since   1.0.0
   * @return  void
   */
  public function load_plugin_textdomain () {
      $domain = 'mdsc';

      $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

      load_textdomain(
        $domain,
        WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo'
      );
      load_plugin_textdomain(
        $domain,
        false,
        dirname( plugin_basename( $this->file ) ) . '/lang/'
      );
  } // End load_plugin_textdomain ()

  /**
   * Main Instance
   *
   * Ensures only one instance is loaded or can be loaded.
   *
   * @since 1.0.0
   * @static
   */
  public static function instance ( $file = '', $version = '1.0.0' ) {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self( $file, $version );
    }
    return self::$_instance;
  } // End instance ()

  /**
   * Cloning is forbidden.
   *
   * @since 1.0.0
   */
  public function __clone () {
    _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
  } // End __clone ()

  /**
   * Unserializing instances of this class is forbidden.
   *
   * @since 1.0.0
   */
  public function __wakeup () {
    _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
  } // End __wakeup ()

  // return an array of strings indicating which updates have been completed.
  public function get_update_flags() {
    $flags = get_option( 'mdsc_update_flags' );
    if (empty($flags)) {
      return array();
    }

    return $flags;
  }

  public function has_update_flag($flag) {
    $update_flags = $this->get_update_flags();
    return in_array($flag, $update_flags);
  }

  public function add_update_flag($flag) {
    $update_flags = $this->get_update_flags();
    if (in_array($flag, $update_flags)) {
      error_log("MDSC update flag '$flag' is already set.");
      return;
    }

    $update_flags[] = $flag;
    update_option( 'mdsc_update_flags' , $update_flags );
    error_log("MDSC update flag '$flag' marked");
  }

  /** Plugin activiation */
  public function install () {
    update_option( 'mdsc_version', $this->_version );
    $this->data->install();
  }

  public function check_for_update() {
    $previous_version = get_option('mdsc_version');
    $current_version = $this->_version;
    if ($previous_version != $current_version) {
      error_log("MDSC update from $previous_version to $current_version.");
      $this->install();
    }
  }
}
