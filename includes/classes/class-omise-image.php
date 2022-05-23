<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Image' ) ) {
	class Omise_Image {
		/**
		 * Compose the given parameters into the string of HTML &lt;img&gt; element
		 *
		 * @param string $file Image file name with extension such as image.jpg
		 * @param string $alternate_text Alternate text for the image
		 * @param int $width Width size for image with default value at 30
		 * @param int $height Max-height size for image with default value at 30
		 * @return string HTML &lt;img&gt; element
		 */
		public static function get_image( $file, $alternate_text, $width = 30, $height = 30 ) {
			$url = WC_HTTPS::force_https_url( plugin_dir_url( '' ) . 'omise/assets/images' );
			return "<img src='$url/$file' class='Omise-Image' style='width: {$width}px; max-height: {$height}px;' alt='$alternate_text' />";
		}
	}
}
