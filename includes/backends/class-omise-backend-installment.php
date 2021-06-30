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
class Omise_Backend_Installment extends Omise_Backend {
	/**
	 * @var array  of known installment providers.
	 */
	protected static $providers = array();

	public function initiate() {
		self::$providers = array(
			'installment_first_choice' => array(
				'bank_code'          => 'first_choice',
				'title'              => __( 'Krungsri First Choice', 'omise' ),
				'interest_rate'      => 1.3,
				'min_allowed_amount' => 300.00,
			),

			'installment_bay' => array(
				'bank_code'          => 'bay',
				'title'              => __( 'Krungsri', 'omise' ),
				'interest_rate'      => 0.8,
				'min_allowed_amount' => 500.00,
			),

			'installment_ktc' => array(
				'bank_code'          => 'ktc',
				'title'              => __( 'Krungthai Card (KTC)', 'omise' ),
				'interest_rate'      => 0.8,
				'min_allowed_amount' => 300.00,
			),

			'installment_bbl' => array(
				'bank_code'          => 'bbl',
				'title'              => __( 'Bangkok Bank', 'omise' ),
				'interest_rate'      => 0.8,
				'min_allowed_amount' => 500.00,
			),

			'installment_kbank' => array(
				'bank_code'          => 'kbank',
				'title'              => __( 'Kasikorn Bank', 'omise' ),
				'interest_rate'      => 0.65,
				'min_allowed_amount' => 300.00,
			),

			'installment_scb' => array(
				'bank_code'          => 'scb',
				'title'              => __( 'Siam Commercial Bank', 'omise' ),
				'interest_rate'      => 0.74,
				'min_allowed_amount' => 500.00,
			),
		);
	}

	/**
	 * @param  string $currency
	 * @param  float  $purchase_amount
	 *
	 * @return array  of an available installment providers
	 */
	public function get_available_providers( $currency, $purchase_amount ) {
		// Note: as installment payment at the moment only supports for THB currency, so the 
		//       $purchase_amount is multiplied with 100 to convert the amount into subunit (satang).
		$providers = $this->capabilities()->getInstallmentBackends( $currency, ( $purchase_amount * 100 ) );

		foreach ( $providers as &$provider ) {
			$provider_detail = self::$providers[ $provider->_id ];

			$provider->provider_code   = str_replace( 'installment_', '', $provider->_id );
			$provider->provider_name   = isset( $provider_detail ) ? $provider_detail['title'] : strtoupper( $provider->code );
			$provider->interest_rate   = $this->capabilities()->is_zero_interest() ? 0 : ( $provider_detail['interest_rate'] );
			$provider->available_plans = $this->get_available_plans(
				$purchase_amount,
				$provider->allowed_installment_terms,
				$provider->interest_rate,
				$provider_detail['min_allowed_amount']
			);
		}

		return $providers;
	}

	/**
	 * @param  float $purchase_amount
	 * @param  array $allowed_installment_terms
	 * @param  float $interest_rate
	 * @param  float $min_allowed_amount
	 *
	 * @return array  of an filtered available terms
	 */
	public function get_available_plans( $purchase_amount, $allowed_installment_terms, $interest_rate, $min_allowed_amount ) {
		$plans = array();

		sort( $allowed_installment_terms );

		foreach ( $allowed_installment_terms as $term_length ) {
			$monthly_amount = $this->calculate_monthly_payment_amount( $purchase_amount, $term_length, $interest_rate );

			if ( $monthly_amount < $min_allowed_amount ) {
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
	public function calculate_monthly_payment_amount( $purchase_amount, $term_length, $interest_rate ) {
		$interest = $purchase_amount * $interest_rate * $term_length / 100;
		return round( ( $purchase_amount + $interest ) / $term_length, 2 );
	}
}
