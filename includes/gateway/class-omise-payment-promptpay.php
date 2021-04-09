<?php

defined( 'ABSPATH' ) || exit;

/**
 * @since 4.1
 */
class Omise_Payment_Promptpay extends Omise_Payment_Offline {
	public function __construct() {
		parent::__construct();

		$this->id                 = 'omise_promptpay';
		$this->has_fields         = false;
		$this->method_title       = __( 'Omise PromptPay', 'omise' );
		$this->method_description = wp_kses(
			__( 'Accept payments through <strong>PromptPay</strong> via Omise payment gateway.', 'omise' ),
			array( 'strong' => array() )
		);

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'TH' );
		$this->source_type          = 'promptpay';

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
				'label'   => __( 'Enable Omise PromptPay Payment', 'omise' ),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( 'PromptPay', 'omise' ),
			),

			'description' => array(
				'title'       => __( 'Description', 'omise' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
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
		if ( ! $this->load_order( $order ) ) {
			return;
		}

		$charge = OmiseCharge::retrieve( $this->get_charge_id_from_order() );
		if ( self::STATUS_PENDING !== $charge['status'] ) {
			return;
		}

		$qrcode = $charge['source']['scannable_code']['image']['download_uri'];

		$expires_datetime = new WC_DateTime( $charge['expires_at'], new DateTimeZone( 'UTC' ) );
		$expires_datetime->set_utc_offset( wc_timezone_offset() );

		$nonce = wp_create_nonce( OmisePluginHelperWcOrder::get_order_key_by_id( $order ) );

		if ( 'view' === $context ) : ?>
			<div id="omise-offline-additional-details" class="omise omise-additional-payment-details-box omise-promptpay-details" <?php echo 'email' === $context ? 'style="margin-bottom: 4em; text-align:center;"' : ''; ?>>
				<p><?php echo __( 'Scan the QR code to pay', 'omise' ); ?></p>
				<div class="omise omise-promptpay-qrcode">
					<img style="margin: 0 auto;" src="<?php echo $qrcode; ?>" alt="Omise QR code ID: <?php echo $charge['source']['scannable_code']['image']['id']; ?>">
				</div>
				<div>
					<?php echo __( 'Payment expires in: ', 'omise' ); ?>
					<?php echo wc_format_datetime( $expires_datetime, wc_date_format() ); ?>
					<?php echo wc_format_datetime( $expires_datetime, wc_time_format() ); ?>
				</div>

				<div id="omise-offline-payment-timeout" style="margin-top: 2em; display: none;">
					<p><button id="omise-offline-payment-refresh-status">refresh status</button></p>
				</div>
			</div>

			<div id="omise-offline-payment-result" class="omise-additional-payment-details-box" style="display: none;"></div>

			<script>
				( function( $ ) {
					let initializeTimer = function() {
						console.log('cc');
						validatePaymentResultTimerId = setInterval( validatePaymentResult, intervalTime );
						setTimeout( function() { clearInterval( validatePaymentResultTimerId ); }, maxIntervalTime );

						// 10 minutes
						watch( 60, $( '#omise-timer' ) );
						return validatePaymentResultTimerId;
					}

					let watch = function( duration, display ) {
						let timer = duration, minutes, seconds;
							timeInterval = setInterval( function () {
								minutes = parseInt( timer / 60, 10 )
								seconds = parseInt( timer % 60, 10 );

								minutes = minutes < 10 ? "0" + minutes : minutes;
								seconds = seconds < 10 ? "0" + seconds : seconds;

								display.text( minutes + ":" + seconds );

								if ( --timer < 0 ) { clearInterval(timeInterval); }
							}, 1000);
					}

					let elementRefreshButton         = $( '#omise-offline-payment-refresh-status' ),
					    elementOmiseOfflineDetails   = $( '#omise-offline-additional-details' ),
					    elementTimeoutMessage        = $( '#omise-offline-payment-timeout' ),
					    elementPaymentResult         = $( '#omise-offline-payment-result' ),
					    validatePaymentResultCount   = 0;
					    validatePaymentResultTimerId = null,
					    intervalTime                 = 10000; // 10 seconds
					    maxIntervalTime              = 600000; // 10 minutes
					    validatePaymentResult        = function() {
							validatePaymentResultCount++;

							$.ajax({
								type: 'POST',
								url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
								dataType: 'json',
								data: {
									'action': 'fetch_order_status',
									'order_id': '<?php echo $order; ?>',
									'nonce': '<?php echo $nonce ?>'
								},
								success: function( response ) {
									if ( 'processing' === response.data.order_status ) {
										elementOmiseOfflineDetails.fadeOut();
										elementPaymentResult.html('<h4><?php echo __( "Payment successful", "omise" ); ?></h4><p><?php echo __( "Thank you. Your order has been paid.", "omise" ); ?></p>').fadeIn();
										clearInterval( validatePaymentResultTimerId );
										return;
									} else if ( 'failed' === response.data.order_status ) {
										elementOmiseOfflineDetails.fadeOut();
										elementPaymentResult.html('<h4><?php echo __( "Payment failed", "omise" ); ?></h4><p><?php echo __( "Please try placing an order with a different payment method.", "omise" ); ?></p>').fadeIn();
										clearInterval( validatePaymentResultTimerId );
										return;
									}
								},
								error: function( response ) { console.log(response); },
								complete: function (response ) {
									if ( validatePaymentResultCount * intervalTime >= maxIntervalTime ) {
										elementTimeoutMessage.fadeIn();
										console.log('stop');
										return;
									}
								}
							});
						};

					elementRefreshButton.click( function() {
						validatePaymentResultCount   = 0;
						validatePaymentResultTimerId = initializeTimer();

						elementTimeoutMessage.fadeOut();
					} );

					$( document ).ready( function() {
						validatePaymentResultCount   = 0;
						validatePaymentResultTimerId = initializeTimer();
					});
				})(jQuery);
			</script>
		<?php elseif ( 'email' === $context ) : ?>
			<div>
				<?php
				echo sprintf(
					wp_kses(
						__( 'Please scan the QR code and pay before: <strong>%1$s %2$s</strong>', 'omise' ),
						array( 'strong' => array() )
					),
					wc_format_datetime( $expires_datetime, wc_date_format() ),
					wc_format_datetime( $expires_datetime, wc_time_format() )
				);
				?>
			</div>
			<p><a href="<?php echo $qrcode; ?>"><?php echo __( 'Click this link to display the QR code', 'omise' ); ?></a></p>
		<?php endif;
	}
}
