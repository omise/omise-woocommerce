import { useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';
import { CART_STORE_KEY } from '@woocommerce/block-data';

const settings = getSetting( 'omise_installment_data', {} )
const label = decodeEntities( settings.title ) || 'No title set'
const Label = ( props ) => {
    const { PaymentMethodLabel } = props.components
    return <PaymentMethodLabel text={ label } />
}

const { select, subscribe } = window.wp.data;

const InstallmentPaymentMethod = (props) => {
    const {eventRegistration, emitResponse} = props;
    const {onPaymentSetup, onCheckoutValidation, onCheckoutFail} = eventRegistration;
    const description = decodeEntities( settings.description || '' )
    const { installments_enabled, public_key } = settings.data;
    const noPaymentMethods = __( 'Purchase Amount is lower than the monthly minimum payment amount.', 'omise' );
    const el = useRef(null);
    const wlbInstallmentRef = useRef(null);
    const cardFormErrors = useRef(null);
    const totalAmount = useRef(null);

    const loadInstallmentForm = () => {
        if (installments_enabled) {
            // Getting the new total price that might be updated when shipping method
            // was updated while other payment method was selected
            const cart = select( CART_STORE_KEY ).getCartData();
            totalAmount.current = cart.totals.total_price

            let locale = settings.locale.toLowerCase();
            let supportedLocales = ['en', 'th', 'ja'];
            locale = supportedLocales.includes(locale) ? locale : 'en';

            // removing previous iframe if there is any
            el.current.innerHTML = "";

            showOmiseInstallmentForm({
                element: el.current,
                publicKey: public_key,
                amount: totalAmount.current,
                locale,
                onSuccess: (payload) => {
                    wlbInstallmentRef.current = payload;
                },
                onError: (error) => {
                    cardFormErrors.current = error;
                },
            });
        }
    }

    // Update total amount on cart update. We need this to send the update amount to the source API
    const onCartChange = () => {
        const cart = select( CART_STORE_KEY ).getCartData();
        totalAmount.current = cart.totals.total_price;
        loadInstallmentForm()
    }

    useEffect(() => {
        const unsubscribe = subscribe( onCartChange, CART_STORE_KEY );
        return unsubscribe;
    }, [CART_STORE_KEY])

    useEffect(() => {
        loadInstallmentForm();
	}, [installments_enabled])

    useEffect(() => {
        const unsubscribe = onCheckoutValidation(() => {
            OmiseCard.requestCardToken()
            return true;
        } );
        return unsubscribe;
	}, [onCheckoutValidation]);

    useEffect(() => {
        const unsubscribe = onCheckoutFail(() => {
            // reset source and token on failure
            wlbInstallmentRef.current = null;
            loadInstallmentForm()
            return true;
        })
        return unsubscribe
    }, [onCheckoutFail])

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
                                        "omise_source": wlbInstallmentRef.current.source,
                                        "omise_token": wlbInstallmentRef.current.token,
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
