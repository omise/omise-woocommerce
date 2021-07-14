<?php
/**
 *
 * @method public initiate
 * @method public get_available_banks
 */
class Omise_Backend_FPX extends Omise_Backend {
	/**
	 * @var array  of known installment providers.
	 */
	protected static $providers = array();

	public function initiate() {
		self::$providers = array();
	}

	/**
	 * @return array  of an available banks
	 */
	public function get_available_banks() {
		$providers = $this->capabilities()->getFPXBanks();
		$first_value = reset($providers);

		if (property_exists($first_value, 'banks')) {
			return $first_value->banks;
		}
	}
}
