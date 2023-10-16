<?php
defined('ABSPATH') or die('No direct script access allowed.');

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
        $this->method_title       = __('Opn Payments Atome', 'omise');
        $this->method_description = wp_kses(
            __('Accept payments through <strong>Atome</strong> via Opn Payments payment gateway.', 'omise'),
            ['strong' => []]
        );

        $this->supports           = ['products', 'refunds'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title                = $this->get_option('title');
        $this->description          = $this->get_option('description');
        $this->restricted_countries = ['TH', 'SG', 'MY'];
        $this->source_type          = 'atome';

        $this->register_omise_atome_scripts();

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_api_' . $this->id . '_callback', 'Omise_Callback::execute');
        add_action('woocommerce_order_action_' . $this->id . '_sync_payment', array($this, 'sync_payment'));
    }

    /**
     * @see WC_Settings_API::init_form_fields()
     * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __('Enable/Disable', 'omise'),
                'type'    => 'checkbox',
                'label'   => __('Enable Opn Payments Atome Payment', 'omise'),
                'default' => 'no'
            ),

            'title' => array(
                'title'       => __('Title', 'omise'),
                'type'        => 'text',
                'description' => __('This controls the title the user sees during checkout.', 'omise'),
                'default'     => __('Atome', 'omise'),
            ),

            'description' => array(
                'title'       => __('Description', 'omise'),
                'type'        => 'textarea',
                'description' => __('This controls the description the user sees during checkout.', 'omise')
            ),
        );
    }

    private function register_omise_atome_scripts() {
		wp_enqueue_script(
			'omise-atome-js',
			plugins_url( '../assets/javascripts/omise-payment-atome.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			WC_VERSION,
			true
		);
	}

    /**
     * @inheritdoc
     */
    public function payment_fields()
    {
        parent::payment_fields();
        $viewData = $this->validateAtomeRequest();

        Omise_Util::render_view('templates/payment/form-atome.php', $viewData);
    }

    private function validateAtomeRequest()
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
        $cart = WC()->cart;

        if ($cart->subtotal === 0) {
            return [
                'status' => false,
                'message' => 'Complimentary products cannot be billed.'
            ];
        }

        if (!isset($limits[$currency])) {
            return [
                'status' => false,
                'message' => 'Currency not supported'
            ];
        }

        $limit = $limits[$currency];

        if ($cart->total < $limit['min']) {
            return [
                'status' => false,
                'message' => sprintf(
                    "Amount must be greater than %u %s",
                    number_format($limit['min'], 2),
                    strtoupper($currency)
                )
            ];
        }

        if ($cart->total > $limit['max']) {
            return [
                'status' => false,
                'message' => __(
                    'Amount must be less than %1 %2',
                    number_format($limit['max'], 2),
                    strtoupper($currency)
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
        $requestData = $this->get_charge_request($order_id, $order);
        return OmiseCharge::create($requestData);
    }

    public function get_charge_request($order_id, $order)
	{
        $requestData = $this->build_charge_request(
			$order_id,
			$order,
			$this->source_type,
			$this->id . "_callback"
		);
        
        $default_phone_selected = isset($_POST['omise_atome_phone_default']) ?
            $_POST['omise_atome_phone_default']
            : false;
        $phone_number = (bool)$default_phone_selected ?
            $order->get_billing_phone()
            : sanitize_text_field($_POST['omise_atome_phone_number']);
		$requestData['source'] = array_merge($requestData['source'], [
			'phone_number' => $phone_number,
            'shipping' => $this->getAddress($order),
            'items' => $this->getItems($order, $order->get_currency())
		]);

        return $requestData;
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
            // item don't have price. So we have to take subtotal and divide it by quantity to get the price
            $pricePerItem = $item['subtotal'] / $item['qty'];

            // Remove product from the list if the price is 0
            if ((float)$item['subtotal'] === 0.00) {
                continue;
            }

            // Check if product has variation.
            $product = $item['variation_id'] ?
                new WC_Product_Variation($item['variation_id'])
                : new WC_Product($item['product_id']);

            $sku = $product->get_sku();

            $products[$key] = [
                'quantity' => $item['qty'],
                'name' => $item['name'],
                'amount' => Omise_Money::to_subunit($pricePerItem, $currency),
                'sku' => empty($sku) ? $product->get_id() : $sku // if sku is not present then pass productId
            ];
        }

        return $products;
    }

    /**
	 * Get icons
	 *
	 * @see WC_Payment_Gateway::get_icon()
	 */
	public function get_icon() {
		$icon = Omise_Image::get_image([
			'file' => 'atome.png',
			'alternate_text' => 'Atome logo',
			'width' => 20,
			'height' => 20
		]);
		return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
	}
}
