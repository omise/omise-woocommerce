<?php
if (! class_exists('Token')) {
    class Token
    {
        public static function random($length = 32)
        {
            // For PHP 7.0 and up
            if (function_exists('random_bytes')) {
                return bin2hex(random_bytes($length));
            }

            // For PHP 7.0 and down
            if (function_exists('mcrypt_create_iv')) {
                return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
            }
        }
    }
}
