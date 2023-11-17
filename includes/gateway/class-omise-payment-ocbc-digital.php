<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class Omise_Payment_OCBC_Digital extends Omise_Payment_Offsite {
	public function __construct() {
		parent::__construct();

		$this->id                 = 'omise_ocbc';
		$this->has_fields         = false;
		$this->method_title       = __( 'Opn Payments OCBC Digital', 'omise' );
		$this->method_description = __( 'Accept payment through <strong>OCBC Digital</strong> via Opn Payments payment gateway.', 'omise' );
		$this->supports           = [ 'products', 'refunds' ];

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = [ 'SG' ];
		$this->source_type          = 'mobile_banking_ocbc';

		add_action( 'woocommerce_api_' . $this->id . '_callback', 'Omise_Callback::execute' );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		add_action( 'woocommerce_order_action_' . $this->id . '_sync_payment', [ $this, 'sync_payment' ] );
	}

	/**
	 * @see WC_Settings_API::init_form_fields()
	 * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'enabled' => [
				'title'   => __( 'Enable/Disable', 'omise' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Opn Payments OCBC Digital', 'omise' ),
				'default' => 'no'
            ],

			'title' => [
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( 'OCBC Digital', 'omise' ),
            ],

			'description' => [
				'title'       => __( 'Description', 'omise' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
            ],
        ];
	}

	/**
	 * @inheritdoc
	 */
	public function charge($order_id, $order)
	{
		$requestData = $this->build_charge_request(
			$order_id, $order, $this->source_type, $this->id . '_callback'
		);

		$requestData['source'] = array_merge($requestData['source'], [
			'platform_type' => Omise_Util::get_platform_type(wc_get_user_agent())
		]);

		return OmiseCharge::create($requestData);
	}

	/**
	 * Get icons
	 *
	 * @see WC_Payment_Gateway::get_icon()
	 */
	public function get_icon()
	{
		$icon = Omise_Image::get_image([
			'file' => 'ocbc-digital.svg',
			'alternate_text' => 'OCBC Digital',
		]);
		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}
}
