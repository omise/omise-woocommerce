import {useEffect, useState} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'omise_konbini_data', {} )
const label = decodeEntities( settings.title ) || 'No title set'
const Label = ( props ) => {
    const { PaymentMethodLabel } = props.components
    return <PaymentMethodLabel text={ label } />
}

const InternetBankingPaymentMethod = (props) => {
    const {eventRegistration, emitResponse} = props;
    const {onPaymentSetup} = eventRegistration;
    const description = decodeEntities( settings.description || '' )
    const [name, setName] = useState(null);
    const [email, setEmail] = useState(null);
    const [phone, setPhone] = useState(null);

    const onNameChange = (e) => {
        setName(e.target.value)
    }

    const onEmailChange = (e) => {
        setEmail(e.target.value)
    }

    const onPhoneChange = (e) => {
        setPhone(e.target.value)
    }

    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            if (!name || !email || !phone) {
                return {type: emitResponse.responseTypes.ERROR, message: 'Name, email and phone fields are required'}
            }

            try {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            "omise_konbini_name": name,
                            "omise_konbini_email": email,
                            "omise_konbini_phone": phone,
                        }
                    }
                };
            } catch (error) {
                return {type: emitResponse.responseTypes.ERROR, message: error.message}
            }
        });
        return () => unsubscribe();
    }, [
        emitResponse.responseTypes.ERROR,
		emitResponse.responseTypes.SUCCESS,
		onPaymentSetup,
        name,
        email,
        phone
    ]);

    return (<>
        {description && <p>{description}</p>}
        <fieldset id="omise-form-konbini">
            <p className="form-row form-row-wide omise-required-field">
                <label htmlFor="omise_konbini_name">{ __( 'Name', 'omise' ) }</label>
                <input
                    id="omise_konbini_name"
                    className="input-text"
                    name="omise_konbini_name"
                    type="text"
                    maxLength="10"
                    autoComplete="off"
                    onChange={onNameChange}
                />
            </p>

            <p className="form-row form-row-wide omise-required-field">
                <label htmlFor="omise_konbini_email">{ __( 'Email', 'omise' ) }</label>
                <input
                    id="omise_konbini_email"
                    className="input-text"
                    name="omise_konbini_email"
                    type="text"
                    maxLength="50"
                    autoComplete="off"
                    onChange={onEmailChange}
                />
            </p>

            <p className="form-row form-row-wide omise-required-field">
                <label htmlFor="omise_konbini_phone">{ __( 'Phone', 'omise' ) }</label>
                <input
                    id="omise_konbini_phone"
                    className="input-text"
                    name="omise_konbini_phone"
                    type="text"
                    maxLength="11"
                    autoComplete="off"
                    onChange={onPhoneChange}
                />
            </p>
        </fieldset>
    </>)
}

registerPaymentMethod( {
    name: settings.name || "",
    label: <Label />,
    content: <InternetBankingPaymentMethod />,
    edit: <InternetBankingPaymentMethod />,
    canMakePayment: () => settings.is_active,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    }
} )
