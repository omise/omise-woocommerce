<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../class-omise-unit-test.php';

class Omise_Backend_Installment_Test extends TestCase {
	public static function setUpBeforeClass(): void {
		Omise_Unit_Test::include_class( 'backends/class-omise-backend.php' );
		Omise_Unit_Test::include_class( 'backends/class-omise-backend-installment.php' );
	}

	/**
	 * @test
	 */
	public function get_only_valid_plans_from_given_bay_allowed_installment_terms() {
		$installment_backend = new Omise_Backend_Installment();
		$purchase_amount     = 2000.00;
		$allowed_terms       = array( 3, 4, 6, 9, 10 );
		$interest_rate       = 0.8;
		$min_allowed_amount  = 500.00;

		$result = $installment_backend->get_available_plans( $purchase_amount, $allowed_terms, $interest_rate, $min_allowed_amount );

		$this->assertEquals( 2, count( $result ) );
		$this->assertEquals( array(
			array( 'term_length' => 3, 'monthly_amount' => 682.67 ),
			array( 'term_length' => 4, 'monthly_amount' => 516.00 ),
		), $result );
	}
	
	/**
	 * @test
	 */
	public function get_only_valid_plans_from_given_kbank_allowed_installment_terms() {
		$installment_backend = new Omise_Backend_Installment();
		$purchase_amount     = 2000.00;
		$allowed_terms       = array( 3, 4, 6, 10 );
		$interest_rate       = 0.65;
		$min_allowed_amount  = 300.00;

		$result = $installment_backend->get_available_plans( $purchase_amount, $allowed_terms, $interest_rate, $min_allowed_amount );

		$this->assertEquals( 3, count( $result ) );
		$this->assertEquals( array(
			array( 'term_length' => 3, 'monthly_amount' => 679.67 ),
			array( 'term_length' => 4, 'monthly_amount' => 513.00 ),
			array( 'term_length' => 6, 'monthly_amount' => 346.33 ),
		), $result );
	}

	/**
	 * @test
	 */
	public function get_only_valid_plans_from_given_scb_allowed_installment_terms() {
		$installment_backend = new Omise_Backend_Installment();
		$purchase_amount     = 2000.00;
		$allowed_terms       = array( 3, 4, 6, 8, 10 );
		$interest_rate       = 0.74;
		$min_allowed_amount  = 500.00;

		$result = $installment_backend->get_available_plans( $purchase_amount, $allowed_terms, $interest_rate, $min_allowed_amount );

		$this->assertEquals( 2, count( $result ) );
		$this->assertEquals( array(
			array( 'term_length' => 3, 'monthly_amount' => 681.47 ),
			array( 'term_length' => 4, 'monthly_amount' => 514.80 ),
		), $result );
	}

	/**
	 * @test
	 */
	public function get_only_valid_plans_from_given_bbl_allowed_installment_terms() {
		$installment_backend = new Omise_Backend_Installment();
		$purchase_amount     = 3000.00;
		$allowed_terms       = array( 3, 4, 6, 8, 10 );
		$interest_rate       = 0.8;
		$min_allowed_amount  = 500.00;

		$result = $installment_backend->get_available_plans( $purchase_amount, $allowed_terms, $interest_rate, $min_allowed_amount );

		$this->assertEquals( 3, count( $result ) );
		$this->assertEquals( array(
			array( 'term_length' => 3, 'monthly_amount' => 1024.00 ),
			array( 'term_length' => 4, 'monthly_amount' => 774.00 ),
			array( 'term_length' => 6, 'monthly_amount' => 524.00 ),
		), $result );
	}

	/**
	 * @test
	 */
	public function get_only_valid_plans_from_given_bbl_unsorted_allowed_installment_terms() {
		$installment_backend = new Omise_Backend_Installment();
		$purchase_amount     = 3000.00;
		$allowed_terms       = array( 3, 10, 4, 8, 6 );
		$interest_rate       = 0.8;
		$min_allowed_amount  = 500.00;

		$result = $installment_backend->get_available_plans( $purchase_amount, $allowed_terms, $interest_rate, $min_allowed_amount );

		$this->assertEquals( 3, count( $result ) );
		$this->assertEquals( array(
			array( 'term_length' => 3, 'monthly_amount' => 1024.00 ),
			array( 'term_length' => 4, 'monthly_amount' => 774.00 ),
			array( 'term_length' => 6, 'monthly_amount' => 524.00 ),
		), $result );
	}

	/**
	 * @test
	 */
	public function correctly_calculating_monthly_payment_amount_as_buyer_absorbs_case_1() {
		$installment_backend = new Omise_Backend_Installment();
		$purchase_amount     = 10000.00;
		$term                = 10;
		$interest_rate       = 0.8;

		$result = $installment_backend->calculate_monthly_payment_amount( $purchase_amount, $term, $interest_rate );

		$this->assertEquals( 1080.00, $result );
	}

	/**
	 * @test
	 */
	public function correctly_calculating_monthly_payment_amount_as_buyer_absorbs_case_2() {
		$installment_backend = new Omise_Backend_Installment();
		$purchase_amount     = 17900.00;
		$term                = 6;
		$interest_rate       = 0.69;

		$result = $installment_backend->calculate_monthly_payment_amount( $purchase_amount, $term, $interest_rate );

		$this->assertEquals( 3106.84, $result );
	}

	/**
	 * @test
	 */
	public function correctly_calculating_monthly_payment_amount_as_buyer_absorbs_case_3() {
		$installment_backend = new Omise_Backend_Installment();
		$purchase_amount     = 5000.00;
		$term                = 10;
		$interest_rate       = 0.8;

		$result = $installment_backend->calculate_monthly_payment_amount( $purchase_amount, $term, $interest_rate );

		$this->assertEquals( 540.00, $result );
	}

	/**
	 * @test
	 */
	public function correctly_calculating_monthly_payment_amount_as_buyer_absorbs_case_4() {
		$installment_backend = new Omise_Backend_Installment();
		$purchase_amount     = 3000.00;
		$term                = 4;
		$interest_rate       = 0.74;

		$result = $installment_backend->calculate_monthly_payment_amount( $purchase_amount, $term, $interest_rate );

		$this->assertEquals( 772.20, $result );
	}

	/**
	 * @test
	 */
	public function correctly_calculating_monthly_payment_amount_as_merchant_absorbs_case_1() {
		$installment_backend = new Omise_Backend_Installment();
		$purchase_amount     = 10000.00;
		$term                = 10;
		$interest_rate       = 0;

		$result = $installment_backend->calculate_monthly_payment_amount( $purchase_amount, $term, $interest_rate );

		$this->assertEquals( 1000.00, $result );
	}

	/**
	 * @test
	 */
	public function correctly_calculating_monthly_payment_amount_as_merchant_absorbs_case_2() {
		$installment_backend = new Omise_Backend_Installment();
		$purchase_amount     = 5000.00;
		$term                = 6;
		$interest_rate       = 0;

		$result = $installment_backend->calculate_monthly_payment_amount( $purchase_amount, $term, $interest_rate );

		$this->assertEquals( 833.33, $result );
	}
}

/**
 * Mock Omise_Capabilities class.
 * NOTE: This might not be an ideal way to mock a class,
 *       feel free to enhance the test or the core code.
 *
 * @see includes/class-omise-capabilities
 */
class Omise_Capabilities {
	/**
	 * @var self
	 */
	protected static $the_instance = null;

	public static function retrieve() {
		self::$the_instance = self::$the_instance ?: new self();
		return self::$the_instance;
	}

	public function is_zero_interest() {
		return false;
	}
}
