<?php
/**
 * Note: all calculations in this class are based only on Thailand VAT and fee
 *       as currently Installment feature supported only for merchants
 *       that have registered with Omise Thailand account.
 *
 * @since 3.4
 *
 * @method public initiate
 * @method public get_available_providers
 * @method public get_available_plans
 * @method public calculate_monthly_payment_amount
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
