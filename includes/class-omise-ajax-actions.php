<?php

defined( 'ABSPATH' ) || exit;

/**
 * @since 4.1
 */
class Omise_Ajax_Actions {
    /**
     * An extra action using to fetch an order status
     * from a given order_id.
     *
     * @action wp_ajax_nopriv_fetch_order_status
     * @action wp_ajax_fetch_order_status
     *
     * @see    omise-woocommerce.php
     * @see    Omise::register_ajax_actions()
     */
    public static function fetch_order_status() {
        $order_id = wc_get_order( $_POST['order_id'] );
        $order_key = OmisePluginHelperWcOrder::get_order_key_by_id( $order_id );

        if ( ! wp_verify_nonce( $_POST['nonce'], $order_key ) ) {
            die ( 'Busted!');
        }

        wp_send_json_success( array( 'order_status' => $order_id->get_status() ) );
    }
}
