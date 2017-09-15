<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Events' ) ) {
	return;
}

class Omise_Events {
	/**
	 * @var array  of event classes that we can handle.
	 */
	protected $events = array();

	public function __construct() {
		$events = array(
			'Omise_Event_Charge_Capture',
			'Omise_Event_Charge_Complete',
			'Omise_Event_Charge_Create'
		);

		foreach ( $events as $event ) {
			$clazz = new $event;
			$this->events[ $clazz->event ] = $clazz;
		}
	}

	/**
	 * Note. It doesn't return anything back because nobody using the result
	 * unless we have a 'log' system.
	 *
	 * @param  string $event
	 * @param  mixed  $data
	 *
	 * @return void
	 */
	public function handle( $event, $data ) {
		if ( ! isset( $this->events[ $event ] ) ) {
			return;
		}

		/**
		 * Hook before Omise handle an event from webhook.
		 *
		 * @param mixed $data  a data of an event object
		 */
		do_action( 'omise_before_handle_event_' . $event, $data );

		$result = $this->events[ $event ]->handle( $data );

		/**
		 * Hook after Omise handle an event from webhook.
		 *
		 * @param mixed $data    a data of an event object
		 * @param mixed $result  a result of an event handler
		 */
		do_action( 'omise_after_handle_event_' . $event, $data, $result );

		return $result;
	}
}
