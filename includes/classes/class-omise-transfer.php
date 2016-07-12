<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Transfer' ) ) {
	class Omise_Transfer {
		/**
		 * Retrieve the transfer information by Omise secret key
		 *
		 * @param  string $private_key The Omise secret key
		 * @return object OmisTransfer
		 */
		public static function list_transfers( $private_key) {
			$paged  = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
			$limit  = 10;
			$offset = $paged > 1 ? ( $paged - 1 ) * $limit : 0;
			$order  = 'reverse_chronological';

			$filters = '?' . http_build_query( array(
				'limit'  => $limit,
				'offset' => $offset,
				'order'  => $order
			) );

			return OmiseTransfer::retrieve( $filters, '', $private_key );
		}
	}
}