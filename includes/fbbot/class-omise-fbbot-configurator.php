<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if( ! class_exists( 'Omise_Messenger_Bot_Configurator' ) ) {
	class Omise_Messenger_Bot_Configurator {
		private static $version = '1';
		private static $namespace = 'omisemsgbot';

		public static function get_namespace() {
			return Omise_Messenger_Bot_Configurator::$namespace . '/v' . Omise_Messenger_Bot_Configurator::$version;
		}
	}

}