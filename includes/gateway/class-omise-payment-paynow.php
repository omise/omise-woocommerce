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
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_qrcode' ) );
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
	public function email_qrcode( $order ) {
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
				var xhr_param_name          = "?order_id="+"<?php echo $this->order->get_id() ?>";
				    refresh_status_url      = "<?php echo get_rest_url( null, 'omise/paynow-payment-status' ); ?>"+xhr_param_name;
				    class_payment_pending   = document.getElementsByClassName("pending");
				    class_payment_completed = document.getElementsByClassName("completed");
					class_payment_timeout   = document.getElementsByClassName("timeout");
					class_qr_image          = document.querySelector(".omise.omise-paynow-qrcode > img");

				var refresh_payment_status = function(intervalIterator) {
					var xmlhttp = new XMLHttpRequest();
					xmlhttp.addEventListener("load", function() {
						if (this.status == 200) {
							var chargeState = JSON.parse(this.responseText);
							if(chargeState.status == "processing") {
								class_qr_image.style.display = "none";
								class_payment_pending[0].style.display = "none";
								class_payment_completed[0].style.display = "block";
								clearInterval(intervalIterator);
							}
						}
					});
					xmlhttp.open("GET", refresh_status_url, true);
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
							refresh_payment_status(intervalIterator);
						}
						if(timer == 0) {
							class_payment_pending[0].style.display = "none";
							class_payment_timeout[0].style.display = "block";
							class_qr_image.style.display = "none";
							clearInterval(intervalIterator);
						}
					}, 1000);
				};

				window.onload = function () {
					var duration = 60 * 10,
					    display  = document.querySelector('#timer');
					intervalTime(duration, display);
				};
			</script>
		<?php elseif ( 'email' === $context && !$order->has_status('failed')) : ?>
			<p>
				<?php echo __( 'Scan the QR code to complete', 'omise' ); ?>
			</p>
			<p><img src="<?php echo $qrcode; ?>"/></p>
		<?php endif;
	}
}
