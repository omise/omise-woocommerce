import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerOmisePaymentMethod } from './common';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'omise_alipay_data', {} )
const defaultLabel = __( 'Alipay', 'omise' );
const label = decodeEntities( settings.title ) || defaultLabel;
registerOmisePaymentMethod({settings, label})

// const Content = () => {
//     return decodeEntities( settings.description || '' )
// }

// const Label = ( props ) => {
//     const { PaymentMethodLabel } = props.components
//     return <PaymentMethodLabel text={ label } />
// }

// registerPaymentMethod( {
//     name: settings.name,
//     label: <Label />,
//     content: <Content />,
//     edit: <Content />,
//     canMakePayment: () => true,
//     ariaLabel: label,
//     supports: {
//         features: settings.supports,
//     }
// } )
