<?php

defined('ABSPATH') or die('No direct script access allowed.');

if (class_exists('Omise_Page_Settings')) {
	return;
}

class Omise_Page_Card_From_Customization extends Omise_Admin_Page
{
	private static $instance;
	const DEFAULT_UPA_THEME_COLOR = '#173799';
	const DEFAULT_UPA_TEXT_COLOR  = '#FFFFFF';

	public static function get_instance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	const PAGE_NAME = 'omise_card_form_customization_option';

	private function get_light_theme()
	{
		return [
			'font' => [
				'name' => 'Poppins',
				'size' => 16,
				'custom_name' => ''
			],
			'input' => [
				'height' => '44px',
				'border_radius' => '4px',
				'border_color' => '#ced3de',
				'active_border_color' => '#1451cc',
				'background_color' => '#ffffff',
				'label_color' => '#212121',
				'text_color' => '#212121',
				'placeholder_color' => '#98a1b2',
			],
			'checkbox' => [
				'text_color' => '#1c2433',
				'theme_color' => '#1451cc',
			],
			'upa' => [
				'theme_color' => self::DEFAULT_UPA_THEME_COLOR,
				'text_color' => self::DEFAULT_UPA_TEXT_COLOR,
			]
		];
	}

	private function get_dark_theme()
	{
		return [
			'font' => [
				'name' => 'Poppins',
				'size' => 16,
				'custom_name' => ''
			],
			'input' => [
				'height' => '44px',
				'border_radius' => '4px',
				'border_color' => '#475266',
				'active_border_color' => '#475266',
				'background_color' => '#131926',
				'label_color' => '#E6EAF2',
				'text_color' => '#ffffff',
				'placeholder_color' => '#DBDBDB',
			],
			'checkbox' => [
				'text_color' => '#E6EAF2',
				'theme_color' => '#1451CC',
			],
			'upa' => [
				'theme_color' => self::DEFAULT_UPA_THEME_COLOR,
				'text_color' => self::DEFAULT_UPA_TEXT_COLOR,
			]
		];
	}

	protected function get_default_design_setting()
	{
		$theme = 'light';
		if (class_exists('Omise_Payment_Creditcard')) {
			$theme = (new Omise_Payment_Creditcard())->get_option('card_form_theme');
		}

		return (empty($theme) || $theme == 'light')
			? $this->get_light_theme()
			: $this->get_dark_theme();
	}

	/**
	 * get design setting
	 */
	public function get_design_setting()
	{
		$formDesign = get_option(self::PAGE_NAME);
		if (!is_array($formDesign)) {
			$formDesign = [];
		}

		$defaultValues = $this->get_default_design_setting();
		foreach ($defaultValues as $componentKey => $componentValues) {
			if (!isset($formDesign[$componentKey]) || !is_array($formDesign[$componentKey])) {
				$formDesign[$componentKey] = [];
			}

			foreach ($componentValues as $key => $defaultValue) {
				if (!array_key_exists($key, $formDesign[$componentKey])) {
					$formDesign[$componentKey][$key] = $defaultValue;
				}
			}
		}

		return $formDesign;
	}

	/**
	 * Retrieve style payload for UPA session customization.
	 *
	 * @return array
	 */
	public function get_upa_style_settings()
	{
		$formDesign = $this->get_design_setting();
		$themeColor = self::DEFAULT_UPA_THEME_COLOR;
		$textColor = self::DEFAULT_UPA_TEXT_COLOR;

		if (isset($formDesign['upa']) && is_array($formDesign['upa'])) {
			if (isset($formDesign['upa']['theme_color'])) {
				$sanitizedThemeColor = $this->sanitize_hex_color($formDesign['upa']['theme_color']);
				if ('' !== $sanitizedThemeColor) {
					$themeColor = $sanitizedThemeColor;
				}
			}

			if (isset($formDesign['upa']['text_color'])) {
				$sanitizedTextColor = $this->sanitize_hex_color($formDesign['upa']['text_color']);
				if ('' !== $sanitizedTextColor) {
					$textColor = $sanitizedTextColor;
				}
			}
		}

		return [
			'theme_color' => $themeColor,
			'text_color' => $textColor
		];
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	private function sanitize_hex_color($value)
	{
		if (!is_string($value)) {
			return '';
		}

		$value = trim($value);
		$sanitized = sanitize_hex_color($value);
		if (is_string($sanitized) && '' !== $sanitized) {
			return $sanitized;
		}

		return '';
	}

	/**
	 * Sanitize UPA color settings on save to ensure persisted values are valid hex colors.
	 *
	 * @param string $componentKey
	 * @param string $key
	 * @param string $value
	 *
	 * @return string
	 */
	private function sanitize_upa_color_setting($componentKey, $key, $value)
	{
		if ('upa' !== $componentKey) {
			return $value;
		}

		if ('theme_color' !== $key && 'text_color' !== $key) {
			return $value;
		}

		$sanitized = $this->sanitize_hex_color($value);
		if ('' !== $sanitized) {
			return $sanitized;
		}

		return 'theme_color' === $key
			? self::DEFAULT_UPA_THEME_COLOR
			: self::DEFAULT_UPA_TEXT_COLOR;
	}

	/**
	 * @param array $data
	 *
	 * @since  3.1
	 */
	protected function save($data)
	{
		if (!isset($data['omise_setting_page_nonce']) || !wp_verify_nonce($data['omise_setting_page_nonce'], 'omise-setting')) {
			wp_die(__('You are not allowed to modify the settings from a suspicious source.', 'omise'));
		}
		$options = [];
		$defaultValues = $this->get_default_design_setting();
		$existingValues = $this->get_design_setting();

		// Sanitize the field POST params
		// the fist loop get the component name. i.e input, checkout, font
		// and send loop get the styling key of the component. i.e name, size, border, color
		foreach ($defaultValues as $componentKey => $componentValue) {
			foreach ($componentValue as $key => $val) {
				$value = isset($data[$componentKey][$key]) ? $data[$componentKey][$key] : $existingValues[$componentKey][$key];
				$sanitizedValue = sanitize_text_field($value);
				$options[$componentKey][$key] = $this->sanitize_upa_color_setting($componentKey, $key, $sanitizedValue);
			}
		}

		update_option(self::PAGE_NAME, $options);
		$this->add_message('message', "Update has been saved!");
	}

	/**
	 * @param array $data
	 *
	 * @since  3.1
	 */
	protected function reset_default_setting($data)
	{
		if (
			!isset($data['omise_setting_page_nonce']) ||
			!wp_verify_nonce($data['omise_setting_page_nonce'], 'omise-setting')
		) {
			wp_die(__('You are not allowed to modify the settings from a suspicious source.', 'omise'));
		}

		update_option(self::PAGE_NAME, null);

		$this->add_message(
			'message',
			"Setting have been reset!"
		);
	}

	/**
	 * @since  3.1
	 */
	public static function render()
	{
		$page = self::get_instance();

		if (isset($_POST['omise_customization_submit'])) {
			$page->save($_POST);
		}

		if (isset($_POST['omise_customization_reset'])) {
			$page->reset_default_setting($_POST);
		}

		$formDesign = $page->get_design_setting();

		include_once __DIR__ . '/views/omise-page-card-form-customization.php';
	}
}
