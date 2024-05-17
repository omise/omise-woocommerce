import {useEffect, useRef, useState, useCallback} from '@wordpress/element';

const CreditCardPaymentMethod = (props) => {
    const { description, settings } = props;
	const el = useRef(null);
	const saveCardRef = useRef(false);
	const cardTokenRef = useRef(null);
	const {eventRegistration, emitResponse} = props;
    const {onPaymentSetup, onCheckoutValidation} = eventRegistration;

	useEffect(() => {
		showOmiseEmbeddedCardForm({
			element: el.current,
			publicKey: settings.public_key,
			hideRememberCard: !settings.user_logged_in,
			locale: settings.lcoale,
			theme: settings.card_form_theme ?? 'light',
			design: settings.form_design,
			brandIcons: settings.card_brand_icons,
			onSuccess: (payload) => {
				if (payload.remember) {
					saveCardRef.current = payload.remember
				}

				cardTokenRef.current = payload.token;
			},
			onError: (error) => {
				console.error(error)
			},
		});
	}, [])

	useEffect( () => {
		const unsubscribe = onCheckoutValidation( () => {
			OmiseCard.requestCardToken()
			return true;
		} );
		return unsubscribe;
	}, [ onCheckoutValidation ] );

	useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
			return await new Promise( ( resolve ) => {
				const intervalId = setInterval( () => {
					if (cardTokenRef.current && cardTokenRef.current.value !== "") {
						clearInterval(intervalId); // Stop the interval once cardToken is not empty
						try {
							const response = {
								type: emitResponse.responseTypes.SUCCESS,
								meta: {
									paymentMethodData: {
										"omise_save_customer_card": saveCardRef.current.value,
										"omise_token": cardTokenRef.current,
									}
								}
							};
							resolve(response)
						} catch (error) {
							const response = {type: emitResponse.responseTypes.ERROR, message: error.message}
							resolve(response)
						}
					}
				}, 1000 );
			} );
        });
        return () => unsubscribe();
    }, [
		emitResponse.responseTypes.ERROR,
		emitResponse.responseTypes.SUCCESS,
		onPaymentSetup,
	]);

	return (<>
        <p>{description}</p>
		<div ref={el} id="omise-card" style={{width:"100%"}}></div>
		<input type="hidden" name="omise_save_customer_card" id="omise_save_customer_card" />
		<input type="hidden" className="omise_token" name="omise_token" />
	</>)
}

export default CreditCardPaymentMethod
