<?php
defined('ABSPATH') or die("No direct script access allowed.");

if(!class_exists('Omise')){
	class Omise{	
		public function __construct(){
		}
		
		/**
		 * Creates a charge
		 * @param string $apiKey
		 * @param Array $chargeInfo
		 * @return mixed
		 */
		public static function create_charge($apiKey, $chargeInfo){
			$result = self::call_api($apiKey, "POST", "/charges", $chargeInfo);
			return json_decode($result);
		}

		/**
		 * Retrieve a charge
		 * @param string $apiKey
		 * @param Array $charge_id
		 * @return mixed
		 */
		public static function get_charge($apiKey, $charge_id){
			$result = self::call_api($apiKey, "GET", "/charges/{$charge_id}");
			return json_decode($result);
		}
		
		/**
		 * Creates a customer
		 * @param string $apiKey
		 * @param Array $customer_data
		 * @return mixed
		 */
		public static function create_customer($apiKey, $customer_data){
			$result = self::call_api($apiKey, "POST", "/customers", $customer_data);
			return json_decode($result);
		}
		
		/**
		 * Get list of customer's cards
		 * @param string $apiKey
		 * @param string $customer_id
		 * @return mixed
		 */
		public static function get_customer_cards($apiKey, $customer_id){
			$result = self::call_api($apiKey, "GET", "/customers/{$customer_id}/cards");
			return json_decode($result);
		}
		
		/**
		 * Creates a card
		 * @param string $apiKey
		 * @param string $customer_id
		 * @param string $token
		 * @return mixed
		 */
		public static function create_card($apiKey, $customer_id, $token){
			$result = self::call_api($apiKey, "PATCH", "/customers/{$customer_id}", "card=".$token);
			return json_decode($result);
		}
		
		/**
		 * Deletes customer card
		 * @param string $apiKey
		 * @param string $customer_id
		 * @param string $card_id
		 * @return mixed
		 */
		public static function delete_card($apiKey, $customer_id, $card_id){
			$result = self::call_api($apiKey, "DELETE", "/customers/{$customer_id}/cards/{$card_id}");
			return json_decode($result);
		}
		
		public static function get_balance($apiKey){
		  $result = self::call_api($apiKey, "GET", "/balance");
		  return json_decode($result);
		}
		
		public static function create_transfer($apiKey, $amount=null){
		  $post_data = isset($amount) ? "amount=".$amount : null;
		  $result = self::call_api($apiKey, "POST", "/transfers", $post_data);
		  return json_decode($result);
		}
		
		/**
		 * Make a request to the API endpoint
		 * @param string $apiKey
		 * @param string $method
		 * @param string $endpoint
		 * @param mixed $data
		 * @return string
		 */
		private static function call_api($apiKey, $method, $endpoint, $data = false){
			global $wp_version;
			$url = OMISE_PROTOCOL_PREFIX.OMISE_API_HOST.$endpoint;
			
			$headers = array(
					'Authorization'  => 'Basic ' . base64_encode( $apiKey.':' ),
					'Omise-Version' => '2014-07-27',
					'User-Agent' => 'OmiseWooCommerce/'.OMISE_WOOCOMMERCE_PLUGIN_VERSION.' WooCommerce/'.WC_VERSION.' Wordpress/'.$wp_version
			);
			
			$request_info = array(
					'timeout'	=> 60,
					'method'    => $method,
					'headers'   => $headers,
					'body'      => $data
			);
			
			$response = wp_remote_request($url, $request_info);
			
			if(is_wp_error($response)){
				return '{ "object": "error", "message": "'.$response->get_error_message().'" }';
			}else{
				$response_body = wp_remote_retrieve_body($response);
				return $response_body;
			}
		}
	}
}

?>
