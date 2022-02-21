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
			'mobile_banking_ocbc_pao' => array(
				'title'              => __( 'OCBC Pay Anyone', 'omise' ),
				'logo'				 => 'ocbc_pao',
			),
			'mobile_banking_kbank' => array(
				'title'              => __( 'Kasikorn Bank', 'omise' ),
				'logo'				 => 'kplus',
			),
		);
	}

	/**
	 *
	 * @return array  of an available mobile banking providers
	 */
	public function get_available_providers() {

		$providers = $this->capabilities()->getBackends();

		$mobile_banking_providers = array();

		foreach ( $providers as &$provider ) {
			if(isset(self::$providers[ $provider->_id ])){

				$provider_detail = self::$providers[ $provider->_id ];
				$provider->provider_name   = $provider_detail['title'];
				$provider->provider_logo   = $provider_detail['logo'];

				array_push($mobile_banking_providers, $provider);
			}
		}

		return $mobile_banking_providers;
	}
}
