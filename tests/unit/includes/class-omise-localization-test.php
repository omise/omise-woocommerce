<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../class-omise-unit-test.php';

class Omise_Localization_Test extends TestCase {
	public static function setUpBeforeClass(): void {
		global $l10n;
		$l10n[] = 'omise';

		require_once __DIR__ . '/../../../includes/class-omise-localization.php';
	}

	/**
	 * @test
	 * Note that because the actualy translation is happening.
	 */
	public function translate_message() {
		$message = 'currency is currently not supported';
		$this->assertEquals( $message, Omise_Localization::translate( $message ) );
	}

	/**
	 * @test
	 */
	public function translate_multi_sentences_message() {
		$message = 'name cannot be blank, email is in invalid format, and phone_number must contain 10-11 digit characters';
		$this->assertEquals( $message, Omise_Localization::translate( $message ) );
	}

	/**
	 * @test
	 */
	public function translate_unknown_message() {
		$message = 'unrecognized message';
		$this->assertEquals( $message, Omise_Localization::translate( $message ) );
	}

	/**
	 * @test
	 */
	public function translate_payment_rejected_message() {
		$message = 'payment rejected';
		$translated_message = 'Payment rejected, please try again and make sure the card info is entered correctly. If the issue persists, please change to another card or contact your bank.';
		$this->assertEquals( $translated_message, Omise_Localization::translate( $message ) );
	}

	/**
	 * @test
	 */
	public function translate_failed_processing_message() {
		$message = 'failed processing';
		$translated_message = 'Payment encountered an unexpected error, please try again later.';
		$this->assertEquals( $translated_message, Omise_Localization::translate( $message ) );
	}
}
