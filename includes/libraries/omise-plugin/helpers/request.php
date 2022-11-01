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

            // For OCBC PAO as it does not include token in the return URI.
            if(self::isCallbackOcbcPao()) {
                return true;
            }

            // For offline payment methods does not include token in the return URI.
            return !self::isUserOriginated();
        }

        /**
         * Since OCBC PAO do not suppor query params, we can't add token to it. Plus, HTTP_SEC_FETCH_SITE
         * is not so reliable. So, we will return true if the callback is of OCBC PAO
         */
        private static function isCallbackOcbcPao()
        {
            $callback = basename($_SERVER['REQUEST_URI']);
            $part = explode('?', $callback);
            return 'omise_ocbc_pao_callback' === $part[0];
        }
    }
}
