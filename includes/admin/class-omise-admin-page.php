<?php

defined( 'ABSPATH' ) || exit;

/**
 * @since 4.0
 */
class Omise_Admin_Page extends Omise_Setting {
	/**
	 * @var array  of system messages.
	 */
	protected $messages = array();

	/**
	 * @var array  of error messages.
	 */
	protected $errors = array();

	/**
	 * @param string $type     Whether 'error' or 'message'
	 * @param string $message  A message to display
	 */
	public function add_message( $type, $message ) {
		switch ( $type ) {
			case 'error':
				$this->errors[] = $message;
				break;

			case 'message':
				$this->messages[] = $message;
				break;
		}
	}

	/**
	 * @return HTML
	 */
	public function display_messages() {
		if ( count( $this->errors ) > 0 ) {
			foreach ( $this->errors as $error ) {
				echo '<div class="error"><p>' . esc_html( $error ) . '</p></div>';
			}
		}

		if ( count( $this->messages ) > 0 ) {
			foreach ( $this->messages as $message ) {
				echo '<div class="updated"><p>' . esc_html( $message ) . '</p></div>';
			}
		}
	}
}
