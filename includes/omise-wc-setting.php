<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

$text_domain = 'Omise';

return array(
	'enabled' => array(
		'title'       => Omise_Util::translate( 'Enable/Disable' ),
		'type'        => 'checkbox',
		'label'       => Omise_Util::translate( 'Enable Omise Payment Module.' ),
		'default'     => 'no'
	),
	'sandbox' => array(
		'title'       => Omise_Util::translate( 'Sandbox' ),
		'type'        => 'checkbox',
		'label'       => Omise_Util::translate( 'Enabling sandbox means that all your transactions will be in TEST mode.' ),
		'default'     => 'yes'
	),
	'test_public_key' => array(
		'title'       => Omise_Util::translate( 'Public key for test' ),
		'type'        => 'text',
		'description' => Omise_Util::translate( 'The "Test" mode public key can be found in Omise Dashboard.' )
	),
	'test_private_key' => array(
		'title'       => Omise_Util::translate( 'Secret key for test' ),
		'type'        => 'password',
		'description' => Omise_Util::translate( 'The "Test" mode secret key can be found in Omise Dashboard.' )
	),
	'live_public_key' => array(
		'title'       => Omise_Util::translate( 'Public key for live' ),
		'type'        => 'text',
		'description' => Omise_Util::translate( 'The "Live" mode public key can be found in Omise Dashboard.' )
	),
	'live_private_key' => array(
		'title'       => Omise_Util::translate( 'Secret key for live' ),
		'type'        => 'password',
		'description' => Omise_Util::translate( 'The "Live" mode secret key can be found in Omise Dashboard.' )
	),
	'advanced' => array(
		'title'       => Omise_Util::translate( 'Advance Settings' ),
		'type'        => 'title',
		'description' => '',
	),
	'accept_visa' => array(
		'title'       => Omise_Util::translate( 'Supported card icons' ),
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
		'description' => Omise_Util::translate( 'This only controls the icons displayed on the checkout page.<br />It is not related to card processing on Omise payment gateway.' )
	),
	'title' => array(
		'title'       => Omise_Util::translate( 'Title', 'Label for setting of checkout form title' ),
		'type'        => 'text',
		'description' => Omise_Util::translate( 'This controls the title which the user sees during checkout.' ),
		'default'     => Omise_Util::translate( 'Omise Payment Gateway', 'Default title at checkout form' )
	),
	'payment_action' => array(
		'title'       => Omise_Util::translate( 'Payment action' ),
		'type'        => 'select',
		'description' => Omise_Util::translate( 'Manual Capture or Capture Automatically' ),
		'default'     => 'auto_capture',
		'class'       => 'wc-enhanced-select',
		'options'     => array(
			'auto_capture'   => Omise_Util::translate( 'Auto Capture', 'Setting auto capture' ),
			'manual_capture' => Omise_Util::translate( 'Manual Capture', 'Setting manual capture' )
		),
		'desc_tip'    => true
	),
	'omise_3ds' => array(
		'title'       => Omise_Util::translate( '3-D Secure support' ),
		'type'        => 'checkbox',
		'label'       => Omise_Util::translate( 'Enable or disable 3-D Secure for the account. (Japan-based accounts are not eligible for the service.)' ),
		'default'     => 'no'
	)
);