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
				'description' => __( 'This controls the description the user sees during checkout.', 'omise' ),
				'default'     => __( 'You will not be charged yet. The PayNow QR code will be displayed at the next page.', 'omise' )
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
		if ( $sent_to_admin || is_a($order, 'WC_Order') && $order->get_status() == 'processing') {
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

		$qrcode    = $charge['source']['scannable_code']['image']['download_uri'];

		if ( 'view' === $context ) : ?>
			<?php
				$order_key = $order->get_order_key();
				$get_order_status_url = add_query_arg(
					[
						'key' => $order_key,
						'_nonce' => wp_create_nonce( 'get_order_status_' . $order_key ),
						'_wpnonce' => wp_create_nonce('wp_rest'),
					],
					get_rest_url( null, 'omise/order-status')
				);
			?>
			<div class="omise omise-paynow-details" <?php echo 'email' === $context ? 'style="margin-bottom: 4em; text-align:center;"' : ''; ?>>
				<div class="omise omise-paynow-logo"></div>
				<p>
					<?php echo __( 'Scan the QR code to pay', 'omise' ); ?>
				</p>
				<div class="omise omise-paynow-qrcode">
					<img src="<?php echo $qrcode; ?>" alt="Omise QR code ID: <?php echo $charge['source']['scannable_code']['image']['id']; ?>">
				</div>
				<div class="omise-paynow-payment-status">
					<div class="pending">
						<?php echo __( 'Payment session will time out in <span id="timer">10:00</span> minutes.', 'omise' ); ?>
					</div>
					<div class="completed" style="display:none">
						<div class="green-check"></div>
						<?php echo __( 'We\'ve received your payment.', 'omise' ); ?>
					</div>
					<div class="timeout" style="display:none">
						<?php echo __( 'Payment session timed out. You can still complete QR payment by scanning the code sent to your email address.', 'omise' ); ?>
					</div>
				</div>
			</div>
			<script type="text/javascript">
				<!--
				var classPaymentPending   = document.getElementsByClassName("pending");
				var classPaymentCompleted = document.getElementsByClassName("completed");
				var classPaymentTimeout   = document.getElementsByClassName("timeout");
				var classQrImage          = document.querySelector(".omise.omise-paynow-qrcode > img");

				var refreshPaymentStatus = function(intervalIterator) {
					var xmlhttp = new XMLHttpRequest();
					xmlhttp.addEventListener("load", function() {
						if (this.status == 200) {
							var chargeState = JSON.parse(this.responseText);
							if (chargeState.status == "processing") {
								classQrImage.style.display = "none";
								classPaymentPending[0].style.display = "none";
								classPaymentCompleted[0].style.display = "block";
								clearInterval(intervalIterator);
							}
						} else if (this.status == 403) {
							clearInterval(intervalIterator);
						}
					});
					xmlhttp.open('GET', '<?php echo $get_order_status_url ?>', true);
					xmlhttp.send();
				},
				intervalTime = function(duration, display) {
					var timer    = duration, minutes, seconds;
					intervalIterator = setInterval(function () {
						minutes      = parseInt(timer / 60, 10);
						seconds      = parseInt(timer % 60, 10);
						minutes = minutes < 10 ? "0" + minutes : minutes;
						seconds = seconds < 10 ? "0" + seconds : seconds;
						display.textContent = minutes + ":" + seconds;
						if (--timer < 0) {
							timer = duration;
						}
						if((timer % 5) == 0 && timer >= 5) {
							refreshPaymentStatus(intervalIterator);
						}
						if(timer == 0) {
							classPaymentPending[0].style.display = "none";
							classPaymentTimeout[0].style.display = "block";
							classQrImage.style.display = "none";
							clearInterval(intervalIterator);
						}
					}, 1000);
				};

				window.onload = function () {
					var duration = 60 * 10,
					    display  = document.querySelector('#timer');
					intervalTime(duration, display);
				};
			//-->
			</script>
		<?php elseif ( 'email' === $context && !$order->has_status('failed')) : ?>
			<p>
				<?php echo __( 'Scan the QR code to complete', 'omise' ); ?>
			</p>
			<p><img src="<?php echo $qrcode; ?>"/></p>
		<?php endif;
	}
}
