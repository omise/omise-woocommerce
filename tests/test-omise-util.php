<?php

require_once "omise-util.php";

class Omise_Util_Test extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
        $this->expected = "192.168.1.1";
    }

    function test_get_client_ip_should_return_ip_from_http_client_ip_if_set() {
        $_SERVER["HTTP_CLIENT_IP"] = "192.168.1.1";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $this->expected, $ip );
    }

    function test_get_client_ip_should_return_ip_from_http_x_forwarded_for_if_set() {
        $_SERVER["HTTP_X_FORWARDED_FOR"] = "192.168.1.1";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $this->expected, $ip );
    }
}
