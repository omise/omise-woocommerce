<?php

define( 'ABSPATH', '' );
define( 'WC_VERSION', '1.0.0' );
define( 'OMISE_PUBLIC_KEY', 'pkey_test_12345' );
define( 'OMISE_SECRET_KEY', 'skey_test_12345' );

/**
 * Mock abstract WooCommerce's gateway
 */
abstract class WC_Payment_Gateway {

	public static $is_available = true;

	public function is_available() {
		return self::$is_available;
	}
}

/**
 * Temporary mock for WP_* class
 * In the future, we should move to use WP_UnitTestCase
 */
class WP_Error {

	public function __construct(
		public $code = '',
		public $message = '',
		public $data = ''
	) {
	}
}
class WP_REST_Server_Stub {

	const EDITABLE = 'POST';
	const READABLE = 'GET';
}

const PLUGIN_PATH = __DIR__ . '/../..';

// Omise WooCommerce
// FIXME: Start including payment gateway here for better test organization.
// In the future, we can move to PSR-4 autoloading.
require_once PLUGIN_PATH . '/includes/libraries/omise-plugin/helpers/charge.php';
require_once PLUGIN_PATH . '/includes/libraries/omise-plugin/helpers/wc_order.php';
require_once PLUGIN_PATH . '/includes/libraries/omise-plugin/helpers/mailer.php';
require_once PLUGIN_PATH . '/includes/libraries/omise-plugin/helpers/request.php';
require_once PLUGIN_PATH . '/includes/libraries/omise-plugin/helpers/token.php';
require_once PLUGIN_PATH . '/includes/class-omise-ajax-actions.php';
require_once PLUGIN_PATH . '/includes/class-omise-callback.php';
require_once PLUGIN_PATH . '/includes/class-omise-events.php';
require_once PLUGIN_PATH . '/includes/class-omise-localization.php';
require_once PLUGIN_PATH . '/includes/class-omise-money.php';
require_once PLUGIN_PATH . '/includes/class-omise-payment-factory.php';
require_once PLUGIN_PATH . '/includes/class-omise-rest-webhooks-controller.php';
require_once PLUGIN_PATH . '/includes/class-omise-wc-myaccount.php';
require_once PLUGIN_PATH . '/omise-util.php';
// Exclude classes that might conflict with test `alias` mocks.
// To avoid this, we might need to refactor actual classes or tests to remove existing alias mocks.
// require_once PLUGIN_PATH . '/includes/class-omise-setting.php';
// require_once PLUGIN_PATH . '/includes/class-omise-capability.php';
// require_once PLUGIN_PATH . '/includes/libraries/omise-plugin/helpers/WcOrderNote.php';
// require_once PLUGIN_PATH . '/includes/libraries/omise-plugin/helpers/RedirectUrl.php';
// require_once PLUGIN_PATH . '/includes/libraries/omise-plugin/helpers/file_get_contents_wrapper.php';

// Omise PHP
require_once PLUGIN_PATH . '/includes/libraries/omise-php/lib/omise/res/obj/OmiseObject.php';
require_once PLUGIN_PATH . '/includes/libraries/omise-php/lib/omise/res/OmiseApiResource.php';

// Test helpers
require_once __DIR__ . '/class-omise-test-case.php';
require_once __DIR__ . '/class-omise-unit-test.php';
require_once __DIR__ . '/class-omise-util-test.php';
require_once __DIR__ . '/includes/gateway/class-omise-offsite-test.php';
