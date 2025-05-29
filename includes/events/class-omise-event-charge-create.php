<?php

defined( 'ABSPATH' ) || exit;

/**
 * There are several cases when make a new charge with the following
 * payment methods that would trigger the 'charge.create' event.
 *
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 * Alipay
 * charge data in payload:
 *     [status: 'pending' (always)], [authorized: 'false' (always)]
 *
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 * Internet Banking
 * charge data in payload:
 *     [status: 'pending' (always)], [authorized: 'false' (always)]
 *
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 * Credit Card (none 3-D Secure)
 * CAPTURE = FALSE
 * charge data in payload could be one of these sets:
 *     [status: 'pending'], [authorized: 'true'], [paid: 'false']
 *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
 *
 * CAPTURE = TRUE
 * charge data in payload could be one of these sets:
 *     [status: 'successful'], [authorized: 'true'], [paid: 'true']
 *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
 *
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 * Credit Card (3-D Secure)
 * CAPTURE = FALSE
 * charge data in payload could be one of these sets:
 *     [status: 'pending'], [authorized: 'false'], [paid: 'false']
 *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
 *
 * CAPTURE = TRUE
 * charge data in payload could be one of these sets:
 *     [status: 'pending'], [authorized: 'false'], [paid: 'false']
 *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
 */
class Omise_Event_Charge_Create extends Omise_Event_Charge {
	/**
	 * @var string  of an event name.
	 */
	const EVENT_NAME = 'charge.create';

	// TODO: Confirm if this event is resolvable or not.
	// The original implementation is to add order note
	// https://github.com/omise/omise-woocommerce/commit/cdc264198cbc0e55fa998d867ca5c40b2bb6177e#diff-c1679266e5ca9820ab663ec1b0308c9634bcecb0a340405b6f1dce520fb33945R65
}
