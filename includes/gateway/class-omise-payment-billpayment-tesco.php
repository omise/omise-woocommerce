<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

/**
 * @since 3.7
 */
class Omise_Payment_Billpayment_Tesco extends Omise_Payment_Offline {
	public function __construct() {
		parent::__construct();

		$this->id                 = 'omise_billpayment_tesco';
		$this->has_fields         = false;
		$this->method_title       = __( 'Omise Bill Payment: Tesco', 'omise' );
		$this->method_description = wp_kses(
			__( 'Accept payments through <strong>Tesco Bill Payment</strong> via Omise payment gateway.', 'omise' ),
			array( 'strong' => array() )
		);

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'TH' );
		$this->source_type          = 'bill_payment_tesco_lotus';

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'display_barcode' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_barcode' ) );
		add_action( 'woocommerce_order_action_' . $this->id . '_sync_payment', array( $this, 'sync_payment' ) );
		add_action( 'omise_checkout_assets', array( $this, 'omise_billpayment_checkout_assets' ) );
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
				'label'   => __( 'Enable Omise Tesco Bill Payment', 'omise' ),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( 'Bill Payment: Tesco', 'omise' ),
			),

			'description' => array(
				'title'       => __( 'Description', 'omise' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
			),
		);
	}

	/**
	 * @see Omise_Payment::omise_checkout_assets()
	 */
	public function omise_billpayment_checkout_assets() {
		wp_enqueue_style( 'omise-billpayment-print', plugins_url( '../../assets/css/omise-billpayment-print.css', __FILE__ ), array(), OMISE_WOOCOMMERCE_PLUGIN_VERSION );
	}

	/**
	 * @param int|WC_Order $order
	 * @param string       $context  pass 'email' value through this argument only for 'sending out an email' case.
	 */
	public function display_barcode( $order, $context = 'view' ) {
		if ( ! $this->load_order( $order ) ) {
			return;
		}

		$charge_id          = $this->get_charge_id_from_order();
		$charge             = OmiseCharge::retrieve( $charge_id );
		$barcode_svg        = file_get_contents( $charge['source']['references']['barcode'] );
		$barcode_html       = $this->barcode_svg_to_html( $barcode_svg );
		$barcode_ref_number = sprintf(
			'| &nbsp; %1$s &nbsp; 00 &nbsp; %2$s &nbsp; %3$s &nbsp; %4$s',
			$charge['source']['references']['omise_tax_id'],
			$charge['source']['references']['reference_number_1'],
			$charge['source']['references']['reference_number_2'],
			$charge['amount']
		);
		$expires_datetime   = new WC_DateTime( $charge['source']['references']['expires_at'], new DateTimeZone( 'UTC' ) );
		$expires_datetime->set_utc_offset( wc_timezone_offset() );
		?>

		<div class="omise omise-billpayment-tesco-details" <?php echo 'email' === $context ? 'style="margin-bottom: 4em; text-align:center;"' : ''; ?>>
			<!-- Display for printing only. -->
			<!-- Hidden by assets/css/omise-billpayment-print.css. -->
			<?php if ( 'email' !== $context ) : ?>
				<div class="omise-billpayment-tesco-print-order-overview">
					<div>
						<?php _e( 'Order number:', 'woocommerce' ); ?>
						<br/><strong><?php echo $this->order()->get_order_number(); ?></strong>
					</div>

					<div>
						<?php _e( 'Date:', 'woocommerce' ); ?>
						<br/><strong><?php echo wc_format_datetime( $this->order()->get_date_created() ); ?></strong>
					</div>

					<div>
						<?php _e( 'Total:', 'woocommerce' ); ?>
						<br/><strong><?php echo $this->order()->get_formatted_order_total(); ?></strong>
					</div>

					<?php if ( $this->order()->get_payment_method_title() ) : ?>
						<div>
							<?php _e( 'Payment method:', 'woocommerce' ); ?>
							<br/><strong><?php echo wp_kses_post( $this->order()->get_payment_method_title() ); ?></strong>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<!-- /END .omise-billpayment-tesco-print-detail -->

			<p>
				<?php
				echo sprintf(
					wp_kses(
						__( 'Please bring this barcode to pay at Tesco Lotus by:<br/><strong>%1$s %2$s</strong>.', 'omise' ),
						array( 'br' => array(), 'strong' => array() )
					),
					wc_format_datetime( $expires_datetime, wc_date_format() ),
					wc_format_datetime( $expires_datetime, wc_time_format() )
				);
				?>
			</p>

			<div class="omise-billpayment-tesco-barcode-wrapper" <?php echo 'email' === $context ? 'style="margin: 1em 0;"' : ''; ?>>
				<?php echo $barcode_html; ?>
			</div>

			<div class="omise-billpayment-tesco-barcode-reference-number">
				<small><?php echo $barcode_ref_number; ?></small>
			</div>

			<?php if ( 'email' !== $context ) : ?>
				<div class="omise-billpayment-tesco-print-button">
					<button onClick="window.print()" class="button button-primary"><?php echo __( 'Print barcode', 'omise' ); ?></button>
				</div>
			<?php endif; ?>

			<div class="omise-billpayment-tesco-footnote" <?php echo 'email' === $context ? 'style="margin-top: 4em; padding: 1em; font-size: 85%; text-align: right; background-color: #f8f8f8;"' : ''; ?>>
				<p <?php echo 'email' === $context ? 'style="margin: 0; padding: 0;"' : ''; ?>>
					<?php
					echo wp_kses(
						__(
							'
							Tesco Lotus may charge a small fee for the transaction.<br/>
							If you fail to make payment by the stated time, your order will be automatically canceled.
							', 'omise'
						),
						array( 'br' => array() )
					);
					?>
				</p>
			</div>
		</div>

		<?php if ( 'email' !== $context ) : ?>
			<script type="text/javascript">
				let omise_billpayment_detail        = document.getElementsByClassName( 'omise-billpayment-tesco-details' );
				let cloned_omise_billpayment_detail = omise_billpayment_detail[0].cloneNode( true );
					cloned_omise_billpayment_detail.classList.add( 'omise-billpayment-tesco-print-details' );

				document.body.appendChild( cloned_omise_billpayment_detail );
			</script>
		<?php endif;
	}

	/**
	 * @param WC_Order $order
	 *
	 * @see   woocommerce/templates/emails/email-order-details.php
	 * @see   woocommerce/templates/emails/plain/email-order-details.php
	 */
	public function email_barcode( $order ) {
		if ( $this->id == $order->get_payment_method() ) {
			$this->display_barcode( $order, 'email' );
		}
	}

	/**
	 * Convert a given SVG Bill Payment Tesco's barcode to HTML format.
	 *
	 * Note that the SVG barcode contains with the following structure:
	 *
	 * <?xml version="1.0" encoding="UTF-8"?>
	 * <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="515px" height="90px" viewBox="0 0 515 90" version="1.1" preserveAspectRatio="none">
	 *   <title>** reference number **</title>
	 *   <g id="canvas">
	 *     <rect x="0" y="0" width="515px" height="90px" fill="#fff" />
	 *     <g id="barcode" fill="#000">
	 *       <rect x="20" y="20" width="2px" height="50px" />
	 *       ... (repeat <rect> node for displaying barcode) ...
	 *       <rect x="493" y="20" width="2px" height="50px" />
	 *     </g>
	 *   </g>
	 * </svg>
	 *
	 * The following code in this method is to read all <rect> nodes' attributes under the <g id="barcode"></g>
	 * in order to replicate the barcode in HTML <div></div> element.
	 *
	 * @param  string $barcode_svg
	 *
	 * @return string  of a generated Bill Payment Tesco's barcode in HTML format.
	 */
	public function barcode_svg_to_html( $barcode_svg ) {
		$xml       = new SimpleXMLElement( $barcode_svg );
		$xhtml     = new DOMDocument();
		$prevX     = 0;
		$prevWidth = 0;

		$div_wrapper = $xhtml->createElement( 'div' );
		$div_wrapper->setAttribute( 'class', 'omise-billpayment-tesco-barcode' );
		$div_wrapper->setAttribute( 'style', 'background-color: #ffffff;' );

		// Read data from all <rect> nodes.
		foreach ( $xml->g->g->children() as $rect ) {
			$attributes = $rect->attributes();
			$width      = $attributes['width'];
			$margin     = ( $attributes['x'] - $prevX - $prevWidth ) . 'px';

			// Set HTML attributes based on <rect> node's attributes.
			$div_rect = $xhtml->createElement( 'div' );
			$div_rect->setAttribute( 'style', "float: left; position: relative; height: 50px; border-left: $width solid #000000; width: 0; margin-left: $margin" );
			$div_wrapper->appendChild( $div_rect );

			$prevX     = $attributes['x'];
			$prevWidth = $attributes['width'];
		}

		// Add an empty <div></div> element to clear those floating elements.
		$div = $xhtml->createElement( 'div' );
		$div->setAttribute( 'style', 'clear:both' );
		$div_wrapper->appendChild( $div );

		$xhtml->appendChild( $div_wrapper );
		return $xhtml->saveXML( null, LIBXML_NOEMPTYTAG );
	}
}
