<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class Omise_Payment_TouchNGo extends Omise_Payment_Offsite {

	private $touch_and_go_wallet_title = 'Touch \'n Go eWallet';

	public function __construct() {
		parent::__construct();

		$this->source_type        = 'touch_n_go';
		$this->provider           = $this->get_provider();
		$this->id                 = 'omise_touch_n_go';
		$this->has_fields         = false;
		$this->method_title       = __( 'Opn Payments ' . $this->GetMethodTitle(), 'omise' );
		$this->method_description = __( 'Accept payment through <strong>' . $this->GetMethodTitle() . '</strong> via Opn Payments payment gateway.', 'omise' );
		$this->supports           = array( 'products', 'refunds' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'SG', 'MY', 'TH' );
		
		add_action( 'woocommerce_api_' . $this->id . '_callback', 'Omise_Callback::execute' );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_order_action_' . $this->id . '_sync_payment', array( $this, 'sync_payment' ) );
	}

	/**
	 * @see WC_Settings_API::init_form_fields()
	 * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
	 */
	public function init_form_fields() {
		$method_title = $this->touch_and_go_wallet_title;
		$default_title = $this->touch_and_go_wallet_title;

		if ($this->provider === 'Alipay_plus') {
			$method_title = 'TNG eWallet';
			$default_title = 'TNG eWallet (Alipay+™ Partner)';
		}

		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'omise' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Opn Payments ' . $method_title . ' Payment', 'omise' ),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( $default_title, 'omise' ),
			),

			'description' => array(
				'title'       => __( 'Description', 'omise' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
			),
		);
	}

	public function GetMethodTitle() {
		if ($this->provider === 'Alipay_plus') {
			return 'TNG eWallet';
		}
		
		return $this->touch_and_go_wallet_title;
	}

	/**
	 * @inheritdoc
	 */
	public function charge($order_id, $order)
	{
		$currency = $order->get_currency();
		return OmiseCharge::create([
			'amount' => Omise_Money::to_subunit($order->get_total(), $currency),
			'currency' => $currency,
			'description' => apply_filters('omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order),
			'source' => ['type' => $this->source_type],
			'return_uri' => $this->getRedirectUrl('omise_touch_n_go_callback', $order_id, $order),
			'metadata' => $this->getMetadata($order_id, $order)
		]);
	}

	/**
	 * Get icons
	 *
	 * @see WC_Payment_Gateway::get_icon()
	 */
	public function get_icon() {
		$icon = Omise_Image::get_image([
			'file' => 'touch-n-go.png',
			'alternate_text' => $this->touch_and_go_wallet_title,
		]);
		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}

}
