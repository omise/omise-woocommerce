<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class Omise_Payment_RabbitLinePay extends Omise_Payment_Offsite {
	const PAYMENT_ACTION_MANUAL_CAPTURE = 'manual_capture';
	const PAYMENT_ACTION_AUTO_CAPTURE   = 'auto_capture';

	public function __construct() {
		parent::__construct();

		$this->id                 = 'omise_rabbit_linepay';
		$this->has_fields         = false;
		$this->method_title       = __( 'Omise Rabbit LINE Pay', 'omise' );
		$this->method_description = __( 'Accept payment through Rabbit LINE Pay', 'omise' );
		$this->supports           = array( 'products', 'refunds' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->payment_action       = $this->get_option( 'payment_action' );
		$this->restricted_countries = array( 'TH' );

		add_action( 'woocommerce_api_' . $this->id . '_callback', 'Omise_Callback::execute' );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_order_action_' . $this->id . '_charge_capture', array( $this, 'process_capture' ) );
		add_action( 'woocommerce_order_action_' . $this->id . '_sync_payment', array( $this, 'sync_payment' ) );
	}

	/**
	 * @see WC_Settings_API::init_form_fields()
	 * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
	 */
	public function init_form_fields() {
		$this->form_fields = array_merge(
			array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'omise' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Omise Rabbit LINE Pay Payment', 'omise' ),
					'default' => 'no'
				),

				'title' => array(
					'title'       => __( 'Title', 'omise' ),
					'type'        => 'text',
					'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
					'default'     => __( 'Rabbit LINE Pay', 'omise' ),
				),

				'description' => array(
					'title'       => __( 'Description', 'omise' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
				),
			),
			array(
				'advanced' => array(
					'title'       => __( 'Advance Settings', 'omise' ),
					'type'        => 'title'
				),
				'payment_action' => array(
					'title'       => __( 'Payment action', 'omise' ),
					'type'        => 'select',
					'description' => __( 'Capture automatically during the checkout process or manually after order has been placed', 'omise' ),
					'default'     => self::PAYMENT_ACTION_AUTO_CAPTURE,
					'class'       => 'wc-enhanced-select',
					'options'     => array(
						self::PAYMENT_ACTION_AUTO_CAPTURE   => __( 'Auto Capture', 'omise' ),
						self::PAYMENT_ACTION_MANUAL_CAPTURE => __( 'Manual Capture', 'omise' )
					),
					'desc_tip'    => true
				)
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function charge( $order_id, $order ) {
		$metadata = array_merge(
			apply_filters( 'omise_charge_params_metadata', array(), $order ),
			array( 'order_id' => $order_id ) // override order_id as a reference for webhook handlers.
		);
		$return_uri = add_query_arg(
			array(
				'wc-api'   => 'omise_rabbit_linepay_callback',
				'order_id' => $order_id
			),
			home_url()
		);

		return OmiseCharge::create( array(
			'amount'      => Omise_Money::to_subunit( $order->get_total(), $order->get_currency() ),
			'currency'    => $order->get_currency(),
			'description' => apply_filters( 'omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order ),
			'source'      => array( 'type' => 'rabbit_linepay' ),
			'return_uri'  => $return_uri,
			'metadata'    => $metadata,
			'capture'     => $this->payment_action === self::PAYMENT_ACTION_AUTO_CAPTURE
		) );
	}


}
