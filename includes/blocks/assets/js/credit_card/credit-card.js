import {useState, useEffect, useRef} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { SavedCard } from './saved-cards';

const CreditCardPaymentMethod = (props) => {
    const { settings } = props;
	const { existing_cards, description } = settings;
	const el = useRef(null);
	const saveCardRef = useRef(false);
	const cardTokenRef = useRef(null);
	const savedCardIdRef = useRef(null);
	const cardFormErrors = useRef(null);
	const [hideCardForm, setHideCardForm] = useState(existing_cards && existing_cards.length > 0);
	const {eventRegistration, emitResponse} = props;
    const {onPaymentSetup, onCheckoutValidation} = eventRegistration;

	useEffect(() => {
		if (!hideCardForm) {
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
					cardFormErrors.current = error;
				},
			});
		}
	}, [hideCardForm])

	useEffect( () => {
		if (!hideCardForm) {
			const unsubscribe = onCheckoutValidation( () => {
				OmiseCard.requestCardToken()
				return true;
			} );
			return unsubscribe;
		}
	}, [ onCheckoutValidation, hideCardForm ] );

	useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
			return await new Promise( ( resolve, reject ) => {
				const intervalId = setInterval( () => {
					if (savedCardIdRef.current && savedCardIdRef.current.value !== "") {
						clearInterval(intervalId); // Stop the interval once cardToken is not empty
						try {
							const response = {
								type: emitResponse.responseTypes.SUCCESS,
								meta: {
									paymentMethodData: {
										"card_id": savedCardIdRef.current.value,
									}
								}
							};
							resolve(response)
						} catch (error) {
							const response = {type: emitResponse.responseTypes.ERROR, message: error.message}
							reject(response)
						}
					} else if (cardTokenRef.current && cardTokenRef.current.value !== "") {
						clearInterval(intervalId); // Stop the interval once cardToken is not empty
						try {
							const response = {
								type: emitResponse.responseTypes.SUCCESS,
								meta: {
									paymentMethodData: {
										"omise_save_customer_card": saveCardRef.current,
										"omise_token": cardTokenRef.current,
									}
								}
							};
							resolve(response)
						} catch (error) {
							const response = {type: emitResponse.responseTypes.ERROR, message: error.message}
							reject(response)
						}
					} else if (cardFormErrors.current) {
						clearInterval(intervalId); // Stop the interval once cardToken is not empty
						const response = {
							type: emitResponse.responseTypes.ERROR,
							message: cardFormErrors.current
						}
						reject(response)
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

	const onChange = (e) => {
		const saveCardEl = e.target;
		setHideCardForm(saveCardEl.value !== "")
		savedCardIdRef.current = saveCardEl
	}

	return (<>
		{existing_cards && existing_cards.length > 0 && (
			<SavedCard existingCards={existing_cards} onChange={onChange} />
		)}

        {!hideCardForm && <p>{decodeEntities( description || '' )}</p>}
		<div ref={el} id="omise-card" style={{width:"100%", display: hideCardForm ? "none" : "block"}}></div>
		{!hideCardForm && <>
			<input type="hidden" name="omise_save_customer_card" id="omise_save_customer_card" />
			<input type="hidden" className="omise_token" name="omise_token" />
		</>}
	</>)
}

export default CreditCardPaymentMethod
