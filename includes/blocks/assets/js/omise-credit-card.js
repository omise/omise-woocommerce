import { __ } from '@wordpress/i18n';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'omise_data', {} )

const defaultLabel = __( 'Credit/Debit card', 'omise' );

const label = decodeEntities( settings.title ) || defaultLabel;

const Content = () => {
	return decodeEntities( settings.description || '' )
}

const Label = ( props ) => {
	const { PaymentMethodLabel } = props.components
	return <PaymentMethodLabel text={ label } />
}

registerPaymentMethod( {
	name: "omise",
	label: <Label />,
	content: <Content />,
	edit: <Content />,
	canMakePayment: () => true,
	ariaLabel: label,
	supports: {
		features: settings.supports,
	}
} )
