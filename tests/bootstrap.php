<?php
class Omise_Unit_Tests_Bootstrap{
  
  /** @var \Omise_Unit_Tests_Bootstrap instance */
  protected static $instance = null;

  public $wordpress_core_dir;

  public $woocommerce_root_dir;

  public $wordpress_test_lib_dir; 

  public $current_test_dir;

  public function __construct(){
    ini_set( 'display_errors','on' );
    error_reporting( E_ALL );
    $this->current_test_dir = dirname( __FILE__ );
    $this->wordpress_core_dir = getenv('WP_CORE_DIR');
    if ( !$this->wordpress_core_dir ) $this->wordpress_core_dir = '/tmp/wordpress';
    $this->woocommerce_root_dir = $this->wordpress_core_dir . '/wp-content/plugins/woocommerce/';
    $this->wordpress_test_lib_dir  = getenv('WP_TESTS_DIR');
    if ( !$this->wordpress_test_lib_dir ) $this->wordpress_test_lib_dir = '/tmp/wordpress-tests-lib';

    require_once $this->wordpress_test_lib_dir . '/includes/functions.php';

    tests_add_filter( 'muplugins_loaded', array($this, 'manually_load_plugin' ));

    tests_add_filter( 'setup_theme', array($this, 'install_woocommerce' ));

    require $this->wordpress_test_lib_dir . '/includes/bootstrap.php';
  }

  public function manually_load_plugin() {
    require $this->woocommerce_root_dir.'woocommerce.php';
    require $this->current_test_dir. '/../omise-gateway.php';
  }


  public function install_woocommerce(){
     // clean existing woocommerce installation first
    define( 'WP_UNINSTALL_PLUGIN', true );
    include( $this->woocommerce_root_dir . '/uninstall.php' );
    WC_Install::install();
    // reload capabilities after install, see https://core.trac.wordpress.org/ticket/28374
    $GLOBALS['wp_roles']->reinit();

    echo "Installing WooCommerce..." . PHP_EOL;
  }

  public static function get_instance() {
    if ( is_null( self::$instance ) ) {
    self::$instance = new self();
    }
    return self::$instance;
  }

}

Omise_Unit_Tests_Bootstrap::get_instance();

