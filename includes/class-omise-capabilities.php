<?php
defined( 'ABSPATH' ) || exit;

/**
 * @since 3.4
 */
class Omise_Capabilities {
	/**
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * @var \OmiseCapabilities
	 */
	protected $capabilities;

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
	 * @return self  The instance of Omise_Capabilities
	 */
	public static function retrieve($pKey = null, $sKey = null)
	{
		if ( !self::shouldCallApi() ) {
			return null;
		}

		$keys = self::getKeys($pKey, $sKey);

		// Do not call capabilities API if keys are not present
		if(empty($keys['public']) || empty($keys['secret'])) {
			return null;
		}

		if(self::$instance) {
			$keysNotChanged = self::$instance->publicKey === $keys['public'] && self::$instance->secretKey === $keys['secret'];

			// if keys are same then we return the previous instance without calling
			// capabilities API. This will prevent multiple calls that happens on each
			// page refresh.
			if($keysNotChanged) {
				return self::$instance;
			}
		}

		try {
			$capabilities = OmiseCapabilities::retrieve( $keys['public'] , $keys['secret'] );
		} catch(\Exception $e) {
			// logging the error and suppressing error on the admin dashboard
			error_log($e->getMessage());
			return null;
		}

		self::$instance = new self();
		self::$instance->capabilities = $capabilities;
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
		// and we do not need to call capabilities on thank you page.
		// If endpoint url is `order-received`, it mean thank you page.
		$isPaymentPage = is_checkout() && !is_wc_endpoint_url( 'order-received' );

		return $isPaymentPage || $isOmiseSettingPage;
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
	 * We have many classes that calls capabilities API before the user entered keys are saved.
	 * This means they will use the old saved keys instead of new user entered keys. This will
	 * cause issues like:
	 *  - 401 unauthorized access
	 *  - Expired keys
	 *  - Others
	 *
	 * To avoid such issue we first get the user entered keys from $_POST so that other classes calls the
	 * capabilities API from the user entered keys.
	 */
	private static function getUserEnteredKeys()
	{
		if (
			! isset( $_POST['omise_setting_page_nonce'] ) ||
			! wp_verify_nonce( $_POST['omise_setting_page_nonce'], 'omise-setting' )
		) {
			wp_die( __( 'You are not allowed to modify the settings from a suspicious source.', 'omise' ) );
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
	 * Retrieves details of installment payment backends from capabilities.
	 *
	 * @return string
	 */
	public function getInstallmentBackends( $currency = '', $amount = null ) {

		$params   = array();
		$params[] = $this->capabilities->backendFilter['type']('installment');

		if ( $currency ) {
			$params[] = $this->capabilities->backendFilter['currency']( $currency );
		}
		if ( ! is_null( $amount ) ) {
			$params[] = $this->capabilities->backendFilter['chargeAmount']( $amount );
		}

		return $this->capabilities->getBackends( $params );
	}

	/**
	 * Retrieves details of payment backends from capabilities.
	 *
	 * @return string
	 */
	public function getBackends( $currency = '' ) {
		$params = array();
		if ( $currency ) {
			$params[] = $this->capabilities->backendFilter['currency']( $currency );
		}

		return $this->capabilities->getBackends( $params );
	}

	/**
	 * Retrieves details of Touch n Go payment backends from capabilities.
	 *
	 * @return string
	 */
	public function getTouchNGoBackends()
	{
		return $this->getBackendByType('touch_n_go');
	}

	/**
	 * Retrieves backend by type
	 */
	public function getBackendByType($sourceType)
	{
		$params = [];
		$params[] = $this->capabilities->backendFilter['type']($sourceType);
		return $this->capabilities->getBackends($params);
	}

	/**
	 * Retrieves details of fpx bank list from capabilities.
	 */
	public function getFPXBanks()
	{
		return $this->getBackendByType('fpx');
	}

	/**
     * Retrieves list of tokenization methods
     *
     * @return array
     */
    public function getTokenizationMethods()
    {
        return $this->capabilities ? $this->capabilities['tokenization_methods'] : null;
    }

	/**
	 * @return bool  True if merchant absorbs the interest or else, false.
	 */
	public function is_zero_interest()
	{
		return $this->capabilities['zero_interest_installments'];
	}

	/**
	 * @return array list of omise backends sourc_type.
	 */
	public function get_available_payment_methods()
	{
		$backends = $this->getBackends();
		$backends = json_decode(json_encode($backends), true);
		$token_methods = $this->getTokenizationMethods();
		return array_merge(array_column($backends, '_id'),$token_methods);
	}

	/**
	 * Retrieves details of Shopee Pay from capabilities.
	 *
	 * @param string $sourceType
	 */
	public function getShopeeBackend($sourceType)
	{
		$shopeePaySourceTypes = [Omise_Payment_ShopeePay::ID, Omise_Payment_ShopeePay::JUMPAPP_ID];

		if (!in_array($sourceType, $shopeePaySourceTypes)) {
			return null;
		}

		return $this->getBackendByType($sourceType);
	}

	public function getInstallmentMinLimit()
	{
		return $this->capabilities['limits']['installment_amount']['min'];
	}

	/**
	 * Retrieves details of TrueMoney from capabilities.
	 *
	 * @param string $source_type
	 */
	public function get_truemoney_backend($source_type)
	{
		$truemoney_source_types = [Omise_Payment_Truemoney::WALLET, Omise_Payment_Truemoney::JUMPAPP];

		if (!in_array($source_type, $truemoney_source_types)) {
			return null;
		}

		return $this->getBackendByType($source_type);
	}
}
