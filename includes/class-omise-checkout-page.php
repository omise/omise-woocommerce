<?php
defined('ABSPATH') or die('No direct script access allowed.');

if (class_exists('Omise_Checkout_Page')) {
  return;
}

class Omise_Checkout_Page
{
  public static $PATH = 'omise_checkout';
  protected static $the_instance;

  public static function get_instance()
  {
    if (!self::$the_instance) {
      self::$the_instance = new self();
    }

    return self::$the_instance;
  }

  public function init()
  {
    // TODO: Recheck another init?
    add_action('init', [$this, 'register_checkout_page']);
    add_filter('query_vars', [$this, 'register_query_vars']);
    add_action('wp', [$this, 'maybe_enqueue_page_scripts']);
    add_action('template_include', [$this, 'load_template']);

    add_action('template_redirect', function() {
            error_log('redirect JS: ' . plugin_dir_url(__DIR__) . 'assets/javascripts/checkout-page-script.js');
});
    // add_action( 'wp', [ $this, 'enqueue_page_assets' ] );
  }

  public function register_checkout_page()
  {
    error_log('Registering Omise Checkout Page');
    $route_path = self::$PATH;
    add_rewrite_rule(
      "{$route_path}/(session_[a-zA-Z0-9]+)",
      'index.php?omise_checkout=1&session=$matches[1]',
      'top'
    );
  }

  public function register_query_vars($vars)
  {
    $vars[] = 'omise_checkout';
    $vars[] = 'session';
    return $vars;
  }

  public function load_template($template)
  {
    error_log('load template' . get_query_var('omise_checkout'));

    if (!get_query_var('omise_checkout')) {
      return $template;
    }

    $session_id = get_query_var('session');
    if (!$session_id) {
      return $template;
    }

    // if (!wp_script_is('omise-checkout-page-script', 'enqueued')) {
    //   $script_path = 'assets/javascripts/checkout-page.js';
    //   error_log('Enqueuing Omise Checkout Page script: ' . plugin_dir_url(__DIR__) . $script_path);
    //   wp_enqueue_script(
    //     'omise-checkout-page-script',
    //     plugin_dir_url(__DIR__) . $script_path,
    //     [],
    //     filemtime(plugin_dir_path(__DIR__) . $script_path),
    //     true
    //   );
    // }

    // $this->enqueue_page_assets();
    // if (!wp_style_is('omise-checkout-page-style', 'enqueued')) {
    //         error_log('Enqueuing Omise Checkout Page style: '.plugin_dir_url(__DIR__) . 'assets/css/checkout-page.css');
    //   wp_enqueue_style(
    //     'omise-checkout-page-style',
    //     plugin_dir_url(__DIR__) . 'assets/css/checkout-page.css',
    //     [],
    //     filemtime(plugin_dir_path(__DIR__) . 'assets/css/checkout-page.css')
    //   );
    // }

    $template_file = plugin_dir_path(__FILE__) . '../templates/payment/checkout-page.php';
    return $template_file;
    // error_log('template file: ' . $template_file);
    // add_filter('template_include', function ($template) use ($template_file) {
    //   return $template_file;
    // });
  }

  public function maybe_enqueue_page_scripts()
  {
    // if (get_query_var('omise_checkout')) {
      add_action('wp_enqueue_scripts', [$this, 'enqueue_page_scripts']);
    // }
  }

  public function enqueue_page_scripts()
  {
    $is_script_loaded = wp_script_is('omise-checkout-page-script', 'enqueued');
    // error_log('enqueue' . intval(get_query_var( 'omise_checkout' )));

    if (!$is_script_loaded) {
      error_log('enqueue Checkout page JS');

      $script_file = plugin_dir_url(__DIR__) . 'assets/javascripts/checkout-page-script.js';
      error_log('Enqueuing Omise Checkout Page script: ' . $script_file);
      wp_enqueue_script(
        'omise-checkout-page-script',
        $script_file,
        [],
        OMISE_WOOCOMMERCE_PLUGIN_VERSION,
        true
      );
    }

    if (!wp_style_is('omise-checkout-page-style', 'enqueued')) {
            error_log('Enqueuing Omise Checkout Page style: '.plugin_dir_url(__DIR__) . 'assets/css/checkout-page.css');
      wp_enqueue_style(
        'omise-checkout-page-style',
        plugin_dir_url(__DIR__) . 'assets/css/checkout-page.css',
        [],
        filemtime(plugin_dir_path(__DIR__) . 'assets/css/checkout-page.css')
      );
    }
  }
}
