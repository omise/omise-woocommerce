<?php
defined( 'ABSPATH' ) || exit;

/**
 * @since 3.4
 */
class Omise_Capabilities {
	/**
	 * @var self
	 */
	protected static $the_instance = null;

	/**
	 * @var \OmiseCapabilities
	 */
	protected $capabilities;

	/**
	 * @return self  The instance of Omise_Capabilities
	 */
	public static function retrieve( $publickey = null, $secretkey = null ) {
		if ( ! self::$the_instance ) {
			try {
				$capabilities = OmiseCapabilities::retrieve( $publickey , $secretkey );
			} catch(\Exception $e) {
				// suppressing error on the admin dashboard
				return null;
			}
	
			self::$the_instance = new self();
			self::$the_instance->capabilities = $capabilities;
		}
	
		return self::$the_instance;
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
		$backends = json_decode(json_encode($backends), true);
		$token_methods = $this->getTokenizationMethods();
		return array_merge(array_column($backends, '_id'),$token_methods);
	}
}
