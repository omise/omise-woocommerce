<?php

require_once __DIR__ . '/../../class-omise-unit-test.php';

class Template_Form_Mobilebanking_Test extends Omise_Test_Case {
	private function render_template( $view_data ) {
		$template_file = __DIR__ . '/../../../../templates/payment/form-mobilebanking.php';
		$viewData      = $view_data;

		ob_start();
		include $template_file;
		return ob_get_clean();
	}

	public function test_template_shows_unavailable_message_when_upa_disabled_and_no_backends() {
		$output = $this->render_template(
			array(
				'is_upa_enabled' => false,
				'mobile_banking_backends' => array(),
			)
		);

		$this->assertStringContainsString( 'There are no payment methods available.', $output );
	}

	public function test_template_renders_nothing_when_upa_enabled_even_if_backends_are_present() {
		$output = $this->render_template(
			array(
				'is_upa_enabled' => true,
				'mobile_banking_backends' => array(
					(object) array(
						'name' => 'mobile_banking_bay',
						'provider_logo' => 'mobile-banking-bay',
						'provider_name' => 'Krungsri',
					),
				),
			)
		);

		$this->assertStringNotContainsString( 'omise-form-mobilebanking', $output );
		$this->assertStringNotContainsString( 'There are no payment methods available.', $output );
	}
}
