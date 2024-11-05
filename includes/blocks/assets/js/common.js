import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';

export function registerOmisePaymentMethod({settings, label}) {
    const Content = () => {
        return decodeEntities( settings.description || '' )
    }
    
    const Label = ( props ) => {
        const { PaymentMethodLabel } = props.components
        return <PaymentMethodLabel text={ label } />
    }

    registerPaymentMethod( {
        name: settings.name || "",
        label: <Label />,
        content: <Content />,
        edit: <Content />,
        canMakePayment: () => settings.is_active,
        ariaLabel: label,
        supports: {
            features: settings.supports,
        }
    } )
}
