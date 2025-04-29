<?php
defined( 'ABSPATH' ) || exit;

/**
 * @since 3.4
 */
class Omise_Capability {
	/**
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * @var \OmiseCapability
	 */
	protected $capability;

	/**
	 * Stores previously used public key to compare with the newly fetched public key
	 * @var string
	 */
	protected $publicKey = null;

	/**
	 * Stores previously used secret key to compare with the newly fetched secret key
	 * @var string
	 */
	protected $secretKey = null;

	/**
	 * @param string|null $pKey
	 * @param string|null $sKey
	 *
	 * @return self  The instance of Omise_Capability
	 */
	public static function retrieve($pKey = null, $sKey = null)
	{
		if ( !self::shouldCallApi() ) {
			return null;
		}

		$keys = self::getKeys($pKey, $sKey);

		// Do not call capability API if keys are not present
		if(empty($keys['public']) || empty($keys['secret'])) {
			return null;
		}

		if(self::$instance) {
			$keysNotChanged = self::$instance->publicKey === $keys['public'] && self::$instance->secretKey === $keys['secret'];

			// if keys are same then we return the previous instance without calling
			// capability API. This will prevent multiple calls that happens on each
			// page refresh.
			if($keysNotChanged) {
				return self::$instance;
			}
		}

		try {
			$capability = OmiseCapability::retrieve( $keys['public'] , $keys['secret'] );
		} catch(\Exception $e) {
			// logging the error and suppressing error on the admin dashboard
			error_log($e->getMessage());
			return null;
		}

		self::$instance = new self();
		self::$instance->capability = $capability;
		self::$instance->publicKey = $keys['public'];
		self::$instance->secretKey = $keys['secret'];
		return self::$instance;
	}

	/**
	 * @return boolean
	 */
	public static function shouldCallApi() {
		$omiseSettingPages = [ 'omise' ];
		$currentAdminPage = isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : '';
		// If page is omise setting page from admin panel.
		$isOmiseSettingPage = is_admin() && in_array( $currentAdminPage, $omiseSettingPages );

		// If page is checkout page but not thank you page.
		// By default thank you page is also part of checkout pages
		// and we do not need to call capability on thank you page.
		// If endpoint url is `order-received`, it mean thank you page.
		$isPaymentPage = is_checkout() && !is_wc_endpoint_url( 'order-received' );

		return $isPaymentPage || $isOmiseSettingPage || self::isFromCheckoutPage();
	}

