<?php
/**
 * Note: all of calculations in this class are based only on Thailand VAT and fee
 *       as currently Installment feature supported only for merchants
 *       that have registered with Omise Thailand account.
 *
 * @since 3.4
 *
 * @method public initiate
 * @method public get_available_providers
 * @method public filter_available_terms
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
				'interest_rate'      => 0.013,
				'min_allowed_amount' => 300.00,
			),

			'installment_bay' => array(
				'bank_code'          => 'bay',
				'title'              => __( 'Krungsri', 'omise' ),
				'interest_rate'      => 0.008,
				'min_allowed_amount' => 300.00,
			),

			'installment_ktc' => array(
				'bank_code'          => 'ktc',
				'title'              => __( 'Krungthai Card (KTC)', 'omise' ),
				'interest_rate'      => 0.008,
				'min_allowed_amount' => 300.00,
			),

			'installment_bbl' => array(
				'bank_code'          => 'bbl',
				'title'              => __( 'Bangkok Bank', 'omise' ),
				'interest_rate'      => 0.008,
				'min_allowed_amount' => 500.00,
			),

			'installment_kbank' => array(
				'bank_code'          => 'kbank',
				'title'              => __( 'Kasikorn Bank', 'omise' ),
				'interest_rate'      => 0.0065,
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
			$provider->provider_code             = str_replace( 'installment_', '', $provider->_id );
			$provider->provider_name             = isset( self::$providers[ $provider->_id ] ) ? self::$providers[ $provider->_id ]['title'] : strtoupper( $provider->code );
			$provider->allowed_installment_terms = $this->filter_available_terms( $provider->allowed_installment_terms, self::$providers[ $provider->_id ], $purchase_amount );
			$provider->interest                  = $this->capabilities()->is_zero_interest() ? 0 : ( self::$providers[ $provider->_id ]['interest_rate'] * 100 );
		}

		return $providers;
	}

	/**
	 * @param  array $available_terms
	 * @param  array $provider_detail
	 * @param  float $purchase_amount
	 *
	 * @return array  of an filtered available terms
	 */
	public function filter_available_terms( $available_terms, $provider_detail, $purchase_amount ) {
		$filtered_available_terms = array();

		sort( $available_terms );

		for ( $i = 0; $i < count( $available_terms ); $i++ ) {
			$term           = $available_terms[ $i ];
			$monthly_amount = $this->calculate_monthly_payment_amount(
				$purchase_amount,
				$term,
				$this->capabilities()->is_zero_interest() ? 0 : $provider_detail['interest_rate']
			);

			if ( $monthly_amount < $provider_detail['min_allowed_amount'] ) {
				break;
			}

			$filtered_available_terms[ $i ] = array(
				'term'           => $term,
				'monthly_amount' => $monthly_amount
			);
		}

		return $filtered_available_terms;
	}

	/**
	 * @param  float $purchase_amount
	 * @param  int   $term             installment term.
	 * @param  float $interest_rate    its value can be '0' if merchant absorbs an interest.
	 *
	 * @return float  of a installment monthly payment (round up to 2 decimals).
	 */
	public function calculate_monthly_payment_amount( $purchase_amount, $term, $interest_rate ) {
		$interest = $purchase_amount * $term * $interest_rate;
		return round( ( $purchase_amount + $interest ) / $term, 2 );
	}
}
