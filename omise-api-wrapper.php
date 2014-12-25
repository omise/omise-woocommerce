<?php
defined('ABSPATH') or die("No direct script access allowed.");

class Omise{	
	public function __construct(){
	}
	
	public static function create_charge($apiKey, $chargeInfo){
		$result = self::call_api($apiKey, "POST", "/charges", $chargeInfo);
		return json_decode($result);
	}
	
	public static function create_customer($apiKey, $customer_data){
		$result = self::call_api($apiKey, "POST", "/customers", $customer_data);
		return json_decode($result);
	}
	
	public static function get_customer_cards($apiKey, $customer_id){
		$result = self::call_api($apiKey, "GET", "/customers/{$customer_id}/cards");
		return json_decode($result);
	}
	
	public static function create_card($apiKey, $customer_id, $token){
		$result = self::call_api($apiKey, "PATCH", "/customers/{$customer_id}", "card=".$token);
		return json_decode($result);
	}
	
	public static function delete_card($apiKey, $customer_id, $card_id){
		$result = self::call_api($apiKey, "DELETE", "/customers/{$customer_id}/cards/{$card_id}");
		return json_decode($result);
	}
	
	private static function call_api($apiKey, $method, $endpoint, $data = false){
		global $wp_version;
		$url = OMISE_PROTOCOL_PREFIX.OMISE_API_HOST.$endpoint;
		
		$headers = array(
				'Authorization'  => 'Basic ' . base64_encode( $apiKey.':' ),
				'User-Agent' => 'OmiseWooCommerce/'.OMISE_WOOCOMMERCE_PLUGIN_VERSION.' WooCommerce/'.WC_VERSION.' Wordpress/'.$wp_version
		);
		
		$request_info = array(
				'method'    => $method,
				'headers'   => $headers,
				'body'      => $data
		);
		
		$response = wp_remote_request($url, $request_info);
		$response_body = wp_remote_retrieve_body($response);
		return $response_body;
	}
}

?>