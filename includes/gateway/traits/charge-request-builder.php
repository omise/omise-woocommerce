<?php

trait Charge_Request_Builder
{
    public function build_charge_request(
		$order_id,
		$order,
		$source_type,
		$callback_endpoint
	)
	{
		$currency = $order->get_currency();
		$return_uri = $this->getRedirectUrl($callback_endpoint, $order_id, $order);
		$description = apply_filters(
			'omise_charge_params_description',
			'WooCommerce Order id ' . $order_id,
			$order
		);

		return [
			'amount'      => Omise_Money::to_subunit($order->get_total(), $currency),
			'currency'    => $currency,
			'description' => $description,
			'return_uri'  => $return_uri,
			'metadata'    => $this->getMetadata($order_id, $order),
			'webhook_endpoints' => [ Omise_Util::getWebhookURL() ],
			'source' 	  => [ 'type' => $source_type ]
		];
	}

	/**
	 * @param string $order_id
	 * @param object $order
	 * @param array $additionalData
	 */
	public function getMetadata($order_id, $order, $additionalData = [])
	{
		// override order_id as a reference for webhook handlers.
		$orderId = [ 'order_id' => $order_id ];

		echo var_dump(apply_filters('omise_charge_params_metadata', [], $order));

		return array_merge(
			apply_filters('omise_charge_params_metadata', [], $order),
			array_merge($orderId, $additionalData)
		);
	}

	/**
	 * @param string $callback_url
	 * @param string $order_id
	 * @param object $order
	 */
	public function getRedirectUrl($callback_url, $order_id, $order)
	{
		// return 'https://opn.ooo';
		$redirectUrl = RedirectUrl::create($callback_url, $order_id);

		// Call after RedirectUrl::create
		$order->add_meta_data('token', RedirectUrl::getToken(), true);

		return $redirectUrl;
	}
}
