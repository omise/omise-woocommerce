<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

/**
 * @since 3.9
 */
class Omise_Payment_Truemoney extends Omise_Payment_Offsite
{
	/**
	 * Backends identifier
	 * @var string
	 */
	const WALLET = 'truemoney';
	const JUMPAPP = 'truemoney_jumpapp';

	public function __construct()
	{
		parent::__construct();

		$this->id                 = 'omise_truemoney';
		$this->has_fields         = true;
		$this->method_title       = __( 'Opn Payments TrueMoney', 'omise' );
		$this->method_description = wp_kses(
			__( 'Accept payments through <strong>TrueMoney</strong> via Opn Payments payment gateway (only available in Thailand).', 'omise' ),
			array( 'strong' => array() )
		);

		$this->supports           = array( 'products', 'refunds' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'TH' );
		$this->source_type        = $this->get_source();

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_' . $this->id . '_callback', 'Omise_Callback::execute' );
		add_action( 'woocommerce_order_action_' . $this->id . '_sync_payment', array( $this, 'sync_payment' ) );
	}

	/**
	 * @see WC_Settings_API::init_form_fields()
	 * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
	 */
	public function init_form_fields()
	{
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'omise' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Opn Payments TrueMoney Payment', 'omise' ),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( 'TrueMoney', 'omise' ),
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
	public function payment_fields()
	{
		parent::payment_fields();
		if (self::WALLET === $this->source_type) {
			Omise_Util::render_view( 'templates/payment/form-truemoney.php', [] );
		}
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
		$request_data = $this->build_charge_request(
			$order_id,
			$order,
			$this->source_type,
			$this->id . '_callback'
		);

		if (self::WALLET === $this->source_type) {
			$phone_option = $_POST['omise_phone_number_default'];
			$is_phone_option_checked = isset($phone_option) && 1 == $phone_option;
			$phone_number = $is_phone_option_checked ?
				$order->get_billing_phone() :
				sanitize_text_field( $_POST['omise_phone_number'] );

			$request_data['source'] = array_merge($request_data['source'], [
				'phone_number' => $phone_number
			]);
		}

		return $request_data;
	}

	/**
	 * Return the right ShopeePay backend depending on the platform and availability of
	 * the backend in the capability
	 */
	public function get_source()
	{
		$capabilities = Omise_Capabilities::retrieve();

		if (!$capabilities) {
			$this->has_fields = false;
			return self::JUMPAPP;
		}

		$is_jumpapp_enabled = $capabilities->get_truemoney_backend(self::JUMPAPP);
		$is_wallet_enabled = $capabilities->get_truemoney_backend(self::WALLET);

		if (!empty($is_wallet_enabled) && empty($is_jumpapp_enabled)) {
			return self::WALLET;
		}

		// Return JUMP APP for the following cases:
		// Case 1: Both jumpapp and wallet are enabled
		// Case 2: jumpapp is enabled and wallet is disabled
		// Case 3: Both are disabled.
		$this->has_fields = false;
		return self::JUMPAPP;
	}
}
