<?php

defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

/**
 * @since 4.1
 */
class Omise_Payment_Konbini extends Omise_Payment_Offline {
	public function __construct() {
		parent::__construct();

		$this->id                 = 'omise_konbini';
		$this->has_fields         = true;
		$this->method_title       = __( 'Convenience Store / Pay-easy / Online Banking', 'omise' );
		$this->method_description = wp_kses(
			__( 'Accept payments through <strong>Convenience Store</strong> / <strong>Pay-easy</strong> / <strong>Online Banking</strong> via Omise payment gateway.', 'omise' ),
			array( 'strong' => array() )
		);

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'JP' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_order_action_' . $this->id . '_sync_payment', array( $this, 'sync_payment' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'display_link' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_link' ) );
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
				'label'   => __( 'Enable Omise Convenience Store / Pay-easy / Online Banking Payment', 'omise' ),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( 'Convenience Store / Pay-easy / Online Banking', 'omise' ),
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
		Omise_Util::render_view( 'templates/payment/form-konbini.php', array() );
	}

	/**
	 * @inheritdoc
		*/
	public function charge( $order_id, $order ) {
		$konbini_name  = isset( $_POST['omise_konbini_name'] ) ? sanitize_text_field( $_POST['omise_konbini_name'] ) : '';
		$konbini_email = isset( $_POST['omise_konbini_email'] ) ? sanitize_text_field( $_POST['omise_konbini_email'] ) : '';
		$konbini_phone = isset( $_POST['omise_konbini_phone'] ) ? sanitize_text_field( $_POST['omise_konbini_phone'] ) : '';

		$total         = $order->get_total();
		$currency      = $order->get_order_currency();
		$metadata      = array_merge(
			apply_filters( 'omise_charge_params_metadata', array(), $order ),
			array( 'order_id' => $order_id ) // override order_id as a reference for webhook handlers.
		);

		return OmiseCharge::create( array(
			'amount'      => Omise_Money::to_subunit( $total, $currency ),
			'currency'    => $currency,
			'description' => apply_filters( 'omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order ),
			'source'      => array( 'type' => 'econtext', 'name' => $konbini_name, 'email' => $konbini_email, 'phone_number' => $konbini_phone ),
			'metadata'    => $metadata
		) );
	}

	/**
	 * @param int|WC_Order $order
	 * @param string       $context  pass 'email' value through this argument only for 'sending out an email' case.
	 */
	public function display_link( $order, $context = 'view' ) {
		if ( ! $this->load_order( $order ) ) {
			return;
		}

		$charge_id          = $this->get_charge_id_from_order();
		$charge             = OmiseCharge::retrieve( $charge_id );
		$payment_link       = $charge['authorize_uri'];
		$expires_datetime   = new WC_DateTime( $charge['source']['references']['expires_at'], new DateTimeZone( 'UTC' ) );
		$expires_datetime->set_utc_offset( wc_timezone_offset() );
		?>

		<div class="omise omise-konbini-details" <?php echo 'email' === $context ? 'style="margin-bottom: 4em; text-align:center;"' : ''; ?>>
			<p>
				<?php echo __( 'Your payment code has been sent to your email', 'omise' ); ?>
				<br/>
				<?php
				echo sprintf(
					wp_kses(
						__( 'Please find the payment instruction there or click on the link below and complete the payment by <br/><strong>%s %s</strong>.', 'omise' ),
						array( 'br' => array(), 'strong' => array() )
					),
					wc_format_datetime( $expires_datetime, wc_date_format() ),
					wc_format_datetime( $expires_datetime, wc_time_format() )
				);
				?>
				<br/>
				<?php
				echo sprintf(
					wp_kses(
						__( '<a href="%s" target="_blank">Payment Link</a>', 'omise' ),
						array( 'a' => array( 'href' => array(), 'target' => array() ) )
					),
					$payment_link
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * @param WC_Order $order
	 *
	 * @see   woocommerce/templates/emails/email-order-details.php
	 * @see   woocommerce/templates/emails/plain/email-order-details.php
	 */
	public function email_link( $order ) {
		if ( $this->id == $order->get_payment_method() ) {
			$this->display_link( $order, 'email' );
		}
	}
}
