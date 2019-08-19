<?php
defined( 'ABSPATH' ) || exit;

/**
 * @since 3.6
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
	 * @return int|float  Note that the expected output value's type of this method is to be `int` as Omise Charge API requires.
	 *                    However, there is a case that this method will return a `float` regarding to
	 *                    the improper WooCommerce currency setting, which considered as an invalid type of amount.
	 *
	 *                    And we would like to let the API raises an error out loud instead of silently remove
	 *                    or casting a `float` value to `int` subunit.
	 *                    This is to prevent any miscalculation for those fractional subunits
	 *                    between the amount that is charged, and the actual amount from the store.
	 */
	public static function to_subunit( $amount, $currency ) {
		$amount   = self::purify_amount( $amount );
		$currency = strtoupper( $currency );

		if ( ! isset( self::$subunit_multiplier[ $currency ] ) ) {
			throw new Exception( __( 'We do not support the currency you are using.', 'omise' ) );
		}

		return $amount * self::$subunit_multiplier[ $currency ];
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
