<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Facebook_Webhook_Event_Messaging_Postbacks' ) ) {
	return;
}

class Omise_Chatbot_Facebook_Webhook_Event_Messaging_Postbacks {
	/**
	 * @var string  of an event name.
	 */
	public $event = 'messaging_postbacks';

	/**
	 * @var Omise_Chatbot
	 */
	protected $chatbot;

	/**
	 * @var array
	 */
	protected $components;

	/**
	 * @since 3.2
	 */
	public function __construct() {
		$this->chatbot    = new Omise_Chatbot;
		$this->components = array(
			'text'             => new Omise_Chatbot_Component_Text,
			'template_generic' => new Omise_Chatbot_Component_Template_Generic,
			'template_button'  => new Omise_Chatbot_Component_Template_Button
		);
	}

	/**
	 * Note. It doesn't return anything back because nobody using the result
	 * unless we have a 'log' system.
	 *
	 * @param  mixed $messaging
	 *
	 * @return void
	 *
	 * @since  3.2
	 */
	public function handle( $messaging ) {
		$payload = strtolower( 'payload_' . $messaging['postback']['payload'] );

		if ( method_exists( $this, $payload ) ) {
			$this->$payload( $messaging );
		}
	}

	/**
	 * @param  mixed $messaging
	 *
	 * @return void
	 *
	 * @since  3.2
	 */
	protected function payload_get_start_tapped( $messaging ) {
		$this->components['template_button']
			->set_text( 'I\'m glad you visited How may I assist ?' )
			->add_buttons(
				array(
					new Omise_Chatbot_Component_Button_Featuredproducts,
					new Omise_Chatbot_Component_Button_Productcategory,
					new Omise_Chatbot_Component_Button_Orderstatus
				)
			);

		$this->chatbot->message_to(
			$messaging['sender']['id'],
			$this->components['template_button']->to_array()
		);
	}

	/**
	 * @param  mixed $messaging
	 *
	 * @return void
	 *
	 * @since  3.2
	 */
	protected function payload_action_featured_products( $messaging ) {
		foreach ( wc_get_featured_product_ids() as $product_id ) {
			$this->components['template_generic']->add_element(
				new Omise_Chatbot_Component_Element_Product( wc_get_product( $product_id ) )
			);
		}

		$this->chatbot->message_to(
			$messaging['sender']['id'],
			$this->components['template_generic']->to_array()
		);

		$this->payload_get_start_tapped( $messaging );
	}

	/**
	 * @param  mixed $messaging
	 *
	 * @return void
	 *
	 * @since  3.2
	 */
	protected function payload_action_product_category( $messaging ) {
		// TODO: This whole code inside this method is just for mock.
		$this->components['text']->set_text( 'Hey! You just tapped "Product Category" button.' );

		$this->chatbot->message_to(
			$messaging['sender']['id'],
			$this->components['text']->to_array()
		);

		$this->payload_get_start_tapped( $messaging );
	}

	/**
	 * @param  mixed $messaging
	 *
	 * @return void
	 *
	 * @since  3.2
	 */
	protected function payload_action_order_status( $messaging ) {
		// TODO: This whole code inside this method is just for mock.
		$this->components['text']->set_text( 'Hey! You just tapped "Order Status" button.' );

		$this->chatbot->message_to(
			$messaging['sender']['id'],
			$this->components['text']->to_array()
		);

		$this->payload_get_start_tapped( $messaging );
	}
}
