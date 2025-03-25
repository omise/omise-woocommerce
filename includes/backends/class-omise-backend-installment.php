<?php

/**
 * Note: The calculations in this class depend on the countries that
 *       the available installment payments are based on.
 *
 * @since 3.4
 *
 * @method public get_available_providers
 */
class Omise_Backend_Installment extends Omise_Backend
{
	/**
	 * @param  string $currency
	 * @param  float  $purchase_amount
	 *
	 * @return array  of an available installment providers
	 */
	public function get_available_providers($currency, $purchase_amount)
	{
		$capability = $this->capability();

		if (!$capability) {
			return null;
		}

		// Note: As installment payment at the moment only supports THB and MYR currency, the
		//       $purchase_amount is multiplied with 100 to convert the amount into subunit (satang and sen).
		$installments = $capability->getInstallmentMethods($currency, ($purchase_amount * 100));
		return count($installments) > 0;
	}
}
