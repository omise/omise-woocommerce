<?php
/**
 * @since 3.4
 */
class Omise_Backend {
	public function __construct() {
		$this->initiate();
	}

	/**
	 * Class initiation.
	 *
	 * @return void
	 */
	public function initiate() {
		return;
	}

	/**
	 * @return Omise_Capabilities  Instant.
	 */
	public function capabilities() {
		return Omise_Capabilities::retrieve();
	}
}
