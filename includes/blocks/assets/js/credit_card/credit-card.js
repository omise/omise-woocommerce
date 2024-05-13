import {useEffect, useRef, useState} from '@wordpress/element';

const CreditCardPaymentMethod = (props) => {
    const { description, settings } = props;
    console.log({settings})
	const [saveCard, setSaveCard] = useState(false);
	const [cardToken, setCardToken] = useState('');
	const el = useRef(null);

	const onSuccess = (payload) => {
		if (payload.remember) {
			setSaveCard(payload.remember);
		}

		setCardToken(payload.token)
	}

	const onError = (error) => {
		console.error(error)
	}

	useEffect(() => {
		showOmiseEmbeddedCardForm({
			element: el.current,
			publicKey: settings.public_key,
			hideRememberCard: !settings.user_logged_in,
			locale: settings.lcoale,
			theme: settings.card_form_theme ?? 'light',
			design: settings.form_design,
			brandIcons: settings.card_brand_icons,
			onSuccess: onSuccess,
			onError: onError,
		})
	}, [el.current])

	return (<>
        <p>{description}</p>
		<div ref={el} id="omise-card" style={{width:"100%"}}></div>
		<input type="hidden" name="omise_save_customer_card" id="omise_save_customer_card" value={saveCard} />
		<input type="hidden" className="omise_token" name="omise_token" value={cardToken} />
	</>)
}

export default CreditCardPaymentMethod
