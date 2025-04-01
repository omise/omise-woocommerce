<?php
/**
 * @method public initiate
 * @method public get_available_providers
 */
class Omise_Backend_Mobile_Banking extends Omise_Backend {
	/**
	 * @var array  of known mobile banking providers.
	 */
	protected static $providers = array();

	public function initiate() {
		self::$providers = array(
			'mobile_banking_kbank' => array(
				'title'              => __( 'K PLUS', 'omise' ),
				'logo'				 => 'kplus',
			),
			'mobile_banking_scb' => array(
				'title'              => __( 'SCB EASY', 'omise' ),
				'logo'				 => 'scb',
			),
			'mobile_banking_bay' => array(
				'title'              => __( 'KMA', 'omise' ),
				'logo'				 => 'bay',
			),
			'mobile_banking_bbl' => array(
				'title'              => __( 'Bualuang mBanking', 'omise' ),
				'logo'				 => 'bbl',
			),
			'mobile_banking_ktb' => array(
				'title'              => __( 'Krungthai NEXT', 'omise' ),
				'logo'				 => 'ktb',
			)
		);
	}

	/**
	 *
	 * @return array of an available mobile banking providers
	 */
	public function get_available_providers( $currency ) {
		$mobile_banking_providers = array();
		$capability = $this->capability();

		if ( $capability ){
			$providers = $capability->getPaymentMethods( $currency );

			foreach ( $providers as &$provider ) {
				if(isset(self::$providers[ $provider->name ])){

					$provider_detail = self::$providers[ $provider->name ];
					$provider->provider_name   = $provider_detail['title'];
					$provider->provider_logo   = $provider_detail['logo'];

					array_push($mobile_banking_providers, $provider);
				}
			}
		}

		return $mobile_banking_providers;
	}
}
