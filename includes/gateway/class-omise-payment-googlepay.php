<?php
defined('ABSPATH') or die('No direct script access allowed.');

class Omise_Payment_GooglePay extends Omise_Payment_Creditcard
{
    public function __construct()
    {
        parent::__construct();

        $this->id = 'omise_googlepay';
        $this->has_fields = true;
        $this->method_title = __('Omise Google Pay', 'omise');
        $this->method_description = wp_kses(
            __('Accept payments through <strong>Google Pay</strong> via Omise payment gateway.', 'omise'),
            array('strong' => array())
        );

        $this->supports = array('products', 'refunds');

        $this->init_form_fields();
        $this->init_settings();
        $this->register_omise_googlepay_scripts();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->restricted_countries = array('TH');

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
                'title' => __('Enable/Disable', 'omise'),
                'type' => 'checkbox',
                'label' => __('Enable Omise Google Pay Payment', 'omise'),
                'default' => 'no'
            ),

            'merchant_id' => array(
                'title' => __('Google Pay Merchant ID', 'omise'),
                'type' => 'text',
                'description' => __('The merchant ID will be available after registering with the <a href="https://pay.google.com/business/console">Google Pay Business Console</a>.', 'omise')
            ),

            'request_billing_address' => array(
                'title' => __('Google Pay Request Billing Address', 'omise'),
                'type' => 'checkbox',
                'description' => __('Request customer\'s name and billing address from their Google Account upon checkout.', 'omise'),
                'default' => 'no'
            ),

            'request_phone_number' => array(
                'title' => __('Google Pay Request Phone Number', 'omise'),
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
    public
    function payment_fields()
    {
        parent::payment_fields();
        Omise_Util::render_view('templates/payment/form-googlepay.php',
            array('config' => $this->google_pay_button_scripts()));
    }

    private
    function google_pay_button_scripts()
    {
        wp_enqueue_script('omise-js', 'https://cdn.staging-omise.co/omise.js', array('jquery'), WC_VERSION, true);

        return array("script" =>
            "<script type='module'>
                 const button = document.createElement('google-pay-button')
                 button.setAttribute('environment', 'TEST')
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
                                 allowedCardNetworks: ['VISA', 'MASTERCARD', 'AMEX'],
                                 billingAddressRequired: " . ($this->get_option('request_billing_address') == 'yes' ? 'true' : 'false') . ",
                                 billingAddressParameters: {
                                     format: 'FULL',
                                     phoneNumberRequired: " . ($this->get_option('request_billing_address') == 'yes' ? 'true' : 'false') . ",
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
                         currencyCode: 'THB',
                     },
                 }

                 const div = document.querySelector('#googlepay-button-container')                 
                 div.appendChild(button)                 

                 button.addEventListener('loadpaymentdata', event => {              
                    const params = {
                        method: 'googlepay',
                        data: JSON.stringify(JSON.parse(event.detail.paymentMethodData.tokenizationData.token))
                    }					
                    Omise.setPublicKey('" . $this->public_key() . "')
					Omise.createToken('tokenization', params, (statusCode, response) => {
                        if (statusCode == 200) {
                            const form = document.querySelector('form.checkout')
                            const input = document.createElement('input')           
                            input.setAttribute('type', 'hidden' )
                            input.setAttribute('class', 'omise_token' )
                            input.setAttribute('name', 'omise_token' )
                            input.setAttribute('value', response.id)
                            form.appendChild(input) 
                        }
                    })
                 })
            </script>");
    }
}
