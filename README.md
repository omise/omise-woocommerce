<p align="center"><a href='https://www.omise.co'><img src='https://cloud.githubusercontent.com/assets/2154669/26388730/437207e4-4080-11e7-9955-2cd36bb3120f.png' height='160'></a></p>

**Omise WooCommerce** is the official payment extension which provides support for Omise payment gateway for store builders working on the WooCommerce platform.

## Supported Versions

Our aim is to support as many versions of WooCommerce as we can.  

**Here's the list of versions we tested on:**
- WooCommerce v3.1.1 _(on WordPress v4.8)_

To report problems for the version you're using, feel free to submit the issue through [GitHub's issue channel](https://github.com/omise/omise-woocommerce/issues) by following the [Reporting the issue Guideline](https://guides.github.com/activities/contributing-to-open-source/#contributing).

**Can't find the version you're looking for?**  
Submit your requirement as an issue to [https://github.com/omise/omise-woocommerce/issues](https://github.com/omise/omise-woocommerce/issues)

## Getting Started

### Installation Instructions

#### Manually

You can manually download the plugin from this repository, then extract and copy the plugin files into your WordPress / WooCommerce application.  

The steps below shows how to install the plugin manually.

1. Download and extract the zip file from [Omise-WooCommerce](https://github.com/omise/omise-woocommerce/archive/v3.0.zip) to your local machine.

    ![screen shot 2560-07-26 at 12 31 34 pm](https://user-images.githubusercontent.com/2154669/28605935-6c0cd2ce-71fe-11e7-8b9a-f912f11d5006.png)

2. Copy all files from the step 1 into WordPress plugin folder (`your-wordpress-dir/wp-content/plugins/omise-woocommerce-3.0`)

3. Rename `omise-woocommerce-3.0` folder to `omise`
    ![screen shot 2560-07-26 at 12 36 43 pm](https://user-images.githubusercontent.com/2154669/28606035-2b9387dc-71ff-11e7-887d-dc90ce774a39.png)

4. Once done, you will see `Omise Payment Gateway` plugin sits right away in the **Installed Plugins** page. Click `activate` to activate the plugin.
    ![Omise WooCommerce plugin](https://user-images.githubusercontent.com/2154669/28614862-642dc20c-7221-11e7-964b-3c4afc120292.png)

Now you've done installing Omise-WooCommerce plugin. Next, check [First Time Setup](#first-time-setup) to continue setting up your Omise account to WooCommerce store.

### First Time Setup

#### Enabling Omise plugin and setup Omise keys

![Omise WooCommerce plugin - setting page](https://user-images.githubusercontent.com/2154669/28621776-38755cc2-723c-11e7-932d-43811cde5ad8.png)

In order to enable **Omise Payment Gateway** in the checkout page to allow buyer make a charge with Omise, you have to enable the plugin and link the store to your Omise account by using your [credentials](https://www.omise.co/api-authentication) (public and secret keys):

1. Log in into WordPress admin page.

2. From the sidebar, go to `WooCommerce > Settings > Checkout > Omise Credit / Debit Card`. (or you can also click at `Omise` menu on the sidebar).

3. Check at `Enable/Disable` option.

4. Set your Omise keys at `Public key` and `Secret key` fields.  
    Make sure that you check at the `Test mode` option and set your Omise keys at `Public key for test` and `Secret key for test` fields if you want to test Omise service integration.

5. Then, click **'Save changes'** to save the setting.

Once done, you will see **Credit / Debit Card** appears at the checkout page.

![screen shot 2560-07-26 at 8 13 55 pm](https://user-images.githubusercontent.com/2154669/28622536-030403e2-723f-11e7-8a93-a06e65e350d3.png)

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
