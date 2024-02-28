<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_Wechat_Pay_Test extends Omise_Offsite_Test
{
    public function setUp(): void
    {
        parent::setUp();
        $this->sourceType = 'wechat_pay';

        Brain\Monkey\setUp();
        Brain\Monkey\Functions\stubs([
            'apply_filters' => function () {
                return Omise_Image::get_image([
                    'file' => 'wechat_pay.svg',
                    'alternate_text' => 'WeChat Pay',
                ]);
            },
        ]);

        require_once __DIR__ . '/../../../../includes/libraries/omise-plugin/helpers/request.php';
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-wechat-pay.php';
    }

    public function test_restricted_countries_field_has_required_countries()
    {
        $obj = new Omise_Payment_Wechat_Pay();
        $expectedCountries = ['TH'];

        $this->assertEqualsCanonicalizing($expectedCountries, $obj->restricted_countries);
        unset($expectedCountries);
    }

    public function test_charge()
    {
        $obj = new Omise_Payment_Wechat_Pay();
        $this->getChargeTest($obj);
    }

    public function test_get_icon()
    {
        $obj = new Omise_Payment_Wechat_Pay();
        $res = $obj->get_icon();
        $expected = "<img src='/wechat_pay.svg' class='Omise-Image' style='width: 30px; max-height: 30px;' alt='WeChat Pay' />";
        $this->assertEquals($expected, trim($res));
    }
}
