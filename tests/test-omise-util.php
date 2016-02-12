<?php

require_once "omise-util.php";

class Omise_Util_Test extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
        $this->expected = "192.168.1.1";
    }

    public function tearDown() {
        $_SERVER["HTTP_CLIENT_IP"]       = "";
        $_SERVER["HTTP_X_FORWARDED_FOR"] = "";
        $_SERVER["HTTP_X_FORWARDED"]     = "";
        $_SERVER["HTTP_FORWARDED_FOR"]   = "";
        $_SERVER["HTTP_FORWARDED"]       = "";
        $_SERVER["REMOTE_ADDR"]          = "";
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

    function test_get_client_ip_should_return_ip_from_http_x_forwarded_if_set() {
        $_SERVER["HTTP_X_FORWARDED"] = "192.168.1.1";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $this->expected, $ip );
    }

    function test_get_client_ip_should_return_ip_from_http_forwarded_for_if_set() {
        $_SERVER["HTTP_FORWARDED_FOR"] = "192.168.1.1";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $this->expected, $ip );
    }

    function test_get_client_ip_should_return_ip_from_http_forwarded_if_set() {
        $_SERVER["HTTP_FORWARDED"] = "192.168.1.1";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $this->expected, $ip );
    }

    function test_get_client_ip_should_return_ip_from_remote_addr_if_set() {
        $_SERVER["REMOTE_ADDR"] = "192.168.1.1";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $this->expected, $ip );
    }

    function test_get_client_ip_should_return_unknown_if_server_variable_is_not_set() {
        $this->expected = "UNKNOWN";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $this->expected, $ip );
    }
}
