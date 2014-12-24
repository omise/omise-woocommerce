<?php
defined('ABSPATH') or die("No direct script access allowed.");

if(!class_exists('Omise_Util')){
	class Omise_Util{
		public static function get_client_ip() {
			$ipaddress = '';
			if ($_SERVER['HTTP_CLIENT_IP'])
				$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
			else if($_SERVER['HTTP_X_FORWARDED_FOR'])
				$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else if($_SERVER['HTTP_X_FORWARDED'])
				$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
			else if($_SERVER['HTTP_FORWARDED_FOR'])
				$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
			else if($_SERVER['HTTP_FORWARDED'])
				$ipaddress = $_SERVER['HTTP_FORWARDED'];
			else if($_SERVER['REMOTE_ADDR'])
				$ipaddress = $_SERVER['REMOTE_ADDR'];
			else
				$ipaddress = 'UNKNOWN';
			return $ipaddress;
		}
		
		public static function render_view($viewPath, $viewData){
			require_once(plugin_dir_path( __FILE__ ).$viewPath);
		}
		
		public static function render_json_error($message){
			echo json_encode('{ "object": "error", "message": "'.$message.'" }');
		}
	}
}
?>
