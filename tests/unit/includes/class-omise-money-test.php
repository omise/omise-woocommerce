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

		$this->assertEquals( 'THB', $money->getCurrency() );
		$this->assertEquals( 849, $money->getAmount() );
		$this->assertEquals( 84900, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function convert_amount_with_decimal() {
		$amount   = 350.49;
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->getCurrency() );
		$this->assertEquals( 350.49, $money->getAmount() );
		$this->assertEquals( 35049, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function convert_amount_with_4_decimal_points() {
		$amount   = 4780.0409;
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->getCurrency() );
		$this->assertEquals( 4780.0409, $money->getAmount() );
		$this->assertEquals( 478004, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function convert_amount_with_123456789_as_decimals() {
		$amount   = 688.123456789;
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->getCurrency() );
		$this->assertEquals( 688.123456789, $money->getAmount() );
		$this->assertEquals( 68812, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function convert_amount_with_987654321_as_decimals() {
		$amount   = 14900.987654321;
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->getCurrency() );
		$this->assertEquals( 14900.987654321, $money->getAmount() );
		$this->assertEquals( 1490098, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function convert_string_amount() {
		$amount   = '5400';
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->getCurrency() );
		$this->assertEquals( 5400, $money->getAmount() );
		$this->assertEquals( 540000, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function convert_string_amount_with_decimal() {
		$amount   = '350.49';
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->getCurrency() );
		$this->assertEquals( 350.49, $money->getAmount() );
		$this->assertEquals( 35049, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function convert_string_amount_with_crazy_decimal() {
		$amount   = '฿46,000.4951 THB';
		$currency = 'thb';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->getCurrency() );
		$this->assertEquals( 46000.4951, $money->getAmount() );
		$this->assertEquals( 4600049, $money->toSubunit() );
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

		$this->assertEquals( 'AUD', $money->getCurrency() );
		$this->assertEquals( 99949, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function CAD_to_subunit() {
		$amount   = '2,749';
		$currency = 'cad';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'CAD', $money->getCurrency() );
		$this->assertEquals( 274900, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function CHF_to_subunit() {
		$amount   = 300.99;
		$currency = 'chf';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'CHF', $money->getCurrency() );
		$this->assertEquals( 30099, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function CNY_to_subunit() {
		$amount   = 9999.50;
		$currency = 'CNY';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'CNY', $money->getCurrency() );
		$this->assertEquals( 999950, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function DKK_to_subunit() {
		$amount   = 20;
		$currency = 'DKK';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'DKK', $money->getCurrency() );
		$this->assertEquals( 2000, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function EUR_to_subunit() {
		$amount   = 9;
		$currency = 'EUR';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'EUR', $money->getCurrency() );
		$this->assertEquals( 900, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function GBP_to_subunit() {
		$amount   = 12.95450;
		$currency = 'gbp';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'GBP', $money->getCurrency() );
		$this->assertEquals( 1295, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function HKD_to_subunit() {
		$amount   = 11.99;
		$currency = 'hkd';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'HKD', $money->getCurrency() );
		$this->assertEquals( 1199, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function JPY_to_subunit() {
		$amount   = '34,980 円';
		$currency = 'jpy';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'JPY', $money->getCurrency() );
		$this->assertEquals( 34980, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function MYR_to_subunit() {
		$amount   = 90.99;
		$currency = 'MYR';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'MYR', $money->getCurrency() );
		$this->assertEquals( 9099, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function SGD_to_subunit() {
		$amount   = 'S$10';
		$currency = 'SGD';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'SGD', $money->getCurrency() );
		$this->assertEquals( 1000, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function THB_to_subunit() {
		$amount   = 20;
		$currency = 'THB';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'THB', $money->getCurrency() );
		$this->assertEquals( 2000, $money->toSubunit() );
	}

	/**
	 * @test
	 */
	public function USD_to_subunit() {
		$amount   = 99.09;
		$currency = 'USD';

		$money = new Omise_Money( $amount, $currency );

		$this->assertEquals( 'USD', $money->getCurrency() );
		$this->assertEquals( 9909, $money->toSubunit() );
	}
}
