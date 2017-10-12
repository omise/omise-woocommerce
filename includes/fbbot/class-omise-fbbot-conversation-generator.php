<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBBot_Conversation_Generator' ) ) {
  return;
}

class Omise_FBBot_Conversation_Generator {
	private $sender_id, $message, $payload, $nlp_enabled;

	public function listen( $sender_id, $message ) {
		$this->sender_id = $sender_id;
		$this->message = $message;

		if ( $message['nlp'] ) {
			$this->nlp_enabled = true;
		} else {
			$this->nlp_enabled = false;
		}
	}

	public function listen_payload( $sender_id, $payload ) {
		$this->sender_id = $sender_id;
		$this->payload = $payload;
	}

	public function reply_for_message() {
		if ( Omise_FBBot_Message_Store::check_order_checking( $this->message['text'] ) ) {
			// Checking order status from order number
			$checking_text = explode('#', $this->message['text']);

			if ( ! $checking_text[1] ) {
			   return self::rechecking_order_number_message();
			}

			$order_id = $checking_text[1];
			return self::get_ordet_status_message( $order_id );
		}

		if ( $this->nlp_enabled ) {
			return $this->entities_handle($this->message['nlp']['entities']);
		}

		if ( Omise_FBBot_Message_Store::check_greeting_words( $this->message['text'] ) ) {
			return self::greeting_message( $this->sender_id );

		} else if ( Omise_FBBot_Message_Store::check_helping_words( $this->message['text'] ) ) {
			return self::helping_message();

		} else {
			// Handle unrecognize message
			return self::unrecognized_message();
		}
	}

	public function reply_for_payload() {
		switch ( $this->payload ) {
			case Omise_FBBot_Payload::GET_START_CLICKED:
				return self::greeting_message( $this->sender_id );

			case Omise_FBBot_Payload::FEATURE_PRODUCTS:
				return self::feature_products_message( $this->sender_id );

			case Omise_FBBot_Payload::PRODUCT_CATEGORY:
				return self::product_category_message();

			case Omise_FBBot_Payload::CHECK_ORDER:
				return self::before_checking_order_message();

			case Omise_FBBot_Payload::HELP:
				return self::helping_message();

			case Omise_FBBot_Payload::CALL_SHOP_OWNER:
				$success = Omise_FBBot_Handover_Protocol_Handler::switch_to_live_agent( $this->sender_id );
				return self::call_shop_owner_message( $success );

			default:
				# Custom payload :
				$explode = explode('__', $this->payload);

				if ($explode[0] == 'VIEW_PRODUCT') {
					$product_id = $explode[1];
					return self::product_gallery_message( $this->sender_id, $product_id );

				} else if ($explode[0] == 'VIEW_CATEGORY_PRODUCTS') {
					$category_slug = $explode[1];
					return self::product_list_in_category_message( $this->sender_id, $category_slug );
				
				} else if ( $explode[0] == 'VIEW_MORE_PRODUCT' ) {
					$category_slug = $explode[1];
					$paged = $explode[2];

					return self::product_list_in_category_message( $this->sender_id, $category_slug, $paged );
				} else if ( $explode[0] == 'VIEW_MORE_CATEGORY' ) {
					$paged = $explode[1];
					error_log('VIEW_MORE_CATEGORY : ' . $paged);
					return self::product_category_message( $paged );
				}

				return self::unrecognized_message();
		}
	}

	private function entities_handle( $entities ) {
		$filtered_entities = array_filter( $entities, function ($value) {
			return ($value[0]['confidence'] > Omise_FBBot_Entity::CONFIDENCE_RATE);
		} );

		if ( count( $filtered_entities ) != 0 ) {
			$max_confidence = 0;
			$user_intent = NULL;

			foreach ( $filtered_entities as $intent => $value ) {
				if ( $value[0]['confidence'] > $max_confidence ) {
					$max_confidence = $value[0]['confidence'];
					$user_intent = $intent;
				}
			}
			
			switch ( $user_intent ) {
				case Omise_FBBot_Entity::GREETINGS:
				case Omise_FBBot_Entity::USER_GREETINGS:
					return self::greeting_message( $this->sender_id );
				
				case Omise_FBBot_Entity::CHECK_ORDER_STATUS:
					return self::rechecking_order_number_message();

				case Omise_FBBot_Entity::NEED_HELP:
					return self::helping_message();

				case Omise_FBBot_Entity::CALL_SHOP_OWNER:
					$success = Omise_FBBot_Handover_Protocol_Handler::switch_to_live_agent( $this->sender_id );
					return self::call_shop_owner_message( $success );
					 
				default:
					return self::unrecognized_message();
			}
		}

		// Handle unrecognize intent
		return self::unrecognized_message();
	}

