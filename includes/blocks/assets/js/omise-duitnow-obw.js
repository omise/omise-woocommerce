import {useEffect, useState} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'omise_duitnow_obw_data', {} )
const label = decodeEntities( settings.title ) || 'No title set'
const Label = ( props ) => {
    const { PaymentMethodLabel } = props.components
    return <PaymentMethodLabel text={ label } />
}

const DuitNowOBWPaymentMethod = (props) => {
    const {eventRegistration, emitResponse} = props;
    const {onPaymentSetup} = eventRegistration;
    const description = decodeEntities( settings.description || '' )
    const { banks } = settings.data;
    const [bank, setBank] = useState(null);

    const onBankSelected = (e) => {
        setBank(e.target.value)
    }

    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            if (!bank) {
                return {type: emitResponse.responseTypes.ERROR, message: 'Select a bank'}
            }

            try {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: { bank }
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
        bank
    ]);

    return (<>
        {description && <p>{description}</p>}
        {
            <fieldset id="omise-form-installment">
            <div className="fpx-select-bank">
                <label htmlFor="fpx-select-bank">Select Bank</label>
                <select
                    className="fpx-bank-logo
                    default"
                    id="duitnow-obw-select-bank"
                    name="source[bank]"
                    defaultValue=""
                    onChange={onBankSelected}
                >
                    <option value="" disabled={true}>-- Select your option --</option> 
                    {
                        banks.map((bank) => (
                            <option
                                key={bank['code']}
                                className={bank['code']}
                                value={bank['code']}
                            >
                                {bank['name']}
                            </option>
                        ))
                    }
                </select>
            </div>
        </fieldset>
        }
    </>)
}

registerPaymentMethod( {
    name: settings.name || "",
    label: <Label />,
    content: <DuitNowOBWPaymentMethod />,
    edit: <DuitNowOBWPaymentMethod />,
    canMakePayment: () => settings.is_active,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    }
} )
