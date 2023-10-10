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
			'metadata'    => $this->getMetadata($order_id),
			'webhook_endpoints' => [ Omise_Util::getWebhookURL() ],
			'source' 	  => [ 'type' => $source_type ]
		];

		if (!$callback_endpoint) {
			$return_uri = $this->getRedirectUrl($callback_endpoint, $order_id, $order);

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
	public function getMetadata($order_id, $additionalData = [])
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
	public function getRedirectUrl($callback_url, $order_id, $order)
	{
		$redirectUrl = RedirectUrl::create($callback_url, $order_id);

		// Call after RedirectUrl::create
		$order->add_meta_data('token', RedirectUrl::getToken(), true);

		return $redirectUrl;
	}
}
