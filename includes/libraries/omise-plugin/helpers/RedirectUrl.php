<?php
if (! class_exists('RedirectUrl')) {
    class RedirectUrl
    {
        private static $token;

        /**
         * @param string $callbackUri
         * @param string $orderId
         */
        public static function create($callbackUri, $orderId)
        {
            self::$token = Token::random();
            return add_query_arg(
                [
                    'wc-api'   => $callbackUri,
                    'order_id' => $orderId,
                    'token' => self::$token
                ],
                home_url()
            );
        }

        /**
         * Get the token created on create. This should be called after create()
         */
        public static function getToken()
        {
            if(!self::$token) {
                throw new \LogicException('Could be called after RedirectUrl::create() method.');
            }

            return self::$token;
        }
    }
}
