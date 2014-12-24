<?php
defined('ABSPATH') or die("No direct script access allowed.");

/**
 * Plugin Name: Omise Gateway Wordpress plugin
 * Plugin URI: http://docs.omise.co/omise-wp
 * Description: Allows easy integrating the Omise Payment gateway
 * Version: 1.0
 * Author: Omise Team
 * Author URI: https://www.omise.co
 * License: Copyright 2014. Omise Co.,Ltd.
 */

define("OMISE_PROTOCOL_PREFIX", "https://");
define("OMISE_VAULT_HOST", "vault.omise.co");
define("OMISE_API_HOST", "api.omise.co");

require_once 'omise-util.php';
require_once 'omise-api-wrapper.php';
require_once 'omise-wc-gateway.php';
require_once 'omise-wc-myaccount.php';

add_action ( 'plugins_loaded', 'register_omise_wc_gateway_plugin', 0 );
add_action ( 'plugins_loaded', 'prepare_omise_myaccount_panel', 0 );
?>