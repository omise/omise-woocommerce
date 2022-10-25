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
            $fetchSite = sanitize_text_field($_SERVER['HTTP_SEC_FETCH_SITE']);

            // "none" means the request is a user-originated operation
            return 'none' === $fetchSite;
        }

        /**
         * @param string|null $orderToken
         */
        public static function validateRequest($orderToken = null)
        {
            $token = isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : null;

            // For all payment except offline and OCBC PAO.
            if ($token) {
                return $token === $orderToken;
            }

            // For offline payment methods and OCBC PAO that does not include token in the return URI.
            return !self::isUserOriginated();
        }
    }
}
