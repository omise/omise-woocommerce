import {useState, useEffect} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';
import GooglePayButton from '@google-pay/button-react';

const settings = getSetting('omise_googlepay_data', {})
const label = decodeEntities(settings.title) || 'No title set'

const Content = (props) => {
    const description = decodeEntities(settings.description || '')
    const { data } = settings;
    const {eventRegistration, emitResponse, onSubmit} = props;
    const {onPaymentSetup} = eventRegistration;
    const [token, setToken] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            if (token) {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            "omise_token": token,
                        }
                    }
                };
            }

            if (error) {
                return {
                    type: emitResponse.responseTypes.ERROR,
                    message: error
                };
            }
        });
        return () => unsubscribe();
    }, [
        emitResponse.responseTypes.ERROR,
		emitResponse.responseTypes.SUCCESS,
		onPaymentSetup,
        token,
        error
    ])

    const onSuccess = (paymentRequest) => {
        const { paymentMethodData } = paymentRequest;
        const params = {
            method: 'googlepay',
            data: JSON.stringify(JSON.parse(paymentMethodData.tokenizationData.token))
        }

        const billingAddress = paymentMethodData.info?.billingAddress;
        if (billingAddress) {
            params = {
                ...params,
                billing_name: billingAddress.name,
                billing_city: billingAddress.locality,
                billing_country: billingAddress.countryCode,
                billing_postal_code: billingAddress.postalCode,
                billing_state: billingAddress.administrativeArea,
                billing_street1: billingAddress.address1,
                billing_street2: [billingAddress.address2, billingAddress.address3].filter(s => s).join(' '),
                billing_phone_number: billingAddress.phoneNumber,
            }
        }

        Omise.setPublicKey(data['public_key']);
		Omise.createToken('tokenization', params, (statusCode, response) => {
            if (statusCode == 200) {
                setError(null);
                setToken(response.id);

                // submit order
                onSubmit();
            } else {
                setError(response.message);
                console.error({response});
            }
        })
    }

    return <>
        {description && <p>{description}</p>}
        <fieldset id="omise-form-googlepay">
            <div id="googlepay-button-container">
                <GooglePayButton
                    environment={data['environment']}
                    paymentRequest={{
                        apiVersion: data['api_version'],
                        apiVersionMinor: data['api_version_minor'],
                        allowedPaymentMethods: [
                            {
                                type: 'CARD',
                                parameters: {
                                    allowedAuthMethods: data['allowed_auth_methods'],
                                    allowedCardNetworks: data['allowed_card_networks'],
                                    billingAddressRequired: data['billing_address_required'],
                                    billingAddressParameters: {
                                        format: 'FULL',
                                        phoneNumberRequired: data['phone_number_required'],
                                    },
                                },
                                tokenizationSpecification: {
                                    type: 'PAYMENT_GATEWAY',
                                    parameters: {
                                        gateway: 'omise',
                                        gatewayMerchantId: data['public_key'],
                                    },
                                },
                            },
                        ],
                        merchantInfo: {
                            merchantId: data['merchant_id'],
                        },
                        transactionInfo: {
                            totalPriceStatus: data['price_status'],
                            currencyCode: data['currency'],
                        },
                    }}
                    onLoadPaymentData={onSuccess}
                />
            </div>

            <p id="googlepay-text" className="omise-secondary-text">
                { __('You will be prompted to select a credit card stored in your Google Account.', 'omise') }
            </p>
        </fieldset>
    </>
}

const Label = (props) => {
    const { PaymentMethodLabel } = props.components
    return <PaymentMethodLabel text={label} />
}

registerPaymentMethod({
    name: settings.name || "",
    label: <Label />,
    content: <Content />,
    edit: <Content />,
    canMakePayment: () => settings.is_active,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    }
})
