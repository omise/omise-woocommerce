<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Image' ) ) {
	class Omise_Image {
		/**
		 * Compose the given parameters into the string of HTML &lt;img&gt; element
		 *
		 * @param string $file Image file name with extension such as image.jpg
		 * @param string $alternate_text Alternate text for the image
		 * @return string HTML &lt;img&gt; element
		 */
		public function get_image( $file, $alternate_text ) {
			$url = WC_HTTPS::force_https_url( plugin_dir_url( '' ) . 'omise/assets/images/' );
			return "<img src='$url/$file' class='Omise-Image' style='width: 30px; max-height: 30px;' alt='$alternate_text' />";
		}
	}
}
