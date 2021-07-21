# CHANGELOG

### [v4.10 _(Jul 21, 2021)_](https://github.com/omise/omise-woocommerce/releases/tag/v4.10)

#### 🚀 Enhancements
- Introducing FPX payment method (only available in Malaysia) (PR [#223](https://github.com/omise/omise-woocommerce/pull/223))

#### 👾 Bug Fixes
- Fix PromptPay QR viewing issue after downloading from email (PR [#222](https://github.com/omise/omise-woocommerce/pull/222))
- Fix phone number input box not showing (PR [#225](https://github.com/omise/omise-woocommerce/pull/225))

### [v4.9 _(Jun 30, 2021)_](https://github.com/omise/omise-woocommerce/releases/tag/v4.9)

#### 🚀 Enhancements
- Update min amount for installment bay and kbank (PR [#218](https://github.com/omise/omise-woocommerce/pull/218))
- Remove QR from email when charge is no longer pending (PR [#219](https://github.com/omise/omise-woocommerce/pull/219))
- Enable credit/debit card payment methods in WooCommerce for MY (PR [#220](https://github.com/omise/omise-woocommerce/pull/220))

### [v4.8 _(Apr 19, 2021)_](https://github.com/omise/omise-woocommerce/releases/tag/v4.8)

#### 🚀 Enhancements
- Change the minimum amount for installment payments from 3000 to 2000 THB. (PR [#210](https://github.com/omise/omise-woocommerce/pull/210))
- Update Omise-PHP library from v2.11.2 to v2.13.0. (PR [#211](https://github.com/omise/omise-woocommerce/pull/211))

#### 👾 Bug Fixes
- Fix CVE-2020-29156 security vulnerability. (PR [#213](https://github.com/omise/omise-woocommerce/pull/213))

### [v4.7 _(Mar 23, 2021)_](https://github.com/omise/omise-woocommerce/releases/tag/v4.7)

#### 🚀 Enhancements
- Updating the error message mapping for payment_rejected and failed_processing error codes. (PR [#207](https://github.com/omise/omise-woocommerce/pull/207))

### [v4.6 _(Dec 23, 2020)_](https://github.com/omise/omise-woocommerce/releases/tag/v4.6)

#### 🚀 Enhancements
- Updating translation for Japanese language in konbini payment. (PR [#204](https://github.com/omise/omise-woocommerce/pull/204))
- Adding support to online refunds in paynow. (PR [#202](https://github.com/omise/omise-woocommerce/pull/202))
- Run tests using GitHub Actions. (PR [#203](https://github.com/omise/omise-woocommerce/pull/203))

### [v4.5 _(Dec 04, 2020)_](https://github.com/omise/omise-woocommerce/releases/tag/v4.5)

#### 🚀 Enhancements
- Removing name attribute from card payment form. (PR [#196](https://github.com/omise/omise-woocommerce/pull/196))
- Rendering barcode in mobile phones vertically to avoid breakline. (PR [#197](https://github.com/omise/omise-woocommerce/pull/197))
- Refresh QR payment screen once customer completes payment. (PR [#198](https://github.com/omise/omise-woocommerce/pull/198))
- Auto formatting card numbers in input field on checkout page and My account page. (PR [#200](https://github.com/omise/omise-woocommerce/pull/200))

---

### [v4.4 _(Nov 17, 2020)_](https://github.com/omise/omise-woocommerce/releases/tag/v4.4)

#### ✨ Highlights
- Adding installment SCB support. (PR [#193](https://github.com/omise/omise-woocommerce/pull/193))

---

### [v4.3 _(Oct 15, 2020)_](https://github.com/omise/omise-woocommerce/releases/tag/v4.3)

#### ✨ Highlights
- Introducing Konbini payment method. (PR [#149](https://github.com/omise/omise-woocommerce/pull/149))

#### 🚀 Enhancements
- PayNow, adding a link to the QR code url at the order-confirmation email. (PR [#185](https://github.com/omise/omise-woocommerce/pull/185))

---

### [v4.2 _(Sep 15, 2020)_](https://github.com/omise/omise-woocommerce/releases/tag/v4.2)

#### 🚀 Enhancements
- Removing Gruntfile and package.json as no longer needed grunt-wp-i18n library. (PR [#187](https://github.com/omise/omise-woocommerce/pull/187))

#### 👾 Bug Fixes
- Updating deprecated functions in WooCommerce v3. (PR [#189](https://github.com/omise/omise-woocommerce/pull/189))
- Adding permission to callback which helps to removes a warning message on Wordpress 5.5. (PR [#188](https://github.com/omise/omise-woocommerce/pull/188))

---

### [v4.1 _(Aug 21, 2020)_](https://github.com/omise/omise-woocommerce/releases/tag/v4.1)

#### ✨ Highlights
- Introducing PromptPay payment method (Thailand). (PR [#170](https://github.com/omise/omise-woocommerce/pull/170))

#### 🚀 Enhancements
- Bypassing Callback function for Offline payment methods. (PR [#184](https://github.com/omise/omise-woocommerce/pull/184))
- Manual Sync, handling more cases: 'expired', 'refunded', 'reversed'. (PR [#183](https://github.com/omise/omise-woocommerce/pull/183))
- Cleaning up code style & indentation. (PR [#182](https://github.com/omise/omise-woocommerce/pull/182))

---

### [v4.0 _(Jul 20, 2020)_](https://github.com/omise/omise-woocommerce/releases/tag/v4.0)

#### ✨ Highlights
- Refactoring Event Handlers, make the code support for asynchronous request to prevent race-condition from Webhook. (PR [#179](https://github.com/omise/omise-woocommerce/pull/179))
- Revising Omise Settings. (PR [#175](https://github.com/omise/omise-woocommerce/pull/175))

#### 🚀 Enhancements
- Credit Card payment, updating order status to 'processing' for successful 'auth-only' payment. (PR [#180](https://github.com/omise/omise-woocommerce/pull/180))
- Bump lodash from 4.17.15 to 4.17.19. (PR [#178](https://github.com/omise/omise-woocommerce/pull/178))
- Revise order statuses (Part 1, failing payment should result as order failed). (PR [#171](https://github.com/omise/omise-woocommerce/pull/171))
- PayNow: adding default payment instruction at the checkout page. (PR [#169](https://github.com/omise/omise-woocommerce/pull/169))
- Reduce duplicated code from offline payment methods to a dedicated class. (PR [#168](https://github.com/omise/omise-woocommerce/pull/168))
- Code Refactoring, simplifying Callback function. (PR [#167](https://github.com/omise/omise-woocommerce/pull/167))
- Integrating WooCommerce refund's reason parameter to Omise Refund object. (PR [#165](https://github.com/omise/omise-woocommerce/pull/165))
- Be able to create a refund when placing an order with Alipay or Installment payment method. (PR [#158](https://github.com/omise/omise-woocommerce/pull/158))
- Code refactoring, simplifying the 'process_refund' method. (PR [#157](https://github.com/omise/omise-woocommerce/pull/157))

#### 📝 Documents
- Create LICENSE. (PR [#173](https://github.com/omise/omise-woocommerce/pull/173))
- Move usage docs to website. (PR [#172](https://github.com/omise/omise-woocommerce/pull/172))
- README, updating document. (PR [#159](https://github.com/omise/omise-woocommerce/pull/159))

---

### [v3.11 _(Mar 18, 2020)_](https://github.com/omise/omise-woocommerce/releases/tag/v3.11)

#### ✨ Highlights

- Introducing PayNow payment method (only available in Singapore) (PR [#152](https://github.com/omise/omise-woocommerce/pull/152))

#### 🚀 Enhancements

- (proposal) Code cleaning for payment method classes. (PR [#153](https://github.com/omise/omise-woocommerce/pull/153))
- Payment Setting: properly display payment methods based on a given Omise Account (for admin) (PR [#151](https://github.com/omise/omise-woocommerce/pull/151))

---

### [v3.10 _(Jan 16, 2020)_](https://github.com/omise/omise-woocommerce/releases/tag/v3.10)

#### ✨ Highlights

- Credit Card payment, automatically detect if a particular charge is processing using 3-D Secure feature (PR [#146](https://github.com/omise/omise-woocommerce/pull/146))

#### 🚀 Enhancements

- Code refactoring for a better i18n for strings that were in JS files (PR [#147](https://github.com/omise/omise-woocommerce/pull/147))
- Refactoring offsite payment methods (introducing abstract offsite class) (PR [#143](https://github.com/omise/omise-woocommerce/pull/143))

#### 📝 Documents

- README, polishing and updating all missing contents (PR [#145](https://github.com/omise/omise-woocommerce/pull/145))

---

### [v3.9 _(Oct 31, 2019)_](https://github.com/omise/omise-woocommerce/releases/tag/v3.9)

#### ✨ Highlights

- Introducing TrueMoney payment (only available in Thailand) (PR [#139](https://github.com/omise/omise-woocommerce/pull/139))

#### 🚀 Enhancements

- Bill Payment, correcting order status after a new order is placed (on-hold instead of pending-payment) (PR [#142](https://github.com/omise/omise-woocommerce/pull/142))
- Adding ability to sync payment status to Bill Payment and TrueMoney Payment (PR [#140](https://github.com/omise/omise-woocommerce/pull/140))

---

### [v3.8 _(Sep 17, 2019)_](https://github.com/omise/omise-woocommerce/releases/tag/v3.8)

#### 👾 Bug Fixes

- Billpayment - check if an order is made by Bill Payment before display a barcode. (PR [#137](https://github.com/omise/omise-woocommerce/pull/137))

---

### [v3.7 _(Sep 13, 2019)_](https://github.com/omise/omise-woocommerce/releases/tag/v3.7)

#### ✨ Highlights

- Introducing Bill Payment. (PR [#122](https://github.com/omise/omise-woocommerce/pull/122), [#125](https://github.com/omise/omise-woocommerce/pull/125), [#126](https://github.com/omise/omise-woocommerce/pull/126), [#128](https://github.com/omise/omise-woocommerce/pull/128), [#129](https://github.com/omise/omise-woocommerce/pull/129))

#### 👾 Bug Fixes

- Event "charge.complete", making sure that event's charge id is identical with order transaction id. (PR [#131](https://github.com/omise/omise-woocommerce/pull/131))
- Fixing calling Omise_Money's non-static method statically. (PR [#130](https://github.com/omise/omise-woocommerce/pull/130))

#### 📝 Documents

- Updating README.md, adding 2 missing payment methods at 'Enable Payment Method' section. (PR [#127](https://github.com/omise/omise-woocommerce/pull/127))

---

### [v3.6 _(Aug 1, 2019)_](https://github.com/omise/omise-woocommerce/releases/tag/v3.6)

#### ✨ Highlights

- [#118](https://github.com/omise/omise-woocommerce/pull/118): Better way to handle amount subunit, adding support for more currencies.

#### 🚀 Enhancements

- [#124](https://github.com/omise/omise-woocommerce/pull/124): Updating npm vulnerable dependencies.

---

### [v3.5 _(Jul 23, 2019)_](https://github.com/omise/omise-woocommerce/releases/tag/v3.5)

#### 👾 Bug Fixes

- [#120](https://github.com/omise/omise-woocommerce/pull/120): Properly assigning querystrings for the payment callback endpoints.

---

### [v3.4 _(Jun 14, 2019)_](https://github.com/omise/omise-woocommerce/releases/tag/v3.4)

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

---

### [v3.3 _(Mar 28, 2019)_](https://github.com/omise/omise-woocommerce/releases/tag/v3.3)

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

---

### [v3.2 _(Apr 20, 2018)_](https://github.com/omise/omise-woocommerce/releases/tag/v3.2)

#### ✨ Highlights

- Support multi currency (PR [#84](https://github.com/omise/omise-woocommerce/pull/84))

#### 🚀 Enhancements

- Remove legacy files and codes (that we no longer use) (PR [#85](https://github.com/omise/omise-woocommerce/pull/85))

#### 👾 Bug Fixes

- Issue #78 fatal error, if install omise plugin before woo commerce (PR [#83](https://github.com/omise/omise-woocommerce/pull/83), [#88](https://github.com/omise/omise-woocommerce/pull/88))

---

### [v3.1 _(Sep 19, 2017)_](https://github.com/omise/omise-woocommerce/releases/tag/v3.1)

#### ✨ Highlights

- Introduce WebHook feature. (PR [#62](https://github.com/omise/omise-woocommerce/pull/62))
- Add Omise Setting page and enhance Omise setting process. (PR [#61](https://github.com/omise/omise-woocommerce/pull/61))

#### 🚀 Enhancements

- Spell WordPress correctly! (PR [#56](https://github.com/omise/omise-woocommerce/pull/56)) :by [@mayukojpn](https://github.com/mayukojpn))
- Support WooCommerce 2.x series & PHP 5.4 (PR [#59](https://github.com/omise/omise-woocommerce/pull/59))

### [v3.0 _(Jul 26, 2017)_](https://github.com/omise/omise-woocommerce/releases/tag/v3.0)

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

---

## [1.2.3] 2016-08-30
- *`Added`* Add a new feature, localization
- *`Added`* Add a translation file for Japanese
- *`Changed`* Change a page header from transactions history to charges history
- *`Removed`* Remove a link, view detail, from each row of transactions and transfers history table
- *`Removed`* Remove sub-tabs, charges and transfers
- *`Removed`* Remove an unused setting, description

## [1.2.2] 2016-08-26
- *`Improved`* Specify the display size of card brand image and allow customer to define their own style
- *`Removed`* Remove an unused unit test of the library, omise-php

## [1.2.1] 2016-08-05
- *`Added`* Configuration for card brand logo display
- *`Added`* List of transfers
- *`Fixed`* Changing page by specify the page number which is not functional

## [1.2.0] 2016-06-01
- *`Added`* manual capture feature
- *`Added`* supported JPY currency
- *`Added`* shortcut menu to Omise's setting page
- *`Added`* Included Omise-PHP 2.4.1 library to the project.
- *`Improved`* Redesigned Omise Dashboard
- *`Improved`* Re-ordered fields in Omise Setting page.
- *`Improved`* Better handle error cases (error messages)
- *`Improved`* Better handle WC order note to trace Omise's actions back.
- *`Improved`* Revised PHP code to following the WordPress Coding Standards.
- *`Improved`* Fixed/Improved various things.

## [1.1.1] 2015-11-16
- *`Added`* Added Omise-Version into the cURL request header.

## [1.1.0] 2015-09-24
- *`Added`* Adds support for 3-D Secure.

## [1.0.2] 2015-03-23
- *`Fixed`* Fix create token issue.

## [1.0.1] 2015-03-10
- *`Added`* Support fund transfers.

## [1.0.0] 2015-01-20
- *`Added`* First version supports.
  - Charge a card
  - Save a card
  - Delete a card
