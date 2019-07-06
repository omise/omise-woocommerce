<?php
define( 'ABSPATH', '' );

class Omise_Unit_Test {
	public static function include_class( $path ): void {
		require_once __DIR__ . '/../../includes/' . $path;
	}
}

/**
 * Mock WordPress __() function.
 *
 * @see wp-includes/l10n.php
 */
function __( $text, $domain = 'default' ) {
	return $text;
}