	public static function greeting_message( $sender_id ) {
		$greeting_message = Omise_FBBot_Message_Store::get_greeting_message( $sender_id  );
		$buttons = Omise_FBBot_Message_Store::get_default_menu_buttons();
		
		return FB_Button_Template::create( $greeting_message, $buttons );
	}

	public static function helping_message() {
		$helping_message = Omise_FBBot_Message_Store::get_helping_message();
		$buttons = Omise_FBBot_Message_Store::get_default_menu_buttons();

		return FB_Button_Template::create( $helping_message, $buttons );
	}

	public static function rechecking_order_number_message() {
		$rechecking_order_massage = Omise_FBBot_Message_Store::get_rechecking_order_number_message();
		return FB_Message_Item::create( $rechecking_order_massage );
	}

	public static function get_ordet_status_message( $order_id ) {
		$order_status = Omise_FBBot_WooCommerce::check_order_status( $order_id );

		if ( ! $order_status ) {
			return FB_Message_Item::create( Omise_FBBot_Message_Store::get_order_not_found_message() );
		}

		return FB_Message_Item::create( Omise_FBBot_Message_Store::get_order_has_found_message( $order_status ) );
	}

	public static function feature_products_message( $sender_id ) {
		$feature_products = Omise_FBBot_WCProduct::featured();

		if ( ! $feature_products ) {
			$product_is_empty = Omise_FBBot_Message_Store::get_feature_products_is_empty_message();
 
			return FB_Message_Item::create( $product_is_empty );
		}

		$elements = array();

		foreach ( $feature_products as $product ) {
			$view_gallery_button = FB_Postback_Button_Item::create( __( 'Gallery ', 'omise' ) . $product->name, 'VIEW_PRODUCT__'.$product->id );

			$view_detail_button = FB_URL_Button_Item::create( __( 'View on website', 'omise' ), $product->permalink );

			$buying_url = site_url() . '/pay-on-messenger/?messenger_id=' . $sender_id .'&product_id=' . $product->id;
			$buy_now_button = FB_URL_Button_Item::create( __( 'Buy Now : ', 'omise' ) . $product->price .' '. $product->currency, $buying_url );

			$buttons = array( $view_gallery_button, $view_detail_button, $buy_now_button );
			$element = FB_Element_Item::create( $product->name, $product->short_description, $product->thumbnail_img, null, $buttons );

			array_push( $elements, $element );
		}

		return FB_Generic_Template::create( $elements );
	}

	public static function product_category_message( $paged = 1 ) {
		$data = Omise_FBBot_WCCategory::collection( $paged );

		$categories = $data['categories'];
		$current_page = $data['current_page'];
		$total_pages = $data['total_pages'];

		$func = function( $category ) {
			$viewProductsButton = FB_Postback_Button_Item::create( __('View ') . $category->name, 'VIEW_CATEGORY_PRODUCTS__' . $category->slug );
		  
			$buttons = array( $viewProductsButton );

			return FB_Element_Item::create( $category->name, $category->description, $category->thumbnail_img, NULL, $buttons );
		};

		$elements = array_map( $func, $categories );

		$category_list_message = array( FB_Generic_Template::create( $elements ) );

		if ( $current_page == $total_pages ) {
			return $category_list_message;
		}

		$view_more_button = FB_Postback_Button_Item::create( __( 'View more', 'omise' ), 'VIEW_MORE_CATEGORY__' . ($current_page + 1) );

		array_push( $category_list_message, FB_Button_Template::create( __( '🙋 Do you want to view more category ? 🛍', 'omise' ), [$view_more_button] ) );

		return $category_list_message;
	}

