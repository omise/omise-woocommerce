<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../class-omise-unit-test.php';

class Omise_Money_Test extends TestCase {
	public static function setUpBeforeClass(): void {
		require_once __DIR__ . '/../../../includes/class-omise-money.php';
	}

	/**
	 * @test
	 */
	public function convert_amount_with_ideal_inputs() {
		$amount   = 849;
		$currency = 'thb';

		$this->assertEquals( 84900, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function convert_amount_with_decimal() {
		$amount   = 350.49;
		$currency = 'thb';

		$this->assertEquals( 35049, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function convert_amount_with_4_decimal_points() {
		$amount   = 4780.0409;
		$currency = 'thb';

		$this->assertEquals( 478004.09, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function convert_amount_with_123456789_as_decimals() {
		$amount   = 688.123456789;
		$currency = 'thb';

		$this->assertEquals( 68812.3456789, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function convert_amount_with_987654321_as_decimals() {
		$amount   = 14900.987654321;
		$currency = 'thb';

		$this->assertEquals( 1490098.7654321, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function convert_string_as_numeric() {
		$amount   = '5400';
		$currency = 'thb';

		$money = Omise_Money::to_subunit( $amount, $currency );
		$this->assertEquals( 540000, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function preventing_a_troll_case() {
		$this->expectException( 'Exception' );
		$this->expectExceptionMessage( 'Invalid amount type given. Should be int, float, or numeric string.' );

		$amount   = [ 'yahhhhh' ];
		$currency = 'thb';

		$money = Omise_Money::to_subunit( $amount, $currency );
	}

	/**
	 * @test
	 */
	public function preventing_unsupport_currency() {
		$this->expectException( 'Exception' );
		$this->expectExceptionMessage( 'We do not support the currency you are using.' );

		$amount   = 890.52;
		$currency = 'omg';

		$money = Omise_Money::to_subunit( $amount, $currency );
	}

	/**
	 * @test
	 */
	public function AUD_to_subunit() {
		$amount   = 999.49;
		$currency = 'aud';

		$this->assertEquals( 99949, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function CAD_to_subunit() {
		$amount   = 2749.00;
		$currency = 'cad';

		$this->assertEquals( 274900, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function CHF_to_subunit() {
		$amount   = 300.99;
		$currency = 'chf';

		$this->assertEquals( 30099, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function CNY_to_subunit() {
		$amount   = 9999.50;
		$currency = 'CNY';

		$this->assertEquals( 999950, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function DKK_to_subunit() {
		$amount   = 20;
		$currency = 'DKK';

		$this->assertEquals( 2000, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function EUR_to_subunit() {
		$amount   = 9;
		$currency = 'EUR';

		$this->assertEquals( 900, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function GBP_to_subunit() {
		$amount   = 12.95;
		$currency = 'gbp';

		$this->assertEquals( 1295, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function HKD_to_subunit() {
		$amount   = 11.99;
		$currency = 'hkd';

		$this->assertEquals( 1199, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function JPY_to_subunit() {
		$amount   = 34980;
		$currency = 'jpy';

		$this->assertEquals( 34980, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function MYR_to_subunit() {
		$amount   = 90.99;
		$currency = 'MYR';

		$this->assertEquals( 9099, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function SGD_to_subunit() {
		$amount   = 10;
		$currency = 'SGD';

		$this->assertEquals( 1000, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function THB_to_subunit() {
		$amount   = 20;
		$currency = 'THB';

		$this->assertEquals( 2000, Omise_Money::to_subunit( $amount, $currency ) );
	}

	/**
	 * @test
	 */
	public function USD_to_subunit() {
		$amount   = 99.09;
		$currency = 'USD';

		$this->assertEquals( 9909, Omise_Money::to_subunit( $amount, $currency ) );
	}
}
