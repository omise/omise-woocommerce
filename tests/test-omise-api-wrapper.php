<?php

require_once( "omise-api-wrapper.php" );

class Omise_Test extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
    }

    function test_create_charge_should_call_method_call_api_with_required_params() {
        $omise = $this->getMockBuilder( "Omise" )
            ->setMethods( array( "call_api" ) )
            ->getMock();

        $result = '{
            "object": "charge",
            "id": "chrg_test_id",
            "livemode": false,
            "location": "/charges/chrg_test_id",
            "amount": 500,
            "currency": "thb",
            "description": "Charge for order 3947",
            "status": "successful",
            "capture": true,
            "authorized": true,
            "paid": true,
            "transaction": "trxn_test_id",
            "refunded": 0,
            "refunds": {
                "object": "list",
                "from": "1970-01-01T00:00:00+00:00",
                "to": "2016-03-02T08:30:51+00:00",
                "offset": 0,
                "limit": 20,
                "total": 0,
                "order": null,
                "location": "/charges/chrg_test_id/refunds",
                "data": []
            },
            "return_uri": "http://www.example.com/orders/3947/complete",
            "reference": "paym_test_id",
            "authorize_uri": "https://api.omise.co/payments/paym_test_id/authorize",
            "failure_code": null,
            "failure_message": null,
            "card": {
                "object": "card",
                "id": "card_test_536y8y2ghduzbchcqun",
                "livemode": false,
                "location": "/customers/customer_id/cards/card_test_id",
                "country": "us",
                "city": null,
                "postal_code": null,
                "financing": "",
                "bank": "JPMORGAN CHASE BANK, N.A.",
                "last_digits": "1111",
                "brand": "Visa",
                "expiration_month": 12,
                "expiration_year": 2019,
                "fingerprint": "test_fingerprint",
                "name": "Pronto Tools",
                "security_code_check": true,
                "created": "2016-03-01T04:27:00Z"
            },
            "customer": "customer_id",
            "ip": null,
            "dispute": null,
            "created": "2016-03-02T08:30:51Z"
        }';

        $chargeInfo = array(
            "amount"      => 500,
            "currency"    => "thb",
            "description" => "Charge for order 3947",
            "return_uri"  => add_query_arg(
                "order_id",
                3947,
                "http://www.example.com/?wc-api=wc_gateway_omise"
            )
        );

        $omise->expects( $this->once() )
            ->method( "call_api" )
            ->with( "private_key", "POST", "/charges", $chargeInfo )
            ->will( $this->returnValue( $result ) );

        $expected = json_decode( $result );
        $actual = $omise->create_charge( "private_key", $chargeInfo );

        $this->assertEquals( $expected, $actual );
    }

    function test_get_charge_should_call_method_call_api_with_required_params() {
        $omise = $this->getMockBuilder( "Omise" )
            ->setMethods( array( "call_api" ) )
            ->getMock();

        $result = '{
            "object": "charge",
            "id": "charge_id",
            "livemode": false,
            "location": "/charges/charge_id",
            "amount": 12000,
            "currency": "thb",
            "description": "WooCommerce Order id 4450",
            "status": "successful",
            "capture": true,
            "authorized": true,
            "paid": true,
            "transaction": "transaction_id",
            "refunded": 0,
            "refunds": {
                "object": "list",
                "from": "1970-01-01T00:00:00+00:00",
                "to": "2016-03-02T23:50:08+00:00",
                "offset": 0,
                "limit": 20,
                "total": 0,
                "order": null,
                "location": "/charges/charge_id/refunds",
                "data": []
            },
            "return_uri": "http://www.example.com?wc-api=wc_gateway_omise&order_id=4450",
            "reference": "payment_id",
            "authorize_uri": "https://api.omise.co/payments/payment_id/authorize",
            "failure_code": null,
            "failure_message": null,
            "card": {
                "object": "card",
                "id": "card_id",
                "livemode": false,
                "location": "/customers/customer_id/cards/card_id",
                "country": "us",
                "city": null,
                "postal_code": null,
                "financing": "",
                "bank": "JPMORGAN CHASE BANK, N.A.",
                "last_digits": "1111",
                "brand": "Visa",
                "expiration_month": 4,
                "expiration_year": 2020,
                "fingerprint": "test_fingerprint",
                "name": "John Doe",
                "security_code_check": true,
                "created": "2016-03-02T15:53:45Z"
            },
            "customer": "customer_id",
            "ip": null,
            "dispute": null,
            "created": "2016-03-02T15:53:48Z"
        }';

        $omise->expects( $this->once() )
            ->method( "call_api" )
            ->with( "private_key", "GET", "/charges/charge_id" )
            ->will( $this->returnValue( $result ) );

        $expected = json_decode( $result );
        $actual = $omise->get_charge( "private_key", "charge_id" );

        $this->assertEquals( $expected, $actual );
    }

    function test_create_customer_should_call_method_call_api_with_required_params() {
        $omise = $this->getMockBuilder( "Omise" )
            ->setMethods( array( "call_api" ) )
            ->getMock();

        $result = '{
            "object": "customer",
            "id": "cust_test_id",
            "livemode": false,
            "location": "/customers/cust_test_id",
            "default_card": null,
            "email": "john.doe@example.com",
            "description": "John Doe (id: 30)",
            "created": "2016-03-02T09:08:47Z",
            "cards": {
                "object": "list",
                "from": "1970-01-01T00:00:00+00:00",
                "to": "2016-03-02T09:08:47+00:00",
                "offset": 0,
                "limit": 20,
                "total": 0,
                "order": null,
                "location": "/customers/cust_test_id/cards",
                "data": []
            }
        }';

        $customer_data = array(
            "description" => "John Doe (id: 30)",
            "card"        => "test_token"
        );

        $omise->expects( $this->once() )
            ->method( "call_api" )
            ->with( "private_key", "POST", "/customers", $customer_data )
            ->will( $this->returnValue( $result ) );

        $expected = json_decode( $result );
        $actual = $omise->create_customer( "private_key", $customer_data );

        $this->assertEquals( $expected, $actual );
    }

    function test_get_customer_cards_should_call_method_call_api_with_required_params() {
        $omise = $this->getMockBuilder( "Omise" )
            ->setMethods( array( "call_api" ) )
            ->getMock();

        $result = '{
            "object": "list",
            "from": "1970-01-01T00:00:00+00:00",
            "to": "2016-03-03T00:09:59+00:00",
            "offset": 0,
            "limit": 20,
            "total": 10,
            "order": "chronological",
            "data": [
                {
                    "object": "card",
                    "id": "card_id_1",
                    "livemode": false,
                    "location": "/customers/customer_id/cards/card_id_1",
                    "country": "us",
                    "city": null,
                    "postal_code": null,
                    "financing": "",
                    "bank": "JPMORGAN CHASE BANK, N.A.",
                    "last_digits": "1111",
                    "brand": "Visa",
                    "expiration_month": 12,
                    "expiration_year": 2019,
                    "fingerprint": "test_fingerprint",
                    "name": "Pronto Tools",
                    "security_code_check": true,
                    "created": "2016-03-01T04:27:00Z"
                },
                {
                    "object": "card",
                    "id": "card_id_2",
                    "livemode": false,
                    "location": "/customers/customer_id/cards/card_id_2",
                    "country": "us",
                    "city": null,
                    "postal_code": null,
                    "financing": "",
                    "bank": "JPMORGAN CHASE BANK, N.A.",
                    "last_digits": "1111",
                    "brand": "Visa",
                    "expiration_month": 12,
                    "expiration_year": 2019,
                    "fingerprint": "test_fingerprint",
                    "name": "Pronto Tools",
                    "security_code_check": true,
                    "created": "2016-03-01T07:26:36Z"
                }
            ]
        }';

        $omise->expects( $this->once() )
            ->method( "call_api" )
            ->with( "private_key", "GET", "/customers/customer_id/cards" )
            ->will( $this->returnValue( $result ) );

        $expected = json_decode( $result );
        $actual = $omise->get_customer_cards( "private_key", "customer_id" );

        $this->assertEquals( $expected, $actual );
    }

    function test_create_card_should_call_method_call_api_with_required_params() {
        $omise = $this->getMockBuilder( "Omise" )
            ->setMethods( array( "call_api" ) )
            ->getMock();

        $result = '{
            "object": "token",
            "id": "tokn_test_id",
            "livemode": false,
            "location": "https://vault.omise.co/tokens/tokn_test_id",
            "used": false,
            "card": {
                "object": "card",
                "id": "card_test_id",
                "livemode": false,
                "country": "us",
                "city": "Bangkok",
                "postal_code": "10320",
                "financing": "",
                "bank": "",
                "last_digits": "4242",
                "brand": "Visa",
                "expiration_month": 3,
                "expiration_year": 2018,
                "fingerprint": "test_fingerprint",
                "name": "JOHN DOE",
                "security_code_check": true,
                "created": "2016-03-02T08:54:53Z"
            },
            "created": "2016-03-02T08:54:53Z"
        }';

        $omise->expects( $this->once() )
            ->method( "call_api" )
            ->with( "private_key", "PATCH", "/customers/customer_id", "card=test_token" )
            ->will( $this->returnValue( $result ) );

        $expected = json_decode( $result );
        $actual = $omise->create_card( "private_key", "customer_id", "test_token" );

        $this->assertEquals( $expected, $actual );
    }

    function test_delete_card_should_call_method_call_api_with_required_params() {
        $omise = $this->getMockBuilder( "Omise" )
            ->setMethods( array( "call_api" ) )
            ->getMock();

        $result = '{
            "object": "card",
            "id": "card_id",
            "livemode": false,
            "deleted": true
        }';

        $omise->expects( $this->once() )
            ->method( "call_api" )
            ->with( "private_key", "DELETE", "/customers/customer_id/cards/card_id" )
            ->will( $this->returnValue( $result ) );

        $expected = json_decode( $result );
        $actual = $omise->delete_card( "private_key", "customer_id", "card_id" );

        $this->assertEquals( $expected, $actual );
    }

    function test_get_balance_should_call_method_call_api_with_required_params() {
        $omise = $this->getMockBuilder( "Omise" )
            ->setMethods( array( "call_api" ) )
            ->getMock();

        $result = '{
            "object": "balance",
            "livemode": false,
            "location": "/balance",
            "available": 209318,
            "total": 209318,
            "currency": "thb"
        }';

        $omise->expects( $this->once() )
            ->method( "call_api" )
            ->with( "private_key", "GET", "/balance" )
            ->will( $this->returnValue( $result ) );

        $expected = json_decode( $result );
        $actual = $omise->get_balance( "private_key" );

        $this->assertEquals( $expected, $actual );
    }

    function test_create_transfer_should_call_method_call_api_with_required_params() {
        $omise = $this->getMockBuilder( "Omise" )
            ->setMethods( array( "call_api" ) )
            ->getMock();

        $result = '{
            "object": "transfer",
            "id": "transfer_id",
            "livemode": false,
            "location": "/transfers/transfer_id",
            "recipient": "recipient_id",
            "bank_account": {
                "object": "bank_account",
                "brand": "test",
                "last_digits": "6789",
                "name": "DEFAULT BANK ACCOUNT",
                "created": "2016-02-22T08:11:15Z"
            },
            "sent": false,
            "paid": false,
            "amount": 20116,
            "currency": "thb",
            "fee": 3000,
            "failure_code": null,
            "failure_message": null,
            "transaction": null,
            "created": "2016-03-03T00:41:50Z"
        }';

        $omise->expects( $this->once() )
            ->method( "call_api" )
            ->with( "private_key", "POST", "/transfers", "amount=69" )
            ->will( $this->returnValue( $result ) );

        $expected = json_decode( $result );
        $actual = $omise->create_transfer( "private_key", $amount = 69 );

        $this->assertEquals( $expected, $actual );
    }
}
