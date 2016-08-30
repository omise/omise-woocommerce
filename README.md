# Omise-WooCommerce

Omise WooCommerce Gateway Plugin is a wordpress plugin designed specifically for WooCommerce. The plugin adds support for Omise Payment Gateway payment method to WooCommerce. 

## Requirement

The plugin was built and tested with Wordpress 4.5.3 and WooCommerce 2.6.4.
The plugin dependencies are jQuery and [Omise.js](https://cdn.omise.co/omise.js) library.

## How it works

The plugin allows WooCommerce user to checkout with Omise Payment Gateway (Now available in Thailand and Japan). The supported currencies are Thai Bath (THB) and Japanese Yen (JPY). User can checkout by input credit card information or if they are logged in to wordpress they can save the card for further charge without having to fill out the card information everytime.

## Installation

Please refer to our full documentation [page](https://www.omise.co/woocommerce-plugin)

## Documentation

Developer Documentation [here](https://www.omise.co/docs/)


## Installation Guide

1. Download the latest release packed as zip format from Releases page: https://github.com/omise/omise-woocommerce/releases
( latest: https://github.com/omise/omise-woocommerce/archive/v1.2.3.zip )

2. Install plugin in wordpress using Plugin Upload method, i.e. https://yourwebsite.com/wp-admin/plugin-install.php?tab=upload 
and upload omise-woocommerce-1.2.3.zip

3. Activate Omise plugin

![Activate Plugin](https://cdn.omise.co/assets/woocommerce/activate-plugin.png)

4. Enable Omise as Checkout option: Go to Woocommerce -> Settings -> Checkout -> Payment Gateways

Enable "Omise payment gateway" and save

![Activate Plugin](https://cdn.omise.co/assets/woocommerce/omise-settings-00.png)

5. Configure Omise Gateway settings with Keys

![Configure Plugin](https://cdn.omise.co/assets/woocommerce/omise-settings-01.png)

Add your API Keys

![Add API Keys](https://cdn.omise.co/assets/woocommerce/omise-settings-02.png)

6. Your customers can now checkout with Omise Payment Gateway

![Checkout](https://cdn.omise.co/assets/woocommerce/checkout.png)



## Other Libraries

* [Omise Ruby Library](https://github.com/omise/omise-ruby)
* [Omise Card.js](https://github.com/omise/card.js)
* [Omise Dotnet](https://github.com/omise/omise-dotnet)
