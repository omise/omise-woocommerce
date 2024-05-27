<?php

class Omise_Block_Installment extends Omise_Block_Payment {
	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'omise_installment';

	public function set_additional_data() {
		$viewData = $this->gateway->get_view_data();
		$installment_backends = [];

		foreach($viewData['installment_backends'] as $backend) {
			$installment_backends[] = (array)$backend;
		}

		$this->additional_data = [
			'is_zero_interest' => $viewData['is_zero_interest'],
			'installment_min_limit' => $viewData['installment_min_limit'],
			'installment_backends' => $installment_backends,
		];
	}
}
