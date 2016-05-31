<?php require_once dirname(__FILE__).'/TestConfig.php';

class CustomerTest extends TestConfig {
  /**
   * OmiseCustomer class must be contain some method below.
   *
   */
  public function testMethodExists() {
    $this->assertTrue(method_exists('OmiseCustomer', 'retrieve'));
    $this->assertTrue(method_exists('OmiseCustomer', 'create'));
    $this->assertTrue(method_exists('OmiseCustomer', 'reload'));
    $this->assertTrue(method_exists('OmiseCustomer', 'update'));
    $this->assertTrue(method_exists('OmiseCustomer', 'destroy'));
    $this->assertTrue(method_exists('OmiseCustomer', 'isDestroyed'));
    $this->assertTrue(method_exists('OmiseCustomer', 'cards'));
    $this->assertTrue(method_exists('OmiseCustomer', 'getCards'));
    $this->assertTrue(method_exists('OmiseCustomer', 'getUrl'));
  }

  /**
   * Assert that a list of customer object could be successfully retrieved.
   *
   */
  public function testRetrieveCustomerListObject() {
    $customer = OmiseCustomer::retrieve();

    $this->assertArrayHasKey('object', $customer);
    $this->assertEquals('list', $customer['object']);
  }

  /**
   * Assert that a customer is successfully created with the given parameters set.
   *
   */
  public function testCreate() {
    $customer = OmiseCustomer::create(array('email'       => 'john.doe@example.com',
                                            'description' => 'John Doe (id: 30)',
                                            'card'        => 'tokn_test_4zmrjhuk2rndz24a6x0'));

    $this->assertArrayHasKey('object', $customer);
    $this->assertEquals('customer', $customer['object']);
  }

  /**
   * Assert that a customer object is returned after a successful retrieve.
   *
   */
  public function testRetrieveSpecificCustomerObject() {
    $customer = OmiseCustomer::retrieve('cust_test_4zmrjg2hct06ybwobqc');

    $this->assertArrayHasKey('object', $customer);
    $this->assertEquals('customer', $customer['object']);
  }

  /**
   * Assert that a customer object is returned after a successful retrieve.
   *
   */
  public function testRetrieveCardObjectFromCustomer() {
    $customer = OmiseCustomer::retrieve('cust_test_5234fzk37pi2mz0cen3');
    $cards    = $customer->cards(array('limit' => 1));

    $this->assertArrayHasKey('object', $cards);
    $this->assertEquals('list', $cards['object']);

    if (!empty($cards['data'])) {
      $this->assertEquals('card', $cards['data'][0]['object']);
      $this->assertEquals(1, count($cards['data']));
    }
  }

  /**
   * Assert that a customer is successfully updated with the given parameters set.
   *
   */
  public function testUpdate() {
    $customer = OmiseCustomer::retrieve('cust_test_4zmrjg2hct06ybwobqc');
    $customer->update(array('email'       => 'john.smith@example.com',
                            'description' => 'Another description'));
    
    $this->assertArrayHasKey('object', $customer);
    $this->assertEquals('customer', $customer['object']);
  }

  /**
   * Assert that a destroyed flag is set after a customer is successfully destroyed.
   *
   */
  public function testDestroy() {
    $customer = OmiseCustomer::retrieve('cust_test_4zmrjg2hct06ybwobqc');
    $customer->destroy();

    $this->assertTrue($customer->isDestroyed());
  }
}
