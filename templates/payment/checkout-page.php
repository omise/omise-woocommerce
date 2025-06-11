<?php
// my-plugin/templates/my-custom-plugin-output.php

// This file outputs the full HTML structure.
// No get_header() or get_footer() means no theme integration.
// But wp_head() and wp_footer() are still essential for enqueued assets.
$checkout_page_session_id = get_query_var('session')
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <title><?php bloginfo( 'name' ); ?> - Omise Checkout</title>
    <style>
        html { margin: 0 !important; }
        body { margin: 0; padding: 0; background-color: #f0f0f0; font-family: sans-serif; }
        /* Add more custom styles specific to this page */
    </style>
</head>
<body <?php body_class(); ?>>

  <!-- <h1 class="omise-hello">Hello, Checkout Page</h1> -->
  <iframe
      src="<?php echo esc_url( "http://192.168.1.197:50001/web/{$checkout_page_session_id}" ); ?>"
      style="width: 100%; height: 100vh; border: none;"
      allow="payment"></iframe>
  <!-- <script>console.log('inline test script');</script> -->
  <?php wp_footer(); ?>
</body>
</html>
