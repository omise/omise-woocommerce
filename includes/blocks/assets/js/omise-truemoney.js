import {useEffect, useState} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'omise_truemoney_data', {} )
const label = decodeEntities( settings.title ) || 'No title set'
const Label = ( props ) => {
    const { PaymentMethodLabel } = props.components
    return <PaymentMethodLabel text={ label } />
}

const TruemoneyPaymentMethod = ({content, ...props}) => {
    const Content = content;
    const {eventRegistration, emitResponse} = props;
    const {onPaymentSetup} = eventRegistration;
    const description = decodeEntities( settings.description.trim() || '' )
    const { is_wallet} = settings.data;
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
                            "omise_phone_number_default": (useDefaultPhoneNumber ? 1 : 0).toString(),
                            'omise_phone_number': phoneNumber,
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
            is_wallet && (
                <fieldset id="omise-form-truemoney">
                    { __( 'TrueMoney phone number', 'omise' ) }<br/>

                    <p id="omise_phone_number_default_field" className="form-row form-row-wide omise-label-inline">
                        <input
                            id="omise_phone_number_default"
                            type="checkbox"
                            name="omise_phone_number_default"
                            value={useDefaultPhoneNumber}
                            defaultChecked={true}
                            onChange={onChangeDefaultPhoneNumber}
                        />
                        <label htmlFor="omise_phone_number_default">{ __( 'Same as Billing Detail', 'omise' ) }</label>
                    </p>

                    <p id="omise_phone_number_field" className="form-row form-row-wide" style={{display: showPhoneField ? "block" : "none"}}>
                        <span className="woocommerce-input-wrapper">
                            <input
                                id="omise_phone_number"
                                className="input-text"
                                name="omise_phone_number"
                                type="tel"
                                autoComplete="off"
                                onChange={onChangePhoneNumber}
                            />
                        </span>
                    </p>

                    <p className="omise-secondary-text">
                        { __( 'One-Time Password (OTP) will be sent to the phone number above', 'omise' ) }
                    </p>
                </fieldset>
            )
        }
    </>)
}

registerPaymentMethod( {
    name: settings.name,
    label: <Label />,
    content: <TruemoneyPaymentMethod />,
    edit: <TruemoneyPaymentMethod />,
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    }
} )
