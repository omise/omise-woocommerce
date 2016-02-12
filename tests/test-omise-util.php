<?php

require_once "omise-util.php";

class Omise_Util_Test extends WP_UnitTestCase {
    function test_get_client_ip_should_return_ip_from_http_client_ip_if_set() {
        $_SERVER["HTTP_CLIENT_IP"] = "192.168.1.1";
        $expected = "192.168.1.1";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $expected, $ip );
    }
}
