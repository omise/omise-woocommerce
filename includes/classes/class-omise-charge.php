<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Charge' ) ) {
	class Omise_Charge {
		/**
		 * @param integer $order_id  WooCommerce's order id
		 * @return WP_Post object | boolean
		 */
		public static function get_post_charge( $order_id ) {
			$query_field = '_wc_order_id';

			$post_type   = 'omise_charge_items';
			$post_meta   = array( 'key' => $query_field, 'value' => $order_id, 'compare' => '=' );

			$posts = get_posts( array(
				'post_type'  => $post_type,
				'meta_query' => array( $post_meta )
			) );

			if ( empty( $posts ) )
				return false;

			return $posts[0];
		}

		/**
		 * @param WC_Post $post  WordPress's post object
		 * @return string
		 */
		public static function get_charge_id_from_post( $post ) {
			$query_field = '_omise_charge_id';

			$value = get_post_custom_values( $query_field, $post->ID );
			if ( ! is_null( $value ) && ! empty( $value ) )
				return $value[0];

			return '';
		}

		/**
		 * @param WC_Post $post  WordPress's post object
		 * @return string
		 */
		public static function get_confirmed_url_from_post( $post ) {
			$query_field = '_wc_confirmed_url';

			$value = get_post_custom_values( $query_field, $post->ID );
			if ( ! is_null( $value ) && ! empty( $value ) )
				return $value[0];

			return '';
		}

		/**
		 * @param OmiseCharge $charge  Omise's charge object
		 * @return boolean
		 */
		public static function is_charge_object( $charge ) {
			return OmisePluginHelperCharge::isChargeObject( $charge );
		}

		/**
		 * @param OmiseCharge $charge  Omise's charge object
		 * @return boolean
		 */
		public static function is_authorized( $charge ) {
			return OmisePluginHelperCharge::isAuthorized( $charge );
		}

		/**
		 * @param OmiseCharge $charge  Omise's charge object
		 * @return boolean
		 */
		public static function is_paid( $charge ) {
			return OmisePluginHelperCharge::isPaid( $charge );
		}

		/**
		 * @param OmiseCharge $charge  Omise's charge object
		 * @return boolean
		 */
		public static function is_failed( $charge ) {
			return OmisePluginHelperCharge::isFailed( $charge );
		}

		/**
		 * @param OmiseCharge $charge  Omise's charge object
		 * @return string | boolean
		 */
		public static function get_error_message( $charge ) {
			if ( '' !== $charge['failure_code'] ) {
				return '(' . $charge['failure_code'] . ') ' . $charge['failure_message'];
			}

			return '';
		}
		
		/**
		 * Retrieve the charge information by Omise secret key
		 *
		 * @param  string $private_key The Omise secret key
		 * @return object OmisCharge
		 */
		public static function list_charges( $private_key ) {
			$paged  = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
			$limit  = 10;
			$offset = $paged > 1 ? ( $paged - 1 ) * $limit : 0;
			$order  = 'reverse_chronological';
		
			$filters = '?' . http_build_query( array(
				'limit'  => $limit,
				'offset' => $offset,
				'order'  => $order
			) );
		
			return OmiseCharge::retrieve( $filters, '', $private_key );
		}
	}
}