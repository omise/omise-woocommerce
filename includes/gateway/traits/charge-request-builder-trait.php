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
}
