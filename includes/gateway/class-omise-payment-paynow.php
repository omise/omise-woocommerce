<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

/**
 * @since 3.11
 */
class Omise_Payment_Paynow extends Omise_Payment {
	public function __construct() {
		parent::__construct();

		$this->id                 = 'omise_paynow';
		$this->has_fields         = false;
		$this->method_title       = __( 'Omise PayNow', 'omise' );
		$this->method_description = wp_kses(
			__( 'Accept payments through <strong>PayNow</strong> via Omise payment gateway.', 'omise' ),
			array( 'strong' => array() )
		);

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'SG' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'display_qrcode' ) );
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
				'default' => 'no'
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
				'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
			),
		);
	}

	/**
	 * @inheritdoc
	 */
	public function charge( $order_id, $order ) {
		$total      = $order->get_total();
		$currency   = $order->get_order_currency();
		$return_uri = add_query_arg(
			array( 'wc-api' => 'omise_paynow_callback', 'order_id' => $order_id ), home_url()
		);
		$metadata   = array_merge(
			apply_filters( 'omise_charge_params_metadata', array(), $order ),
			array( 'order_id' => $order_id ) // override order_id as a reference for webhook handlers.
		);

		return OmiseCharge::create( array(
			'amount'      => Omise_Money::to_subunit( $total, $currency ),
			'currency'    => $currency,
			'description' => apply_filters( 'omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order ),
			'source'      => array( 'type' => 'paynow' ),
			'return_uri'  => $return_uri,
			'metadata'    => $metadata
		) );
	}

	/**
	 * @inheritdoc
	 */
	public function result( $order_id, $order, $charge ) {
		if ( 'failed' === $charge['status'] ) {
			return $this->payment_failed( $charge['failure_message'] . ' (code: ' . $charge['failure_code'] . ')' );
		}

		if ( 'pending' === $charge['status'] ) {
			$order->update_status( 'on-hold', __( 'Omise: Awaiting PayNow to be paid.', 'omise' ) );

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order )
			);
		}

		return $this->payment_failed(
			sprintf(
				__( 'Please feel free to try submitting your order again, or contact our support team if you have any questions (Your temporary order id is \'%s\')', 'omise' ),
				$order_id
			)
		);
	}

	/**
	 * @param int|WC_Order $order
	 * @param string       $context  pass 'email' value through this argument only for 'sending out an email' case.
	 */
	public function display_qrcode( $order, $context = 'view' ) {
		if ( ! $this->load_order( $order ) ) {
			return;
		}

		$charge_id = $this->get_charge_id_from_order();
		$charge    = OmiseCharge::retrieve( $charge_id );
		$qrcode    = $charge['source']['scannable_code']['image']['download_uri'];
		?>
		<div class="omise omise-paynow-details" <?php echo 'email' === $context ? 'style="margin-bottom: 4em; text-align:center;"' : ''; ?>>
			<div class="omise omise-paynow-logo"></div>
			<p>
				<?php echo __( 'Scan the QR code to pay', 'omise' ); ?>
			</p>
			<div class="omise omise-paynow-qrcode">
				<img src="<?php echo $qrcode; ?>" alt="Omise QR code ID: <?php echo $charge['source']['scannable_code']['image']['id']; ?>">
			</div>
		</div>
		<?php
	}
}
