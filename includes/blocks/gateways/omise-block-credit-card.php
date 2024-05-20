<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

class Omise_Block_Credit_Card extends AbstractPaymentMethodType {
    /**
	 * The gateway instance.
	 *
	 * @var Omise_Block_Credit_Card
	 */
	private $gateway;

	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'omise';

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( "woocommerce_{$this->name}_settings", [] );
		$gateways       = WC()->payment_gateways->payment_gateways();
		$this->gateway  = $gateways[ $this->name ];
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return $this->gateway->is_available();
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		if ( is_checkout() && $this->is_active() ) {
			$script_asset = require __DIR__ .  '/../assets/js/build/credit_card.asset.php';
			wp_register_script(
				"{$this->name}-payments-blocks",
				plugin_dir_url( __DIR__ ) . 'assets/js/build/credit_card.js',
				$script_asset[ 'dependencies' ],
				$script_asset[ 'version' ],
				true
			);
		}

		return [ "{$this->name}-payments-blocks" ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {

		if ( is_user_logged_in() ) {
			$viewData['user_logged_in'] = true;

			$current_user      = wp_get_current_user();
			$omise_customer_id = $this->gateway->is_test() ? $current_user->test_omise_customer_id : $current_user->live_omise_customer_id;

			if ( ! empty( $omise_customer_id ) ) {
				try {
					$cards = new OmiseCustomerCard;
					$existingCards = $cards->get($omise_customer_id);

					foreach($existingCards['data'] as $card) {
						$viewData['existing_cards'][] = [
							'id' => $card['id'],
							'brand' => $card['brand'],
							'last_digits' => $card['last_digits'],
						];
					}
				} catch (Exception $e) {
					// nothing
				}
			}
		} else {
			$viewData['user_logged_in'] = false;
		}

		$viewData['secure_form_enabled'] = (boolean)$this->gateway->get_option('secure_form_enabled');

		if ($viewData['secure_form_enabled'] === $this->gateway::SECURE_FORM_ENABLED) {
			$viewData['card_form_theme'] = $this->gateway->get_option('card_form_theme');
			$viewData['card_icons'] = $this->gateway->get_card_icons();
			$viewData['form_design'] = Omise_Page_Card_From_Customization::get_instance()->get_design_setting();
		}

		return array_merge($viewData, [
			'name'        => $this->name,
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'features'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] ),
			'locale'      => get_locale(),
			'public_key'  => Omise()->settings()->public_key(),
		]);
	}
}
