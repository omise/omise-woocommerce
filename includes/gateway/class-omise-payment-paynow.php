<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

/**
 * @since 3.11
 */
class Omise_Payment_Paynow extends Omise_Payment_Offline {
	public function __construct() {
		parent::__construct();

		$this->id                 = 'omise_paynow';
		$this->has_fields         = false;
		$this->method_title       = __( 'Omise PayNow', 'omise' );
		$this->method_description = wp_kses(
			__( 'Accept payments through <strong>PayNow</strong> via Omise payment gateway.', 'omise' ),
			array( 'strong' => array() )
		);
		$this->supports           = array( 'products', 'refunds' );
		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'SG' );
		$this->source_type          = 'paynow';

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_order_action_' . $this->id . '_sync_payment', array( $this, 'sync_payment' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'display_qrcode' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_qrcode' ), 10, 2 );
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
				'label'   => __( 'Enable Omise PayNow Payment', 'omise' ),
				'default' => 'no',
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( 'PayNow', 'omise' ),
			),

			'description' => array(
				'title'       => __( 'Description', 'omise' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description the user sees during checkout.', 'omise' ),
				'default'     => __( 'You will not be charged yet. The PayNow QR code will be displayed at the next page.', 'omise' ),
			),
		);
	}

	/**
	 * @param WC_Order $order
	 *
	 * @see   woocommerce/templates/emails/email-order-details.php
	 * @see   woocommerce/templates/emails/plain/email-order-details.php
	 */
	public function email_qrcode( $order, $sent_to_admin = false ) {
		// Avoid sending QR code if email is sent to admin or if order is processing
		if ( $sent_to_admin || is_a( $order, 'WC_Order' ) && $order->get_status() == 'processing' ) {
			return;
		}

		if ( $this->id == $order->get_payment_method() ) {
			$this->display_qrcode( $order, 'email' );
		}
	}

	/**
	 * @param int|WC_Order $order
	 * @param string       $context  pass 'email' value through this argument only for 'sending out an email' case.
	 */
	public function display_qrcode( $order, $context = 'view' ) {
		if ( ! $order = $this->load_order( $order ) ) {
			return;
		}

		$charge_id = $this->get_charge_id_from_order();
		$charge    = OmiseCharge::retrieve( $charge_id );
		if ( self::STATUS_PENDING !== $charge['status'] ) {
			return;
		}

		$qrcode = $charge['source']['scannable_code']['image']['download_uri'];
		$qrcode_id = $charge['source']['scannable_code']['image']['id'];

		if ( 'view' === $context ) {
			$expires_at_datetime = new DateTime( $charge['expires_at'] );
			$qrcode_expires_at = $expires_at_datetime->format( 'c' );
			$is_qrcode_expired = new DateTime() >= $expires_at_datetime;

			if ( ! $is_qrcode_expired ) {
				$this->register_omise_countdown_script( $qrcode_expires_at );

				$order_key = $order->get_order_key();
				$get_order_status_url = add_query_arg(
					[
						'key' => $order_key,
						'_nonce' => wp_create_nonce( 'get_order_status_' . $order_key ),
						'_wpnonce' => wp_create_nonce( 'wp_rest' ),
					],
					get_rest_url( null, 'omise/order-status' )
				);
			} else {
				$get_order_status_url = '';
			}

			Omise_Util::render_view(
				'templates/payment/paynow/qr.php',
				array(
					'get_order_status_url' => $get_order_status_url,
					'qrcode' => $qrcode,
					'qrcode_id' => $qrcode_id,
					'is_qrcode_expired' => $is_qrcode_expired ? 'true' : 'false',
				)
			);
		} elseif ( 'email' === $context && ! $order->has_status( 'failed' ) ) { ?>
			<p>
				<?php echo __( 'Scan the QR code to complete', 'omise' ); ?>
			</p>
			<p><img src="<?php echo $qrcode; ?>"/></p>
			<?php
		}
	}

	/**
	 * Registers the countdown script for PayNow QR code expiration.
	 *
	 * @param string $expires_at The expiration datetime in ISO 8601 format for the QR code.
	 */
	private function register_omise_countdown_script( $expires_at ) {
		wp_enqueue_script(
			'omise-paynow-countdown',
			plugins_url( '../assets/javascripts/omise-countdown.js', __DIR__ ),
			array(),
			WC_VERSION,
			true
		);
		wp_localize_script(
			'omise-paynow-countdown', 'omise', [
				'countdown_id' => 'timer',
				'qr_expires_at' => $expires_at,
			]
		);
	}
}
