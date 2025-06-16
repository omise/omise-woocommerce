<?php

define( 'ABSPATH', value: '' );
define( 'OMISE_PUBLIC_KEY', 'pkey_test_12345');
define( 'OMISE_SECRET_KEY', 'skey_test_12345');

class Omise_Unit_Test {
	public static function include_class( $path ): void {
		require_once __DIR__ . '/../../includes/' . $path;
	}
}

/**
 * Mock WordPress __() function.
 *
 * @see wp-includes/l10n.php
 * @see https://developer.wordpress.org/reference/functions/__
 */
function __( $text, $domain = 'default' ) {
	return $text;
}

/**
 * Mock WordPress _x() function.
 *
 * @see wp-includes/l10n.php
 * @see https://developer.wordpress.org/reference/functions/_x
 */
function _x( $text, $context, $domain = 'default' ) {
	return $text;
}

function load_fixture($name) {
	return file_get_contents(__DIR__ . "/../fixtures/{$name}.json");
}
