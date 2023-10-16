<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class Omise_Payment_ShopeePay extends Omise_Payment_Offsite
{
	/**
	 * Backends identifier
	 * @var string
	 */
	const ID = 'shopeepay';
	const JUMPAPP_ID = 'shopeepay_jumpapp';

	public function __construct() {
		parent::__construct();

		$this->id                 = 'omise_shopeepay';
		$this->has_fields         = false;
		$this->method_title       = __( 'Opn Payments ShopeePay', 'omise' );
		$this->method_description = __( 'Accept payment through <strong>ShopeePay</strong> via Opn Payments payment gateway.', 'omise' );
		$this->supports           = array( 'products', 'refunds' );
		$this->source_type        = $this->getSource();

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'MY', 'TH', 'SG' );

		add_action( 'woocommerce_api_' . $this->id . '_callback', 'Omise_Callback::execute' );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_order_action_' . $this->id . '_sync_payment', array( $this, 'sync_payment' ) );
	}

	/**
	 * @see WC_Settings_API::init_form_fields()
	 * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'omise' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Opn Payments ShopeePay Payment', 'omise' ),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( 'ShopeePay', 'omise' ),
			),

			'description' => array(
				'title'       => __( 'Description', 'omise' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
			),
		);
	}

	/**
	 * Return the right ShopeePay backend depending on the platform and availability of
	 * the backend in the capability
	 */
	private function getSource()
	{
		$capabilities = Omise_Capabilities::retrieve();

		if (!$capabilities) {
			return self::ID;
		}

		$isShopeepayJumpAppEnabled = $capabilities->getShopeeBackend(self::JUMPAPP_ID);
		$isShopeepayEnabled = $capabilities->getShopeeBackend(self::ID);

		// If user is in mobile and jump app is enabled then return shopeepay_jumpapp as source
		if (Omise_Util::isMobilePlatform() && !empty($isShopeepayJumpAppEnabled)) {
			return self::JUMPAPP_ID;
		}

		// If above condition fails then it means either
		//
		// Case 1.
		// User is using mobile device but jump app is not enabled.
		// This means shopeepay direct is enabled otherwise this code would not execute.
		//
		// Case 2.
		// Jump app is enabled but user is not using mobile device
		//
		// In both cases we will want to show the shopeepay MPM backend first if MPM is enabled.
		// If MPM is not enabled then it means jump app is enabled because this code would never
		// execute if none of the shopee backends were disabled.
		return !empty($isShopeepayEnabled) ? self::ID : self::JUMPAPP_ID;
	}

	/**
	 * Get icons
	 *
	 * @see WC_Payment_Gateway::get_icon()
	 */
	public function get_icon() {
		$icon = Omise_Image::get_image([
			'file' => 'shopeepay.png',
			'alternate_text' => 'ShopeePay',
		]);
		return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
	}
}
