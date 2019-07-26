<?php
defined( 'ABSPATH' ) || exit;

/**
 * @since 3.5
 */
class Omise_Money {
	/**
	 * @var array
	 */
	private static $subunit_multiplier = array(
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
	 * @param  int|float|string $amount
	 * @param  string           $currency
	 *
	 * @return int
	 */
	public function to_subunit( $amount, $currency ) {
		$amount   = static::purify_amount( $amount );
		$currency = strtoupper( $currency );

		if ( ! isset( static::$subunit_multiplier[ $currency ] ) ) {
			throw new Exception( __( 'We do not support the currency you are using.', 'omise' ) );
		}

		return $amount * static::$subunit_multiplier[ $currency ];
	}

	/**
	 * @param  int|float $amount
	 *
	 * @return float
	 */
	private static function purify_amount( $amount ) {
		if ( ! is_numeric( $amount ) ) {
			throw new Exception( __( 'Invalid amount type given. Should be int, float, or numeric string.', 'omise' ) );
		}

		return (float) $amount;
	}
}
