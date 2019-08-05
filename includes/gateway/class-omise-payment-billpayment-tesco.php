<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

function register_omise_billpayment_tesco() {
	require_once dirname( __FILE__ ) . '/class-omise-payment.php';

	if ( ! class_exists( 'WC_Payment_Gateway' ) || class_exists( 'Omise_Payment_Billpayment_Tesco' ) ) {
		return;
	}

	/**
	 * @since 3.7
	 */
	class Omise_Payment_Billpayment_Tesco extends Omise_Payment {
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

			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'display_barcode' ) );
			add_action( 'woocommerce_email_after_order_table', array( $this, 'email_barcode' ) );
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
		 * @inheritdoc
		 */
		public function charge( $order_id, $order ) {
			$total      = $order->get_total();
			$currency   = $order->get_order_currency();
			$return_uri = add_query_arg(
				array( 'wc-api' => 'omise_billpayment_tesco_callback', 'order_id' => $order_id ), home_url()
			);
			$metadata   = array_merge(
				apply_filters( 'omise_charge_params_metadata', array(), $order ),
				array( 'order_id' => $order_id ) // override order_id as a reference for webhook handlers.
			);

			return OmiseCharge::create( array(
				'amount'      => Omise_Money::to_subunit( $total, $currency ),
				'currency'    => $currency,
				'description' => apply_filters( 'omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order ),
				'source'      => array( 'type' => 'bill_payment_tesco_lotus' ),
				'return_uri'  => $return_uri,
				'metadata'    => $metadata
			) );
		}

		/**
		 * @inheritdoc
		 */
		public function result( $order_id, $order, $charge ) {
			if ( 'failed' == $charge['status'] ) {
				return $this->payment_failed( $charge['failure_message'] . ' (code: ' . $charge['failure_code'] . ')' );
			}

			if ( 'pending' == $charge['status'] ) {
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
		public function display_barcode( $order, $context = 'view' ) {
			if ( ! $order = $this->load_order( $order ) ) {
				return;
			}

			$charge_id    = $this->get_charge_id_from_order();
			$charge       = OmiseCharge::retrieve( $charge_id );
			$barcode_svg  = file_get_contents( $charge['source']['references']['barcode'] );
			$barcode_html = $this->barcode_svg_to_html( $barcode_svg );
			?>

			<div class="omise omise-billpayment-tesco-details" <?php echo 'email' === $context ? 'style="margin-bottom: 4em; text-align:center;"' : ''; ?>>
				<p><?php echo __( 'Use this barcode to pay at Tesco Lotus.', 'omise' ); ?></p>
				<div class="omise-billpayment-tesco-barcode">
					<?php if ( 'email' === $context ) : ?>
						<?php echo $barcode_html; ?>
					<?php else : ?>
						<?php echo $barcode_svg; ?>
					<?php endif; ?>
				</div>
				<small class="omise-billpayment-tesco-reference-number">
					<?php
					echo sprintf(
						'| &nbsp; %1$s &nbsp; 00 &nbsp; %2$s &nbsp; %3$s &nbsp; %4$s',
						$charge['source']['references']['omise_tax_id'],
						$charge['source']['references']['reference_number_1'],
						$charge['source']['references']['reference_number_2'],
						$charge['amount']
					);
					?>
				</small>
			</div>
			<?php
		}

		public function email_barcode( $order ) {
			$this->display_barcode( $order, 'email' );
		}

		/**
		 * Convert a given SVG Bill Payment Tesco's barcode to HTML format.
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

			// Get data from all <rect> nodes.
			foreach ( $xml->g->g->children() as $rect ) {
				$attributes = $rect->attributes();
				$width      = $attributes['width'];
				$margin     = ( $attributes['x'] - $prevX - $prevWidth ) . 'px';

				//set html attributes based on SVG attributes
				$divRect = $xhtml->createElement( 'div' );
				$divRect->setAttribute( 'style', "float:left; position:relative; height:50px; width:$width; background-color:#000; margin-left:$margin" );
				$xhtml->appendChild( $divRect );

				$prevX     = $attributes['x'];
				$prevWidth = $attributes['width'];
			}

			// Add outer empty div tag to clear 'float' css property.
			$div = $xhtml->createElement( 'div' );
			$div->setAttribute( 'style', 'clear:both' );
			$xhtml->appendChild( $div );

			return $xhtml->saveXML( null, LIBXML_NOEMPTYTAG );
		}
	}

	if ( ! function_exists( 'add_omise_billpayment_tesco' ) ) {
		/**
		 * @param  array $methods
		 *
		 * @return array
		 */
		function add_omise_billpayment_tesco( $methods ) {
			$methods[] = 'Omise_Payment_Billpayment_Tesco';
			return $methods;
		}

		add_filter( 'woocommerce_payment_gateways', 'add_omise_billpayment_tesco' );
	}
}
