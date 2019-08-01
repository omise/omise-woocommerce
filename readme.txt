=== Omise WooCommerce ===
Contributors: Omise
Tags: omise, payment, payment gateway, woocommerce plugin, installment, internet banking, alipay
Requires at least: 4.3.1
Tested up to: 5.2.2
Stable tag: 3.6
License: MIT
License URI: https://opensource.org/licenses/MIT

Omise plugin is is the official payment extension which provides support for Omise payment gateway for store builders working on the WooCommerce platform

== Description ==

Omise WooCommerce Gateway Plugin is a WordPress plugin designed specifically for WooCommerce. The plugin adds support for Omise Payment Gateway payment method to WooCommerce.

== Installation ==

After getting the source code, either downloading as a zip or git clone, put it in WordPress plugins folder (i.e. mv omise /wp-content/plugins/ or upload a zip via WordPress admin Plugins section, just like the other WordPress plugins).

Then, Omise Gateway WordPress plugin should be appeared in WordPress admin page, under the Plugins menu.
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

= 3.6 =

#### ✨ Highlights

- [#118](https://github.com/omise/omise-woocommerce/pull/118): Better way to handle amount subunit, adding support for more currencies.

#### 🚀 Enhancements

- [#124](https://github.com/omise/omise-woocommerce/pull/124): Updating npm vulnerable dependencies.

= 3.5 =

#### 👾 Bug Fixes

- [#120](https://github.com/omise/omise-woocommerce/pull/120): Properly assigning querystrings for the payment callback endpoints.

= 3.4 =

#### ✨ Highlights

- [#115](https://github.com/omise/omise-woocommerce/pull/115): Installment, display instalment monthly amount & interest fee at the checkout page.
- [#99](https://github.com/omise/omise-woocommerce/pull/99): Introduce Installment payment method.

#### 🚀 Enhancements

- [#114](https://github.com/omise/omise-woocommerce/pull/114): Update Omise-PHP library from v2.11.1 to v2.11.2.
- [#113](https://github.com/omise/omise-woocommerce/pull/113): Unifying stylesheet & fixing some broken styles.
- [#112](https://github.com/omise/omise-woocommerce/pull/112): Correcting & enhancing payment response messages.
- [#111](https://github.com/omise/omise-woocommerce/pull/111): Removing all redundant code regarding to WC Order transaction ID assignment.
- [#109](https://github.com/omise/omise-woocommerce/pull/109): 🧹 Code Cleaning: Relocating 'capture' method from Omise_Payment class to Omise_Payment_Creditcard.
- [#108](https://github.com/omise/omise-woocommerce/pull/108): Refactoring, unify Omise key(s)-defining into one place.
- [#101](https://github.com/omise/omise-woocommerce/pull/101): Refactoring, simplifying payment processors.

= 3.3 =

#### 🚀 Enhancements

- [#106](https://github.com/omise/omise-woocommerce/pull/106): Removing unused stylesheet & js file.
- [#102](https://github.com/omise/omise-woocommerce/pull/102): Migrating all related code to support Omise API version v2017-11-02.
- [#98](https://github.com/omise/omise-woocommerce/pull/98): Added filter hooks for charge.description and charge.metadata.
- [#96](https://github.com/omise/omise-woocommerce/pull/96): Refactoring plugin-initial code structure - part 3: Organizing Omise_Admin class.
- [#95](https://github.com/omise/omise-woocommerce/pull/95): Refactoring plugin-initial code structure - part 2: Relocating, renaming functions and method.
- [#94](https://github.com/omise/omise-woocommerce/pull/94): Refactoring plugin-initial code structure - part 1: Enhancing the behavior of checking dependency plugin.
- [#93](https://github.com/omise/omise-woocommerce/pull/93): Upgrade Omise-PHP library from v2.8.0 to v2.11.1.
- [#91](https://github.com/omise/omise-woocommerce/pull/91): Removing the deprecated function (from jQuery's reported).
- [#86](https://github.com/omise/omise-woocommerce/pull/86): README, update the installation instruction, enhance overall contents.

#### 👾 Bug Fixes

- [#104](https://github.com/omise/omise-woocommerce/pull/104): Omise Setting Page, sanitizing input fields before save.

= 3.2 =

#### ✨ Highlights

- Support multi currency (PR [#84](https://github.com/omise/omise-woocommerce/pull/84))

#### 🚀 Enhancements

- Remove legacy files and codes (that we no longer use) (PR [#85](https://github.com/omise/omise-woocommerce/pull/85))

#### 👾 Bug Fixes

- Issue #78 fatal error, if install omise plugin before woo commerce (PR [#83](https://github.com/omise/omise-woocommerce/pull/83), [#88](https://github.com/omise/omise-woocommerce/pull/88))

= 3.1 =

#### ✨ Highlights

- Introduce WebHook feature. (PR [#62](https://github.com/omise/omise-woocommerce/pull/62))
- Add Omise Setting page and enhance Omise setting process. (PR [#61](https://github.com/omise/omise-woocommerce/pull/61))

#### 🚀 Enhancements

- Spell WordPress correctly! (PR [#56](https://github.com/omise/omise-woocommerce/pull/56)) :by [@mayukojpn](https://github.com/mayukojpn))
- Support WooCommerce 2.x series & PHP 5.4 (PR [#59](https://github.com/omise/omise-woocommerce/pull/59))

= 3.0 =

#### ✨ Highlights

- Support Alipay payment! (PR [#48](https://github.com/omise/omise-woocommerce/pull/48))
- Be able to manual sync Omise charge status directly in a WooCommerce store. (PR [#47](https://github.com/omise/omise-woocommerce/pull/47))
- Now can create a refund inside the order detail page! (for credit card payment method only). (PR [#42](https://github.com/omise/omise-woocommerce/pull/42))
- Support Internet Banking payment! (PR [#41](https://github.com/omise/omise-woocommerce/pull/41), [#46](https://github.com/omise/omise-woocommerce/pull/46))
- Switch to fully use 'Omise-PHP' library to connect with Omise API instead of the previous custom one. (PR [#38](https://github.com/omise/omise-woocommerce/pull/38))
- Huge plugin code refactoring & provides a new plugin code structure (for anyone who did customize on the core code of plugin, please check this carefully!) (PR [#36](https://github.com/omise/omise-woocommerce/pull/36), [#37](https://github.com/omise/omise-woocommerce/pull/37), [#39](https://github.com/omise/omise-woocommerce/pull/39), [#40](https://github.com/omise/omise-woocommerce/pull/40))

#### 🚀 Enhancements

- Backward compatible with Omsie-WooCommerce v1.2.3. (PR [#50](https://github.com/omise/omise-woocommerce/pull/50))
- Humanize messages that will be displayed on a user's screen (PR [#49](https://github.com/omise/omise-woocommerce/pull/49))
- Remove Omise Dashboard support. (PR [#44](https://github.com/omise/omise-woocommerce/pull/44))
- Upgrade Omise-PHP library to v2.8.0 (the latest one). (PR [#43](https://github.com/omise/omise-woocommerce/pull/43))
- Improve UX of the payment credit card form (after our UX team did researches on user behaviours on a credit card form). (PR [#45](https://github.com/omise/omise-woocommerce/pull/45))
- Update plugin's 'text-domain' to support GlotPress translation system.  (PR [#32](https://github.com/omise/omise-woocommerce/pull/32) & [#34](https://github.com/omise/omise-woocommerce/pull/34). Big thanks for [@mayukojpn](https://github.com/mayukojpn))

#### 👾 Bug Fixes

- Fix 'save credit card for next time' feature for WooCommerce v3.x. (PR [#45](https://github.com/omise/omise-woocommerce/pull/45))

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

= 3.0 =
For anyone who did customize on the core plugin code, please carefully check on our README log at https://github.com/omise/omise-woocommerce/releases/tag/v3.0 and backup your website before update to this version (note, this release doesn't touch any database, just code structure changed).

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