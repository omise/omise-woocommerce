<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class Omise_Payment_TouchNGo extends Omise_Payment_Offsite {
	public function __construct() {
		parent::__construct();

		$this->id                 = 'omise_touch_n_go';
		$this->has_fields         = false;
		$this->method_title       = __( 'Omise Touch \'n Go', 'omise' );
		$this->method_description = __( 'Accept payment through <strong>Touch \'n Go</strong> via Omise payment gateway.', 'omise' );
		$this->supports           = array( 'products', 'refunds' );

		$this->backend     = new Omise_Backend_TouchNGo;
		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'SG', 'MY' );
		

		add_action( 'woocommerce_api_' . $this->id . '_callback', 'Omise_Callback::execute' );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_order_action_' . $this->id . '_sync_payment', array( $this, 'sync_payment' ) );
	}

	/**
	 * @see WC_Settings_API::init_form_fields()
	 * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
	 */
	public function init_form_fields() {
		if ($this->backend->get_provider() === 'Alipay_plus') {
			$this->form_fields = array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'omise' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Omise TNG eWallet Payment', 'omise' ),
					'default' => 'no'
				),
	
				'title' => array(
					'title'       => __( 'Title', 'omise' ),
					'type'        => 'text',
					'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
					'default'     => __( 'TNG eWallet (Alipay+™ Partner)', 'omise' ),
				),
	
				'description' => array(
					'title'       => __( 'Description', 'omise' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
				),
			);
		}
		else {
			$this->form_fields = array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'omise' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Omise Touch \'n Go Payment', 'omise' ),
					'default' => 'no'
				),
	
				'title' => array(
					'title'       => __( 'Title', 'omise' ),
					'type'        => 'text',
					'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
					'default'     => __( 'Touch \'n Go', 'omise' ),
				),
	
				'description' => array(
					'title'       => __( 'Description', 'omise' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
				),
			);
		}
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
				'wc-api'   => 'omise_touch_n_go_callback',
				'order_id' => $order_id
			),
			home_url()
		);

		return OmiseCharge::create( array(
			'amount'      => Omise_Money::to_subunit( $order->get_total(), $order->get_currency() ),
			'currency'    => $order->get_currency(),
			'description' => apply_filters( 'omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order ),
			'source'      => array( 'type' => 'touch_n_go' ),
			'return_uri'  => $return_uri,
			'metadata'    => $metadata
		) );
	}
}
