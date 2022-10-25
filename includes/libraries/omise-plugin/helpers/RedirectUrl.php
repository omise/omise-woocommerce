<?php
if (! class_exists('RedirectUrl')) {
    class RedirectUrl
    {
        private static $token;

        public static function create($callbackUri, $orderId)
        {
            self::$token = Token::random();
            return add_query_arg(
                [
                    'wc-api'   => $callbackUri,
                    'order_id' => $orderId,
                    'token' => self::$token
                ],
                'https://localhost:8000'// home_url()
            );
        }

        public static function getToken()
        {
            return self::$token;
        }
    }
}
