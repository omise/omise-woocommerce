<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

$text_domain = 'Omise';

return array(
	'enabled' => array(
		'title'       => __( 'Enable/Disable', 'omise' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable Omise Payment Module.', 'omise' ),
		'default'     => 'no'
	),
	'sandbox' => array(
		'title'       => __( 'Sandbox', 'omise' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enabling sandbox means that all your transactions will be in TEST mode.', 'omise' ),
		'default'     => 'yes'
	),
	'test_public_key' => array(
		'title'       => __( 'Public key for test', 'omise' ),
		'type'        => 'text',
		'description' => __( 'The "Test" mode public key can be found in Omise Dashboard.', 'omise' )
	),
	'test_private_key' => array(
		'title'       => __( 'Secret key for test', 'omise' ),
		'type'        => 'password',
		'description' => __( 'The "Test" mode secret key can be found in Omise Dashboard.', 'omise' )
	),
	'live_public_key' => array(
		'title'       => __( 'Public key for live', 'omise' ),
		'type'        => 'text',
		'description' => __( 'The "Live" mode public key can be found in Omise Dashboard.', 'omise' )
	),
	'live_private_key' => array(
		'title'       => __( 'Secret key for live', 'omise' ),
		'type'        => 'password',
		'description' => __( 'The "Live" mode secret key can be found in Omise Dashboard.', 'omise' )
	),
	'advanced' => array(
		'title'       => __( 'Advance Settings', 'omise' ),
		'type'        => 'title',
		'description' => '',
	),
	'accept_visa' => array(
		'title'       => __( 'Supported card icons', 'omise' ),
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
		'description' => __( 'This only controls the icons displayed on the checkout page.<br />It is not related to card processing on Omise payment gateway.', 'omise' )
	),
	'title' => array(
		'title'       => _x( 'Title', 'Label for setting of checkout form title', 'omise' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'omise' ),
		'default'     => _x( 'Omise Payment Gateway', 'Default title at checkout form', 'omise' )
	),
	'payment_action' => array(
		'title'       => __( 'Payment action', 'omise' ),
		'type'        => 'select',
		'description' => __( 'Manual Capture or Capture Automatically', 'omise' ),
		'default'     => 'auto_capture',
		'class'       => 'wc-enhanced-select',
		'options'     => array(
			'auto_capture'   => _x( 'Auto Capture', 'Setting auto capture', 'omise' ),
			'manual_capture' => _x( 'Manual Capture', 'Setting manual capture', 'omise' )
		),
		'desc_tip'    => true
	),
	'omise_3ds' => array(
		'title'       => __( '3-D Secure support', 'omise' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable or disable 3-D Secure for the account. (Japan-based accounts are not eligible for the service.)', 'omise' ),
		'default'     => 'no'
	)
);