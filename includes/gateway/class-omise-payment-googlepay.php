<?php
defined('ABSPATH') or die('No direct script access allowed.');

class Omise_Payment_GooglePay extends Omise_Payment_Base_Card {
    public function __construct() {
        Omise_Payment::__construct();

        $this->id = 'omise_googlepay';
        $this->has_fields = true;
        $this->method_title = __('Opn Payments Google Pay', 'omise');
        $this->method_description = wp_kses(
            __('Accept payments through <strong>Google Pay</strong> via Opn Payments payment gateway.', 'omise'),
            array('strong' => array())
        );

        $this->supports = array('products', 'refunds');

        $this->init_form_fields();
        $this->init_settings();
        $this->register_omise_googlepay_scripts();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->payment_action       = $this->get_option('payment_action');
        $this->restricted_countries = array('TH', 'JP', 'SG', 'MY');
        $this->source_type	        = 'googlepay';

        add_action('woocommerce_api_' . $this->id . '_callback', 'Omise_Callback::execute');
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('wp_enqueue_scripts', array($this, 'omise_scripts'));
        add_action('woocommerce_order_action_' . $this->id . '_charge_capture', array($this, 'process_capture'));
        add_action('woocommerce_order_action_' . $this->id . '_sync_payment', array($this, 'sync_payment'));
    }

    /**
     * @see WC_Settings_API::init_form_fields()
     * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
     */
    public function init_form_fields() {
        $this->form_fields = array_merge(
            array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'omise'),
                    'type' => 'checkbox',
                    'label' => __('Enable Opn Payments Google Pay Payment', 'omise'),
                    'default' => 'no'
                ),

                'merchant_id' => array(
                    'title' => __('Merchant ID', 'omise'),
                    'type' => 'text',
                    'description' => __('The merchant ID will be available after registering with the <a href="https://pay.google.com/business/console">Google Pay Business Console</a>. (Not needed for test mode)', 'omise')
                ),

                'request_billing_address' => array(
                    'title' => __('Request Billing Address', 'omise'),
                    'type' => 'checkbox',
                    'description' => __('Request customer\'s name and billing address from their Google Account upon checkout.', 'omise'),
                    'default' => 'no'
                ),

                'request_phone_number' => array(
                    'title' => __('Request Phone Number', 'omise'),
                    'type' => 'checkbox',
                    'description' => __('Request customer\'s phone number from their Google Account upon checkout when billing address is requested.', 'omise'),
                    'default' => 'no'
                ),

                'title' => array(
                    'title' => __('Title', 'omise'),
                    'type' => 'text',
                    'description' => __('This controls the title the user sees during checkout.', 'omise'),
                    'default' => __('Google Pay', 'omise'),
                ),

