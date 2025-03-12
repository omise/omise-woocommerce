<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class Omise_Payment_Creditcard extends Omise_Payment_Base_Card {

	public function __construct()
	{
		parent::__construct();

		$this->id                 = 'omise';
		// for card payment bc we using credit_card at key for capability api 
		// version 2017-2014
		$this->source_type        = 'credit_card';
		$this->has_fields         = true;
		$this->method_title       = __( 'Omise Credit / Debit Card', 'omise' );
		$this->method_description = wp_kses(
			__( 'Accept payment through <strong>Credit / Debit Card</strong> via Omise.', 'omise' ),
			array(
				'strong' => array()
			)
		);
		$this->supports           = array( 'products', 'refunds' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->payment_action       = $this->get_option( 'payment_action' );
		$this->restricted_countries = array( 'TH', 'JP', 'SG', 'MY' );

		add_action( 'woocommerce_api_' . $this->id . '_callback', 'Omise_Callback::execute' );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'omise_scripts' ) );
		add_action( 'woocommerce_order_action_' . $this->id . '_charge_capture', array( $this, 'process_capture' ) );
		add_action( 'woocommerce_order_action_' . $this->id . '_sync_payment', array( $this, 'sync_payment' ) );

		/** @deprecated 3.0 */
		add_action( 'woocommerce_api_wc_gateway_' . $this->id, 'Omise_Callback::execute' );
	}

	/**
	 * @see WC_Settings_API::init_form_fields()
	 * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
	 */
	function init_form_fields() {
		$this->form_fields = array_merge(
			array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'omise' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Omise Credit / Debit Card Payment', 'omise' ),
					'default' => 'no'
				),

				'title' => array(
					'title'       => __( 'Title', 'omise' ),
					'type'        => 'text',
					'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
					'default'     => __( 'Credit / Debit Card', 'omise' )
				),

				'description' => array(
					'title'       => __( 'Description', 'omise' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
				),
			),
			array(
				'advanced' => array(
					'title'       => __( 'Advanced Settings', 'omise' ),
					'type'        => 'title'
				),
				'payment_action' => array(
					'title'       => __( 'Payment action', 'omise' ),
					'type'        => 'select',
					'description' => __( 'Capture automatically during the checkout process or manually after order has been placed', 'omise' ),
					'default'     => self::PAYMENT_ACTION_AUTHORIZE_CAPTURE,
					'class'       => 'wc-enhanced-select',
					'options'     => array(
						self::PAYMENT_ACTION_AUTHORIZE_CAPTURE => __( 'Auto Capture', 'omise' ),
						self::PAYMENT_ACTION_AUTHORIZE         => __( 'Manual Capture', 'omise' )
					),
					'desc_tip'    => true
				),
				'card_form_theme' => [
					'title'       => __( 'Secure form theme', 'omise' ),
					'type'        => 'select',
					'default'     => 'light',
					'class'       => 'wc-enhanced-select',
					'options'     => array(
						'light' => __( 'Light', 'omise' ),
						'dark'  => __( 'Dark', 'omise' )
					),
					'description' => wp_kses(
						__( 'Credit / Debit card form design on checkout page. <br /> <a href="admin.php?page=omise_card_form_customization">Click here for more card form customization.</a>', 'omise' ),
						['a' => ['href' => []], 'br' => []]
					),
				],
				'accept_visa' => array(
					'title'       => __( 'Supported card icons', 'omise' ),
					'type'        => 'checkbox',
					'label'       => Omise_Card_Image::get_visa_image(),
					'css'         => Omise_Card_Image::get_css(),
					'default'     => Omise_Card_Image::get_visa_default_display()
				),
				'accept_mastercard' => array(
					'type'        => 'checkbox',
					'label'       => Omise_Card_Image::get_mastercard_image(),
					'css'         => Omise_Card_Image::get_css(),
					'default'     => Omise_Card_Image::get_mastercard_default_display()
				),
				'accept_jcb' => array(
					'type'        => 'checkbox',
					'label'       => Omise_Card_Image::get_jcb_image(),
					'css'         => Omise_Card_Image::get_css(),
					'default'     => Omise_Card_Image::get_jcb_default_display()
				),
				'accept_diners' => array(
					'type'        => 'checkbox',
					'label'       => Omise_Card_Image::get_diners_image(),
					'css'         => Omise_Card_Image::get_css(),
					'default'     => Omise_Card_Image::get_diners_default_display()
				),
				'accept_discover' => array(
					'type'        => 'checkbox',
					'label'       => Omise_Card_Image::get_discover_image(),
					'css'         => Omise_Card_Image::get_css(),
					'default'     => Omise_Card_Image::get_discover_default_display()
				),
				'accept_amex' => array(
					'type'        => 'checkbox',
					'label'       => Omise_Card_Image::get_amex_image(),
					'css'         => Omise_Card_Image::get_css(),
					'default'     => Omise_Card_Image::get_amex_default_display(),
					'description' => wp_kses(
						__( 'This only controls the icons displayed on the checkout page.<br />It is not related to card processing on Omise.', 'omise' ),
						array( 'br' => array() )
					)
				)
			)
		);
	}

	/**
	 * @see WC_Payment_Gateway::payment_fields()
	 * @see woocommerce/includes/abstracts/abstract-wc-payment-gateway.php
	 */
	public function payment_fields() {
		parent::payment_fields();
		$viewData = array_merge($this->get_existing_cards(), $this->get_secure_form_config());
		Omise_Util::render_view( 'templates/payment/form.php', $viewData );
	}

	public function get_existing_cards() {
		if ( is_user_logged_in() ) {
			$data['user_logged_in'] = true;

			$current_user      = wp_get_current_user();
			$omise_customer_id = $this->is_test() ? $current_user->test_omise_customer_id : $current_user->live_omise_customer_id;

			if ( ! empty( $omise_customer_id ) ) {
				try {
					$cards = new OmiseCustomerCard;
					$existingCards = $cards->get($omise_customer_id);
					$data['existing_cards'] = $existingCards['data'];
				} catch (Exception $e) {
					// nothing
				}
			}

			return $data;
		}

		return [ 'user_logged_in' => false ];
	}

	public function get_secure_form_config() {
		$data['card_form_theme'] = $this->get_option('card_form_theme');
		$data['card_icons'] = $this->get_card_icons();
		$data['form_design'] = Omise_Page_Card_From_Customization::get_instance()->get_design_setting();

		return $data;
	}

	/**
	 * Get card icons for credit card form
	 */
	public function get_card_icons() {
		$enable_icons = [];
		$card_icons = [
			'amex' => 'accept_amex',
			'diners' => 'accept_diners',
			'jcb' => 'accept_jcb',
			'mastercard' => 'accept_mastercard',
			'visa' => 'accept_visa',
			'discover' => 'accept_discover',
		];

		foreach($card_icons as $key => $value) {
			if($this->get_option($value) == "yes") {
				$enable_icons[] = $key;
			}
		}

		return $enable_icons;
	}
}
