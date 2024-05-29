import {useEffect, useState, useRef} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'omise_fpx_data', {} )
const label = decodeEntities( settings.title ) || 'No title set'
const Label = ( props ) => {
    const { PaymentMethodLabel } = props.components
    return <PaymentMethodLabel text={ label } />
}

const FpxPaymentMethod = (props) => {
    const {eventRegistration, emitResponse} = props;
    const {onPaymentSetup} = eventRegistration;
    const description = decodeEntities( settings.description || '' )
    const { bank_list } = settings.data;
    const noPaymentMethods = __( 'FPX is currently not available.', 'omise' );
    const bankRef = useRef(null);

    const onChange = (e) => {
        bankRef.current = e.target.value
    }

    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            if (!bankRef.current) {
                return {type: emitResponse.responseTypes.ERROR, message: 'Select a bank'}
            }
            try {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: { "bank": bankRef.current }
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
    ]);

    return (<>
        {description && <p>{description}</p>}
        {
            bank_list.length == 0
                ? <p>{noPaymentMethods}</p>
                : (
                    <fieldset id="omise-form-installment">
                        <div className="fpx-select-bank">
                            <label htmlFor="fpx-select-bank">Select Bank</label>			  
                            <select
                                className="fpx-bank-logo default"
                                id="fpx-select-bank"
                                name="source[bank]"
                                defaultValue=""
                                onChange={onChange}
                            >
                                <option value="" disabled={true}>-- Select your option --</option>
                                {bank_list.map(bank => (
                                    <option	
                                        key={bank['code']}
                                        className={bank["code"]}
                                        value={bank["code"]}
                                        disabled={bank['active'] === "1" ? true : false}
                                    >
                                        {bank["name"]}
                                        {(!bank['active']) && " (offline)" }
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="fpx-terms-and-conditions-block">
                            <span>By clicking on the <b>"Place Order"</b> button, you agree to FPX's 
                                <a href="https://www.mepsfpx.com.my/FPXMain/termsAndConditions.jsp" target="_blank">
                                    Terms and Conditions
                                </a>
                            </span>
                        </div>
                    </fieldset>
                )
        }
    </>)
}

registerPaymentMethod( {
    name: settings.name || "",
    label: <Label />,
    content: <FpxPaymentMethod />,
    edit: <FpxPaymentMethod />,
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    }
} )
