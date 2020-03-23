<p align="center"><a href='https://www.omise.co'><img src='https://cloud.githubusercontent.com/assets/2154669/26388730/437207e4-4080-11e7-9955-2cd36bb3120f.png' height='160'></a></p>

**Omise WooCommerce** is the official payment extension which provides support for Omise payment gateway for store builders working on the WooCommerce platform.

## Supported Versions

WooCommerce version 3.3.4 and above (tested to version 3.9.3).

**The extension doesn't work on your version?**  
Our aim is to support as many versions of WooCommerce as we can.  
If the version you are currently using has not been listed, you can try installing it and report any issues to us at [GitHub's issue channel](https://github.com/omise/omise-woocommerce/issues) by following the [Reporting the issue Guideline](https://guides.github.com/activities/contributing-to-open-source/#contributing).

## Getting Started

- [Installation Instructions](#installation-instructions)
  - [WordPress Plugin Directory (recommended)](#wordpress-plugin-directory-recommended)
  - [Manually](#manually)
- [First Time Setup](#first-time-setup)
  - [Connect your store with your Omise account](#connect-your-store-with-your-omise-account)
  - [Enable payment methods](#enable-payment-methods)
- [Available Payment Methods](#available-payment-methods)
  - [🇯🇵Japan](#-japan)
  - [🇸🇬Singapore](#-singapore)
  - [🇹🇭Thailand](#-thailand)

...

### Installation Instructions

In order to install Omise WooCommerce plugin, you can either download it via WordPress Plugin Directory or manually download the plugin from this repository.

#### WordPress Plugin Directory (recommended)

Install Omise WooCommerce plugin via WordPress Plugin Directory by following these steps:
1. From the left sidebar at your WordPress Admin page, under **"Plugin"** section. Click **"Add New"**.

2. At the **"Add Plugins"** page, search for Omise WooCommerce plugin using keyword: `Omise`.

3. Click **"Install Now"** to download and install Omise WooCommerce plugin into your WordPress website.

![Install Omise-WooCommerce via WordPress Plugin Directory](https://user-images.githubusercontent.com/2154669/68250269-274f1080-0053-11ea-8db1-bab9cc32ea46.png)

4. After the plugin has been downloaded and installed. The **"Install Now"** button will now be changed to **"Activate"**. Make sure to click **"Activate"**

![Activate Omise-WooCommerce plugin](https://user-images.githubusercontent.com/2154669/68250334-477ecf80-0053-11ea-9817-6a9da5b53335.png)

#### Manually

1. Download and extract the zip file from [Omise-WooCommerce](https://github.com/omise/omise-woocommerce/archive/v3.11.zip) to your local machine.
  ![Manually download and extract the zip file](https://user-images.githubusercontent.com/2154669/68250447-8876e400-0053-11ea-9c8f-209474b2ec7c.png)

2. Copy all files from the step 1 to WordPress plugin folder, `your-wordpress-dir/wp-content/plugins/omise-woocommerce-3.11`.

3. Rename `omise-woocommerce-3.11` folder to `omise`
  ![Renaming its foldername to omise](https://user-images.githubusercontent.com/2154669/68250537-b1977480-0053-11ea-8778-3e9697506630.png)

4. Once done, `Omise Payment Gateway` plugin will be shown at the **Installed Plugins** page. Click **"Activate"** to activate the plugin.
  ![Activate Omise-WooCommerce plugin](https://user-images.githubusercontent.com/2154669/68250581-c7a53500-0053-11ea-8db8-c710c6cd9a3d.png)

Now you've done installing Omise-WooCommerce plugin.  
Next, check **[First Time Setup](#first-time-setup)** to continue setting up your Omise account with your WooCommerce store.

### First Time Setup

#### Connect your store with your Omise account

![Omise-WooCommerce plugin - setting page](https://user-images.githubusercontent.com/2154669/77301338-1e20c080-6d22-11ea-9cd9-906fe6ca4900.png)

Once the installation is done, the next thing that you are going to do is to connect your store with your Omise account and enable payment methods so your customers can make a purchase with an online payment.

1. Log in to WordPress admin page.

2. From the sidebar, go to `Omise > Settings`.

3. At the Omise Settings page, you are going to set your `Public key` and `Secret key` (these keys can be found at [Omise Dashboard](https://dashboard.omise.co/test/keys)).

4. Make sure that the option `Test mode` is checked and set your Omise keys at `Public key for test` and `Secret key for test` fields if you would like to test Omise service integration.

5. Click **"Save Settings"**.

#### Enable payment methods

1. After setting up your Omise Account, there will be a number of available payment methods shown in the **"Payment Methods"** section of the Omise Settings page.

2. Click **"config"** in the table for the payment method you'd like to accept payment with.

3. You will see a configuration page differently depends on which payment method you are choosing. The screenshot below shows Credit / Debit Card payment method's configuration page.
  ![omise-woocommerce-creditcard-setting-page](https://user-images.githubusercontent.com/2154669/38306405-a9afba30-383a-11e8-8c7b-e54ba1f2df88.png)

4. At the configuration page, mark **"Enable/Disable"** as checked.

5. You may change or update other options in this configuration page (optional).

5. Click **"Save changes"**.

Once done, those payment methods will be shown at the store's checkout page.
  ![screen shot 2560-07-26 at 8 13 55 pm](https://user-images.githubusercontent.com/2154669/28622536-030403e2-723f-11e7-8a93-a06e65e350d3.png)

### Available Payment Methods

Payment methods that are available on the store will be determined based on the country that merchants are registered.
The following is the list of payment methods that are supported in each country.

#### 🇯🇵 Japan
Credit / Debit Card

#### 🇸🇬 Singapore
Credit / Debit Card, and PayNow

#### 🇹🇭 Thailand
Alipay, Bill Payment: Tesco Lotus, Credit / Debit Card, Installment, Internet Banking, and TrueMoney Wallet.

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
