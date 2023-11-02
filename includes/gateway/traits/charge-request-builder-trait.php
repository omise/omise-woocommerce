<?php

trait Charge_Request_Builder
{
    public function build_charge_request(
		$order_id,
		$order,
		$source_type,
		$callback_endpoint = null
	)
	{
		$currency = $order->get_currency();
		$description = 'WooCommerce Order id ' . $order_id;

		$request = [
			'amount'      => Omise_Money::to_subunit($order->get_total(), $currency),
			'currency'    => $currency,
			'description' => $description,
			'metadata'    => $this->get_metadata($order_id),
			'source' 	  => [ 'type' => $source_type ]
		];

		// add platform type for certain payment methods
		if ($this->source_requires_platform_type($source_type)) {
			$request['source']['platform_type'] = Omise_Util::get_platform_type(wc_get_user_agent());
		}

		$omise_settings = Omise_Setting::instance();

		if ($omise_settings->is_dynamic_webhook_enabled()) {
			$request = array_merge($request, [
				'webhook_endpoints' => [ Omise_Util::get_webhook_url() ],
			]);
		}

		if ($callback_endpoint) {
			$return_uri = $this->get_redirect_url($callback_endpoint, $order_id, $order);

			return array_merge($request, [
				'return_uri'  => $return_uri,
			]);
		}

		return $request;
	}

	/**
	 * @param string $order_id
	 * @param array $additionalData
	 */
	public function get_metadata($order_id, $additionalData = [])
	{
		// override order_id as a reference for webhook handlers.
		$orderId = [ 'order_id' => $order_id ];
		return array_merge($orderId, $additionalData);
	}

	/**
	 * @param string $callback_url
	 * @param string $order_id
	 * @param object $order
	 */
	public function get_redirect_url($callback_url, $order_id, $order)
	{
		$redirectUrl = RedirectUrl::create($callback_url, $order_id);

		// Call after RedirectUrl::create
		$order->add_meta_data('token', RedirectUrl::getToken(), true);

		return $redirectUrl;
	}

	public function source_requires_platform_type($source_type)
	{
		$requires_platform_type = [
			'mobile_banking_kbank',
			'mobile_banking_scb',
			'mobile_banking_bay',
			'mobile_banking_bbl',
			'mobile_banking_ktb',
			'mobile_banking_ocbc_pao',
			'mobile_banking_ocbc',
			'installment_first_choice',
			'installment_bay',
			'installment_bbl',
			'installment_kbank',
			'installment_ktc',
			'installment_scb',
			'installment_citi',
			'installment_ttb',
			'installment_uob',
			'installment_mbb',
			'alipay_cn',
			'alipay_hk',
			'dana',
			'gcash',
			'kakaopay',
		];

		return in_array($source_type, $requires_platform_type);
	}
}
