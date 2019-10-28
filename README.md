<p align="center"><a href='https://www.omise.co'><img src='https://cloud.githubusercontent.com/assets/2154669/26388730/437207e4-4080-11e7-9955-2cd36bb3120f.png' height='160'></a></p>

**Omise WooCommerce** is the official payment extension which provides support for Omise payment gateway for store builders working on the WooCommerce platform.

## Supported Versions

WooCommerce version 3.3.4 and above (tested to version 3.7.0).

**The extension doesn't work on your version?**  
Our aim is to support as many versions of WooCommerce as we can.  
If the version you are currently using has not been listed, you can try installing it and report any issues to us at [GitHub's issue channel](https://github.com/omise/omise-woocommerce/issues) by following the [Reporting the issue Guideline](https://guides.github.com/activities/contributing-to-open-source/#contributing).

## Getting Started

- [Installation Instructions](https://github.com/omise/omise-woocommerce/tree/improve-readme#installation-instructions)
  - [Manually](https://github.com/omise/omise-woocommerce/tree/improve-readme#manually)
- [First Time Setup](https://github.com/omise/omise-woocommerce/tree/improve-readme#first-time-setup)
  - [Connect your store with your Omise account](https://github.com/omise/omise-woocommerce/tree/improve-readme#connect-your-store-with-your-omise-account)
  - [Enable payment methods](https://github.com/omise/omise-woocommerce/tree/improve-readme#enable-payment-methods)

...

### Installation Instructions

In order to install Omise-WooCommerce plugin, you can either manually download the plugin from this repository and install it or download it via WordPress Plugin Store.  

#### Manually

1. Download and extract the zip file from [Omise-WooCommerce](https://github.com/omise/omise-woocommerce/archive/v3.9.zip) to your local machine.
  ![screen shot 2560-07-26 at 12 36 43 pm](https://user-images.githubusercontent.com/2154669/38302382-ac3b1cf8-382c-11e8-80d4-61e935b7a567.png)

2. Copy all files from the step 1 to WordPress plugin folder, `your-wordpress-dir/wp-content/plugins/omise-woocommerce-3.9`.

3. Rename `omise-woocommerce-3.9` folder to `omise`
  ![screen shot 2560-07-26 at 12 36 43 pm](https://user-images.githubusercontent.com/2154669/28606035-2b9387dc-71ff-11e7-887d-dc90ce774a39.png)

4. Once done, `Omise Payment Gateway` plugin will be shown at the **Installed Plugins** page. Click `activate` to activate the plugin.
  ![omise-woocommerce-plugin-activate](https://user-images.githubusercontent.com/2154669/38302722-dd2404c8-382d-11e8-9f21-09cbe9829dbe.png)

Now you've done installing Omise-WooCommerce plugin.  
Next, check **[First Time Setup](#first-time-setup)** to continue setting up your Omise account with your WooCommerce store.

### First Time Setup

#### Connect your store with your Omise account

![Omise-WooCommerce plugin - setting page](https://user-images.githubusercontent.com/2154669/62671292-7b991480-b9c0-11e9-8627-0bc85c078365.png)

Once the installation is done, the next thing that you are going to do is to connect your store with your Omise account and enable payment methods so your customers can make a purchase with an online payment.

1. Log in to WordPress admin page.

2. From the sidebar, go to `Omise > Settings`.

3. At the Omise Settings page, you are going to set your `Public key` and `Secret key` (these keys can be found at Omise Dashboard).

4. Make sure that the option `Test mode` is checked and set your Omise keys at `Public key for test` and `Secret key for test` fields if you would like to test Omise service integration.

5. Click **'Save Settings'**.

#### Enable payment methods

> Note that Alipay, Bill Payment: Tesco, Installment, and Internet Banking payment methods are only available for merchants with a Thai-registered Omise account.

There are 5 payment methods that you can enable on your store, which are **Alipay**, **Bill Payment: Tesco**, **Credit / Debit Card**, **Installment**, and **Internet Banking**.

1. At Omise Settings page, Payment Methods section, there will be 5 available payment methods show on the list.

2. Click `config` at the table on a payment method that you would like to accept payment with.

3. You will see a configuration page differently depends on which payment method you are choosing. The screenshot below shows Credit / Debit Card payment method's configuration page.
  ![omise-woocommerce-creditcard-setting-page](https://user-images.githubusercontent.com/2154669/38306405-a9afba30-383a-11e8-8c7b-e54ba1f2df88.png)

4. At the configuration page, mark **Enable/Disable** as checked.

5. You may change or update other options in this configuration page (optional).

5. Click **'Save changes'**.

Once done, those payment methods will be shown at the store's checkout page.
  ![screen shot 2560-07-26 at 8 13 55 pm](https://user-images.githubusercontent.com/2154669/28622536-030403e2-723f-11e7-8a93-a06e65e350d3.png)

...

### What's Next?

Want to know more detail on how to create a charge, refund, and using the plugin?  
Please check [Omise-WooCommerce's Wiki](https://github.com/omise/omise-woocommerce/wiki).

## Contributing

Thanks for your interest in contributing to Omise WooCommerce. We're looking forward to hearing your thoughts and willing to review your changes.

The following subjects are instructions for contributors who consider to submit changes and/or issues.

### Submit the changes

You're all welcome to submit a pull request.
Please consider the [pull request template](https://github.com/omise/omise-woocommerce/blob/master/.github/PULL_REQUEST_TEMPLATE.md) and fill the form when you submit a new pull request.

Learn more about submitting pull request here: [https://help.github.com/articles/about-pull-requests](https://help.github.com/articles/about-pull-requests)

### Submit the issue

Submit the issue through [GitHub's issue channel](https://github.com/omise/omise-woocommerce/issues).

Learn more about submitting an issue here: [https://guides.github.com/features/issues](https://guides.github.com/features/issues)

## License

Omise-WooCommerce is open-sourced software released under the [MIT License](https://opensource.org/licenses/MIT).
