import {useState, useEffect, useRef} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { SavedCard } from './saved-cards';
import { CART_STORE_KEY } from '@woocommerce/block-data';

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

	function getSelectedStateName(stateCode) {
		const billingStateField = document.getElementById('billing-state');
		return billingStateField?.querySelector(`option[value="${stateCode}"]`)?.innerText;
	}

	useEffect(() => {
		if (!hideCardForm) {
			showOmiseEmbeddedCardForm({
				element: el.current,
				publicKey: settings.public_key,
				hideRememberCard: !settings.user_logged_in,
				locale: settings.locale,
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
				const { select } = window.wp.data;
				const { billingAddress } = select( CART_STORE_KEY ).getCartData();

				if (billingAddress instanceof Object) {
					OmiseCard.requestCardToken({
						email: billingAddress.email,
						billingAddress: {
							street1: billingAddress.address_1,
							street2: billingAddress.address_2,
							city: billingAddress.city,
							country: billingAddress.country,
							state: getSelectedStateName(billingAddress.state),
							postal_code: billingAddress.postcode,
							phone_number: billingAddress.phone,
						}
					});
				}	else {
					/**
					 * Expect billingAddress to always returned as an object.
					 * In case if it's not, fallback to request card token without address.
					 * https://github.com/woocommerce/woocommerce/blob/1601aa341e4f1bb6f785d39696d8f25448a7372d/plugins/woocommerce/client/blocks/assets/js/types/type-defs/cart.ts#L47
					 */
					OmiseCard.requestCardToken();
				}

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
										"wc_block_payment": true,
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
										"wc_block_payment": true,
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
