<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class Omise_Payment_DuitNow_OBW extends Omise_Payment_Offsite {
	public function __construct() {
		parent::__construct();

		$this->id                 = 'omise_duitnow_obw';
		$this->has_fields         = true;
		$this->method_title       = __( 'Omise DuitNow Online Banking/Wallets', 'omise' );
		$this->method_description = __( 'Accept payment through <strong>DuitNow Online Banking/Wallets</strong> via Omise payment gateway.', 'omise' );
		$this->supports           = array( 'products', 'refunds' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'MY' );

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
				'label'   => __( 'Enable Omise DuitNow Online Banking/Wallets Payment', 'omise' ),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( 'DuitNow Online Banking/Wallets', 'omise' ),
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
		parent::payment_fields();
		$currency   = get_woocommerce_currency();
		$cart_total = WC()->cart->total;

		Omise_Util::render_view(
			'templates/payment/form-duitnow-obw.php',
			array(
				'duitnow_obw_banklist' => array(
					'affin' => array(
						'code' => 'affin',
						'name' => 'Affin Bank'
					),
					'alliance' => array(
						'code' => 'alliance',
						'name' => 'Alliance Bank'
					),
					'agro' => array(
						'code' => 'agro',
						'name' => 'Agrobank'
					),
					'ambank' => array(
						'code' => 'ambank',
						'name' => 'AmBank'
					),
					'cimb' => array(
						'code' => 'cimb',
						'name' => 'CIMB Bank'
					),
					'islam' => array(
						'code' => 'islam',
						'name' => 'Bank Islam'
					),
					'rakyat' => array(
						'code' => 'rakyat',
						'name' => 'Bank Rakyat'
					),
					'muamalat' => array(
						'code' => 'muamalat',
						'name' => 'Bank Muamalat'
					),
					'bsn' => array(
						'code' => 'bsn',
						'name' => 'Bank Simpanan Nasional'
					),
					'hongleong' => array(
						'code' => 'hongleong',
						'name' => 'Hong Leong'
					),
					'hsbc' => array(
						'code' => 'hsbc',
						'name' => 'HSBC Bank'
					),
					'kfh' => array(
						'code' => 'kfh',
						'name' => 'Kuwait Finance House'
					),
					'maybank2u' => array(
						'code' => 'maybank2u',
						'name' => 'Maybank'
					),
					'ocbc' => array(
						'code' => 'ocbc',
						'name' => 'OCBC'
					),
					'public' => array(
						'code' => 'public',
						'name' => 'Public Bank'
					),
					'rhb' => array(
						'code' => 'rhb',
						'name' => 'RHB Bank'
					),
					'sc' => array(
						'code' => 'sc',
						'name' => 'Standard Chartered'
					),
					'uob' => array(
						'code' => 'uob',
						'name' => 'United Overseas Bank'
					),
				)
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function charge( $order_id, $order ) {
		$source_bank	= isset( $_POST['source']['bank'] ) ? $_POST['source']['bank'] : '';
		
		$metadata = array_merge(
			apply_filters( 'omise_charge_params_metadata', array(), $order ),
			array( 'order_id' => $order_id ) // override order_id as a reference for webhook handlers.
		);

		$return_uri = add_query_arg(
			array(
				'wc-api'   => 'omise_duitnow_obw_callback',
				'order_id' => $order_id
			),
			home_url()
		);

		return OmiseCharge::create( array(
			'amount'      => Omise_Money::to_subunit( $order->get_total(), $order->get_currency() ),
			'currency'    => $order->get_currency(),
			'description' => apply_filters( 'omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order ),
			'source'      => array(
				'type' => 'duitnow_obw',
				'bank' => sanitize_text_field( $source_bank ),
			),
			'return_uri'  => $return_uri,
			'metadata'    => $metadata
		) );
	}

	/**
	 * Get icons
	 *
	 * @see WC_Payment_Gateway::get_icon()
	 */
	public function get_icon() {
		$icon = Omise_Image::get_image( array(
			    'file' => 'duitnow-obw.png',
			    'alternate_text' => 'DuitNow Online Banking/Wallets',
		));
		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}
}