	public static function isFromCheckoutPage()
	{
		global $wp;

		if (!$wp) {
			return false;
		}

		/**
		 * Check Ajax actions to handle the capability call on shortcode component.
		 */
		$ajaxActions = ['update_order_review', 'checkout'];
		if (wp_doing_ajax()) {
			$action = isset($_GET['wc-ajax']) ? $_GET['wc-ajax'] : '';
			return in_array($action, $ajaxActions);
		}

		$path = self::getRequestPath($wp);
		$endpoints = ['checkout', 'batch', 'cart', 'cart/select-shipping-rate'];

		foreach($endpoints as $endpoint) {
			if ($path !== '') {
				$len = strlen($path);
				if (strpos($path, $endpoint) === $len - strlen($endpoint)) {
					return true;
				}
			}

			if (isset($wp->query_vars['rest_route'])) {
				$route = $wp->query_vars['rest_route'];
				$len = strlen($route);
				if (strpos($route, $endpoint) === $len - strlen($endpoint)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get current request path
	 * The current path can be retrieved from `$wp->request`.
	 * In case if it returns nothing, extract the path from `$_SERVER['REQUEST_URI']` instead.
	 *
	 * @return string returns current path, otherwise empty string is returned if it can't be resolved.
	 */
	private static function getRequestPath($wp)
	{
		$path = trim($wp->request);

		if (empty($path)) {
			$serverRequestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

			if (is_string($serverRequestPath)) {
				$path = trim($serverRequestPath, '/');
			}
		}

		return $path;
	}

	/**
	 * @param string|null $pKey
	 * @param string|null $sKey
	*/
	private static function getKeys($pKey = null, $sKey = null)
	{
		// Check if user has submitted a form
		if ( ! empty( $_POST ) && isset($_POST['submit']) && $_POST['submit'] === 'Save Settings' ) {
			return self::getUserEnteredKeys();
		}

		$settings = Omise_Setting::instance();

		return [
			'public' => !$pKey ? $settings->public_key() : $pKey,
			'secret' => !$sKey ? $settings->secret_key() : $sKey
		];
	}

	/**
	 * We have many classes that calls capability API before the user entered keys are saved.
	 * This means they will use the old saved keys instead of new user entered keys. This will
	 * cause issues like:
	 *  - 401 unauthorized access
	 *  - Expired keys
	 *  - Others
	 *
	 * To avoid such issue we first get the user entered keys from $_POST so that other classes calls the
	 * capability API from the user entered keys.
	 */
	private static function getUserEnteredKeys()
	{
		if (
			! isset( $_POST['omise_setting_page_nonce'] ) ||
			! wp_verify_nonce( $_POST['omise_setting_page_nonce'], 'omise-setting' )
		) {
			return wp_die( __( 'You are not allowed to modify the settings from a suspicious source.', 'omise' ) );
		}

		return [
			'public' => isset( $_POST['sandbox'] ) ?
				sanitize_text_field($_POST['test_public_key']) :
				sanitize_text_field($_POST['live_public_key']),
			'secret' => isset( $_POST['sandbox'] ) ?
				sanitize_text_field($_POST['test_private_key']) :
				sanitize_text_field($_POST['live_private_key'])
		];
	}

	/**
	 * Retrieves details of installment payment methods from capability.
	 *
	 * @return array
	 */
	public function getInstallmentMethods( $currency = '', $amount = null ) {

		$params   = array();
		$params[] = $this->capability->filterPaymentMethod['name']('installment');

		if ( $currency ) {
			$params[] = $this->capability->filterPaymentMethod['currency']( $currency );
		}
		if ( ! is_null( $amount ) ) {
			$params[] = $this->capability->filterPaymentMethod['chargeAmount']( $amount );
		}

		return $this->capability->getPaymentMethods( $params );
	}

	/**
	 * Retrieves details of payment methods from capability.
	 *
	 * @return array
	 */
	public function getPaymentMethods( $currency = '' ) {
		$params = array();
		if ( $currency ) {
			$params[] = $this->capability->filterPaymentMethod['currency']( $currency );
		}

		return $this->capability->getPaymentMethods( $params );
	}

	/**
	 * Retrieves details of fpx bank list from capability.
	 */
	public function getFPXBanks()
	{
		return $this->getPaymentMethodByName('fpx');
	}

	/**
     * Retrieves list of tokenization methods
     *
     * @return array
     */
    public function getTokenizationMethods()
    {
        return $this->capability ? $this->capability['tokenization_methods'] : null;
    }

	/**
	 * @return bool  True if merchant absorbs the interest or else, false.
	 */
	public function is_zero_interest()
	{
		return $this->capability['zero_interest_installments'];
	}

	/**
	 * @return array list of omise payment methods source_type.
	 */
	public function get_available_payment_methods()
	{
		$methods = $this->getPaymentMethods();
		$token_methods = $this->getTokenizationMethods();
		return array_merge(array_column($methods, 'name'), $token_methods);
	}

	/**
	 * Retrieves details of Shopee Pay from capability.
	 *
	 * @param string $sourceType
	 */
	public function getShopeeMethod($sourceType)
	{
		$shopeePaySourceTypes = [Omise_Payment_ShopeePay::ID, Omise_Payment_ShopeePay::JUMPAPP_ID];

		if (!in_array($sourceType, $shopeePaySourceTypes)) {
			return null;
		}

		return $this->getPaymentMethodByName($sourceType);
	}

	public function getInstallmentMinLimit()
	{
		return $this->capability['limits']['installment_amount']['min'];
	}

	/**
	 * Retrieves details of TrueMoney from capability.
	 *
	 * @param string $sourceType
	 */
	public function get_truemoney_method($sourceType)
	{
		$truemoneySourceTypes = [Omise_Payment_Truemoney::WALLET, Omise_Payment_Truemoney::JUMPAPP];

		if (!in_array($sourceType, $truemoneySourceTypes)) {
			return null;
		}

		return $this->getPaymentMethodByName($sourceType);
	}

	/**
	 * Retrieves payment method by name
	 * @return object The first payment method that matched with the given name
	 */
	private function getPaymentMethodByName($sourceType)
	{
		$params = [];
		$params[] = $this->capability->filterPaymentMethod['exactName']($sourceType);
		$methods = $this->capability->getPaymentMethods($params);
		// Only variables should be passed
		// https://www.php.net/reset
		return reset($methods);
	}
}
