<?php
defined('ABSPATH') or die('No direct script access allowed.');

class Omise_Payment_GooglePay extends Omise_Payment
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

        wp_enqueue_script(
            'omise-googlepay-button-handler',
            plugins_url('../assets/javascripts/omise-googlepay-button-handler.js', dirname(__FILE__)),
            array(),
            WC_VERSION,
            true
        );

        $params = array(
            'key' => $this->public_key(),
        );

        wp_localize_script('omise-googlepay-button-handler', 'params', $params);
    }

    /**
     * @inheritdoc
     */
    public function payment_fields()
    {
        parent::payment_fields();
        Omise_Util::render_view('templates/payment/form-googlepay.php', array());
    }

    /**
     * @inheritdoc
     */
    public function result($order_id, $order, $charge)
    {
        return;
    }

    /**
     * @inheritdoc
     */
    public function charge($order_id, $order)
    {
        return;
    }
}
