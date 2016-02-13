<?php

require_once "omise-util.php";

class Omise_Util_Test extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
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

        $expected = "192.168.1.1";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $expected, $ip );
    }

    function test_get_client_ip_should_return_ip_from_http_x_forwarded_for_if_set() {
        $_SERVER["HTTP_X_FORWARDED_FOR"] = "192.168.1.1";

        $expected = "192.168.1.1";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $expected, $ip );
    }

    function test_get_client_ip_should_return_ip_from_http_x_forwarded_if_set() {
        $_SERVER["HTTP_X_FORWARDED"] = "192.168.1.1";

        $expected = "192.168.1.1";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $expected, $ip );
    }

    function test_get_client_ip_should_return_ip_from_http_forwarded_for_if_set() {
        $_SERVER["HTTP_FORWARDED_FOR"] = "192.168.1.1";

        $expected = "192.168.1.1";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $expected, $ip );
    }

    function test_get_client_ip_should_return_ip_from_http_forwarded_if_set() {
        $_SERVER["HTTP_FORWARDED"] = "192.168.1.1";

        $expected = "192.168.1.1";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $expected, $ip );
    }

    function test_get_client_ip_should_return_ip_from_remote_addr_if_set() {
        $_SERVER["REMOTE_ADDR"] = "192.168.1.1";

        $expected = "192.168.1.1";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $expected, $ip );
    }

    function test_get_client_ip_should_return_unknown_if_server_variable_is_not_set() {
        $expected = "UNKNOWN";
        $ip = Omise_Util::get_client_ip();
        $this->assertEquals( $expected, $ip );
    }

    function test_render_view_should_require_view_path_and_render_it_correctly() {
        $viewPath = "includes/templates/omise-payment-form.php";

        ob_start();
        Omise_Util::render_view( $viewPath, NULL );
        $actual = ob_get_clean();

        $expected = '<div id="omise_cc_form">';
        $this->assertContains( $expected, $actual );

        $expected = '<fieldset id="new_card_form" class="">';
        $this->assertContains( $expected, $actual );

        $expected = '<label for="omise_card_name">Name <span class="required">*</span></label>';
        $this->assertContains( $expected, $actual );
        $expected = '<input id="omise_card_name" class="input-text" type="text"';
        $this->assertContains( $expected, $actual );
        $expected = 'maxlength="255" autocomplete="off" placeholder="Name"';
        $this->assertContains( $expected, $actual );
        $expected = 'name="omise_card_name">';
        $this->assertContains( $expected, $actual );

        $expected = '<label for="omise_card_number">Card Number <span class="required">*</span></label>';
        $this->assertContains( $expected, $actual );
        $expected = '<input id="omise_card_number" class="input-text" type="text"';
        $this->assertContains( $expected, $actual );
        $expected = 'maxlength="20" autocomplete="off" placeholder="Card number"';
        $this->assertContains( $expected, $actual );
        $expected = 'name="omise_card_number">';
        $this->assertContains( $expected, $actual );

        $expected = '<label for="omise_card_expiration_month">Expiration month <span';
        $this->assertContains( $expected, $actual );
        $expected = '<input id="omise_card_expiration_month" class="input-text" type="text"';
        $this->assertContains( $expected, $actual );
        $expected = 'autocomplete="off" placeholder="MM" name="omise_card_expiration_month">';
        $this->assertContains( $expected, $actual );

        $expected = '<label for="omise_card_expiration_year">Expiration year <span';
        $this->assertContains( $expected, $actual );
        $expected = '<input id="omise_card_expiration_year" class="input-text" type="text"';
        $this->assertContains( $expected, $actual );
        $expected = 'autocomplete="off" placeholder="YYYY"';
        $this->assertContains( $expected, $actual );
        $expected = 'name="omise_card_expiration_year">';
        $this->assertContains( $expected, $actual );

        $expected = '<label for="omise_card_security_code">Security Code <span';
        $this->assertContains( $expected, $actual );
        $expected = '<input id="omise_card_security_code"';
        $this->assertContains( $expected, $actual );
        $expected = 'class="input-text" type="password" autocomplete="off"';
        $this->assertContains( $expected, $actual );
        $expected = 'placeholder="CVC" name="omise_card_security_code">';
        $this->assertContains( $expected, $actual );
    }
}
