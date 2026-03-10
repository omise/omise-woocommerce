<?php
/**
 * Stub classes for UPA tests that need lightweight payment class definitions
 * without loading the full payment gateway infrastructure.
 */

if ( ! class_exists( 'Omise_Payment' ) ) {
	class Omise_Payment {}
}

if ( ! class_exists( 'Omise_Payment_Offsite' ) ) {
	class Omise_Payment_Offsite extends Omise_Payment {}
}

if ( ! class_exists( 'Omise_Payment_Offline' ) ) {
	class Omise_Payment_Offline extends Omise_Payment {}
}
