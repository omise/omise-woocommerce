<?php

class Omise_Block_Truemoney extends Omise_Block_Payment {
    /**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'omise_truemoney';

	public function set_additional_data() {
        $this->additional_data = [ 'is_wallet' => $this->gateway->is_wallet() ];
    }
}
