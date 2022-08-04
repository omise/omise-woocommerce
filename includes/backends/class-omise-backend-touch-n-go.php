<?php
/**
 *
 * @method public initiate
 * @method public get_provider
 */
class Omise_Backend_TouchNGo extends Omise_Backend {
	/**
	 * @var array  of known providers.
	 */
	protected static $providers = array();
	
	public function initiate() {
		self::$providers = array();
	}
	
	/**
	 * @return string of touch n go provider
	 */
	public function get_provider() {
		if (!$this->capabilities){
			return null;
		}
		$tng = $this->capabilities()->getTouchNGoBackends();
		$first_value = reset($tng);
		
		// Preventing the following error:
		// Uncaught TypeError: property_exists(): Argument #1 must be of type object|string, bool given
		$typeofFirstValue = gettype($first_value);
		$isObjectOrString = 'object' === $typeofFirstValue || 'string' === $typeofFirstValue;
		
		$provider = null;
		if ($isObjectOrString && property_exists($first_value, 'provider')) {
			$provider = $first_value->provider;
		}
		return $provider;
	}
}
