<?php
/**
 * Plugin Name: Omise Payment Gateway
 * Plugin URI: https://www.omise.co/woocommerce-plugin
 * Description: Omise WooCommerce Gateway Plugin is a wordpress plugin designed specifically for WooCommerce. The plugin adds support for Omise Payment Gateway payment method to WooCommerce.
 * Version: 1.1.1
 * Author: Omise
 * Author URI: https://www.omise.co
 *
 * Copyright: Copyright 2014-2015. Omise Co., Ltd.
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 */

defined('ABSPATH') or die("No direct script access allowed.");

define("OMISE_PROTOCOL_PREFIX", "https://");
define("OMISE_VAULT_HOST", "vault.omise.co");
define("OMISE_API_HOST", "api.omise.co");
define("OMISE_WOOCOMMERCE_PLUGIN_VERSION", "1.1.1");

require_once dirname( __FILE__ ) . '/includes/libraries/omise-plugin/helpers/currency.php';
require_once 'omise-util.php';
require_once 'omise-api-wrapper.php';
require_once 'omise-wc-gateway.php';
require_once 'omise-wc-myaccount.php';
require_once 'omise-wp-admin.php';

add_action ( 'init', 'register_omise_wc_gateway_post_type' );
add_action ( 'plugins_loaded', 'register_omise_wc_gateway_plugin', 0 );
add_action ( 'plugins_loaded', 'prepare_omise_myaccount_panel', 0 );
add_action( 'plugins_loaded', array( Omise_Admin::get_instance(), 'register_admin_page_and_actions' ) );
?>
