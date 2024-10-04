import {useEffect, useRef} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'omise_installment_data', {} )
const label = decodeEntities( settings.title ) || 'No title set'
const Label = ( props ) => {
    const { PaymentMethodLabel } = props.components
    return <PaymentMethodLabel text={ label } />
}

const InstallmentPaymentMethod = (props) => {
    const {eventRegistration, emitResponse} = props;
    const {onPaymentSetup, onCheckoutValidation} = eventRegistration;
    const description = decodeEntities( settings.description || '' )
    const { installments_enabled, total_amount, public_key } = settings.data;
    const noPaymentMethods = __( 'Purchase Amount is lower than the monthly minimum payment amount.', 'omise' );
    const el = useRef(null);
    const wlbInstallmentRef = useRef(null);
    const cardFormErrors = useRef(null);

    useEffect(() => {
        if (installments_enabled) {
            let locale = settings.locale.toLowerCase();
            let supportedLocales = ['en', 'th', 'ja'];
            locale = supportedLocales.includes(locale) ? locale : 'en';

            showOmiseInstallmentForm({
                element: el.current,
                publicKey: public_key,
                amount: total_amount,
                locale,
                onSuccess: (payload) => {
                    wlbInstallmentRef.current = payload;
                },
                onError: (error) => {
                    cardFormErrors.current = error;
                },
            });
        }
	}, [installments_enabled])

    useEffect( () => {
        const unsubscribe = onCheckoutValidation( () => {
            OmiseCard.requestCardToken()
            return true;
        } );
        return unsubscribe;
	}, [ onCheckoutValidation ] );

    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            return await new Promise(( resolve, reject ) => {
				const intervalId = setInterval( () => {
                    if (wlbInstallmentRef.current) {
                        clearInterval(intervalId);
                        try {
                            const response = {
                                type: emitResponse.responseTypes.SUCCESS,
                                meta: {
                                    paymentMethodData: {
                                        "source": wlbInstallmentRef.current.source,
                                        "token": wlbInstallmentRef.current.token,
                                    }
                                }
                            };
                            resolve(response)
                        } catch (error) {
                            clearInterval(intervalId);
                            const response = {type: emitResponse.responseTypes.ERROR, message: error.message}
							reject(response)
                        }
                    }
                }, 1000 );
			});
        });
        return () => unsubscribe();
    }, [ onPaymentSetup ]);

    return (<>
        {description && <p>{description}</p>}
        {
            !installments_enabled
                ? <p>{noPaymentMethods}</p>
                : <div ref={el} id="omise-installment" style={{ width:"100%", maxWidth: "400px" }}></div>
        }
    </>)
}

registerPaymentMethod( {
    name: settings.name || "",
    label: <Label />,
    content: <InstallmentPaymentMethod />,
    edit: <InstallmentPaymentMethod />,
    canMakePayment: () => settings.is_active,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    }
} )
