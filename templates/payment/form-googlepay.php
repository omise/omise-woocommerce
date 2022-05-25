<fieldset id="omise-form-googlepay">
    <?php _e('Google Pay', 'omise'); ?><br/>

    <p id="omise_billing_default_field" class="form-row form-row-wide omise-label-inline">
        <input id="omise_billing_default" type="checkbox" name="omise_billing_default" value="1" checked="checked"/>
        <label for="omise_billing_default"><?php _e('Same as Billing Detail', 'omise'); ?></label>
    </p>

    <div id="buttons">
        <google-pay-button environment="TEST" button-type="short" button-color="black"></google-pay-button>
    </div>
    <script type="module">
        const button = document.querySelector('google-pay-button');
        button.paymentRequest = {
            apiVersion: 2,
            apiVersionMinor: 0,
            allowedPaymentMethods: [
                {
                    type: 'CARD',
                    parameters: {
                        allowedAuthMethods: ['PAN_ONLY', 'CRYPTOGRAM_3DS'],
                        allowedCardNetworks: ['MASTERCARD', 'VISA'],
                        billingAddressRequired: true,
                    },
                    tokenizationSpecification: {
                        type: 'PAYMENT_GATEWAY',
                        parameters: {
                            gateway: 'example',
                            gatewayMerchantId: 'exampleGatewayMerchantId',
                        },
                    },
                },
            ],
            merchantInfo: {
                merchantId: '12345678901234567890',
                merchantName: 'Demo Merchant',
            },
            transactionInfo: {
                totalPriceStatus: 'FINAL',
                totalPriceLabel: 'Total',
                totalPrice: '100.00',
                currencyCode: 'USD',
                countryCode: 'US',
            },
        };

        button.addEventListener('loadpaymentdata', event => {
            console.log('load payment data', event.detail);
        });
    </script>

    <p class="omise-secondary-text">
        <?php _e('You will be prompted to select a credit card stored in your Google Account.', 'omise'); ?>
    </p>
</fieldset>

<script type="text/javascript">
    var billing_default = document.getElementById('omise_billing_default');

    billing_default.addEventListener('change', (e) => {
        billing_field.style.display = e.target.checked ? "none" : "block";
    });
</script>
