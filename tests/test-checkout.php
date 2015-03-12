<?php
require_once('./class-omise-wc-gateway.php');

class CheckoutTest extends WP_UnitTestCase {

  protected $klass;

  public function __construct(){
    $this->klass =   new WC_Gateway_Omise();
  }

  function getMethod($object, $method_name) {
    $class = new ReflectionClass($object);
    $method = $class->getMethod($method_name);
    $method->setAccessible(true);
    return $method;
  }

  function invoke($method_name, $args){
    $method = $this->getMethod($this->klass, $method_name);
    return $method->invokeArgs($this->klass, array($args));
  }

  function test_is_charge_success() {
    $result = array(
        "id" => "123",
        "object" => "charge",
        "captured" => true
      );

    $this->assertTrue($this->invoke('is_charge_success', (object)$result));
  }

  function test_get_charge_error_message(){
    $result = array(
      "message" => "foo"
    );

    $this->assertEquals("foo", $this->invoke('get_charge_error_message', (object)$result));
  }

}

