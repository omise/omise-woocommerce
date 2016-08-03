<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

return array(
	'enabled' => array(
		'title'       => 'Enable/Disable',
		'type'        => 'checkbox',
		'label'       => 'Enable Omise Payment Module.',
		'default'     => 'no'
	),
	'sandbox' => array(
		'title'       => 'Sandbox',
		'type'        => 'checkbox',
		'label'       => 'Sandbox mode means everything is in TEST mode',
		'default'     => 'yes'
	),
	'test_public_key' => array(
		'title'       => 'Public key for test',
		'type'        => 'text',
		'description' => 'The "Test" mode public key which can be found in Omise Dashboard'
	),
	'test_private_key' => array(
		'title'       => 'Secret key for test',
		'type'        => 'password',
		'description' => 'The "Test" mode secret key which can be found in Omise Dashboard'
	),
	'live_public_key' => array(
		'title'       => 'Public key for live',
		'type'        => 'text',
		'description' => 'The "Live" mode public key which can be found in Omise Dashboard'
	),
	'live_private_key' => array(
		'title'       => 'Secret key for live',
		'type'        => 'password',
		'description' => 'The "Live" mode secret key which can be found in Omise Dashboard'
	),
	'advanced' => array(
		'title'       => 'Advanced options', 'woocommerce',
		'type'        => 'title'
	),
	'accept_visa' => array(
		'title'       => 'Supported card icons',
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
		'description' => 'This only controls the icons displayed on the checkout page.<br />It is not related to card processing on Omise payment gateway.'
	),
	'title' => array(
		'title'       => 'Title',
		'type'        => 'text',
		'description' => 'This controls the title which the user sees during checkout.',
		'default'     => 'Omise Payment Gateway'
	),
	'payment_action' => array(
		'title'       => 'Payment Action',
		'type'        => 'select',
		'description' => 'Manual Capture or Capture Automatically',
		'default'     => 'auto_capture',
		'class'       => 'wc-enhanced-select',
		'options'     => $this->form_field_payment_actions(),
		'desc_tip'    => true
	),
	'omise_3ds' => array(
		'title'       => '3DSecure Support',
		'type'        => 'checkbox',
		'label'       => 'Enables 3DSecure on this account (does not support for Japan account)',
		'default'     => 'no'
	),
	'description' => array(
		'title'       => 'Description',
		'type'        => 'textarea',
		'description' => 'This controls the description which the user sees during checkout.',
		'default'     => 'Omise payment gateway.'
	)
);