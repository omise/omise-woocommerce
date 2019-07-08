<?php
defined( 'ABSPATH' ) || exit;

/**
 * @since 3.5
 */
class Omise_Money {
	/**
	 * @var float
	 */
	protected $amount;

	/**
	 * @var string
	 */
	protected $currency;

	/**
	 * @var array
	 */
	private $subunit_multiplier = array(
		'AUD' => 100,
		'CAD' => 100,
		'CHF' => 100,
		'CNY' => 100,
		'DKK' => 100,
		'EUR' => 100,
		'GBP' => 100,
		'HKD' => 100,
		'JPY' => 1,
		'MYR' => 100,
		'SGD' => 100,
		'THB' => 100,
		'USD' => 100
	);

	/**
	 * @param int|float|string $amount
	 * @param string           $currency
	 */
	public function __construct( $amount, $currency ) {
		if ( ! isset( $this->subunit_multiplier[ strtoupper( $currency ) ] ) ) {
			throw new Exception( __( 'We do not support the currency you are using.', 'omise' ) );
		}

		$this->amount   = (float) $this->purifyAmount( $amount );
		$this->currency = strtoupper( $currency );
	}

	/**
	 * @param  int|float|string $amount
	 *
	 * @return int|float|string
	 */
	public function purifyAmount( $amount ) {
		if ( ! is_string( $amount ) && ! is_float( $amount ) && ! is_numeric( $amount ) ) {
			throw new Exception( __( 'An amount has to be integer, float, or string.', 'omise' ) );
		} 

		if ( is_string( $amount ) ) {
			$amount = preg_replace("/[^0-9.]/", '', $amount);
		}

		return $amount;
	}

	/**
	 * @return int
	 */
	public function toSubunit() {
		return (int) ( ( floor( $this->amount * 100 ) / 100 ) * $this->subunit_multiplier[ $this->currency ] );
	}

	/**
	 * @return int|float|string  Depending on what type of value that is passed through the construction.
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * @return string
	 */
	public function getCurrency() {
		return $this->currency;
	}
}
