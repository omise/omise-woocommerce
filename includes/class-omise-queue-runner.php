<?php

defined( 'ABSPATH' ) || exit;

/**
 * @since 4.0
 */
class Omise_Queue_Runner {
	public static function execute_webhook_event_handler( $event_key, $data, $attempt ) {
		$events = array();
		foreach ( Omise_Events::$event_classes as $event ) {
			$events[ $event::EVENT_NAME ] = $event;
		}

		$event          = new $events[ $event_key ]( unserialize( $data ) );
		$event->attempt = $attempt;

		if ( $event->validate() ) {
			$event->resolve();
		}
	}
}
