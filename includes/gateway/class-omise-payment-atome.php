<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

/**
 * @since 3.9
 */
class Omise_Payment_Atome extends Omise_Payment_Offsite
{
	public function __construct()
	{
		parent::__construct();

		$this->id                 = 'omise_atome';
		$this->has_fields         = true;
		$this->method_title       = __( 'Opn Payments Atome', 'omise' );
		$this->method_description = wp_kses(
			__( 'Accept payments through <strong>Atome</strong> via Opn Payments payment gateway.', 'omise' ),
            [ 'strong' => [] ]
		);

		$this->supports           = array( 'products', 'refunds' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'TH' );
		$this->source_type          = 'atome';

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_' . $this->id . '_callback', 'Omise_Callback::execute' );
		add_action( 'woocommerce_order_action_' . $this->id . '_sync_payment', array( $this, 'sync_payment' ) );
	}

	/**
	 * @see WC_Settings_API::init_form_fields()
	 * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
	 */
	public function init_form_fields()
	{
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'omise' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Opn Payments Atome Payment', 'omise' ),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( 'Atome', 'omise' ),
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
	public function payment_fields()
	{
		parent::payment_fields();
        $viewData = $this->validateMinRequiredAmount();

		Omise_Util::render_view('templates/payment/form-atome.php', $viewData);
	}

    private function validateMinRequiredAmount()
    {
        $limits = [
            'thb' => [
                'min' => 20,
                'max' => 150000,
            ],
            'sgd' => [
                'min' => 1.5,
                'max' => 20000,
            ],
            'myr' => [
                'min' => 10,
                'max' => 100000,
            ]
        ];

        $currency = strtolower(get_woocommerce_currency());
        $cartTotal = (WC()->cart->total);

        if (!isset($limits[$currency])) {
            return [
                'status' => false,
                'message' => 'Currency not supported'
            ];
        }

        $limit = $limits[$currency];

        if ($cartTotal < $limit['min']) {
            return [
                'status' => false,
                'message' => sprintf(
                    "Amount must be greater than %u %s",
                    number_format($limit['min'], 2),
                    strtoupper($currency)
                )
            ];
        }

        if ($cartTotal > $limit['max']) {
            return [
                'status' => false,
                'message' => __(
                    'Amount must be less than %1 %2',
                    number_format($limit['max'], 2),
                    $currency
                )
            ];
        }

        return ['status' => true];
    }

	/**
	 * @inheritdoc
	 */
	public function charge($order_id, $order)
	{
		$phone_number = isset($_POST['omise_atome_phone_default'] ) && 1 == $_POST['omise_atome_phone_default'] ? $order->get_billing_phone() : sanitize_text_field( $_POST['omise_phone_number'] );
		$currency = $order->get_currency();

		return OmiseCharge::create([
			'amount' => Omise_Money::to_subunit($order->get_total(), $currency),
			'currency' => $currency,
			'description' => apply_filters('omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order),
			'source' => [
                'type' => $this->source_type,
                'phone_number' => $phone_number,
                'shipping' => $this->getAddress($order),
                'items' => $this->getItems($order, $currency)
            ],
			'return_uri' => $this->getRedirectUrl('omise_atome_callback', $order_id, $order),
			'metadata' => $this->getMetadata($order_id, $order)
		]);
	}

    private function getAddress($order)
    {
        $address = $order->get_address('billing');

        return [
            'country' => $address['country'],
            'city' => $address['city'],
            'postal_code' => $address['postcode'],
            'state' => $address['state'],
            'street1' => $address['address_1']
        ];
    }

    private function getItems($order, $currency)
    {
        $items = $order->get_items();
        $products = [];

        // Loop through ordered items
        foreach ($items as $key => $item) {
            $product_variation_id = $item['variation_id'];
        
            // Check if product has variation.
            $productId = $product_variation_id ? $item['variation_id'] : $item['product_id'];
            $product = new WC_Product($productId);

            $products[$key] = [
                'quantity' => $item['qty'],
                'name' => $item['name'],
                'amount' => Omise_Money::to_subunit($item['total'], $currency),
                'sku' => $product->get_sku()
            ];
        }

        return $products;
    }
}