	public static function product_list_in_category_message( $messenger_id, $category_slug, $paged = 1 ) {
		$data = Omise_FBBot_WCCategory::products( $category_slug, $paged );

		if ( ! $data ) {
			return FB_Message_Item::create( Omise_FBBot_Message_Store::get_products_is_empty_message() );
		}

		$products = $data['products'];
		$current_page = $data['current_page'];
		$total_pages = $data['total_pages'];
	
		$elements = array();

		foreach ($products as $product) {
			$view_gallery_button = FB_Postback_Button_Item::create( __('Gallery ') . $product->name, 'VIEW_PRODUCT__'.$product->id );

			$view_detail_button = FB_URL_Button_Item::create( __( 'View on website', 'omise' ), $product->permalink );

			$buying_url = site_url() . '/pay-on-messenger/?messenger_id=' . $messenger_id .'&product_id=' . $product->id;
			$buy_now_button = FB_URL_Button_Item::create( __( 'Buy Now : ', 'omise' ) . $product->price.' '.$product->currency, $buying_url );

			$buttons = array( $view_gallery_button, $view_detail_button, $buy_now_button );
			$element = FB_Element_Item::create( $product->name, $product->short_description, $product->thumbnail_img, null, $buttons );

			array_push( $elements, $element );
		}

		$product_list_message = array( FB_Generic_Template::create( $elements ) );

		if ( $current_page == $total_pages ) {
			return $product_list_message;
		}

		$view_more_button = FB_Postback_Button_Item::create( __( 'View more', 'omise' ), 'VIEW_MORE_PRODUCT__' . $category_slug . '__' . ($current_page + 1) );

		array_push( $product_list_message, FB_Button_Template::create( __( '🙋 Do you want to view more product ? 🛍', 'omise' ), [$view_more_button] ) );
		
		return $product_list_message;
	}

	public static function product_gallery_message( $messenger_id, $product_id ) {
		$product = Omise_FBBot_WCProduct::create( $product_id );

		if ( ! $product->attachment_images ) {
			return FB_Message_Item::create( Omise_FBBot_Message_Store::get_product_image_is_empty_message() );
		}

		$elements = array();

		foreach ( $product->attachment_images as $image_url ) {
			// For test on localhost
			// $image_url = str_replace('http://localhost:8888', 'your tunnel url', $image_url);

			$buying_url = site_url() . '/pay-on-messenger/?messenger_id=' . $messenger_id .'&product_id=' . $product->id;
			$buy_now_button = FB_URL_Button_Item::create( __( 'Buy Now : ', 'omise' ) . $product->price.' '.$product->currency, $buying_url );

			$buttons = array( $buy_now_button );

			$element = FB_Element_Item::create( $product->name, $product->short_description, $image_url, null, $buttons );

			array_push( $elements, $element );
		}

		return FB_Generic_Template::create( $elements );
	}

	public static function prepare_confirm_order_message( $order_id ) {
		return FB_Message_Item::create( Omise_FBBot_Message_Store::get_prepare_confirm_order_message( $order_id ) );
	}

	public static function reply_for_purchase_message( $charge ) {
		$metadata = $charge->metadata;
		$order_id = $metadata->order_id;

		$message = '';

		switch ( $charge->status ) {
			case 'failed':
				$fail_message = $charge->failure_message;
				$message = Omise_FBBot_Message_Store::get_purchase_fail_message( $fail_message );
			break;

			case 'pending':
				if ( $charge->return_uri ) {
					$message = Omise_FBBot_Message_Store::get_purchase_pending_with3ds_message();
				} else {
					$message = Omise_FBBot_Message_Store::get_purchase_pending_message();
				}

			break;

			case 'reversed':
				$message = Omise_FBBot_Message_Store::get_purchase_reversed_message();
			break;

			case 'successful':
				$message = Omise_FBBot_Message_Store::get_purchase_completed_message();
			break;

			default:
				$message = Omise_FBBot_Message_Store::get_unknow_purchase_status_message();
			break;
		}

		return FB_Message_Item::create( $message );
	}

	public static function before_checking_order_message() { 
		return FB_Message_Item::create( Omise_FBBot_Message_Store::get_checking_order_helper_message() );
	}

	public static function unrecognized_message() {
		$unrecognized_message = Omise_FBBot_Message_Store::get_unrecognized_message();
		$buttons = Omise_FBBot_Message_Store::get_unrecognized_menu_buttons();

		return FB_Button_Template::create( $unrecognized_message, $buttons );
	}

	public static function call_shop_owner_message( $success ) {
		if ( $success ) {
			return FB_Message_Item::create( Omise_FBBot_Message_Store::get_call_shop_owner_success_message() );
		}

		return FB_Message_Item::create( Omise_FBBot_Message_Store::get_call_shop_owner_fail_message() ); 
	}
}