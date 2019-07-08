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

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->get_currency() );
		$this->assertEquals( 849, $money->get_amount() );
		$this->assertEquals( 84900, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function convert_amount_with_decimal() {
		$amount   = 350.49;
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->get_currency() );
		$this->assertEquals( 350.49, $money->get_amount() );
		$this->assertEquals( 35049, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function convert_amount_with_4_decimal_points() {
		$amount   = 4780.0409;
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->get_currency() );
		$this->assertEquals( 4780.0409, $money->get_amount() );
		$this->assertEquals( 478004, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function convert_amount_with_123456789_as_decimals() {
		$amount   = 688.123456789;
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->get_currency() );
		$this->assertEquals( 688.123456789, $money->get_amount() );
		$this->assertEquals( 68812, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function convert_amount_with_987654321_as_decimals() {
		$amount   = 14900.987654321;
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->get_currency() );
		$this->assertEquals( 14900.987654321, $money->get_amount() );
		$this->assertEquals( 1490098, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function convert_string_amount() {
		$amount   = '5400';
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->get_currency() );
		$this->assertEquals( 5400, $money->get_amount() );
		$this->assertEquals( 540000, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function convert_string_amount_with_decimal() {
		$amount   = '350.49';
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->get_currency() );
		$this->assertEquals( 350.49, $money->get_amount() );
		$this->assertEquals( 35049, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function convert_string_amount_with_crazy_decimal() {
		$amount   = '฿46,000.4951 THB';
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->get_currency() );
		$this->assertEquals( 46000.4951, $money->get_amount() );
		$this->assertEquals( 4600049, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function preventing_a_troll_case() {
		$this->expectException( 'Exception' );
		$this->expectExceptionMessage( 'An amount has to be integer, float, or string.' );

		$amount   = [ 'yahhhhh' ];
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );
	}

	/**
	 * @test
	 */
	public function preventing_unsupport_currency() {
		$this->expectException( 'Exception' );
		$this->expectExceptionMessage( 'We do not support the currency you are using.' );

		$amount   = 890.52;
		$currency = 'omg';

		$money = new Omise_Money( $amount, $currency );
	}

	/**
	 * @test
	 */
	public function AUD_to_subunit() {
		$amount   = 999.49;
		$currency = 'aud';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'AUD', $money->get_currency() );
		$this->assertEquals( 99949, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function CAD_to_subunit() {
		$amount   = '2,749';
		$currency = 'cad';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'CAD', $money->get_currency() );
		$this->assertEquals( 274900, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function CHF_to_subunit() {
		$amount   = 300.99;
		$currency = 'chf';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'CHF', $money->get_currency() );
		$this->assertEquals( 30099, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function CNY_to_subunit() {
		$amount   = 9999.50;
		$currency = 'CNY';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'CNY', $money->get_currency() );
		$this->assertEquals( 999950, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function DKK_to_subunit() {
		$amount   = 20;
		$currency = 'DKK';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'DKK', $money->get_currency() );
		$this->assertEquals( 2000, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function EUR_to_subunit() {
		$amount   = 9;
		$currency = 'EUR';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'EUR', $money->get_currency() );
		$this->assertEquals( 900, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function GBP_to_subunit() {
		$amount   = 12.95450;
		$currency = 'gbp';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'GBP', $money->get_currency() );
		$this->assertEquals( 1295, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function HKD_to_subunit() {
		$amount   = 11.99;
		$currency = 'hkd';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'HKD', $money->get_currency() );
		$this->assertEquals( 1199, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function JPY_to_subunit() {
		$amount   = '34,980 円';
		$currency = 'jpy';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'JPY', $money->get_currency() );
		$this->assertEquals( 34980, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function MYR_to_subunit() {
		$amount   = 90.99;
		$currency = 'MYR';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'MYR', $money->get_currency() );
		$this->assertEquals( 9099, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function SGD_to_subunit() {
		$amount   = 'S$10';
		$currency = 'SGD';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'SGD', $money->get_currency() );
		$this->assertEquals( 1000, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function THB_to_subunit() {
		$amount   = 20;
		$currency = 'THB';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->get_currency() );
		$this->assertEquals( 2000, $money->to_subunit() );
	}

	/**
	 * @test
	 */
	public function USD_to_subunit() {
		$amount   = 99.09;
		$currency = 'USD';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'USD', $money->get_currency() );
		$this->assertEquals( 9909, $money->to_subunit() );
	}
}
