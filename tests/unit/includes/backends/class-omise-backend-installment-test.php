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
	public function filtering_bbl_installment_term() {
		$installment_backend = new Omise_Backend_Installment();
		$purchase_amount     = 3000.00;
		$available_terms     = array( 3, 4, 6, 8, 10 );
		$provider_detail     = array(
			'bank_code'          => 'bbl',
			'title'              => __( 'Bangkok Bank', 'omise' ),
			'interest_rate'      => 0.008,
			'min_allowed_amount' => 500.00,
		);

		$result = $installment_backend->filter_available_terms( $available_terms, $provider_detail, $purchase_amount );

		$this->assertEquals( 3, count( $result ) );
		$this->assertEquals( array(
			array( 'term' => 3, 'monthly_amount' => 1024.00 ),
			array( 'term' => 4, 'monthly_amount' => 774.00 ),
			array( 'term' => 6, 'monthly_amount' => 524.00 ),
		), $result );
	}

	/**
	 * @test
	 */
	public function making_sure_that_we_can_filter_terms_properly_even_available_terms_is_not_in_a_proper_order() {
		$installment_backend = new Omise_Backend_Installment();
		$purchase_amount     = 3000.00;
		$available_terms     = array( 3, 10, 4, 8, 6 );
		$provider_detail     = array(
			'bank_code'          => 'bbl',
			'title'              => __( 'Bangkok Bank', 'omise' ),
			'interest_rate'      => 0.008,
			'min_allowed_amount' => 500.00,
		);

		$result = $installment_backend->filter_available_terms( $available_terms, $provider_detail, $purchase_amount );

		$this->assertEquals( 3, count( $result ) );
		$this->assertEquals( array(
			array( 'term' => 3, 'monthly_amount' => 1024.00 ),
			array( 'term' => 4, 'monthly_amount' => 774.00 ),
			array( 'term' => 6, 'monthly_amount' => 524.00 ),
		), $result );
	}

	/**
	 * @test
	 */
	public function correctly_calculating_monthly_payment_amount_as_buyer_absorbs_case_1() {
		$installment_backend = new Omise_Backend_Installment();
		$purchase_amount     = 10000.00;
		$term                = 10;
		$interest_rate       = 0.008;

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
		$interest_rate       = 0.0069;

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
		$interest_rate       = 0.008;

		$result = $installment_backend->calculate_monthly_payment_amount( $purchase_amount, $term, $interest_rate );

		$this->assertEquals( 540.00, $result );
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
		self::$the_instance = new self();
		return self::$the_instance;
	}

	public function is_zero_interest() {
		return false;
	}
}
