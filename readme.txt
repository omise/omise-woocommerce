=== Omise WooCommerce ===
Contributors: Omise
Tags: omise, payment, payment gateway, woocommerce plugin
Requires at least: 4.3.1
Tested up to: 4.5.3
Stable tag: 1.2.3
License: MIT
License URI: https://opensource.org/licenses/MIT

Omise plugin is a plugin designed specifically for WooCommerce. The plugin adds support for Omise Payment Gateway payment method to WooCommerce.

== Description ==
Omise WooCommerce Gateway Plugin is a wordpress plugin designed specifically for WooCommerce. The plugin adds support for Omise Payment Gateway payment method to WooCommerce.

== Installation ==
After getting the source code, either downloading as a zip or git clone, put it in Wordpress plugins folder (i.e. mv omise-woocommerce /wp-content/plugins/ or upload a zip via Wordpress admin Plugins section, just like the other Wordpress plugins).

Then, Omise Gateway Wordpress plugin should be appeared in Wordpress admin page, under the Plugins menu.
From there:
1. Activate the plugin
2. Go to WooCommerce -> Settings
3. Select the Checkout tab on top.
4. Select Omise payment gateway at the bottom of the page, under Payment Gateways.
5. Optionally, if you\'d like to have Omise Payment gateway as a default payment gateway, you can check Default.
6. Click the Settings button and adjust the options.

== Screenshots ==
1. Omise Payment Gateway Dashboard
2. Omise Payment Gateway Setting Page
3. Omise Payment Gateway Checkout Form

== Changelog ==
= 1.2.3 =
(Added) Add a new feature, localization
(Added) Add a translation file for Japanese
(Changed) Change a page header from transactions history to charges history
(Removed) Remove a link, view detail, from each row of transactions and transfers history table
(Removed) Remove sub-tabs, charges and transfers
(Removed) Remove an unused setting, description

= 1.2.2 =
(Improved) Specify the display size of card brand image and allow customer to define their own style
(Removed) Remove an unused unit test of the library, omise-php

= 1.2.1 =
(Added) Configuration for card brand logo display
(Added) List of transfers
(Fixed) Changing page by specify the page number which is not functional

= 1.2.0 =
(Added) manual capture feature
(Added) supported JPY currency
(Added) shortcut menu to Omise's setting page
(Added) Included Omise-PHP 2.4.1 library to the project.
(Improved) Redesigned Omise Dashboard
(Improved) Re-ordered fields in Omise Setting page.
(Improved) Better handle error cases (error messages)
(Improved) Better handle WC order note to trace Omise's actions back.
(Improved) Revised PHP code to following the WordPress Coding Standards.
(Improved) Fixed/Improved various things.

= 1.1.1 =
Added Omise-Version into the cURL request header.

= 1.1.0 =
Adds support for 3-D Secure feature

== Upgrade Notice ==
= 1.2.3 =
(Added) Add a new feature, localization
(Added) Add a translation file for Japanese
(Changed) Change a page header from transactions history to charges history
(Removed) Remove a link, view detail, from each row of transactions and transfers history table
(Removed) Remove sub-tabs, charges and transfers
(Removed) Remove an unused setting, description

= 1.2.2 =
(Improved) Specify the display size of card brand image and allow customer to define their own style
(Removed) Remove an unused unit test of the library, omise-php

= 1.2.1 =
(Added) Configuration for card brand logo display
(Added) List of transfers
(Fixed) Changing page by specify the page number which is not functional

= 1.2.0 =
(Added) manual capture feature
(Added) supported JPY currency
(Added) shortcut menu to Omise's setting page
(Added) Included Omise-PHP 2.4.1 library to the project.
(Improved) Redesigned Omise Dashboard
(Improved) Re-ordered fields in Omise Setting page.
(Improved) Better handle error cases (error messages)
(Improved) Better handle WC order note to trace Omise's actions back.
(Improved) Revised PHP code to following the WordPress Coding Standards.
(Improved) Fixed/Improved various things.