<?php

defined( 'ABSPATH' ) || exit;

class Omise_UPA_Back_Navigation_Guard {
	const QUERY_PARAM = 'omise_upa_guard';

	/**
	 * Register hooks for rendering history guard script.
	 */
	public static function register_hooks() {
		add_action( 'wp_footer', array( __CLASS__, 'render' ) );
	}

	/**
	 * Render history back-navigation guard script on successful UPA return page.
	 */
	public static function render() {
		$order = self::resolve_order_for_guard();

		if ( ! $order ) {
			return;
		}

		$enabled = apply_filters( 'omise_upa_enable_back_navigation_guard', true, $order );
		if ( ! $enabled ) {
			return;
		}

		$query_param_key = wp_json_encode( self::QUERY_PARAM );
		$notice_message  = wp_json_encode(
			__( 'Payment is already completed. Back navigation to previous payment page is disabled.', 'omise' )
		);

		if ( false === $query_param_key || false === $notice_message ) {
			return;
		}

		$script = implode(
			'',
			array(
				'(function(){',
				'if(!window.history||!window.history.pushState||typeof URL==="undefined"){return;}',
				'var queryKey=' . $query_param_key . ';',
				'var noticeMessage=' . $notice_message . ';',
				'var currentUrl;try{currentUrl=new URL(window.location.href);}catch(e){return;}',
				'currentUrl.searchParams.delete(queryKey);',
				'var cleanUrl=currentUrl.toString();',
				'var noticeRendered=false;',
				'function showNotice(){if(noticeRendered){return;}noticeRendered=true;',
				'var el=document.createElement("div");',
				'el.id="omise-upa-back-navigation-notice";',
				'el.textContent=noticeMessage;',
				'el.style.position="fixed";el.style.left="50%";el.style.bottom="24px";',
				'el.style.transform="translateX(-50%)";el.style.padding="10px 14px";',
				'el.style.background="#1f2937";el.style.color="#ffffff";',
				'el.style.borderRadius="6px";el.style.fontSize="13px";',
				'el.style.lineHeight="1.4";el.style.zIndex="99999";',
				'document.body.appendChild(el);}',
				'try{window.history.replaceState({omiseUpaGuard:true},document.title,cleanUrl);',
				'window.history.pushState({omiseUpaGuard:true},document.title,cleanUrl);}catch(e){return;}',
				'window.addEventListener("popstate",function(){',
				'try{window.history.pushState({omiseUpaGuard:true},document.title,cleanUrl);}catch(e){}',
				'showNotice();});',
				'})();',
			)
		);

		if ( function_exists( 'wp_print_inline_script_tag' ) ) {
			wp_print_inline_script_tag(
				$script,
				array(
					'id' => 'omise-upa-back-navigation-guard',
				)
			);
			return;
		}

		echo '<script id="omise-upa-back-navigation-guard">' . $script . '</script>';
	}

	/**
	 * Determine if the current page requires UPA back-navigation guard.
	 *
	 * @return WC_Order|null
	 */
	private static function resolve_order_for_guard() {
		if ( is_admin() || wp_doing_ajax() ) {
			return null;
		}

		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || ! is_wc_endpoint_url( 'order-received' ) ) {
			return null;
		}

		if ( ! isset( $_GET[ self::QUERY_PARAM ] ) ) {
			return null;
		}

		$guard_flag = sanitize_text_field( wp_unslash( $_GET[ self::QUERY_PARAM ] ) );
		if ( '1' !== $guard_flag ) {
			return null;
		}

		$order = self::resolve_order();
		if ( ! $order ) {
			return null;
		}

		$flow = $order->get_meta( Omise_UPA_Session_Service::META_FLOW );
		$is_upa_flow = in_array(
			$flow,
			array(
				Omise_UPA_Session_Service::FLOW_OFFSITE,
				Omise_UPA_Session_Service::FLOW_OFFLINE,
			),
			true
		);

		if ( ! $is_upa_flow ) {
			return null;
		}

		if ( ! $order->is_paid() ) {
			return null;
		}

		return $order;
	}

	/**
	 * Resolve order from order-received endpoint payload.
	 *
	 * @return WC_Order|null
	 */
	private static function resolve_order() {
		$order_id = absint( get_query_var( 'order-received' ) );

		if ( $order_id > 0 ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				return $order;
			}
		}

		if ( ! isset( $_GET['key'] ) ) {
			return null;
		}

		$order_key = sanitize_text_field( wp_unslash( $_GET['key'] ) );
		if ( empty( $order_key ) ) {
			return null;
		}

		$order_id = wc_get_order_id_by_order_key( $order_key );
		if ( empty( $order_id ) ) {
			return null;
		}

		return wc_get_order( $order_id );
	}
}
