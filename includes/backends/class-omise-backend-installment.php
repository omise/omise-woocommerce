<?php

/**
 * Note: The calculations in this class depend on the countries that
 *       the available installment payments are based on.
 *
 * @since 3.4
 *
 * @method public initiate
 * @method public get_available_providers
 * @method public get_available_plans
 * @method public calculate_monthly_payment_amount
 */
class Omise_Backend_Installment extends Omise_Backend
{
	/**
	 * @var array  of known installment providers.
	 */
	protected static $providers = array();

	public function initiate()
	{
		self::$providers = array(
			'installment_first_choice' => array(
				'bank_code'          => 'first_choice',
				'title'              => __('Krungsri First Choice', 'omise'),
				'interest_rate'      => 1.16,
				'min_allowed_amount' => 300.00,
			),

			'installment_bay' => array(
				'bank_code'          => 'bay',
				'title'              => __('Krungsri', 'omise'),
				'interest_rate'      => 0.74,
				'min_allowed_amount' => 500.00,
			),

			'installment_ktc' => array(
				'bank_code'          => 'ktc',
				'title'              => __('Krungthai Card (KTC)', 'omise'),
				'interest_rate'      => 0.74,
				'min_allowed_amount' => 300.00,
			),

			'installment_bbl' => array(
				'bank_code'          => 'bbl',
				'title'              => __('Bangkok Bank', 'omise'),
				'interest_rate'      => 0.74,
				'min_allowed_amount' => 500.00,
			),

			'installment_kbank' => array(
				'bank_code'          => 'kbank',
				'title'              => __('Kasikorn Bank', 'omise'),
				'interest_rate'      => 0.65,
				'min_allowed_amount' => 300.00,
			),

			'installment_scb' => array(
				'bank_code'          => 'scb',
				'title'              => __('Siam Commercial Bank', 'omise'),
				'interest_rate'      => 0.74,
				'min_allowed_amount' => 500.00,
			),

			'installment_ttb' => array(
				'bank_code'          => 'ttb',
				'title'              => __('TMBThanachart Bank', 'omise'),
				'interest_rate'      => 0.8,
				'min_allowed_amount' => 500.00,
			),

			'installment_uob' => array(
				'bank_code'          => 'uob',
				'title'              => __('United Overseas Bank', 'omise'),
				'interest_rate'      => 0.64,
				'min_allowed_amount' => 500.00,
			),

			'installment_mbb' => array(
				'bank_code'          => 'mbb',
				'title'              => __('Maybank', 'omise'),
				'interest_rate'      => 0,
				'min_allowed_amount' => 500.00,
				'zero_interest_installments' => true,
				'terms_min_allowed_amount' => [
					6 => 500.00,
					12 => 1000.00,
					18 => 1500.00,
					24 => 2000.00
				]
			),
		);
	}

	public function get_provider($id)
	{
		return self::$providers[$id];
	}

	/**
	 * @param  string $currency
	 * @param  float  $purchase_amount
	 *
	 * @return array  of an available installment providers
	 */
	public function get_available_providers($currency, $purchase_amount)
	{
		$capabilities = $this->capabilities();

		if (!$capabilities) {
			return null;
		}

		$supportedProviderList = [];

		// Note: As installment payment at the moment only supports THB and MYR currency, the 
		//       $purchase_amount is multiplied with 100 to convert the amount into subunit (satang and sen).
		$providers = $capabilities->getInstallmentBackends($currency, ($purchase_amount * 100));

		foreach ($providers as &$provider) {
			if (isset(self::$providers[$provider->_id])) {
				$provider_detail = self::$providers[$provider->_id];

				$provider->provider_code   = str_replace('installment_', '', $provider->_id);
				$provider->provider_name   = isset($provider_detail)
					? $provider_detail['title']
					: strtoupper($provider->code);
				$provider->interest_rate   = $capabilities->is_zero_interest()
					? 0 : ($provider_detail['interest_rate']);
				$provider->available_plans = $this->get_available_plans(
					$purchase_amount,
					$provider->allowed_installment_terms,
					$provider->interest_rate,
					$provider_detail
				);
				if (count($provider->available_plans) > 0) {
					$supportedProviderList[] = $provider;
				}
			}
		}

		usort($supportedProviderList, function ($a, $b) {
			return strcmp($a->provider_name, $b->provider_name);
		});

		return $supportedProviderList;
	}

	/**
	 * @param  float $purchase_amount
	 * @param  array $allowed_installment_terms
	 * @param  float $interest_rate
	 * @param  float $min_allowed_amount
	 *
	 * @return array  of an filtered available terms
	 */
	public function get_available_plans($purchase_amount, $allowed_installment_terms, $interest_rate, $provider_detail)
	{
		$plans = array();

		$min_allowed_amount = $provider_detail['min_allowed_amount'];
		sort($allowed_installment_terms);

		foreach ($allowed_installment_terms as $term_length) {
			$monthly_amount = $this->calculate_monthly_payment_amount($purchase_amount, $term_length, $interest_rate);

			if (isset($provider_detail['terms_min_allowed_amount'])) {
				$terms_min_allowed_amount = $provider_detail['terms_min_allowed_amount'];
				$min_allowed_amount = round($terms_min_allowed_amount[$term_length] / $term_length, 2);
			}

			if ($monthly_amount < $min_allowed_amount) {
				break;
			}

			$plans[] = array(
				'term_length'    => $term_length,
				'monthly_amount' => $monthly_amount
			);
		}

		return $plans;
	}

	/**
	 * @param  float $purchase_amount
	 * @param  int   $term_length      A length of a given installment term.
	 * @param  float $interest_rate    Its value can be '0' if merchant absorbs an interest.
	 *
	 * @return float  of a installment monthly payment (round up to 2 decimals).
	 */
	public function calculate_monthly_payment_amount($purchase_amount, $term_length, $interest_rate)
	{
		$interest = $purchase_amount * $interest_rate * $term_length / 100;
		return round(($purchase_amount + $interest) / $term_length, 2);
	}
}
