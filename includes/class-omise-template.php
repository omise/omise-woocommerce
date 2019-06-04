<?php
/**
 * @since 3.8
 */
class Omise_Template {
	/**
	 * @var string
	 */
	protected $default_template_location = 'templates';

	/**
	 * @var string
	 */
	protected $default_override_folder = 'omise';

	/**
	 * @var WP_Theme|null
	 */
	protected $current_theme;

	/**
	 * @var string|null
	 */
	protected $view_path;

	/**
	 * @var array|null
	 */
	protected $view_data;

	public function __construct() {
		$this->current_theme = wp_get_theme();
	}

	/**
	 * @param string     $path
	 * @param mixed|null $data
	 */
	public static function view( $path, $data = null ) {
		$template = new static;
		$template->set_view_path( $path );
		$template->set_view_data( $data );

		$data     = $template->get_view_data();
		$viewData = $data; // Backward compatible for Omise-WooCommerce v3.3 and below.

		require_once $template->get_view_path();
	}

	/**
	 * @return string
	 */
	public function get_view_path() {
		if ( $this->current_theme->exists() ) {
			$path = $this->current_theme->get_template_directory() . '/' . $this->default_override_folder . '/' . $this->view_path;
		}

		return file_exists( $path )
			? $path
			: OMISE_WOOCOMMERCE_PLUGIN_PATH . '/' . $this->default_template_location . '/' . $this->view_path;
	}

	/**
	 * @return array 
	 */
	public function get_view_data() {
		return $this->view_data;
	}

	/**
	 * @param string $path
	 */	
	public function set_view_path( $path ) {
		$this->view_path = trim( $path, '/' );
	}

	/**
	 * @param mixed $data
	 */
	public function set_view_data( $data ) {
		$this->view_data = is_array( $data ) ? $data : array( $data );
	}
}
