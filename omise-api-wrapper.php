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
		return $result;
	}
	
	public static function delete_card($apiKey, $customer_id, $card_id){
		$result = self::call_api($apiKey, "DELETE", "/customers/{$customer_id}/cards/{$card_id}");
		return $result;
	}
	
	private static function call_api($apiKey, $method, $endpoint, $data = false)
	{
		$url = "https://api.omise.co".$endpoint;
		$curl = curl_init();
	
		switch ($method)
		{
			case "POST":
				curl_setopt($curl, CURLOPT_POST, 1);
	
				if ($data)
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;
			case "PATCH":
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
	
				if ($data)
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;
			case "DELETE":
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
				
					if ($data)
						curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
					break;
			default:
				if ($data)
					$url = sprintf("%s?%s", $url, http_build_query($data));
		}
	
		// Optional Authentication:
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, $apiKey.":");
	
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	
		$result = curl_exec($curl);
	
		curl_close($curl);
	
		return $result;
	}
}

?>