import { decodeEntities } from '@wordpress/html-entities';

const { registerPaymentMethod } = window.wc.wcBlocksRegistry
const { getSetting } = window.wc.wcSettings

const settings = getSetting( 'omise_promptpay_data', {} )

const defaultLabel = __( 'Promptpay', 'omise' );

const label = decodeEntities( settings.title ) || defaultLabel;

const Content = () => {
	return decodeEntities( settings.description || '' )
}

const Label = ( props ) => {
	const { PaymentMethodLabel } = props.components
	return <PaymentMethodLabel text={ label } />
}

registerPaymentMethod( {
	name: "omise_promptpay",
	label: <Label />,
	content: <Content />,
	edit: <Content />,
	canMakePayment: () => true,
	ariaLabel: label,
	supports: {
		features: settings.supports,
	}
} )
