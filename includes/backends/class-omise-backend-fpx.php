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
		$capabilities = $this->capabilities();

		if ( !$capabilities ){
			return null;
		}

		$providers = $capabilities->getFPXBanks();
		$first_value = reset($providers);

		// Preventing the following error:
		// Uncaught TypeError: property_exists(): Argument #1 must be of type object|string, bool given
		$typeofFirstValue = gettype($first_value);
		$isObjectOrString = 'object' === $typeofFirstValue || 'string' === $typeofFirstValue;

		if ($isObjectOrString && property_exists($first_value, 'banks')) {
			return $first_value->banks;
		}
	}
}
