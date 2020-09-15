<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

/**
 * @since 3.9
 */
class Omise_Payment_Truemoney extends Omise_Payment_Offsite {
	public function __construct() {
		parent::__construct();

		$this->id                 = 'omise_truemoney';
		$this->has_fields         = true;
		$this->method_title       = __( 'Omise TrueMoney Wallet', 'omise' );
		$this->method_description = wp_kses(
			__( 'Accept payments through <strong>TrueMoney Wallet</strong> via Omise payment gateway (only available in Thailand).', 'omise' ),
			array( 'strong' => array() )
		);

		$this->supports           = array( 'products', 'refunds' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'TH' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_' . $this->id . '_callback', 'Omise_Callback::execute' );
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
				'label'   => __( 'Enable Omise TrueMoney Wallet Payment', 'omise' ),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( 'TrueMoney Wallet', 'omise' ),
			),

			'description' => array(
				'title'       => __( 'Description', 'omise' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
			),
		);
	}

	/**
	 * @inheritdoc
	 */
	public function payment_fields() {
		Omise_Util::render_view( 'templates/payment/form-truemoney.php', array() );
	}

	/**
	 * @inheritdoc
	 */
	public function charge( $order_id, $order ) {
		$phone_number = isset( $_POST['omise_phone_number_default'] ) && 1 == $_POST['omise_phone_number_default'] ? $order->get_billing_phone() : sanitize_text_field( $_POST['omise_phone_number'] );
		$total        = $order->get_total();
		$currency     = $order->get_currency();
		$return_uri   = add_query_arg(
			array( 'wc-api' => 'omise_truemoney_callback', 'order_id' => $order_id ), home_url()
		);
		$metadata     = array_merge(
			apply_filters( 'omise_charge_params_metadata', array(), $order ),
			array( 'order_id' => $order_id ) // override order_id as a reference for webhook handlers.
		);

		return OmiseCharge::create( array(
			'amount'      => Omise_Money::to_subunit( $total, $currency ),
			'currency'    => $currency,
			'description' => apply_filters( 'omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order ),
			'source'      => array( 'type' => 'truemoney', 'phone_number' => $phone_number ),
			'return_uri'  => $return_uri,
			'metadata'    => $metadata
		) );
	}
}
