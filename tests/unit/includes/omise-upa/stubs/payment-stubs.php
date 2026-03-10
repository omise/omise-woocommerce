<?php
/**
 * Stub classes for UPA tests that need lightweight payment class definitions
 * without loading the full payment gateway infrastructure.
 */

if ( ! class_exists( 'Omise_Payment' ) ) {
	#[AllowDynamicProperties]
	class Omise_Payment {
		public $order;
		public $method_title = '';
		public $source_type  = '';
		public $id           = '';

		public function load_order( $order_id ) { return false; }
		public function order() { return $this->order; }
		public function process_payment( $order_id ) { return null; }
		protected function invalid_order( $order_id ) { return array( 'result' => 'failure' ); }
		protected function payment_failed( $charge, $reason = '' ) { return array( 'result' => 'failure' ); }
	}
}

if ( ! class_exists( 'Omise_Payment_Offsite' ) ) {
	#[AllowDynamicProperties]
	class Omise_Payment_Offsite extends Omise_Payment {}
}

if ( ! class_exists( 'Omise_Payment_Offline' ) ) {
	#[AllowDynamicProperties]
	class Omise_Payment_Offline extends Omise_Payment {}
}
