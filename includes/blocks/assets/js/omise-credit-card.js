import {useEffect, useRef, useState} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'omise_data', {} )
const defaultLabel = __( 'Credit/Debit card', 'omise' );
const label = decodeEntities( settings.title ) || defaultLabel;
window.OMISE_CUSTOM_FONT_OTHER = 'Other';

const Content = ( props ) => {
	const description = decodeEntities( settings.description || '' );
	return (
		<>
			<span>{description}</span>
			<PaymentMethod {...props} />
		</>
	)
}

const PaymentMethod = (props) => {
	const [saveCard, setSaveCard] = useState(false);
	const [cardToken, setCardToken] = useState('');
	const el = useRef(null);
	const form = document.querySelector('.wc-block-components-form');

	// Select the fieldset element with id payment-method inside the form
	const paymentMethodFieldset = form.querySelector('#payment-method');

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
		<div ref={el} id="omise-card" style={{width:"100%"}}></div>
		<input type="hidden" name="omise_save_customer_card" id="omise_save_customer_card" value={saveCard} />
		<input type="hidden" className="omise_token" name="omise_token" value={cardToken} />
	</>)
}

const Label = ( props ) => {
	const { PaymentMethodLabel } = props.components
	return <PaymentMethodLabel text={ label } />
}

registerPaymentMethod( {
	name: settings.name,
	label: <Label />,
	content: <Content />,
	edit: <Content />,
	canMakePayment: () => true,
	ariaLabel: label,
	supports: {
		features: settings.supports,
	}
} )
