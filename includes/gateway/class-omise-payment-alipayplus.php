<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

abstract class Omise_Payment_Alipayplus extends Omise_Payment_Offsite {

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

	public function __construct( $wallet_source, $wallet_title, $wallet_countries ) {
		parent::__construct();

		$this->source_type = $wallet_source;
		$this->wallet_title = $wallet_title;
		$this->wallet_countries = $wallet_countries;

		$this->id                 = 'omise_' . $wallet_source;
		$this->has_fields         = false;
		$this->method_title       = __( 'Opn Payments ' . $wallet_title, 'omise' );
		$this->method_description = wp_kses(
			__( 'Accept payments through <strong>' . $wallet_title . '</strong> via Opn Payments payment gateway.', 'omise' ),
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
				'label'   => __( 'Enable Opn Payments ' . $this->wallet_title . ' Payment', 'omise' ),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( $this->wallet_title . ' (Alipay+™ Partner)', 'omise' ),
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
	public function charge($order_id, $order)
	{
		$requestData = $this->get_charge_request($order_id, $order);
		return OmiseCharge::create($requestData);
	}

	public function get_charge_request($order_id, $order)
	{
		$requestData = $this->build_charge_request(
			$order_id, $order, $this->source_type, $this->id . "_callback"
		);
		$requestData['source'] = array_merge($requestData['source'], [
			'platform_type' => Omise_Util::get_platform_type(wc_get_user_agent())
		]);
		return $requestData;
	}
}

class Omise_Payment_Alipay_China extends Omise_Payment_Alipayplus {
	public function __construct() {
		$source = 'alipay_cn';
		$title = 'Alipay';
		$countries = array( 'SG' );
		parent::__construct( $source, $title, $countries );
	}
}

class Omise_Payment_Alipay_Hk extends Omise_Payment_Alipayplus {
	public function __construct() {
		$source = 'alipay_hk';
		$title = 'AlipayHK';
		$countries = array( 'SG', 'TH' );
		parent::__construct( $source, $title, $countries );
	}
}

class Omise_Payment_Dana extends Omise_Payment_Alipayplus {
	public function __construct() {
		$source = 'dana';
		$title = 'DANA';
		$countries = array( 'SG' );
		parent::__construct( $source, $title, $countries );
	}
}

class Omise_Payment_Gcash extends Omise_Payment_Alipayplus {
	public function __construct() {
		$source = 'gcash';
		$title = 'GCash';
		$countries = array( 'SG' );
		parent::__construct( $source, $title, $countries );
	}
}

class Omise_Payment_Kakaopay extends Omise_Payment_Alipayplus {
	public function __construct() {
		$source = 'kakaopay';
		$title = 'Kakao Pay';
		$countries = array( 'SG', 'TH' );
		parent::__construct( $source, $title, $countries );
	}
}
