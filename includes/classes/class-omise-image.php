<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Image' ) ) {
	class Omise_Image {
		/**
		 * Compose the given parameters into the string of HTML &lt;img&gt; element
		 *
		 * @param array $image array that contain image data => file name, alternate text, width, height
		 */
		public static function get_image(array $image) {

			$file			= isset( $image['file'] ) ? $image['file'] : '';
			$alternate_text         = isset( $image['alternate_text'] ) ? $image['alternate_text'] : '';
			$width			= isset( $image['width'] ) ? $image['width'] : 30;
			$height			= isset( $image['height'] ) ? $image['height'] : 30;
			
			$url = plugins_url( '../assets/images', dirname( __FILE__ ));
			return "<img src='$url/$file' class='Omise-Image' style='width: {$width}px; max-height: {$height}px;' alt='$alternate_text' />";
		}
	}
}
