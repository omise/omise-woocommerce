import {useEffect, useState} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'omise_atome_data', {} )
const label = decodeEntities( settings.title ) || 'No title set'
const Label = ( props ) => {
    const { PaymentMethodLabel } = props.components
    return <PaymentMethodLabel text={ label } />
}

const AtomePaymentMethod = (props) => {
    const {eventRegistration, emitResponse} = props;
    const {onPaymentSetup} = eventRegistration;
    const description = decodeEntities( settings.description || '' )
    const { status, message } = settings.data;
    const [showPhoneField, setShowPhoneField] = useState(false)
    const [useDefaultPhoneNumber, setUseDefaultPhoneNumber] = useState(true)
    const [phoneNumber, setPhoneNumber] = useState('')

    const onChangeDefaultPhoneNumber = (e) => {
        setUseDefaultPhoneNumber(!useDefaultPhoneNumber)
        setShowPhoneField(!showPhoneField)

        if (useDefaultPhoneNumber) {
            setPhoneNumber('');
        }
    }

    const onChangePhoneNumber = (e) => {
        setPhoneNumber(e.target.value);
        // phoneNumberRef.current = e.target.value;
    }

    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            if (!useDefaultPhoneNumber && phoneNumber.length === 0) {
                return {type: emitResponse.responseTypes.ERROR, message: 'Enter a phone number'}
            }
            try {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            "omise_atome_phone_default": (useDefaultPhoneNumber ? 1 : 0).toString(),
                            'omise_atome_phone_number': phoneNumber,
                        }
                    }
                };
            } catch (error) {
                return {type: emitResponse.responseTypes.ERROR, message: error.message}
            }
        });
        return () => unsubscribe();
    }, [
        onPaymentSetup,
        emitResponse.responseTypes.ERROR,
		emitResponse.responseTypes.SUCCESS,
        useDefaultPhoneNumber,
        phoneNumber
    ]);

    return (<>
        {description && <p>{description}</p>}
        {
            !status
                ? <p>{message}</p>
                : (
                    <fieldset id="omise-form-atome">
                        { __('Atome phone number', 'omise') }<br />

                        <p className="form-row form-row-wide omise-label-inline">
                            <input
                                id="omise_atome_phone_default"
                                type="checkbox"
                                name="omise_atome_phone_default"
                                value={useDefaultPhoneNumber}
                                defaultChecked={true}
                                onChange={onChangeDefaultPhoneNumber}
                            />
                            <label htmlFor="omise_atome_phone_default">{ __('Same as Billing Detail', 'omise') }</label>
                        </p>

                        <p id="omise_atome_phone_field" className="form-row form-row-wide" style={{display: showPhoneField ? "block" : "none"}}>
                            <span className="woocommerce-input-wrapper">
                                <input
                                    id="omise_atome_phone_number"
                                    className="input-text"
                                    name="omise_atome_phone_number"
                                    type="tel"
                                    autoComplete="off"
                                    placeholder="+66123456789"
                                    onChange={onChangePhoneNumber}
                                />
                            </span>
                        </p>

                        <p className="omise-secondary-text">
                            { __('The phone number will be used for creating Atome charge', 'omise') }
                        </p>
                    </fieldset>
                )
        }
    </>)
}

registerPaymentMethod( {
    name: settings.name,
    label: <Label />,
    content: <AtomePaymentMethod />,
    edit: <AtomePaymentMethod />,
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    }
} )
