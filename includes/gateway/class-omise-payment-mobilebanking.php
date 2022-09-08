<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class Omise_Payment_Mobilebanking extends Omise_Payment_Offsite {
	public function __construct() {
		parent::__construct();

		$this->id                 = 'omise_mobilebanking';
		$this->has_fields         = true;
		$this->method_title       = __( 'Omise Mobile Banking', 'omise' );
		$this->method_description = wp_kses(
			__( 'Accept payment through <strong>Mobile Banking</strong> via Omise payment gateway.', 'omise' ),
			array(
				'strong' => array()
			)
		);

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'TH' );

		$this->backend     = new Omise_Backend_Mobile_Banking;

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
				'label'   => __( 'Enable Omise Mobile Banking Payment', 'omise' ),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( 'Mobile Banking', 'omise' ),
			),

			'description' => array(
				'title'       => __( 'Description', 'omise' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
			),
		);
	}

	/**
	 * @see WC_Payment_Gateway::payment_fields()
	 * @see woocommerce/includes/abstracts/abstract-wc-payment-gateway.php
	 */
	public function payment_fields() {
		$currency   = get_woocommerce_currency();
		parent::payment_fields();

		Omise_Util::render_view( 'templates/payment/form-mobilebanking.php', 
		array(
			'mobile_banking_backends' => $this->backend->get_available_providers( $currency ),
		) );
	}

	/**
	 * @inheritdoc
	 */
	public function charge( $order_id, $order ) {
		$metadata = array_merge(
			apply_filters( 'omise_charge_params_metadata', array(), $order ),
			array( 'order_id' => $order_id ) // override order_id as a reference for webhook handlers.
		);

		$source_type = sanitize_text_field( $_POST['omise-offsite']);
		$token = TokenHelper::random();
		$return_uri = add_query_arg(
			[
				'order_id' => $order_id,
				'token' => $token
			],
			home_url('wc-api/omise_mobilebanking_callback')
		);

		$order->add_meta_data( 'token', $token, true );

		return OmiseCharge::create( array(
			'amount'      => Omise_Money::to_subunit( $order->get_total(), $order->get_currency() ),
			'currency'    => $order->get_currency(),
			'description' => apply_filters('omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order),
			'source'      => array(
				'type' => $source_type,
				'platform_type' => Omise_Util::get_platform_type( wc_get_user_agent() ) 
			),
			'return_uri'  => $return_uri,
			'metadata'    => $metadata
		) );
	}

	/**
	 * check if payment method is support by omise capability api version 2017
	 * 
	 * @param  array of backends source_type 
	 *
	 * @return array|false
	 */
	public function is_capability_support( $available_payment_methods ) {
		//filter ocbc pao out bc is no longer mobile banking payments
		return preg_grep('/^mobile_banking_(?!ocbc_pao)/', $available_payment_methods);
	}
}
