<?php

use Brain\Monkey;

require_once __DIR__ . '/../../class-omise-unit-test.php';
require_once __DIR__ . '/class-omise-offsite-test.php';

use voku\helper\HtmlDomParser;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_Payment_Mobilebanking_Test extends Omise_Offsite_Test
{
    protected function setUp(): void
    {
        parent::setUp();
        // Capability API
        require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/res/obj/OmiseObject.php';
        require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/res/OmiseApiResource.php';
        require_once __DIR__ . '/../../../../includes/class-omise-capability.php';
        // Mobile Banking Payment
        require_once __DIR__ . '/../../../../includes/backends/class-omise-backend.php';
        require_once __DIR__ . '/../../../../includes/backends/class-omise-backend-mobile-banking.php';
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-mobilebanking.php';
        require_once __DIR__ . '/../../../../omise-util.php';

        Monkey\Functions\stubs([
            'add_action' => true,
            'wp_kses' => null,
            'sanitize_text_field' => null,
            'plugin_dir_path' => __DIR__ . '/../../../../',
        ]);
    }

    public function testPaymentFieldsRendersMobileBankingForm()
    {
        ob_start();

        $mobileBanking = new Omise_Payment_Mobilebanking();

        Monkey\Functions\expect('get_woocommerce_currency')
            ->andReturn('THB');
        $this->mockApiCall('omise-capability-get');

        $mobileBanking->payment_fields();
        $output = ob_get_clean();
        $page = HtmlDomParser::str_get_html($output);

        $optionList = $page->find('.omise-banks-list .item');
        $this->assertCount(5, $optionList);
        $this->assertNotFalse($optionList->findOneOrFalse('input[value=mobile_banking_bay]'));
        $this->assertNotFalse($optionList->findOneOrFalse('input[value=mobile_banking_bbl]'));
        $this->assertNotFalse($optionList->findOneOrFalse('input[value=mobile_banking_kbank]'));
        $this->assertNotFalse($optionList->findOneOrFalse('input[value=mobile_banking_ktb]'));
        $this->assertNotFalse($optionList->findOneOrFalse('input[value=mobile_banking_scb]'));

        $this->assertEquals('t', 't');
    }

    public function testCharge()
    {
        Monkey\Functions\expect('wc_get_user_agent')
            ->with('123')
            ->andReturn('Chrome Web');
        $_POST['omise-offsite'] = 'mobile_banking_bbl';
        $obj = new Omise_Payment_Mobilebanking();
        $this->getChargeTest($obj);
    }
}
