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
		$settings = Omise_Setting::instance();
		$publicKey = !$pKey ? $settings->public_key() : $pKey;
		$secretKey = !$sKey ? $settings->secret_key() : $sKey;

		// Do not call capabilities API if keys are not present
		if(empty($publicKey) || empty($secretKey)) {
			return null;
		}

		if(self::$instance) {
			$keysNotChanged = self::$instance->publicKey === $publicKey && self::$instance->secretKey === $secretKey;

			// if keys are same then we return the previous instance without calling
			// capabilities API. This will prevent multiple calls that happens on each
			// page refresh.
			if($keysNotChanged) {
				return self::$instance;
			}
		}

		try {
			$capabilities = OmiseCapabilities::retrieve( $publicKey , $secretKey );
		} catch(\Exception $e) {
			// logging the error and suppressing error on the admin dashboard
			error_log(print_r($e, true));
			return null;
		}

		self::$instance = new self();
		self::$instance->capabilities = $capabilities;
		self::$instance->publicKey = $publicKey;
		self::$instance->secretKey = $secretKey;
		return self::$instance;
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
	public function getTouchNGoBackends() {
		$params   = array();
		$params[] = $this->capabilities->backendFilter['type']('touch_n_go');
	
		return $this->capabilities->getBackends( $params );
	}

	/**
	 * Retrieves details of fpx bank list from capabilities.
	 *
	 * @return string
	 */
	public function getFPXBanks() {
		$params   = array();
		$params[] = $this->capabilities->backendFilter['type']('fpx');
	
		return $this->capabilities->getBackends( $params );
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
	public function is_zero_interest() {
		return $this->capabilities['zero_interest_installments'];
	}

	/**
	 * @return array list of omise backends sourc_type.
	 */
	public function get_available_payment_methods() {
		$backends = $this->getBackends();
		$token_methods = $this->getTokenizationMethods();
		return array_merge(array_column($backends, '_id'),$token_methods);
	}
}
