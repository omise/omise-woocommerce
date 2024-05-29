import {useEffect, useRef} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'omise_installment_data', {} )
const label = decodeEntities( settings.title ) || 'No title set'
const Label = ( props ) => {
    const { PaymentMethodLabel } = props.components
    return <PaymentMethodLabel text={ label } />
}

const InstallmentPaymentMethod = (props) => {
    const {eventRegistration, emitResponse} = props;
    const {onPaymentSetup} = eventRegistration;
    const description = decodeEntities( settings.description || '' )
    const { installment_backends, is_zero_interest } = settings.data;
    const noPaymentMethods = __( 'Purchase Amount is lower than the monthly minimum payment amount.', 'omise' );
    const installmentRef = useRef(null);
    const termRef = useRef(null);

    const onInstallmentSelected = (e) => {
        installmentRef.current = e.target.value
        termRef.current = null
    }

    const onTermsSelected = (e) => {
        termRef.current = e.target.value
    }

    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            if (!installmentRef.current || !termRef.current) {
                return {type: emitResponse.responseTypes.ERROR, message: 'Select a bank and term'}
            }
            try {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            "source": installmentRef.current,
                            [`${installmentRef.current}_installment_terms`]: termRef.current,
                        }
                    }
                };
            } catch (error) {
                return {type: emitResponse.responseTypes.ERROR, message: error.message}
            }
        });
        return () => unsubscribe();
    }, [ onPaymentSetup ]);

    return (<>
        {description && <p>{description}</p>}
        {
            installment_backends.length == 0
                ? <p>{noPaymentMethods}</p>
                : (
                    <fieldset id="omise-form-installment">
                        <ul className="omise-banks-list">
                        {
                            installment_backends.map((backend, i) => (
                                <li key={backend['_id'] + i} className="item">
                                    <input id={backend['_id']} type="radio" name="source[type]" value={backend['_id']} onChange={onInstallmentSelected} />
                                    <label htmlFor={backend['_id']}>
                                        <div className={`bank-logo ${backend['provider_code']}`}></div>
                                        <div className="bank-label">
                                            <span className="title">{backend['provider_name']}</span><br/>
                                            <select
                                                id={`${backend['_id']}_installment_terms`}
                                                name={`${backend['_id']}_installment_terms`}
                                                className="installment-term-select-box"
                                                onChange={onTermsSelected}
                                            >
                                                <option>Select term</option>
                                                {
                                                    backend['available_plans'].map((installment_plan, i) => (
                                                        <option
                                                            key={`${installment_plan['term_length']}_${installment_plan['monthly_amount']}_${i}`}
                                                            value={installment_plan['term_length']}
                                                        >
                                                            {__(`${installment_plan['term_length']} months`, 'omise')}
                                                            <>&nbsp;</>
                                                            ({__(`${installment_plan['monthly_amount']} / months`, 'omise')})
                                                        </option>
                                                    ))
                                                }
                                            </select>
                                            {
                                                is_zero_interest && <>
                                                    <br />
                                                    <span className="omise-installment-interest-rate">
                                                        {__( `( interest ${backend.interest_rate} )`, 'omise' )}
                                                    </span>
                                                </>
                                            }
                                        </div>
                                    </label>
                                </li>
                            ))
                        }
                        </ul>
                    </fieldset>
                )
        }
    </>)
}

registerPaymentMethod( {
    name: settings.name || "",
    label: <Label />,
    content: <InstallmentPaymentMethod />,
    edit: <InstallmentPaymentMethod />,
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    }
} )
