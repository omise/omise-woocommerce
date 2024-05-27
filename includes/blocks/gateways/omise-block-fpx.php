<?php

class Omise_Block_Fpx extends Omise_Block_Payment {
	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'omise_fpx';

	public function set_additional_data() {
		$this->additional_data = [
			'bank_list' => $this->gateway->backend->get_available_banks()
		];
	}
}
