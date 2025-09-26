<?php
if ( ! class_exists( 'Omise_WC_Order_Note' ) ) {
	class Omise_WC_Order_Note {

		protected static $allowedHtml = [
			'br' => [],
			'b' => [],
		];

		/**
		 * @param OmiseCharge $charge
		 */
		public static function get_charge_created_note( $charge ) {
			return self::sanitize(
				sprintf(
					__( 'Omise: Charge (ID: %s) has been created', 'omise' ),
					$charge['id']
				) . self::get_missing_3ds_fields( $charge )
			);
		}

		/**
		 * @param OmiseCharge|null $charge
		 * @param string           $reason
		 */
		public static function get_payment_failed_note( $charge, $reason = '' ) {
			$reason = $charge ? Omise_Charge::get_error_message( $charge ) . self::get_merchant_advice( $charge ) : $reason;
			$message = sprintf( __( 'Omise: Payment failed.<br/><b>Error Description:</b> %s', 'omise' ), $reason );

			return self::sanitize( $message );
		}

		private static function sanitize( $message ) {
			return wp_kses( $message, self::$allowedHtml );
		}

		private static function get_merchant_advice( $charge ) {
			if ( empty( $charge['merchant_advice'] ) ) {
				return '';
			}

			return '<br/><b>Advice:</b> ' . $charge['merchant_advice'];
		}

		private static function get_missing_3ds_fields( $charge ) {
			if ( empty( $charge['missing_3ds_fields'] ) || ! is_array( $charge['missing_3ds_fields'] ) ) {
				return '';
			}

			return '<br/><b>Missing 3DS Fields:</b> ' . join( ', ', $charge['missing_3ds_fields'] );
		}
	}
}
