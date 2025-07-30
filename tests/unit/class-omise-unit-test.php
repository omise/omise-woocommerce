<?php

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

/**
 * Mock WordPress _e() function.
 *
 * @see wp-includes/l10n.php
 * @see https://developer.wordpress.org/reference/functions/_e
 */
function _e( $text, $context, $domain = 'default' ) {
	echo $text;
}

function load_fixture($name) {
	return file_get_contents(__DIR__ . "/../fixtures/{$name}.json");
}
