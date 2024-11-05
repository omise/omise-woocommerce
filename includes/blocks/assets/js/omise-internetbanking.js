import {useEffect, useState} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'omise_internetbanking_data', {} )
const label = decodeEntities( settings.title ) || 'No title set'
const Label = ( props ) => {
    const { PaymentMethodLabel } = props.components
    return <PaymentMethodLabel text={ label } />
}

const InternetBankingPaymentMethod = (props) => {
    const {eventRegistration, emitResponse} = props;
    const {onPaymentSetup} = eventRegistration;
    const description = decodeEntities( settings.description || '' )
    const [selectedBank, setSelectedBank] = useState(null);

    const onBankSelected = (e) => {
        setSelectedBank(e.target.value)
    }

    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            if (!selectedBank) {
                return {type: emitResponse.responseTypes.ERROR, message: 'Select a bank'}
            }

            try {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            "omise-offsite": selectedBank,
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
        selectedBank
    ]);

    return (<>
        {description && <p>{description}</p>}
        <fieldset id="omise-form-internetbanking">
            <ul className="omise-banks-list">
                {/* <!-- BAY --> */}
                <li className="item">
                    <input id="internet_banking_bay" type="radio" name="omise-offsite" value="internet_banking_bay" onChange={onBankSelected} />
                    <label htmlFor="internet_banking_bay">
                        <div className="bank-logo bay"></div>
                        <div className="bank-label">
                            <span className="title">{ __( 'Krungsri Bank', 'omise' ) }</span><br/>
                            <span className="omise-secondary-text">{ __( 'Fee: 15 THB (same zone), 15 THB (out zone)', 'omise' ) }</span>
                        </div>
                    </label>
                </li>

                {/* <!-- BBL --> */}
                <li className="item">
                    <input id="internet_banking_bbl" type="radio" name="omise-offsite" value="internet_banking_bbl" onChange={onBankSelected} />
                    <label htmlFor="internet_banking_bbl">
                        <div className="bank-logo bbl"></div>
                        <div className="bank-label">
                            <span className="title">{ __( 'Bangkok Bank', 'omise' ) }</span><br/>
                            <span className="omise-secondary-text">{ __( 'Fee: 10 THB (same zone), 20 THB (out zone)', 'omise' ) }</span>
                        </div>
                    </label>
                </li>
            </ul>
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
