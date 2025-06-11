<?php
defined( 'ABSPATH' ) || die( 'No direct script access allowed.' );

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

// if ( class_exists( 'Omise_Payment_Checkout_Page' ) ) {
// 	return;
// }

class Omise_Payment_Checkout_Page extends Omise_Payment {

	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Omise_Payment constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->id                 = 'omise_checkout_page';
		$this->has_fields         = false;
		$this->method_title       = __( 'Omise Checkout Page', 'omise' );
		$this->method_description = __( 'Accept payments through Omise Checkout Page.', 'omise' );

		$this->init_form_fields();
		$this->init_settings();

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function charge( $order_id, $order ) {
		throw new Exception( 'This payment method does not support charge() method.' );
	}

	public function result( $order_id, $order, $charge ) {
		throw new Exception( 'This payment method does not support result() method.' );
	}

	public function is_available() {
		return true;
	}

	public function is_capability_support( $available_payment_methods ) {
		return true;
	}

	public function process_payment( $order_id ) {
		$order = $this->load_order( $order_id );
		if ( ! $order ) {
			return $this->invalid_order( $order_id );
		}

		$response = $this->make_http_request(
			'http://host.docker.internal:50001/api/sessions',
			'POST',
			$this->secret_key() . ':',
			[
				'amount' => Omise_Money::to_subunit( $order->get_total(), $order->get_currency() ),
				'currency' => $order->get_currency(),
				'redirect_urls' => [
					'complete_url' => 'http://www.google.com',
					'cancel_url' => 'http://www.google.com',
				],
				'locale' => 'en',
				'payment_methods' => [
					// TODO: List enabled payment methods.
					"installment",
					"shopeepay",
					"alipay",
					"paypay",
					"internet_banking",
					"promptpay",
					"credit_card"
				]
			]
		);

		error_log( print_r( $response, true ) );

		$session_id = $response['id'];
		if ( ! $session_id ) {
			throw new Exception( 'Failed to create Omise Checkout Page session.' );
		}

		$this->order->update_meta_data( 'checkout_session_id', $session_id );
		$this->order->save();

		return [
			'result' => 'success',
			'redirect' => home_url('/') . Omise_Checkout_Page::$PATH . '/' . $session_id,
		];
	}

	private function make_http_request( $url, $method, $auth, $params = [] ) {
		$ch = curl_init( $url );

		curl_setopt_array( $ch, $this->genOptions( $method, $auth, $params ) );

		// Make a request or thrown an exception.
		if ( ( $result = curl_exec( $ch ) ) === false ) {
				$error = curl_error( $ch );
				curl_close( $ch );

				throw new Exception( $error );
		}

		// Close.
		curl_close( $ch );
		$json_result = json_decode($result, true);

		if ($json_result === null) {
			$error = json_last_error_msg();
			throw new Exception( "JSON decode error: $error" );
		}

		return $json_result;
	}

	private function genOptions( $method, $userpwd, $params ) {
		$options = [
			// Set the HTTP version to 1.1.
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json',
				'Accept: application/json',
			],
			// Set the request method.
			CURLOPT_POST => true,
			CURLOPT_CUSTOMREQUEST => $method,
			// Make php-curl returns the data as string.
			CURLOPT_RETURNTRANSFER => true,
			// Do not include the header in the output.
			CURLOPT_HEADER => false,
			// Track the header request string and set the referer on redirect.
			CURLINFO_HEADER_OUT => true,
			CURLOPT_AUTOREFERER => true,
			// Time before the request is aborted.
			CURLOPT_TIMEOUT => 60,
			// Time before the request is aborted when attempting to connect.
			CURLOPT_CONNECTTIMEOUT => 60,
			// Authentication.
			CURLOPT_USERPWD => $userpwd,
		];

		// Config UserAgent
		if ( defined( 'OMISE_USER_AGENT_SUFFIX' ) ) {
			$options += [ CURLOPT_USERAGENT => OMISE_USER_AGENT_SUFFIX ];
		}

		// Also merge POST parameters with the option.
		if ( is_array( $params ) && count( $params ) > 0 ) {
			// $http_query = http_build_query( $params );
			// $http_query = preg_replace( '/%5B\d+%5D/simU', '%5B%5D', $http_query );
			// error_log(print_r($options, true));
			// error_log($http_query);

			$data = json_encode( $params );

			$options += [ CURLOPT_POSTFIELDS => $data ];
		}

		return $options;
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'omise' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Omise Checkout Page Payment', 'omise' ),
				'default' => 'no',
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( 'Checkout Page', 'omise' ),
			),

			'description' => array(
				'title'       => __( 'Description', 'omise' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description the user sees during checkout.', 'omise' ),
			),
		);
	}
}