                'description' => array(
                    'title' => __('Description', 'omise'),
                    'type' => 'textarea',
                    'description' => __('This controls the description the user sees during checkout.', 'omise')
                ),
            ),
            array(
                'advanced' => array(
                    'title'       => __('Advanced Settings', 'omise'),
                    'type'        => 'title'
                ),
                'payment_action' => array(
                    'title'       => __('Payment action', 'omise'),
                    'type'        => 'select',
                    'description' => __('Capture automatically during the checkout process or manually after order has been placed', 'omise'),
                    'default'     => self::PAYMENT_ACTION_AUTHORIZE_CAPTURE,
                    'class'       => 'wc-enhanced-select',
                    'options'     => array(
                        self::PAYMENT_ACTION_AUTHORIZE_CAPTURE => __('Auto Capture', 'omise'),
                        self::PAYMENT_ACTION_AUTHORIZE         => __('Manual Capture', 'omise')
                    ),
                    'desc_tip'    => true
                ),
                'accept_visa' => array(
                    'title'       => __('Supported card networks', 'omise'),
                    'type'        => 'checkbox',
                    'label'       => Omise_Card_Image::get_visa_image(),
                    'css'         => Omise_Card_Image::get_css(),
                    'default'     => Omise_Card_Image::get_visa_default_display()
                ),
                'accept_mastercard' => array(
                    'type'        => 'checkbox',
                    'label'       => Omise_Card_Image::get_mastercard_image(),
                    'css'         => Omise_Card_Image::get_css(),
                    'default'     => Omise_Card_Image::get_mastercard_default_display()
                ),
                'accept_jcb' => array(
                    'type'        => 'checkbox',
                    'label'       => Omise_Card_Image::get_jcb_image(),
                    'css'         => Omise_Card_Image::get_css(),
                    'default'     => Omise_Card_Image::get_jcb_default_display()
                ),
                'accept_amex' => array(
                    'type'        => 'checkbox',
                    'label'       => Omise_Card_Image::get_amex_image(),
                    'css'         => Omise_Card_Image::get_css(),
                    'default'     => Omise_Card_Image::get_amex_default_display(),
                    'description' => wp_kses(
                        __('This only controls the allowed card networks GooglePay will allow the customer to select.
                        <br />It is not related to card processing on Opn Payments payment gateway.
                        <br />Note: This payment method will not be available on the checkout page if no card network is selected.', 'omise'),
                        array('br' => array())
                    )
                )
            )
        );
    }

    /**
     * Register all required javascripts
     */
    private function register_omise_googlepay_scripts()
    {
        wp_enqueue_script(
            'googlepay-button-component',
            plugins_url('../assets/javascripts/googlepay-button-index.umd.min.js', dirname(__FILE__)),
            array(),
            WC_VERSION,
            true
        );
    }

    /**
     * @inheritdoc
     */
    public function payment_fields() {
        Omise_Util::render_view(
            'templates/payment/form-googlepay.php',
            array('config' => $this->google_pay_button_scripts())
        );
    }

    private function google_pay_button_scripts() {
        $cardNetworks = [];
        $this->get_option('accept_amex') == 'yes' ? array_push($cardNetworks, "AMEX") : null;
        $this->get_option('accept_jcb') == 'yes' ? array_push($cardNetworks, "JCB") : null;
        $this->get_option('accept_mastercard') == 'yes' ? array_push($cardNetworks, "MASTERCARD") : null;
        $this->get_option('accept_visa') == 'yes' ? array_push($cardNetworks, "VISA") : null;
        $allowedCardNetworks = json_encode($cardNetworks);

        return array("script" =>
            "<script type='module'>
                const button = document.createElement('google-pay-button')
                button.setAttribute('environment', '" . ($this->is_test() ? 'TEST' : 'PRODUCTION') . "')
                button.setAttribute('button-type', 'pay')
                button.setAttribute('button-color', 'black')
                button.paymentRequest = {
                    apiVersion: 2,
                    apiVersionMinor: 0,
                    allowedPaymentMethods: [
                        {
                            type: 'CARD',
                            parameters: {
                                allowedAuthMethods: ['PAN_ONLY'],
                                allowedCardNetworks: " . $allowedCardNetworks . ",
                                billingAddressRequired: " . ($this->get_option('request_billing_address') == 'yes' ? 'true' : 'false') . ",
                                billingAddressParameters: {
                                    format: 'FULL',
                                    phoneNumberRequired: " . ($this->get_option('request_phone_number') == 'yes' ? 'true' : 'false') . ",
                                },
                            },
                            tokenizationSpecification: {
                                type: 'PAYMENT_GATEWAY',
                                parameters: {
                                    gateway: 'omise',
                                    gatewayMerchantId: '" . $this->public_key() . "',
                                },
                            },
                        },
                    ],
                    merchantInfo: {
                        merchantId: '" . $this->get_option('merchant_id') . "',
                    },
                    transactionInfo: {
                        totalPriceStatus: 'NOT_CURRENTLY_KNOWN',
                        currencyCode: '" . get_woocommerce_currency(). "',
                    },
                }
                
                const div = document.getElementById('googlepay-button-container')                 
                div.appendChild(button)
                
                function toggleOrderButton() {
                    const placeOrderButton = document.getElementById('place_order')
                    const paymentBox = document.getElementById('payment_method_omise_googlepay')

                    if (document.getElementsByClassName('omise-secondary-text googlepay-selected').length < 1) {
                        placeOrderButton.style.display = paymentBox.checked ? 'none' : 'inline-block'
                    }
                }
                
                toggleOrderButton()
                const paymentMethods = document.getElementsByClassName('input-radio')
                Array.from(paymentMethods).forEach((el) => {
                    el.addEventListener('click', toggleOrderButton)
                })
            </script>"
        );
    }

    /**
	 * Get icons
	 *
	 * @see WC_Payment_Gateway::get_icon()
	 */
	public function get_icon() {
		$icon = Omise_Image::get_image([
			'file' => 'googlepay.svg',
			'alternate_text' => 'Google Pay logo',
		]);
		return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
	}
}
