<?php
/**
 * Plugin Name: Omise Payment Gateway
 * Plugin URI: https://www.omise.co/woocommerce
 * Description: Omise WooCommerce Gateway Plugin is a wordpress plugin designed specifically for WooCommerce. The plugin adds support for Omise Payment Gateway payment method to WooCommerce.
 * Version: 1.2.3
 * Author: Omise
 * Author URI: https://www.omise.co
 *
 * Copyright: Copyright 2014-2015. Omise Co., Ltd.
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 */
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );
defined( 'OMISE_PROTOCOL_PREFIX' ) || define( 'OMISE_PROTOCOL_PREFIX', 'https://' );
defined( 'OMISE_VAULT_HOST' ) || define( 'OMISE_VAULT_HOST', 'vault.omise.co' );
defined( 'OMISE_API_HOST' ) || define( 'OMISE_API_HOST', 'api.omise.co' );
defined( 'OMISE_WOOCOMMERCE_PLUGIN_VERSION' ) || define( 'OMISE_WOOCOMMERCE_PLUGIN_VERSION', '1.2.3' );
defined( 'OMISE_API_VERSION' ) || define( 'OMISE_API_VERSION', '2014-07-27' );
defined( 'OMISE_WOOCOMMERCE_TEXT_DOMAIN' ) || define( 'OMISE_WOOCOMMERCE_TEXT_DOMAIN', 'omise-woocommerce' );

require_once dirname( __FILE__ ) . '/includes/libraries/omise-php/lib/Omise.php';
require_once dirname( __FILE__ ) . '/includes/libraries/omise-plugin/Omise.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-omise-charge.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-omise-hooks.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-omise-transfer.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-omise-card-image.php';

require_once 'omise-util.php';
require_once 'omise-api-wrapper.php';
require_once 'omise-wc-gateway.php';
require_once 'omise-wc-myaccount.php';
require_once 'omise-wp-admin.php';

add_action( 'init', 'register_omise_wc_gateway_post_type' );
add_action( 'plugins_loaded', 'register_omise_wc_gateway_plugin', 0 );
add_action( 'plugins_loaded', 'prepare_omise_myaccount_panel', 0 );

// Include these files only when we are in the admin pages
if ( is_admin() ) {
    require_once dirname( __FILE__ ) . '/includes/classes/class-omise-charges-table.php';
    require_once dirname( __FILE__ ) . '/includes/classes/class-omise-transfers-table.php';

    add_action( 'plugins_loaded', array( Omise_Admin::get_instance(), 'register_admin_page_and_actions' ) );
}
?>
