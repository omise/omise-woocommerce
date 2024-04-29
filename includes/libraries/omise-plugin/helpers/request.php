<?php
if (!class_exists('RequestHelper')) {
    class RequestHelper
    {
        /**
         * Check whether the request is a user-originated operation or not.
         * For example: entering a URL into the address bar, opening a bookmark,
         * or dragging-and-dropping a file into the browser window.
         * 
         * Ref: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Site
         */
        public static function is_user_originated()
        {
            $fetch_site = sanitize_text_field($_SERVER['HTTP_SEC_FETCH_SITE']);

            // "none" means the request is a user-originated operation
            return 'none' === $fetch_site;
        }

        /**
         * @param string|null $order_token
         */
        public static function validate_request($order_token = null)
        {
            $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : null;

            // For all payment except offline
            if ($token) {
                return $token === $order_token;
            }

            // For offline payment methods does not include token in the return URI.
            return !self::is_user_originated();
        }

        public static function get_client_ip()
        {
            $headersToCheck = [
                // Check for a client using a shared internet connection
                'HTTP_CLIENT_IP',

                // Check if the proxy is used for IP/IPs
                'HTTP_X_FORWARDED_FOR',

                // check for other possible forwarded IP headers
                'HTTP_X_FORWARDED',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
            ];

            foreach($headersToCheck as $header) {
                if (empty($_SERVER[$header])) {
                    continue;
                }

                if ($header === 'HTTP_X_FORWARDED_FOR') {
                    return self::process_forwarded_for_header($_SERVER[$header]);
                }

                return $_SERVER[$header];
            }

            // return default remote IP address
            return $_SERVER['REMOTE_ADDR'];
        }

        private static function process_forwarded_for_header($forwardedForHeader)
        {
            // Split if multiple IP addresses exist and get the last IP address
            if (strpos($forwardedForHeader, ',') !== false) {
                $multiple_ips = explode(",", $forwardedForHeader);
                return trim(current($multiple_ips));
            }
            return $forwardedForHeader;
        }
    }
}
