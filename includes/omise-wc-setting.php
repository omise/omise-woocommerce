<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

$text_domain = 'Omise';

return array(
	'enabled' => array(
		'title'       => __( 'Enable/Disable', $text_domain ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable Omise Payment Module.', $text_domain ),
		'default'     => 'no'
	),
	'sandbox' => array(
		'title'       => __( 'Sandbox', $text_domain ),
		'type'        => 'checkbox',
		'label'       => __( 'Sandbox mode means everything is in TEST mode', $text_domain ),
		'default'     => 'yes'
	),
	'test_public_key' => array(
		'title'       => __( 'Public key for test', $text_domain ),
		'type'        => 'text',
		'description' => __( 'The "Test" mode public key which can be found in Omise Dashboard', $text_domain )
	),
	'test_private_key' => array(
		'title'       => __( 'Secret key for test', $text_domain ),
		'type'        => 'password',
		'description' => __( 'The "Test" mode secret key which can be found in Omise Dashboard', $text_domain )
	),
	'live_public_key' => array(
		'title'       => __( 'Public key for live', $text_domain ),
		'type'        => 'text',
		'description' => __( 'The "Live" mode public key which can be found in Omise Dashboard', $text_domain )
	),
	'live_private_key' => array(
		'title'       => __( 'Secret key for live', $text_domain ),
		'type'        => 'password',
		'description' => __( 'The "Live" mode secret key which can be found in Omise Dashboard', $text_domain )
	),
	'advanced' => array(
		'title'       => __( 'Advanced options', $text_domain ),
		'type'        => 'title'
	),
	'accept_visa' => array(
		'title'       => __( 'Supported card icons', $text_domain ),
		'type'        => 'checkbox',
		'label'       => Omise_Card_Image::get_visa_image(),
		'css'         => Omise_Card_Image::get_css(),
		'default'     => Omise_Card_Image::get_visa_default_display()
	),
	'accept_mastercard' => array(
		'type'        => 'checkbox',
		'label'       => Omise_Card_Image::get_mastercard_image(),
		'css'         => Omise_Card_Image::get_css(),
		'default'     => Omise_Card_Image::get_mastercard_default_display()
	),
	'accept_jcb' => array(
		'type'        => 'checkbox',
		'label'       => Omise_Card_Image::get_jcb_image(),
		'css'         => Omise_Card_Image::get_css(),
		'default'     => Omise_Card_Image::get_jcb_default_display()
	),
	'accept_diners' => array(
		'type'        => 'checkbox',
		'label'       => Omise_Card_Image::get_diners_image(),
		'css'         => Omise_Card_Image::get_css(),
		'default'     => Omise_Card_Image::get_diners_default_display()
	),
	'accept_amex' => array(
		'type'        => 'checkbox',
		'label'       => Omise_Card_Image::get_amex_image(),
		'css'         => Omise_Card_Image::get_css(),
		'default'     => Omise_Card_Image::get_amex_default_display(),
		'description' => __( 'This only controls the icons displayed on the checkout page.<br />It is not related to card processing on Omise payment gateway.', $text_domain )
	),
	'title' => array(
		'title'       => __( 'Title', $text_domain ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', $text_domain ),
		'default'     => __( 'Omise Payment Gateway', $text_domain )
	),
	'payment_action' => array(
		'title'       => __( 'Payment Action', $text_domain ),
		'type'        => 'select',
		'description' => __( 'Manual Capture or Capture Automatically', $text_domain ),
		'default'     => 'auto_capture',
		'class'       => 'wc-enhanced-select',
		'options'     => $this->form_field_payment_actions(),
		'desc_tip'    => true
	),
	'omise_3ds' => array(
		'title'       => __( '3DSecure Support', $text_domain ),
		'type'        => 'checkbox',
		'label'       => __( 'Enables 3DSecure on this account (does not support for Japan account)', $text_domain ),
		'default'     => 'no'
	),
	'description' => array(
		'title'       => __( 'Description', $text_domain ),
		'type'        => 'textarea',
		'description' => __( 'This controls the description which the user sees during checkout.', $text_domain ),
		'default'     => __( 'Omise payment gateway.', $text_domain )
	)
);