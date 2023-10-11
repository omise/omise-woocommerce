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
			__( 'Accept payments through <strong>Convenience Store</strong> / <strong>Pay-easy</strong> / <strong>Online Banking</strong> via Opn Payments payment gateway.', 'omise' ),
			array( 'strong' => array() )
		);

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'JP' );
		$this->source_type          = 'econtext';

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
				'label'   => __( 'Enable Opn Payments Convenience Store / Pay-easy / Online Banking Payment', 'omise' ),
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
		parent::payment_fields();
		Omise_Util::render_view( 'templates/payment/form-konbini.php', array() );
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
			$order_id,
			$order,
			$this->source_type
		);

		$konbini_name = $_POST['omise_konbini_name'];
		$konbini_name  = isset($konbini_name) ? $konbini_name : '';

		$konbini_email = $_POST['omise_konbini_email'];
		$konbini_email = isset($konbini_email) ? $konbini_email : '';

		$konbini_phone = $_POST['omise_konbini_phone'];
		$konbini_phone = isset($konbini_phone) ? $konbini_phone : '';

		$requestData['source'] = array_merge($requestData['source'], [
			'name' => sanitize_text_field($konbini_name),
			'email' => sanitize_text_field($konbini_email),
			'phone_number' => sanitize_text_field($konbini_phone)
		]);

		return $requestData;
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
				<?= __( 'Your payment code has been sent to your email', 'omise' ); ?>
				<br/>
				<?= sprintf(
					wp_kses(
						__( 'Please find the payment instruction there or click on the link below and complete the payment by <br/><strong>%s %s</strong>.', 'omise' ),
						array( 'br' => array(), 'strong' => array() )
					),
					wc_format_datetime( $expires_datetime, wc_date_format() ),
					wc_format_datetime( $expires_datetime, wc_time_format() )
				);
				?>
				<br/>
				<?= sprintf(
					wp_kses(
						'<a href="%s" target="_blank">' . __( 'Payment Link', 'omise' ) . '</a>' ,
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
