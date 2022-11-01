<?php
if (! class_exists('RequestHelper')) {
    class RequestHelper
    {
        /**
         * Check whether the request is a user-originated operation or not.
         * For example: entering a URL into the address bar, opening a bookmark,
         * or dragging-and-dropping a file into the browser window.
         * 
         * Ref: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Site
         */
        public static function isUserOriginated()
        {
            $fetch_site = sanitize_text_field($_SERVER['HTTP_SEC_FETCH_SITE']);

            // "none" means the request is a user-originated operation
            return 'none' === $fetch_site;
        }

        /**
         * @param string|null $order_token
         */
        public static function validateRequest($order_token = null)
        {
            $token = isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : null;

            // For all payment except offline and OCBC PAO.
            if ($token) {
                return $token === $order_token;
            }

            // For offline payment methods does not include token in the return URI.
            return !self::isUserOriginated();
        }
    }
}
