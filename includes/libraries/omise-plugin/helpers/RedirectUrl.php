<?php
if (! class_exists('RedirectUrl')) {
    class RedirectUrl
    {
        private static $token;

        /**
         * @param string $callback_uri
         * @param string $order_id
         */
        public static function create($callback_uri, $order_id)
        {
            self::$token = Token::random();
            return add_query_arg(
                [
                    'wc-api'   => $callback_uri,
                    'order_id' => $order_id,
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
