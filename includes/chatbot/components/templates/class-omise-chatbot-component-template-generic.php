<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Template_Generic' ) ) {
	return;
}

/**
 * @see https://developers.facebook.com/docs/messenger-platform/send-messages/template/generic
 */
class Omise_Chatbot_Component_Template_Generic extends Omise_Chatbot_Component_Template {
	/**
	 * @var string
	 */
	protected $template_type = 'generic';

	/**
	 * @var array
	 */
	protected $template_payload = array(
		'elements' => array()
	);
}
