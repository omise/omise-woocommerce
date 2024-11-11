<?php

use Brain\Monkey;

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_DuitNow_OBW_Test extends Omise_Offsite_Test
{
    public function setUp(): void
    {
        $this->sourceType = 'duitnow_obw';
        parent::setUp();
        Monkey\Functions\expect('add_action');
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-duitnow-obw.php';
    }

    public function testCharge()
    {
        if (!function_exists('sanitize_text_field')) {
            function sanitize_text_field() {
                return 'Sanitized text';
            }
        }

        $_POST['source'] = ['bank' => 'SCB'];
        $obj = new Omise_Payment_DuitNow_OBW();
        $this->getChargeTest($obj);
    }

    public function testGetBankList()
    {
        $expected = [
			'affin' => [
				'code' => 'affin',
				'name' => 'Affin Bank'
			],
			'alliance' => [
				'code' => 'alliance',
				'name' => 'Alliance Bank'
			],
			'agro' => [
				'code' => 'agro',
				'name' => 'Agrobank'
			],
			'ambank' => [
				'code' => 'ambank',
				'name' => 'AmBank'
			],
			'cimb' => [
				'code' => 'cimb',
				'name' => 'CIMB Bank'
			],
			'islam' => [
				'code' => 'islam',
				'name' => 'Bank Islam'
			],
			'rakyat' => [
				'code' => 'rakyat',
				'name' => 'Bank Rakyat'
			],
			'muamalat' => [
				'code' => 'muamalat',
				'name' => 'Bank Muamalat'
			],
			'bsn' => [
				'code' => 'bsn',
				'name' => 'Bank Simpanan Nasional'
			],
			'hongleong' => [
				'code' => 'hongleong',
				'name' => 'Hong Leong'
			],
			'hsbc' => [
				'code' => 'hsbc',
				'name' => 'HSBC Bank'
			],
			'kfh' => [
				'code' => 'kfh',
				'name' => 'Kuwait Finance House'
			],
			'maybank2u' => [
				'code' => 'maybank2u',
				'name' => 'Maybank'
			],
			'ocbc' => [
				'code' => 'ocbc',
				'name' => 'OCBC'
			],
			'public' => [
				'code' => 'public',
				'name' => 'Public Bank'
			],
			'rhb' => [
				'code' => 'rhb',
				'name' => 'RHB Bank'
			],
			'sc' => [
				'code' => 'sc',
				'name' => 'Standard Chartered'
			],
			'uob' => [
				'code' => 'uob',
				'name' => 'United Overseas Bank'
			],
		];
        $obj = new Omise_Payment_DuitNow_OBW();
        $bankList = $obj->get_bank_list();
        $this->assertEquals($expected, $bankList);
    }
}
