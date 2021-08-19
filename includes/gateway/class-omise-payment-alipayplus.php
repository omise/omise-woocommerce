<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

abstract class Omise_Payment_Alipayplus extends Omise_Payment_Offsite {
	/**
	 * Holds a private string
	 * @var string
	 */
	private $wallet_source = '';

	/**
	 * Holds a private string
	 * @var string
	 */
	private $wallet_title = '';
	
	/**
	 * Holds a private string
	 * @var string
	 */
	private $wallet_countries = '';

	public function __construct($wallet_source, $wallet_title, $wallet_countries) {
		parent::__construct();

		$this->wallet_source = $wallet_source;
		$this->wallet_title = $wallet_title;
		$this->wallet_countries = $wallet_countries;

		$this->id                 = 'omise_' . $source;
		$this->has_fields         = false;
		$this->method_title       = __( 'Omise ' . $wallet_title, 'omise' );
		$this->method_description = wp_kses(
			__( 'Accept payments through <strong>' . $wallet_title . '</strong> via Omise payment gateway.', 'omise' ),
			array( 'strong' => array() )
		);
		$this->supports           = array( 'products', 'refunds' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = $this->wallet_countries;

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
				'label'   => __( 'Enable Omise ' . $this->wallet_title . ' Payment', 'omise' ),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( $this->wallet_title, 'omise' ),
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
	public function charge( $order_id, $order ) {
		$metadata = array_merge(
			apply_filters( 'omise_charge_params_metadata', array(), $order ),
			array( 'order_id' => $order_id ) // override order_id as a reference for webhook handlers.
		);
		$return_uri = add_query_arg(
			array(
				'wc-api'   => 'omise_' . $this->wallet_source . '_callback',
				'order_id' => $order_id
			),
			home_url()
		);

		return OmiseCharge::create( array(
			'amount'      => Omise_Money::to_subunit( $order->get_total(), $order->get_currency() ),
			'currency'    => $order->get_currency(),
			'description' => apply_filters( 'omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order ),
			'source'      => array( 'type' => $this->wallet_source ),
			'return_uri'  => $return_uri,
			'metadata'    => $metadata
		) );
	}
}

class Omise_Payment_Alipay_China extends Omise_Payment_Alipayplus {
	public function __construct() {
		$source = 'alipay_cn';
		$title = 'Alipay';
		$countries = array( 'TH', 'JP', 'MY', 'SG' );
		parent::__construct($source, $title, $countries);
	}
}

class Omise_Payment_Alipay_Hk extends Omise_Payment_Alipayplus {
	public function __construct() {
		$source = 'alipay_hk';
		$title = 'AlipayHK';
		$countries = array( 'TH', 'JP', 'MY', 'SG' );
		parent::__construct($source, $title, $countries);
	}
}